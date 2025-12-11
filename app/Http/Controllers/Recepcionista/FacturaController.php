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
    /** LISTAR FACTURAS */
    public function index()
    {
        $facturas = Factura::with(['cliente:id,nombres,apellidos', 'reserva:id,codigo_reserva'])
            ->orderByDesc('fecha_emision')
            ->paginate(10);

        return Inertia::render('Recepcionista/Facturas/Index', [
            'facturas' => $facturas
        ]);
    }

    /** FORMULARIO CREAR */
    public function create()
    {
        return Inertia::render('Recepcionista/Facturas/Form', [
            'factura' => null,
            'clientes' => User::where('rol_id', 2)->get(['id', 'nombres', 'apellidos']),
            'reservas' => Reserva::all(['id', 'codigo_reserva']),
        ]);
    }

    /** GUARDAR FACTURA NUEVA */
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

        // Número consecutivo
        $ultimo = Factura::max('numero_factura') ?? 1000;
        $data['numero_factura'] = $ultimo + 1;
        $data['prefijo'] = 'FV';

        Factura::create($data);

        return redirect()->route('recepcionista.facturas.index')
            ->with('success', 'Factura registrada correctamente.');
    }

    /** EDITAR FACTURA */
    public function edit(Factura $factura)
    {
        return Inertia::render('Recepcionista/Facturas/Form', [
            'factura' => $factura,
            'clientes' => User::where('rol_id', 2)->get(),
            'reservas' => Reserva::all(['id', 'codigo_reserva']),
        ]);
    }

    /** ACTUALIZAR FACTURA */
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

    /** MOSTRAR FACTURA */
    public function show(Factura $factura)
    {
        $detalles = DetalleFactura::where('factura_id', $factura->id)
            ->with('servicio:id,nombre,precio')
            ->get();

        return Inertia::render('Recepcionista/Facturas/Show', [
            'factura' => $factura->load('cliente'),
            'detalles' => $detalles,
            'servicios' => Servicio::all(),
        ]);
    }

    /** AGREGAR DETALLE */
    public function agregarDetalle(Request $request, Factura $factura)
    {
        $data = $request->validate([
            'servicio_id' => 'required|exists:servicios,id',
            'cantidad' => 'required|integer|min:1',
            'precio_unitario' => 'required|numeric|min:0',
        ]);

        $servicio = Servicio::find($data['servicio_id']);

        $subtotal = $data['cantidad'] * $data['precio_unitario'];
        $impuesto = round($subtotal * 0.19, 2);
        $total = $subtotal + $impuesto;

        DetalleFactura::create([
            'factura_id' => $factura->id,
            'servicio_id' => $servicio->id,
            'descripcion' => $servicio->nombre,
            'cantidad' => $data['cantidad'],
            'precio_unitario' => $data['precio_unitario'],
            'subtotal' => $subtotal,
            'impuesto' => $impuesto,
            'total' => $total,
        ]);

        $this->recalcularTotales($factura);

        return back()->with('success', 'Servicio agregado.');
    }

    private function recalcularTotales(Factura $factura)
    {
        $subtotal = $factura->detalles->sum('subtotal');
        $impuestos = round($subtotal * 0.19, 2);
        $total = $subtotal + $impuestos;

        $factura->update([
            'subtotal' => $subtotal,
            'impuestos' => $impuestos,
            'total' => $total,
        ]);
    }

    /** PDF */
    public function descargarPDF(Factura $factura)
    {
        $hotel = InformacionHotel::first();

        $pdf = Pdf::loadView('factura.pdf', [
            'factura' => $factura,
            'hotel' => $hotel,
            'cliente' => $factura->cliente,
            'detalles' => $factura->detalles()->with('servicio')->get(),
        ]);

        return $pdf->download("Factura_{$factura->prefijo}_{$factura->numero_factura}.pdf");
    }
}
