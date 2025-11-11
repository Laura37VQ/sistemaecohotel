<?php

namespace App\Http\Controllers\Recepcionista;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Factura;
use App\Models\User;
use App\Models\Reserva;
use App\Models\Servicio;
use App\Models\DetalleFactura;
use App\Models\InformacionHotel;
use Barryvdh\DomPDF\Facade\Pdf;

class FacturaController extends Controller
{
    /** 🧾 Listar facturas */
    public function index()
    {
        $facturas = Factura::with(['cliente:id,nombres,apellidos', 'reserva:id,codigo_reserva'])
            ->orderByDesc('fecha_emision')
            ->paginate(10);

        return Inertia::render('Recepcionista/Facturas/Index', [
            'facturas' => $facturas
        ]);
    }

    /** ➕ Crear factura manualmente */
    public function create()
    {
        $clientes = User::where('rol_id', 2)->get(['id', 'nombres', 'apellidos']);
        $reservas = Reserva::whereIn('estado', ['Confirmada', 'Finalizada'])
            ->get(['id', 'codigo_reserva']);

        return Inertia::render('Recepcionista/Facturas/Form', [
            'factura' => null,
            'clientes' => $clientes,
            'reservas' => $reservas,
        ]);
    }

    /** 💾 Guardar factura */
    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id' => 'required|exists:users,id',
            'reserva_id' => 'required|exists:reservas,id',
            'fecha_emision' => 'required|date',
            'metodo_pago' => 'required|string',
            'estado' => 'required|string',
            'observaciones' => 'nullable|string',
            'subtotal' => 'required|numeric|min:0',
            'impuestos' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
        ]);

        // Generar número consecutivo automático
        $ultimoNumero = Factura::max('numero_factura') ?? 1000;
        $data['numero_factura'] = $ultimoNumero + 1;
        $data['prefijo'] = 'FV';

        Factura::create($data);

        return redirect()->route('recepcionista.facturas.index')
            ->with('success', 'Factura registrada correctamente.');
    }

    /** ✏️ Editar factura */
    public function edit(Factura $factura)
    {
        $clientes = User::where('rol_id', 2)->get(['id', 'nombres', 'apellidos']);
        $reservas = Reserva::get(['id', 'codigo_reserva']);

        return Inertia::render('Recepcionista/Facturas/Form', [
            'factura' => $factura,
            'clientes' => $clientes,
            'reservas' => $reservas,
        ]);
    }

    /** 🔁 Actualizar factura */
    public function update(Request $request, Factura $factura)
    {
        $data = $request->validate([
            'metodo_pago' => 'required|string',
            'estado' => 'required|string',
            'observaciones' => 'nullable|string',
            'subtotal' => 'required|numeric|min:0',
            'impuestos' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
        ]);

        $factura->update($data);

        return redirect()->route('recepcionista.facturas.index')
            ->with('success', 'Factura actualizada correctamente.');
    }

    /** 👁️ Mostrar factura con detalles */
    public function show(Factura $factura)
    {
        $detalles = DetalleFactura::with('servicio:id,nombre,precio')
            ->where('factura_id', $factura->id)
            ->get();

        $servicios = Servicio::all(['id', 'nombre', 'precio']);

        return Inertia::render('Recepcionista/Facturas/Show', [
            'factura' => $factura->load('cliente'),
            'detalles' => $detalles,
            'servicios' => $servicios,
        ]);
    }

    /** ➕ Agregar detalle */
    public function agregarDetalle(Request $request, Factura $factura)
    {
        $data = $request->validate([
            'servicio_id' => 'required|exists:servicios,id',
            'cantidad' => 'required|integer|min:1',
            'precio_unitario' => 'required|numeric|min:0',
        ]);
        // 🔹 Buscar el servicio seleccionado
        $servicio = \App\Models\Servicio::find($data['servicio_id']);

        $subtotal = $data['cantidad'] * $data['precio_unitario'];
        $impuesto = round($subtotal * 0.19, 2);
        $total = $subtotal + $impuesto;

        $detalle = new \App\Models\DetalleFactura([
            'factura_id'     => $factura->id,
            'servicio_id'     => $servicio->id,
            'descripcion'    => $servicio->nombre,
            'cantidad'        => $data['cantidad'],
            'precio_unitario' => $data['precio_unitario'],
            'subtotal'       => $subtotal,
            'impuesto'       => $impuesto,
            'total'          => $total,
        ]);

        $detalle->save();

        $this->recalcularTotales($factura);

        return back()->with('success', 'Servicio agregado a la factura.');

    }

    /** ❌ Eliminar detalle */
    public function eliminarDetalle(DetalleFactura $detalle)
    {
        $factura = $detalle->factura;
        $detalle->delete();

        $this->recalcularTotales($factura);

        return back()->with('success', 'Detalle eliminado correctamente.');
    }

    /** 💳 Marcar factura como pagada */
    public function marcarPagada(Factura $factura)
    {
        $factura->update(['estado' => 'Pagada']);
        return back()->with('success', 'Factura marcada como pagada.');
    }

    /** 📄 Descargar factura en PDF */
    public function descargarPDF(Factura $factura)
    {
        $hotel = InformacionHotel::first();
        $cliente = $factura->cliente;
        $detalles = $factura->detalles()->with('servicio')->get();

        // ✅ Usa la vista correcta según tu archivo real
        //  resources/views/pdf.blade.php
        $pdf = Pdf::loadView('factura.pdf', compact('factura', 'hotel', 'cliente', 'detalles'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("Factura_{$factura->prefijo}_{$factura->numero_factura}.pdf");
    }

    /** 🔹 Recalcular totales */
    private function recalcularTotales(Factura $factura)
    {
        $subtotal = $factura->detalles->sum(fn($d) => $d->cantidad * $d->precio_unitario);
        $impuestos = round($subtotal * 0.19, 2);
        $total = $subtotal + $impuestos;

        $factura->update([
            'subtotal' => $subtotal,
            'impuestos' => $impuestos,
            'total' => $total,
        ]);
    }
}
