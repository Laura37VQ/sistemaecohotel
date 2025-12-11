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
    /** Página principal de los reportes */
    public function index()
    {
        return Inertia::render('Admin/Reportes/Index');
    }

    /** ---------------------------------------------------------
     *  REPORTE DE OCUPACIÓN (WEB)
     * ---------------------------------------------------------*/
    public function ocupacion(Request $request)
    {
        $fechaInicio = $request->fecha_inicio;
        $fechaFin    = $request->fecha_fin;

        $reservas = Reserva::with(['habitacion', 'usuario'])
            ->when($fechaInicio && $fechaFin, function ($q) use ($fechaInicio, $fechaFin) {

                // Validación lógica: no filtrar si la fecha inicial es mayor
                if ($fechaInicio > $fechaFin) {
                    return;
                }

                // Reservas que se cruzan con el rango seleccionado
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

        // Total de habitaciones registradas en el sistema
        $totalHabitaciones = Habitacion::count();

        // Habitaciones diferentes que estuvieron ocupadas
        $habitacionesOcupadas = $reservas->pluck('habitacion_id')->unique()->count();

        // Cálculo de la tasa de ocupación sin exceder 100%
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

    /** ---------------------------------------------------------
     *  REPORTE DE INGRESOS (WEB)
     * ---------------------------------------------------------*/
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

    /** ---------------------------------------------------------
     *  PDF DE INGRESOS
     * ---------------------------------------------------------*/
    public function pdfIngresos(Request $request)
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

        $pdf = Pdf::loadView('pdf.ingresos', [
            'facturas'      => $facturas,
            'fechaInicio'   => $fechaInicio,
            'fechaFin'      => $fechaFin,
            'totalIngresos' => $facturas->sum('total'),
        ]);

        return $pdf->download('Reporte_Ingresos.pdf');
    }

    /** ---------------------------------------------------------
     *  REPORTE DE CLIENTES (WEB)
     * ---------------------------------------------------------*/
    public function clientes()
    {
        $clientes = User::with(['reservas.habitacion'])
            ->where('rol_id', 2)
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

    /** ---------------------------------------------------------
     *  PDF DE CLIENTES (NUEVO)
     * ---------------------------------------------------------*/
    public function pdfClientes()
    {
        $clientes = User::with(['reservas'])
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

    /** ---------------------------------------------------------
     *  PDF DE OCUPACIÓN (NUEVO)
     * ---------------------------------------------------------*/
    public function pdfOcupacion(Request $request)
    {
        $fechaInicio = $request->fecha_inicio;
        $fechaFin    = $request->fecha_fin;

        // Se obtienen las mismas reservas que se muestran en el reporte web
        $reservas = Reserva::with(['habitacion', 'usuario'])
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

        // Generar PDF
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
