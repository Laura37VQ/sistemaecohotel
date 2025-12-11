<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Factura;
use App\Models\User;
use App\Models\Servicio;
use App\Models\Reserva;
use App\Models\DetalleFactura;
use App\Models\InformacionHotel;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class FacturacionController extends Controller
{
    /* ============================================================
       LISTADO CON FILTROS
    ============================================================ */
    public function index(Request $request)
    {
        // Filtros recibidos desde Vue
        $q          = trim($request->query('q', ''));
        $estado     = $request->query('estado', '');
        $metodo     = $request->query('metodo', '');
        $fechaDesde = $request->query('desde', '');
        $fechaHasta = $request->query('hasta', '');

        $facturas = Factura::with('cliente')

            // Buscar por número, prefijo, cliente nombre/apellido
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('numero_factura', 'like', "%$q%")
                        ->orWhere('prefijo', 'like', "%$q%")
                        ->orWhereHas('cliente', function ($q2) use ($q) {
                            $q2->where('nombres', 'like', "%$q%")
                               ->orWhere('apellidos', 'like', "%$q%");
                        });
                });
            })

            // Estado (Pendiente, Pagada, Anulada)
            ->when($estado !== '', fn($q) => $q->where('estado', $estado))

            // Método de pago
            ->when($metodo !== '', fn($q) => $q->where('metodo_pago', $metodo))

            // Fechas
            ->when($fechaDesde !== '', fn($q) => $q->whereDate('fecha_emision', '>=', $fechaDesde))
            ->when($fechaHasta !== '', fn($q) => $q->whereDate('fecha_emision', '<=', $fechaHasta))

            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Facturas/Index', [
            'facturas' => $facturas,
            'filtros' => [
                'q'      => $q,
                'estado' => $estado,
                'metodo' => $metodo,
                'desde'  => $fechaDesde,
                'hasta'  => $fechaHasta,
            ]
        ]);
    }

    /* ============================================================
       FORMULARIO CREAR
    ============================================================ */
    public function create()
    {
        return Inertia::render('Admin/Facturas/Form', [
            'clientes' => User::where('rol_id', 2)->get(),
            'reservas' => Reserva::all(),
        ]);
    }

    /* ============================================================
       GUARDAR FACTURA
    ============================================================ */
    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:users,id',
            'metodo_pago' => 'required|string',
            'reserva_id' => 'nullable|exists:reservas,id',
        ]);

        $ultimo = Factura::max('numero_factura') ?? 1000;

        $factura = Factura::create([
            'numero_factura' => $ultimo + 1,
            'prefijo'        => 'FV',
            'cliente_id'     => $request->cliente_id,
            'reserva_id'     => $request->reserva_id,
            'fecha_emision'  => now()->toDateString(),
            'metodo_pago'    => $request->metodo_pago,
            'subtotal'       => 0,
            'impuestos'      => 0,
            'total'          => 0,
            'estado'         => 'Pendiente',
            'observaciones'  => $request->observaciones,
            'datos_empresa'  => '{}',
            'datos_facturacion' => '{}',
        ]);

        return redirect()->route('admin.facturas.show', $factura->id)
            ->with('success', 'Factura creada correctamente.');
    }

    /* ============================================================
       EDITAR FACTURA
    ============================================================ */
    public function edit(Factura $factura)
    {
        return Inertia::render('Admin/Facturas/Form', [
            'factura' => $factura,
            'clientes' => User::where('rol_id', 2)->get(),
            'reservas' => Reserva::all(),
        ]);
    }

    /* ============================================================
       ACTUALIZAR FACTURA
    ============================================================ */
    public function update(Request $request, Factura $factura)
    {
        $request->validate([
            'cliente_id'   => 'required|exists:users,id',
            'metodo_pago'  => 'required|string',
            'estado'       => 'required|string',
        ]);

        $factura->update([
            'cliente_id'    => $request->cliente_id,
            'reserva_id'    => $request->reserva_id,
            'fecha_emision' => $request->fecha_emision,
            'metodo_pago'   => $request->metodo_pago,
            'estado'        => $request->estado,
            'observaciones' => $request->observaciones,
        ]);

        return redirect()->route('admin.facturas.index')
            ->with('success', 'Factura actualizada correctamente.');
    }

    /* ============================================================
       MOSTRAR FACTURA COMPLETA
    ============================================================ */
    public function show($id)
    {
        $factura = Factura::with(['cliente', 'detalles.servicio'])->findOrFail($id);

        return Inertia::render('Admin/Facturas/Show', [
            'factura'  => $factura,
            'detalles' => $factura->detalles,
            'servicios'=> Servicio::all(),
        ]);
    }

    /* ============================================================
       AGREGAR DETALLE
    ============================================================ */
    public function agregarDetalle(Request $request, Factura $factura)
    {
        $request->validate([
            'servicio_id'     => 'required|exists:servicios,id',
            'cantidad'        => 'required|integer|min:1',
            'precio_unitario' => 'required|numeric|min:0',
        ]);

        $servicio = Servicio::find($request->servicio_id);

        $subtotal = $request->cantidad * $request->precio_unitario;
        $impuesto = $subtotal * 0.19;
        $total    = $subtotal + $impuesto;

        DetalleFactura::create([
            'factura_id' => $factura->id,
            'servicio_id'=> $servicio->id,
            'descripcion'=> $servicio->nombre,
            'cantidad'   => $request->cantidad,
            'precio_unitario' => $request->precio_unitario,
            'subtotal'   => $subtotal,
            'impuesto'   => $impuesto,
            'total'      => $total,
        ]);

        $this->recalcularTotales($factura);

        return back()->with('success', 'Servicio agregado.');
    }

    private function recalcularTotales(Factura $factura)
    {
        $factura->update([
            'subtotal' => $factura->detalles()->sum('subtotal'),
            'impuestos'=> $factura->detalles()->sum('impuesto'),
            'total'    => $factura->detalles()->sum('total'),
        ]);
    }

    /* ============================================================
       ANULAR FACTURA
    ============================================================ */
    public function anular(Factura $factura)
    {
        $factura->update(['estado' => 'Anulada']);
        return back()->with('success', 'Factura anulada correctamente.');
    }

    /* ============================================================
       DESCARGAR PDF
    ============================================================ */
    public function descargarPDF(Factura $factura)
    {
        $hotel = InformacionHotel::first();

        $pdf = Pdf::loadView('factura.pdf', [
            'hotel'   => $hotel,
            'factura' => $factura,
            'cliente' => $factura->cliente,
            'detalles'=> $factura->detalles,
        ]);

        return $pdf->download("Factura_{$factura->prefijo}_{$factura->numero_factura}.pdf");
    }
}
