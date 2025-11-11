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
use Illuminate\Support\Facades\DB;

class FacturacionController extends Controller
{
    /** Listado de facturas */
    public function index()
    {
        $facturas = Factura::with('cliente')
            ->orderByDesc('created_at')
            ->paginate(10);

        return Inertia::render('Admin/Facturas/Index', [
            'facturas' => $facturas
        ]);
    }

    /**  Formulario para crear nueva factura */
    public function create()
    {
        $clientes = User::whereHas('rol', fn($q) => $q->where('nombre_rol', 'Cliente'))->get();
        $reservas = Reserva::all();

        return Inertia::render('Admin/Facturas/Form', [
            'clientes' => $clientes,
            'reservas' => $reservas,
        ]);
    }

    /**  Guardar nueva factura */
    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:users,id',
            'metodo_pago' => 'required|string',
            'reserva_id' => 'nullable|exists:reservas,id',
        ]);

        $factura = Factura::create([
            'numero_factura' => rand(1000, 9999),
            'prefijo' => 'FV',
            'cliente_id' => $request->cliente_id,
            'reserva_id' => $request->reserva_id,
            'fecha_emision' => now(),
            'metodo_pago' => $request->metodo_pago,
            'subtotal' => 0,
            'impuestos' => 0,
            'total' => 0,
            'estado' => 'Pendiente',
            'observaciones' => $request->observaciones,
            'datos_empresa' => 'Eco Hotel Villa del Sol',
            'datos_facturacion' => '',
        ]);

        return redirect()->route('admin.facturas.show', $factura->id)
            ->with('success', 'Factura creada correctamente.');
    }

    /**  Editar factura (solo ciertos campos) */
    public function edit(Factura $factura)
    {
        $clientes = User::whereHas('rol', fn($q) => $q->where('nombre_rol', 'Cliente'))->get();
        $reservas = Reserva::all();

        return Inertia::render('Admin/Facturas/Form', [
            'factura' => $factura,
            'clientes' => $clientes,
            'reservas' => $reservas,
        ]);
    }

    /**  Actualizar factura */
    public function update(Request $request, Factura $factura)
    {
        $request->validate([
            'cliente_id' => 'required|exists:users,id',
            'metodo_pago' => 'required|string',
            'estado' => 'required|string',
        ]);

        $factura->update([
            'cliente_id' => $request->cliente_id,
            'reserva_id' => $request->reserva_id,
            'fecha_emision' => $request->fecha_emision,
            'metodo_pago' => $request->metodo_pago,
            'estado' => $request->estado,
            'observaciones' => $request->observaciones,
        ]);

        return redirect()->route('admin.facturas.index')->with('success', 'Factura actualizada correctamente.');
    }

    /**  Mostrar factura */
    public function show($id)
    {
        $factura = Factura::with(['cliente', 'detalles.servicio'])->findOrFail($id);
        $servicios = Servicio::all();

        return Inertia::render('Admin/Facturas/Show', [
            'factura' => $factura,
            'detalles' => $factura->detalles,
            'servicios' => $servicios
        ]);
    }

    /**  Agregar detalle */
    public function agregarDetalle(Request $request, Factura $factura)
    {
        $request->validate([
            'servicio_id' => 'nullable|exists:servicios,id',
            'descripcion' => 'nullable|string|max:255',
            'cantidad' => 'required|integer|min:1',
            'precio_unitario' => 'required|numeric|min:0',
        ]);

        $descripcion = $request->descripcion;
        if ($request->servicio_id) {
            $servicio = Servicio::find($request->servicio_id);
            $descripcion = $servicio->nombre ?? 'Servicio';
        }

        $subtotal = $request->cantidad * $request->precio_unitario;
        $impuesto = $subtotal * 0.19;
        $total = $subtotal + $impuesto;

        DetalleFactura::create([
            'factura_id' => $factura->id,
            'servicio_id' => $request->servicio_id,
            'descripcion' => $descripcion,
            'cantidad' => $request->cantidad,
            'precio_unitario' => $request->precio_unitario,
            'subtotal' => $subtotal,
            'impuesto' => $impuesto,
            'total' => $total,
        ]);

        $this->recalcularTotales($factura);
        return back()->with('success', 'Servicio agregado correctamente.');
    }

    /**  Eliminar detalle */
    public function eliminarDetalle(DetalleFactura $detalle)
    {
        $factura = $detalle->factura;
        $detalle->delete();
        $this->recalcularTotales($factura);

        return back()->with('success', 'Detalle eliminado correctamente.');
    }

    /**  Recalcular totales */
    private function recalcularTotales(Factura $factura)
    {
        $subtotal = $factura->detalles()->sum('subtotal');
        $impuestos = $factura->detalles()->sum('impuesto');
        $total = $factura->detalles()->sum('total');

        $factura->update([
            'subtotal' => $subtotal,
            'impuestos' => $impuestos,
            'total' => $total,
        ]);
    }

    /**  Anular factura (en vez de eliminar) */
    public function anular(Factura $factura)
    {
        $factura->update(['estado' => 'Anulada']);
        return back()->with('success', 'Factura anulada correctamente.');
    }

    /**  Descargar PDF */
    public function descargarPDF(Factura $factura)
    {
        $hotel = InformacionHotel::first();
        $cliente = $factura->cliente;
        $detalles = $factura->detalles;

        $pdf = Pdf::loadView('factura.pdf', compact('factura', 'hotel', 'cliente', 'detalles'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("Factura_{$factura->prefijo}_{$factura->numero_factura}.pdf");
    }
}
