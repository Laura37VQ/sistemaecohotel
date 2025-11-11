<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Models\Factura;
use App\Models\User;
use App\Models\Habitacion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReporteController extends Controller
{
    /**  Página principal de reportes */
    public function index()
    {
        return Inertia::render('Admin/Reportes/Index');
    }

    /**  Reporte de Ocupación de Habitaciones */
    public function ocupacion(Request $request)
    {
        //  Si no selecciona fechas, se muestran todas las reservas
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        $reservas = Reserva::with(['habitacion', 'usuario'])
            ->when($fechaInicio && $fechaFin, function ($q) use ($fechaInicio, $fechaFin) {
                $q->whereBetween('fecha_ingreso', [$fechaInicio, $fechaFin]);
            })
            ->orderByDesc('fecha_ingreso')
            ->get();

        $totalHabitaciones = Habitacion::count();
        $ocupadas = $reservas->count();
        $tasaOcupacion = $totalHabitaciones > 0
            ? round(($ocupadas / $totalHabitaciones) * 100, 2)
            : 0;

        return Inertia::render('Admin/Reportes/Ocupacion', [
            'reservas' => $reservas,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'tasaOcupacion' => $tasaOcupacion,
        ]);
    }

    /**  Reporte de Ingresos (Facturación) */
    public function ingresos(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        $facturas = Factura::with('cliente')
            ->when($fechaInicio && $fechaFin, function ($q) use ($fechaInicio, $fechaFin) {
                $q->whereBetween('fecha_emision', [$fechaInicio, $fechaFin]);
            })
            ->where('estado', '!=', 'Anulada')
            ->orderByDesc('fecha_emision')
            ->get();

        $totalFacturas = $facturas->count();
        $totalIngresos = $facturas->sum('total');
        $promedioFactura = $totalFacturas > 0 ? round($totalIngresos / $totalFacturas, 2) : 0;

        return Inertia::render('Admin/Reportes/Ingresos', [
            'facturas' => $facturas,
            'totalIngresos' => $totalIngresos,
            'promedioFactura' => $promedioFactura,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
        ]);
    }

    /**  Reporte de Clientes */
    public function clientes()
    {
        $clientes = User::with(['reservas.habitacion'])
            ->where('rol_id', 2)
            ->orderBy('nombres')
            ->get();

        $totalClientes = $clientes->count();
        $clientesConReservas = $clientes->filter(fn($c) => $c->reservas->count() > 0)->count();

        return Inertia::render('Admin/Reportes/Clientes', [
            'clientes' => $clientes,
            'totalClientes' => $totalClientes,
            'clientesConReservas' => $clientesConReservas,
        ]);
    }
}
