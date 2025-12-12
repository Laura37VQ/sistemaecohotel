<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Habitacion;
use App\Models\Reserva;
use App\Models\Servicio;
use App\Models\Factura;
use App\Models\DetalleFactura;
use App\Models\InformacionHotel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ClienteController extends Controller
{
    //  Dashboard del cliente
    public function index()
    {
        $user = Auth::user();
        $reservas = Reserva::where('usuario_id', $user->id)
            ->with('habitacion')
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Cliente/Dashboard', [
            'user' => $user,
            'reservas' => $reservas
        ]);
    }

    //  Formulario de búsqueda de habitaciones
    public function disponibilidad()
    {
        return Inertia::render('Cliente/Disponibilidad', [
            'habitaciones' => [],
            'form' => ['fecha_ingreso' => '', 'fecha_salida' => '']
        ]);
    }

    //  Buscar habitaciones disponibles
    public function buscarDisponibilidad(Request $request)
    {
        $request->validate([
            'fecha_ingreso' => 'required|date',
            'fecha_salida' => 'required|date|after:fecha_ingreso',
        ]);

        $habitaciones = Habitacion::where('estado', 'Disponible')->get();

        return Inertia::render('Cliente/Disponibilidad', [
            'habitaciones' => $habitaciones,
            'form' => [
                'fecha_ingreso' => $request->fecha_ingreso,
                'fecha_salida' => $request->fecha_salida
            ]
        ]);
    }

    //  Formulario de reserva
    public function reservar(Habitacion $habitacion)
    {
        return Inertia::render('Cliente/ReservaForm', [
            'habitacion' => $habitacion
        ]);
    }

    //  Guardar reserva
    public function storeReserva(Request $request)
    {
        $request->validate([
            'habitacion_id' => 'required|exists:habitaciones,id',
            'fecha_ingreso' => 'required|date',
            'fecha_salida' => 'required|date|after:fecha_ingreso',
            'descripcion' => 'nullable|string'
        ]);

        $codigo = 'RES-' . Str::upper(Str::random(6));

        $reserva = Reserva::create([
            'usuario_id' => Auth::id(),
            'habitacion_id' => $request->habitacion_id,
            'codigo_reserva' => $codigo,
            'fecha_ingreso' => $request->fecha_ingreso,
            'fecha_salida' => $request->fecha_salida,
            'estado' => 'Confirmada',
            'descripcion' => $request->descripcion,
        ]);

        $reserva->habitacion->update(['estado' => 'Ocupada']);

        return redirect()->route('cliente.servicios', $reserva->id)
            ->with('success', 'Reserva creada correctamente.');
    }

    //  Servicios disponibles
    public function servicios()
    {
        $reserva = Reserva::where('usuario_id', Auth::id())
            ->where('estado', 'Confirmada')
            ->orderByDesc('created_at')
            ->first();
            
        //  Si no hay reserva válida, mostrar mensaje informativo
        if (!$reserva) {
            return Inertia::render('Cliente/Servicios', [
                'reserva' => null,
                'servicios' => [],
                'error' => 'No se encontró una reserva válida. Por favor crea una antes de añadir servicios.'
            ]);
        }
        
        //  Si hay reserva válida, mostrar los servicios activos
        $servicios = Servicio::where('estado', 'Activo')->get();
        
        return Inertia::render('Cliente/Servicios', [
            'reserva' => $reserva,
            'servicios' => $servicios,
            'error' => null
        ]);
    }


    //  Guardar servicios y generar o actualizar factura
    public function storeServicios(Request $request, Reserva $reserva)
    {
        DB::transaction(function () use ($request, $reserva) {
            $items = $request->input('items', []);
            $detalles = [];

            $fechaIngreso = new \DateTime($reserva->fecha_ingreso);
            $fechaSalida = new \DateTime($reserva->fecha_salida);
            $noches = max($fechaIngreso->diff($fechaSalida)->days, 1);

            $precioHabitacion = $reserva->habitacion->precio;
            $subtotal = $precioHabitacion * $noches;

            foreach ($items as $item) {
                $servicio = Servicio::find($item['servicio_id']);
                if ($servicio) {
                    $cantidad = $item['cantidad'] ?? 1;
                    $totalServicio = $cantidad * $servicio->precio;
                    $subtotal += $totalServicio;

                    $detalles[] = [
                        'descripcion' => $servicio->nombre,
                        'cantidad' => $cantidad,
                        'precio_unitario' => $servicio->precio,
                        'total' => $totalServicio,
                    ];
                }
            }

            $impuesto = $subtotal * 0.19;
            $totalFinal = $subtotal + $impuesto;

            //  Buscar si ya existe una factura para esta reserva
            $factura = Factura::where('reserva_id', $reserva->id)->first();

            if ($factura) {
                //  Actualizar totales si ya existe
                $factura->update([
                    'subtotal' => $factura->subtotal + $subtotal,
                    'impuestos' => $factura->impuestos + $impuesto,
                    'total' => $factura->total + $totalFinal,
                    'fecha_emision' => now(),
                ]);
            } else {
                //  Crear nueva factura si no existe
                $factura = Factura::create([
                    'numero_factura' => rand(1000, 9999),
                    'prefijo' => 'FV',
                    'reserva_id' => $reserva->id,
                    'cliente_id' => $reserva->usuario_id,
                    'fecha_emision' => now(),
                    'metodo_pago' => 'Pendiente',
                    'subtotal' => $subtotal,
                    'impuestos' => $impuesto,
                    'total' => $totalFinal,
                    'datos_facturacion' => '{}',
                    'datos_empresa' => '{}',
                    'estado' => 'Pendiente',
                ]);
            }

            //  Registrar detalle del alojamiento
            DetalleFactura::create([
                'factura_id' => $factura->id,
                'cantidad' => $noches,
                'descripcion' => 'Alojamiento en habitación ' . $reserva->habitacion->tipo,
                'precio_unitario' => $precioHabitacion,
                'subtotal' => $precioHabitacion * $noches,
                'impuesto' => ($precioHabitacion * $noches) * 0.19,
                'total' => ($precioHabitacion * $noches) * 1.19,
            ]);

            //  Registrar detalles de nuevos servicios
            foreach ($detalles as $detalle) {
                DetalleFactura::create([
                    'factura_id' => $factura->id,
                    'cantidad' => $detalle['cantidad'],
                    'descripcion' => $detalle['descripcion'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'subtotal' => $detalle['total'],
                    'impuesto' => $detalle['total'] * 0.19,
                    'total' => $detalle['total'] * 1.19,
                ]);
            }
        });

        return redirect()->route('cliente.facturacion')
            ->with('success', 'Factura actualizada correctamente.');
    }

    //  Mostrar todas las reservas del cliente
    public function misReservas()
    {
        $reservas = Reserva::where('usuario_id', Auth::id())
            ->with('habitacion')
            ->orderByDesc('fecha_ingreso')
            ->get();

        return Inertia::render('Cliente/MisReservas', [
            'reservas' => $reservas
        ]);
    }

    //  Mostrar todas las facturas del cliente
    public function verFacturas()
    {
        $facturas = Factura::where('cliente_id', Auth::id())
            ->with('reserva.habitacion')
            ->orderByDesc('fecha_emision')
            ->get();

        return Inertia::render('Cliente/Facturacion', [
            'facturas' => $facturas
        ]);
    }

    //  Descargar PDF desde la lista
    public function descargarFactura(Factura $factura)
    {
        if ($factura->cliente_id !== Auth::id()) {
            abort(403, 'No tienes permiso para acceder a esta factura.');
        }

        $hotel = InformacionHotel::first();
        $cliente = Auth::user();
        $detalles = DetalleFactura::where('factura_id', $factura->id)->get();

        $pdf = Pdf::loadView('factura.pdf', compact('factura', 'hotel', 'cliente', 'detalles'))
            ->setPaper('A4', 'portrait');

        return $pdf->download('Factura_' . $factura->numero_factura . '.pdf');
    }

    //  Editar reserva
    public function editarReserva(Reserva $reserva)
    {
        if ($reserva->usuario_id !== Auth::id()) {
            abort(403, 'No tienes permiso para editar esta reserva.');
        }

        return Inertia::render('Cliente/EditarReserva', [
            'reserva' => $reserva->load('habitacion')
        ]);
    }

    //  Actualizar reserva (solo fechas y descripción)
    public function actualizarReserva(Request $request, Reserva $reserva)
    {
        if ($reserva->usuario_id !== Auth::id()) {
            abort(403, 'No tienes permiso para modificar esta reserva.');
        }

        $request->validate([
            'fecha_ingreso' => 'required|date',
            'fecha_salida' => 'required|date|after:fecha_ingreso',
            'descripcion' => 'nullable|string'
        ]);

        $reserva->update($request->only([
            'fecha_ingreso',
            'fecha_salida',
            'descripcion'
        ]));

        $this->actualizarFactura($reserva);

        return redirect()->route('cliente.reservas')
            ->with('success', 'Reserva actualizada correctamente.');
    }


    private function actualizarFactura(Reserva $reserva)
    {
        
        // Buscar factura asociada
        $factura = Factura::where('reserva_id', $reserva->id)->first();
        
        if (!$factura) {
            return;
        }
        
        // Calcular nuevas noches
        $fechaIngreso = new \DateTime($reserva->fecha_ingreso);
        $fechaSalida = new \DateTime($reserva->fecha_salida);
        $noches = max($fechaIngreso->diff($fechaSalida)->days, 1);
        
        $precioHabitacion = $reserva->habitacion->precio;
        
        DetalleFactura::where('factura_id', $factura->id)
            ->where('descripcion', 'like', 'Alojamiento%')
            ->delete();
            
        DetalleFactura::create([
            'factura_id' => $factura->id,
            'descripcion' => 'Alojamiento en habitación ' . $reserva->habitacion->tipo,
            'cantidad' => $noches,
            'precio_unitario' => $precioHabitacion,
            'subtotal' => $precioHabitacion * $noches,
            'impuesto' => ($precioHabitacion * $noches) * 0.19,
            'total' => ($precioHabitacion * $noches) * 1.19,
        ]);
        
        $subtotal = DetalleFactura::where('factura_id', $factura->id)->sum('subtotal');
        $impuestos = $subtotal * 0.19;
        $total = $subtotal + $impuestos;
        
        $factura->update([
            'subtotal' => $subtotal,
            'impuestos' => $impuestos,
            'total' => $total,
            'fecha_emision' => now(),
        ]);
    }

    //  Cancelar una reserva y anular su factura asociada
    public function cancelarReserva(Reserva $reserva)
    {
        if ($reserva->usuario_id !== Auth::id()) {
             abort(403, 'No tienes permiso para cancelar esta reserva.');
        }
        
        DB::transaction(function () use ($reserva) {
            
            //  1. Cambiar el estado de la reserva
            $reserva->update(['estado' => 'Cancelada']);

            //  2. Liberar la habitación (si estaba ocupada)
            if ($reserva->habitacion) {
                $reserva->habitacion->update(['estado' => 'Disponible']);
            }
            
            //  3. Anular la factura asociada (si existe)
            $factura = Factura::where('reserva_id', $reserva->id)->first();
            
            if ($factura) {
                $factura->update([
                    'estado' => 'Anulada',
                    'metodo_pago' => 'Pendiente',
                ]);
            }
        });
        
        return redirect()->route('cliente.reservas')
            ->with('success', 'Reserva cancelada correctamente. La factura fue anulada.');
    }

    public function serviciosActivos()
    {
        $reserva = Reserva::where('usuario_id', Auth::id())
            ->where('estado', 'Confirmada')
            ->where('fecha_salida', '>=', now())
            ->orderByDesc('fecha_ingreso')
            ->first();
            
        if (!$reserva) {
            return Inertia::render('Cliente/Servicios', [
                'reserva' => null,
                'servicios' => [],
                'error' => 'No tienes una reserva activa en este momento.'
            ]);
        }
        
        $servicios = Servicio::where('estado', 'Activo')->get();
        
        return Inertia::render('Cliente/Servicios', [
            'reserva' => $reserva,
            'servicios' => $servicios,
            'error' => null
        ]);
    }

}
