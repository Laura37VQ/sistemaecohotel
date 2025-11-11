<?php

namespace App\Http\Controllers\Recepcionista;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use Inertia\Inertia;

class RecepcionistaController extends Controller
{
    // Dashboard recepcionista
    public function index()
    {
        return Inertia::render('Recepcionista/Dashboard');
    }


    public function reservas()
    {
        return redirect()->route('recepcionista.reservas.index');
    }


    public function facturas()
    {
        return Inertia::render('Recepcionista/Facturas/Index');
    }


    public function checkinCheckout()
    {
        $hoy = now()->toDateString();
        
        // Llegadas (Pendientes con fecha_ingreso hoy o anterior)
        $llegadas = Reserva::with([
                'usuario:id,nombres,apellidos',
                'habitacion:id,numero_habitacion,tipo',
                'factura:id,reserva_id'
            ])
            ->where('estado', 'Pendiente')
            ->whereDate('fecha_ingreso', '<=', $hoy)
            ->orderBy('fecha_ingreso')
            ->get(['id','codigo_reserva','usuario_id','habitacion_id','fecha_ingreso','fecha_salida','estado']);
            
        // En casa (Confirmadas vigentes)
        $enCasa = Reserva::with([
                'usuario:id,nombres,apellidos',
                'habitacion:id,numero_habitacion,tipo',
                'factura:id,reserva_id'
            ])
            ->where('estado', 'Confirmada')
            ->whereDate('fecha_ingreso', '<=', $hoy)
            ->whereDate('fecha_salida', '>=', $hoy)
            ->orderBy('fecha_salida')
            ->get(['id','codigo_reserva','usuario_id','habitacion_id','fecha_ingreso','fecha_salida','estado']);
            
        // Salidas (Confirmadas que ya deben salir hoy o antes)
        $salidas = Reserva::with([
                'usuario:id,nombres,apellidos',
                'habitacion:id,numero_habitacion,tipo',
                'factura:id,reserva_id'
            ])
            ->where('estado', 'Confirmada')
            ->whereDate('fecha_salida', '<=', $hoy)
            ->orderBy('fecha_salida')
            ->get(['id','codigo_reserva','usuario_id','habitacion_id','fecha_ingreso','fecha_salida','estado']);
            
        return Inertia::render('Recepcionista/CheckinCheckout/Index', [
            'hoy'      => $hoy,
            'llegadas' => $llegadas,
            'enCasa'   => $enCasa,
            'salidas'  => $salidas,
        ]);
    }

}
