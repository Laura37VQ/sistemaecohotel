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
use Illuminate\Support\Facades\DB;

class FacturaController extends Controller
{
    /**
     * Listado de facturas con filtros.
     * Se permite filtrar por cliente, estado, fecha y búsqueda general.
     */
    public function index(Request $request)
    {
        // Captura de filtros enviados desde Vue
        $buscar = $request->query('buscar', '');
        $estado = $request->query('estado', '');
        $cliente = $request->query('cliente', '');
        $fecha_inicio = $request->query('fecha_inicio', '');
        $fecha_fin = $request->query('fecha_fin', '');

        $facturas = Factura::with(['cliente:id,nombres,apellidos', 'reserva:id,codigo_reserva'])

            // Búsqueda por número de factura o nombre del cliente
            ->when($buscar !== '', function ($q) use ($buscar) {
                $q->where('numero_factura', 'like', "%$buscar%")
                  ->orWhereHas('cliente', function ($sub) use ($buscar) {
                      $sub->where('nombres', 'like', "%$buscar%")
                          ->orWhere('apellidos', 'like', "%$buscar%");
                  });
            })

            // Filtrar por estado
            ->when($estado !== '', fn($q) => $q->where('estado', $estado))

            // Filtrar por cliente
            ->when($cliente !== '', fn($q) => $q->where('cliente_id', $cliente))

            // Filtrar por fecha de emisión
            ->when($fecha_inicio !== '', fn($q) => $q->whereDate('fecha_emision', '>=', $fecha_inicio))
            ->when($fecha_fin !== '', fn($q) => $q->whereDate('fecha_emision', '<=', $fecha_fin))

            ->orderByDesc('fecha_emision')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Recepcionista/Facturas/Index', [
            'facturas' => $facturas,
            'clientes' => User::where('rol_id', 2)->get(['id','nombres','apellidos']),
            'filtros' => [
                'buscar' => $buscar,
                'estado' => $estado,
                'cliente' => $cliente,
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
            ]
        ]);
    }

    /**
     * Formulario de creación de factura.
     */
    public function create()
    {
        return Inertia::render('Recepcionista/Facturas/Form', [
            'factura' => null,
            'clientes' => User::where('rol_id', 2)->get(['id', 'nombres', 'apellidos']),
            'reservas' => Reserva::all(['id', 'codigo_reserva']),
        ]);
    }

    /**
     * Guarda una factura nueva.
     */
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

        // Generación del número consecutivo
        $ultimo = Factura::max('numero_factura') ?? 1000;
        $data['numero_factura'] = $ultimo + 1;
        $data['prefijo'] = 'FV';

        Factura::create($data);

        return redirect()->route('recepcionista.facturas.index')
            ->with('success', 'Factura registrada correctamente.');
    }

    /**
     * Formulario de edición.
     */
    public function edit(Factura $factura)
    {
        return Inertia::render('Recepcionista/Facturas/Form', [
            'factura' => $factura,
            'clientes' => User::where('rol_id', 2)->get(),
            'reservas' => Reserva::all(['id', 'codigo_reserva']),
        ]);
    }

    /**
     * Actualiza una factura existente.
     */
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

    /**
     * Muestra una factura con todos sus detalles.
     */
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

    /**
     * Agrega un detalle a la factura.
     */
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

    /**
     * Elimina un detalle de una factura
     * y recalcula los totales.
     */
    public function eliminarDetalle(DetalleFactura $detalle)
    {
        DB::transaction(function () use ($detalle) {
            $factura = $detalle->factura;
            $detalle->delete();

            $this->recalcularTotales($factura);
        });

        return back()->with('success', 'Detalle eliminado correctamente.');
    }

    /**
     * Método interno: recalcula los totales de la factura.
     */
    private function recalcularTotales(Factura $factura)
    {
        $subtotal = $factura->detalles()->sum('subtotal');
        $impuestos = round($subtotal * 0.19, 2);
        $total = $subtotal + $impuestos;

        $factura->update([
            'subtotal' => $subtotal,
            'impuestos' => $impuestos,
            'total' => $total,
        ]);
    }

    /**
     * Descarga en PDF la factura.
     */
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
