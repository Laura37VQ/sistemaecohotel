<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Models\Habitacion;
use App\Models\User;
use App\Models\Factura;
use App\Models\DetalleFactura;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ReservaController extends Controller
{
    /* ==========================================================================
       1. LISTADO DE RESERVAS CON FILTROS AVANZADOS
       ========================================================================== */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Filtros recibidos desde Vue
        $q      = trim((string)$request->query('q', ''));
        $estado = $request->query('estado', '');
        $desde  = $request->query('desde', '');
        $hasta  = $request->query('hasta', '');

        // Construcción dinámica de filtros
        $reservas = Reserva::with(['usuario', 'habitacion', 'factura'])

            // Búsqueda general: cliente / habitación / código
            ->when($q !== '', function ($query) use ($q) {
                $query->where('codigo_reserva', 'like', "%$q%")
                    ->orWhereHas('usuario', function ($u) use ($q) {
                        $u->where('nombres', 'like', "%$q%")
                          ->orWhere('apellidos', 'like', "%$q%");
                    })
                    ->orWhereHas('habitacion', function ($h) use ($q) {
                        $h->where('numero_habitacion', 'like', "%$q%")
                          ->orWhere('tipo', 'like', "%$q%");
                    });
            })

            // Filtrar por estado
            ->when($estado !== '', fn($q2) => $q2->where('estado', $estado))

            // Filtrar por fecha ingreso
            ->when($desde !== '', fn($q2) => $q2->whereDate('fecha_ingreso', '>=', $desde))

            // Filtrar por fecha salida
            ->when($hasta !== '', fn($q2) => $q2->whereDate('fecha_salida', '<=', $hasta))

            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        // Seleccionar vista según el rol en sesión
        $vista = $user->rol->nombre_rol === 'Recepcionista'
            ? 'Recepcionista/Reservas/Index'
            : 'Admin/Reservas/Index';

        return Inertia::render($vista, [
            'reservas' => $reservas,
            'filtros'  => [
                'q'      => $q,
                'estado' => $estado,
                'desde'  => $desde,
                'hasta'  => $hasta,
            ]
        ]);
    }

    /* ==========================================================================
       2. FORMULARIO CREAR RESERVA
       ========================================================================== */
    public function create()
    {
        $user = Auth::user();

        // Habitaciones disponibles
        $habitaciones = Habitacion::where('estado', 'Disponible')
            ->get()
            ->map(fn($h) => [
                'id' => $h->id,
                'numero_habitacion' => $h->numero_habitacion,
                'tipo' => $h->tipo
            ]);

        // Si es cliente, solo se muestra él mismo
        $usuarios = ($user->rol_id == 2)
            ? collect([$user])->map(fn($u) => [
                'id' => $u->id,
                'nombres' => $u->nombres,
                'apellidos' => $u->apellidos
            ])
            : User::where('rol_id', 2)->get()->map(fn($u) => [
                'id' => $u->id,
                'nombres' => $u->nombres,
                'apellidos' => $u->apellidos
            ]);

        // Vista según rol
        $vista = $user->rol->nombre_rol === 'Recepcionista'
            ? 'Recepcionista/Reservas/ReservaForm'
            : 'Admin/Reservas/ReservaForm';

        return Inertia::render($vista, [
            'usuarios'     => $usuarios,
            'habitaciones' => $habitaciones,
            'reserva'      => null,
            'rol'          => (int)$user->rol_id
        ]);
    }

    /* ==========================================================================
       3. GUARDAR RESERVA (CREA FACTURA Y DETALLE)
       ========================================================================== */
    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'usuario_id'     => 'required|exists:usuarios,id',
            'habitacion_id'  => 'required|exists:habitaciones,id',
            'fecha_ingreso'  => 'required|date',
            'fecha_salida'   => 'required|date|after:fecha_ingreso',
            'estado'         => 'required|in:Pendiente,Confirmada,Cancelada,Completada',
            'descripcion'    => 'nullable|string',
        ]);

        // Código único RES-ABC123
        $codigo = 'RES-' . strtoupper(Str::random(6));

        // Crear reserva
        $reserva = Reserva::create([
            'usuario_id'     => $request->usuario_id,
            'habitacion_id'  => $request->habitacion_id,
            'codigo_reserva' => $codigo,
            'fecha_ingreso'  => $request->fecha_ingreso,
            'fecha_salida'   => $request->fecha_salida,
            'estado'         => $request->estado,
            'descripcion'    => $request->descripcion,
        ]);

        // Crear factura
        $ultimo = Factura::max('numero_factura') ?? 1000;

        $factura = Factura::create([
            'numero_factura' => $ultimo + 1,
            'prefijo'        => 'FV',
            'reserva_id'     => $reserva->id,
            'cliente_id'     => $reserva->usuario_id,
            'fecha_emision'  => now()->toDateString(),
            'metodo_pago'    => 'Pendiente',
            'subtotal'       => 0,
            'impuestos'      => 0,
            'total'          => 0,
            'estado'         => 'Pendiente',
            'datos_facturacion' => '{}',
            'datos_empresa'     => '{}',
        ]);

        // Generar línea de alojamiento
        $this->generarDetalleAlojamiento($reserva, $factura);

        $ruta = $user->rol->nombre_rol === 'Recepcionista'
            ? 'recepcionista.reservas.index'
            : 'admin.reservas.index';

        return redirect()->route($ruta)->with('success', 'Reserva creada correctamente.');
    }

    /* ==========================================================================
       4. FORMULARIO EDITAR RESERVA
       ========================================================================== */
    public function edit($id)
    {
        $reserva = Reserva::with(['habitacion'])->findOrFail($id);
        $user = Auth::user();

        $usuarios = User::where('rol_id', 2)->get()->map(fn($u) => [
            'id' => $u->id,
            'nombres' => $u->nombres,
            'apellidos' => $u->apellidos
        ]);

        $habitaciones = Habitacion::all()->map(fn($h) => [
            'id' => $h->id,
            'numero_habitacion' => $h->numero_habitacion,
            'tipo' => $h->tipo
        ]);

        $vista = $user->rol->nombre_rol === 'Recepcionista'
            ? 'Recepcionista/Reservas/ReservaForm'
            : 'Admin/Reservas/ReservaForm';

        return Inertia::render($vista, compact('reserva', 'usuarios', 'habitaciones'));
    }

    /* ==========================================================================
       5. ACTUALIZAR RESERVA + RECALCULAR ALOJAMIENTO
       ========================================================================== */
    public function update(Request $request, Reserva $reserva)
    {
        $request->validate([
            'usuario_id'     => 'required|exists:usuarios,id',
            'habitacion_id'  => 'required|exists:habitaciones,id',
            'fecha_ingreso'  => 'required|date',
            'fecha_salida'   => 'required|date|after:fecha_ingreso',
            'estado'         => 'required|in:Pendiente,Confirmada,Cancelada,Completada',
            'descripcion'    => 'nullable|string',
        ]);

        // Actualizar reserva
        $reserva->update($request->only([
            'usuario_id',
            'habitacion_id',
            'fecha_ingreso',
            'fecha_salida',
            'estado',
            'descripcion'
        ]));

        // Recalcular factura si existe
        if ($reserva->factura) {
            $this->generarDetalleAlojamiento($reserva, $reserva->factura, recalc: true);
        }

        return back()->with('success', 'Reserva actualizada correctamente.');
    }

    /* ==========================================================================
       6. ACTUALIZAR SOLO EL ESTADO DE LA RESERVA
       ========================================================================== */
    public function actualizarEstado(Request $request, Reserva $reserva)
    {
        $request->validate([
            'estado' => 'required|in:Pendiente,Confirmada,Cancelada,Completada',
        ]);

        $estadoNuevo = $request->estado;
        $estadoActual = $reserva->estado;

        // Validación de transición entre estados
        $transicionValida =
            ($estadoActual === 'Pendiente' && $estadoNuevo === 'Confirmada') ||
            ($estadoActual === 'Confirmada' && $estadoNuevo === 'Completada') ||
            ($estadoNuevo === 'Cancelada') ||
            ($estadoActual === 'Pendiente' && $estadoNuevo === 'Pendiente');

        if (!$transicionValida) {
            return back()->withErrors(['estado' => "Transición inválida de $estadoActual a $estadoNuevo"]);
        }

        $reserva->estado = $estadoNuevo;
        $reserva->save();

        // Actualizar estado de habitación
        if ($estadoNuevo === 'Confirmada') {
            $reserva->habitacion()->update(['estado' => 'Ocupada']);
        } elseif (in_array($estadoNuevo, ['Completada', 'Cancelada'])) {
            $reserva->habitacion()->update(['estado' => 'Disponible']);
        }

        return back()->with('success', "Estado actualizado a: {$estadoNuevo}");
    }

    /* ==========================================================================
       7. ELIMINAR RESERVA
       ========================================================================== */
    public function destroy(Reserva $reserva)
    {
        $reserva->delete();
        return back()->with('success', 'Reserva eliminada correctamente.');
    }

    /* ==========================================================================
       8. GENERAR / RECALCULAR DETALLE DE ALOJAMIENTO
       ========================================================================== */
    private function generarDetalleAlojamiento(Reserva $reserva, Factura $factura, bool $recalc = false)
    {
        if ($recalc) {
            // Eliminar detalle anterior de alojamiento
            DetalleFactura::where('factura_id', $factura->id)
                ->whereNull('servicio_id')
                ->delete();
        }

        // Calcular noches
        $ingreso = new \DateTime($reserva->fecha_ingreso);
        $salida  = new \DateTime($reserva->fecha_salida);
        $noches  = $ingreso->diff($salida)->days;

        $habitacion   = Habitacion::find($reserva->habitacion_id);
        $precioNoche  = $habitacion->precio;

        $subtotal = $precioNoche * $noches;
        $impuesto = round($subtotal * 0.19, 2);
        $total    = $subtotal + $impuesto;

        // Crear línea de detalle
        DetalleFactura::create([
            'factura_id'      => $factura->id,
            'servicio_id'     => null,
            'descripcion'     => "Alojamiento en habitación {$habitacion->tipo}",
            'cantidad'        => $noches,
            'precio_unitario' => $precioNoche,
            'subtotal'        => $subtotal,
            'impuesto'        => $impuesto,
            'total'           => $total,
        ]);

        // Actualizar totales de factura
        $factura->update([
            'subtotal' => $factura->detalles()->sum('subtotal'),
            'impuestos'=> $factura->detalles()->sum('impuesto'),
            'total'    => $factura->detalles()->sum('total'),
        ]);
    }
}
