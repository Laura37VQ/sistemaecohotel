<?php

namespace App\Http\Controllers\Recepcionista;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DetalleFactura;
use App\Models\Factura;
use App\Models\Servicio;
use Illuminate\Support\Facades\DB;

class DetalleFacturaController extends Controller
{
    /**
     * Guardar un nuevo detalle en una factura existente
     */
    public function store(Request $request, Factura $factura)
    {
        $data = $request->validate([
            'servicio_id' => 'required|exists:servicios,id',
            'cantidad' => 'required|integer|min:1',
            'precio_unitario' => 'required|numeric|min:0',
        ]);

        $servicio = Servicio::findOrFail($data['servicio_id']);

        DB::transaction(function () use ($data, $factura, $servicio) {
            $subtotal = $data['cantidad'] * $data['precio_unitario'];
            $impuesto = $subtotal * 0.19; // IVA 19%
            $total = $subtotal + $impuesto;

            DetalleFactura::create([
                'factura_id' => $factura->id,
                'servicio_id' => $servicio->id,
                'cantidad' => $data['cantidad'],
                'precio_unitario' => $data['precio_unitario'],
                'descripcion' => $servicio->nombre,
                'subtotal' => $subtotal,
                'impuesto' => $impuesto,
                'total' => $total,
            ]);

            // 🔁 Recalcular totales desde todos los detalles
            $totales = $factura->detalles()
                ->selectRaw('SUM(subtotal) as subtotal, SUM(impuesto) as impuestos, SUM(total) as total')
                ->first();

            $factura->update([
                'subtotal' => $totales->subtotal ?? 0,
                'impuestos' => $totales->impuestos ?? 0,
                'total' => $totales->total ?? 0,
            ]);
        });

        return back()->with('success', 'Detalle agregado correctamente.');
    }

    /**
     * Eliminar un detalle de la factura
     */
    public function destroy(DetalleFactura $detalle)
    {
        DB::transaction(function () use ($detalle) {
            $factura = $detalle->factura;
            $detalle->delete();

            // 🔁 Recalcular totales de la factura
            $totales = $factura->detalles()
                ->selectRaw('SUM(subtotal) as subtotal, SUM(impuesto) as impuestos, SUM(total) as total')
                ->first();

            $factura->subtotal = $totales->subtotal ?? 0;
            $factura->impuestos = $totales->impuestos ?? 0;
            $factura->total = $totales->total ?? 0;
            $factura->save();
        });

        return back()->with('success', 'Detalle eliminado correctamente.');
    }
}
