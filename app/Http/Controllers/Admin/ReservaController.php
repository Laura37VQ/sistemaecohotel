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
    /*
     * 1. LISTADO DE RESERVAS PARA ADMIN/RECEPCIONISTA
     * Permite buscar por cliente, habitación, rango de fechas y estado.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $q      = trim((string)$request->query('q', ''));
        $estado = $request->query('estado', '');
        $desde  = $request->query('desde', '');
        $hasta  = $request->query('hasta', '');

        $reservas = Reserva::with(['usuario', 'habitacion', 'factura'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where('codigo_reserva', 'like', "%$q%")
                      ->orWhereHas('usuario', fn($u) => 
                          $u->where('nombres', 'like', "%$q%")
                            ->orWhere('apellidos', 'like', "%$q%")
                      )
                      ->orWhereHas('habitacion', fn($h) => 
                          $h->where('numero_habitacion', 'like', "%$q%")
                            ->orWhere('tipo', 'like', "%$q%")
                      );
            })
            ->when($estado !== '', fn($q2) => $q2->where('estado', $estado))
            ->when($desde !== '', fn($q2) => $q2->whereDate('fecha_ingreso', '>=', $desde))
            ->when($hasta !== '', fn($q2) => $q2->whereDate('fecha_salida', '<=', $hasta))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

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

    /*
     * 2. FORMULARIO PARA CREAR UNA RESERVA
     * Admin/Recepcionista pueden elegir cliente y habitación disponible.
     */
    public function create()
    {
        $user = Auth::user();

        // Solo mostrar habitaciones disponibles
        $habitaciones = Habitacion::where('estado', 'Disponible')
            ->get()
            ->map(fn($h) => [
                'id' => $h->id,
                'numero_habitacion' => $h->numero_habitacion,
                'tipo' => $h->tipo
            ]);

        // Si es cliente, solo se muestra a sí mismo
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

    /*
     * 3. GUARDAR RESERVA DESDE ADMIN/RECEPCIONISTA
     * Importante: actualiza la habitación a "Ocupada".
     */
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

        // Código único tipo RES-ABC123
        $codigo = 'RES-' . strtoupper(Str::random(6));

        // Se crea la reserva
        $reserva = Reserva::create([
            'usuario_id'     => $request->usuario_id,
            'habitacion_id'  => $request->habitacion_id,
            'codigo_reserva' => $codigo,
            'fecha_ingreso'  => $request->fecha_ingreso,
            'fecha_salida'   => $request->fecha_salida,
            'estado'         => $request->estado,
            'descripcion'    => $request->descripcion,
        ]);

        // Al crear la reserva, la habitación pasa a "Ocupada"
        if ($reserva->habitacion) {
            $reserva->habitacion->update(['estado' => 'Ocupada']);
        }

        // Crear factura base
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

        // Crear el detalle del alojamiento
        $this->generarDetalleAlojamiento($reserva, $factura);

        $ruta = $user->rol->nombre_rol === 'Recepcionista'
            ? 'recepcionista.reservas.index'
            : 'admin.reservas.index';

        return redirect()->route($ruta)->with('success', 'Reserva creada correctamente.');
    }

    /*
     * 4. FORMULARIO EDITAR RESERVA
     */
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

    /*
     * 5. ACTUALIZAR RESERVA Y RECALCULAR FACTURA
     * (Aquí cambiamos el "back()" por un redirect al índice)
     */
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

        $reserva->update($request->only([
            'usuario_id',
            'habitacion_id',
            'fecha_ingreso',
            'fecha_salida',
            'estado',
            'descripcion'
        ]));

        if ($reserva->factura) {
            $this->generarDetalleAlojamiento($reserva, $reserva->factura, recalc: true);
        }

        // 🔁 En lugar de back(), redirigimos al índice según el rol
        $user = Auth::user();
        $ruta = $user->rol->nombre_rol === 'Recepcionista'
            ? 'recepcionista.reservas.index'
            : 'admin.reservas.index';

        return redirect()->route($ruta)->with('success', 'Reserva actualizada correctamente.');
    }

    /*
     * 6. ACTUALIZAR SOLO EL ESTADO (Check-in / Check-out)
     */
    public function actualizarEstado(Request $request, Reserva $reserva)
    {
        $request->validate([
            'estado' => 'required|in:Pendiente,Confirmada,Cancelada,Completada',
        ]);

        $estadoNuevo = $request->estado;
        $estadoActual = $reserva->estado;

        // Reglas de estados válidos
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

        // Actualizar habitación según estado
        if ($estadoNuevo === 'Confirmada') {
            $reserva->habitacion()->update(['estado' => 'Ocupada']);
        } elseif (in_array($estadoNuevo, ['Completada', 'Cancelada'])) {
            $reserva->habitacion()->update(['estado' => 'Disponible']);
        }

        return back()->with('success', "Estado actualizado a: {$estadoNuevo}");
    }

    /*
     * 7. "CANCELAR" RESERVA DESDE EL BOTÓN ROJO
     * Usamos destroy, pero en vez de borrar, marcamos como Cancelada
     * y liberamos la habitación.
     */
    public function destroy(Reserva $reserva)
    {
        // Cambiar estado de la reserva
        $reserva->update(['estado' => 'Cancelada']);

        // Liberar habitación
        if ($reserva->habitacion) {
            $reserva->habitacion->update(['estado' => 'Disponible']);
        }

        return back()->with('success', 'Reserva cancelada correctamente.');
    }

    /*
     * 8. CREAR O RECALCULAR EL DETALLE DE ALOJAMIENTO
     */
    private function generarDetalleAlojamiento(Reserva $reserva, Factura $factura, bool $recalc = false)
    {
        if ($recalc) {
            DetalleFactura::where('factura_id', $factura->id)
                ->whereNull('servicio_id')
                ->delete();
        }

        $ingreso = new \DateTime($reserva->fecha_ingreso);
        $salida  = new \DateTime($reserva->fecha_salida);
        $noches  = $ingreso->diff($salida)->days;

        $habitacion   = Habitacion::find($reserva->habitacion_id);
        $precioNoche  = $habitacion->precio;

        $subtotal = $precioNoche * $noches;
        $impuesto = round($subtotal * 0.19, 2);
        $total    = $subtotal + $impuesto;

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

        $factura->update([
            'subtotal' => $factura->detalles()->sum('subtotal'),
            'impuestos'=> $factura->detalles()->sum('impuesto'),
            'total'    => $factura->detalles()->sum('total'),
        ]);
    }
}
