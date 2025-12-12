<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Models\Factura;
use App\Models\User;
use App\Models\Habitacion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteController extends Controller
{
    /** Página principal donde se listan los tipos de reportes */
    public function index()
    {
        return Inertia::render('Admin/Reportes/Index');
    }

    /* ============================================================
       REPORTE DE OCUPACIÓN (VERSIÓN WEB)
       Muestra las habitaciones que han tenido reservas en un
       rango de fechas, junto con la tasa de ocupación.
       ============================================================ */
    public function ocupacion(Request $request)
    {
        $fechaInicio = $request->fecha_inicio;
        $fechaFin    = $request->fecha_fin;

        // Se cargan reservas junto con habitación y usuario,
        // incluyendo los registros desactivados (soft delete).
        $reservas = Reserva::with([
                'habitacion' => fn($q) => $q->withTrashed(),
                'usuario'    => fn($q) => $q->withTrashed(),
            ])
            ->when($fechaInicio && $fechaFin, function ($q) use ($fechaInicio, $fechaFin) {

                // Si las fechas vienen invertidas, no aplicar filtro.
                if ($fechaInicio > $fechaFin) {
                    return;
                }

                // Reservas que se cruzan con el intervalo solicitado.
                $q->where(function ($query) use ($fechaInicio, $fechaFin) {
                    $query->whereBetween('fecha_ingreso', [$fechaInicio, $fechaFin])
                          ->orWhereBetween('fecha_salida', [$fechaInicio, $fechaFin])
                          ->orWhere(function ($q2) use ($fechaInicio, $fechaFin) {
                              // Caso en el que la reserva cubre todo el intervalo
                              $q2->where('fecha_ingreso', '<=', $fechaInicio)
                                 ->where('fecha_salida', '>=', $fechaFin);
                          });
                });
            })
            ->orderByDesc('fecha_ingreso')
            ->get();

        // Habitaciones registradas (activas o no)
        $totalHabitaciones = Habitacion::count();

        // Habitaciones que estuvieron ocupadas al menos una vez
        $habitacionesOcupadas = $reservas->pluck('habitacion_id')->unique()->count();

        // Cálculo de ocupación (%) con 2 decimales
        $tasaOcupacion = $totalHabitaciones > 0
            ? round(($habitacionesOcupadas / $totalHabitaciones) * 100, 2)
            : 0;

        return Inertia::render('Admin/Reportes/Ocupacion', [
            'reservas'          => $reservas,
            'fechaInicio'       => $fechaInicio,
            'fechaFin'          => $fechaFin,
            'totalHabitaciones' => $totalHabitaciones,
            'tasaOcupacion'     => $tasaOcupacion,
        ]);
    }

    /* ============================================================
       REPORTE DE INGRESOS (WEB)
       Lista las facturas emitidas en un rango de fechas y muestra
       totales, promedios y valores acumulados.
       ============================================================ */
    public function ingresos(Request $request)
    {
        $fechaInicio = $request->fecha_inicio;
        $fechaFin    = $request->fecha_fin;

        $facturas = Factura::with('cliente')
            ->when($fechaInicio && $fechaFin, function ($q) use ($fechaInicio, $fechaFin) {
                $q->whereBetween('fecha_emision', [$fechaInicio, $fechaFin]);
            })
            ->where('estado', '!=', 'Anulada')
            ->orderByDesc('fecha_emision')
            ->get();

        return Inertia::render('Admin/Reportes/Ingresos', [
            'facturas'        => $facturas,
            'totalIngresos'   => $facturas->sum('total'),
            'promedioFactura' => $facturas->count() > 0
                ? round($facturas->sum('total') / $facturas->count(), 2)
                : 0,
            'fechaInicio'     => $fechaInicio,
            'fechaFin'        => $fechaFin,
        ]);
    }

    /* ============================================================
       PDF DE INGRESOS
       ============================================================ */
    public function pdfIngresos(Request $request)
    {
        $fechaInicio = $request->fecha_inicio;
        $fechaFin    = $request->fecha_fin;

        $facturas = Factura::with('cliente')
            ->when($fechaInicio && $fechaFin, fn($q) =>
                $q->whereBetween('fecha_emision', [$fechaInicio, $fechaFin])
            )
            ->where('estado', '!=', 'Anulada')
            ->orderByDesc('fecha_emision')
            ->get();

        $pdf = Pdf::loadView('pdf.ingresos', [
            'facturas'      => $facturas,
            'fechaInicio'   => $fechaInicio,
            'fechaFin'      => $fechaFin,
            'totalIngresos' => $facturas->sum('total'),
        ]);

        return $pdf->download('Reporte_Ingresos.pdf');
    }

    /* ============================================================
       REPORTE DE CLIENTES (WEB)
       ============================================================ */
    public function clientes()
    {
        $clientes = User::with(['reservas.habitacion' => fn($q) => $q->withTrashed()])
            ->where('rol_id', 2) // Rol cliente
            ->orderBy('nombres')
            ->get();

        return Inertia::render('Admin/Reportes/Clientes', [
            'clientes'            => $clientes,
            'totalClientes'       => $clientes->count(),
            'clientesConReservas' => $clientes->filter(
                fn($c) => $c->reservas->count() > 0
            )->count(),
        ]);
    }

    /* ============================================================
       PDF DE CLIENTES
       ============================================================ */
    public function pdfClientes()
    {
        $clientes = User::with(['reservas' => fn($q) => $q->withTrashed()])
            ->where('rol_id', 2)
            ->orderBy('nombres')
            ->get();

        $pdf = Pdf::loadView('pdf.clientes', [
            'clientes'            => $clientes,
            'totalClientes'       => $clientes->count(),
            'clientesConReservas' => $clientes->filter(
                fn($c) => $c->reservas->count() > 0
            )->count(),
        ]);

        return $pdf->download('Reporte_Clientes.pdf');
    }

    /* ============================================================
       PDF REPORTE DE OCUPACIÓN
       (Incluye habitaciones desactivadas sin romper el proceso)
       ============================================================ */
    public function pdfOcupacion(Request $request)
    {
        $fechaInicio = $request->fecha_inicio;
        $fechaFin    = $request->fecha_fin;

        $reservas = Reserva::with([
                'habitacion' => fn($q) => $q->withTrashed(),
                'usuario'    => fn($q) => $q->withTrashed(),
            ])
            ->when($fechaInicio && $fechaFin, function ($q) use ($fechaInicio, $fechaFin) {

                if ($fechaInicio > $fechaFin) {
                    return;
                }

                $q->where(function ($query) use ($fechaInicio, $fechaFin) {
                    $query->whereBetween('fecha_ingreso', [$fechaInicio, $fechaFin])
                          ->orWhereBetween('fecha_salida', [$fechaInicio, $fechaFin])
                          ->orWhere(function ($q2) use ($fechaInicio, $fechaFin) {
                              $q2->where('fecha_ingreso', '<=', $fechaInicio)
                                 ->where('fecha_salida', '>=', $fechaFin);
                          });
                });
            })
            ->orderByDesc('fecha_ingreso')
            ->get();

        $totalHabitaciones = Habitacion::count();
        $habitacionesOcupadas = $reservas->pluck('habitacion_id')->unique()->count();

        $tasaOcupacion = $totalHabitaciones > 0
            ? round(($habitacionesOcupadas / $totalHabitaciones) * 100, 2)
            : 0;

        $pdf = Pdf::loadView('pdf.ocupacion', [
            'reservas'          => $reservas,
            'fechaInicio'       => $fechaInicio,
            'fechaFin'          => $fechaFin,
            'totalHabitaciones' => $totalHabitaciones,
            'tasaOcupacion'     => $tasaOcupacion,
        ]);

        return $pdf->download('Reporte_Ocupacion.pdf');
    }
}
