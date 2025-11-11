<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DetalleFactura;
use App\Models\Factura;

class DetalleFacturaController extends Controller
{
    public function store(Request $request, $factura_id)
    {
        $request->validate([
            'servicio_id' => 'required|exists:servicios,id',
            'cantidad' => 'required|integer|min:1',
            'precio_unitario' => 'required|numeric|min:0',
        ]);

        $factura = Factura::findOrFail($factura_id);

        $subtotal = $request->cantidad * $request->precio_unitario;
        $impuesto = $subtotal * 0.19;
        $total = $subtotal + $impuesto;

        DetalleFactura::create([
            'factura_id' => $factura->id,
            'servicio_id' => $request->servicio_id,
            'descripcion' => 'Detalle generado automáticamente',
            'cantidad' => $request->cantidad,
            'precio_unitario' => $request->precio_unitario,
            'subtotal' => $subtotal,
            'impuesto' => $impuesto,
            'total' => $total,
        ]);

        // Recalcular totales de la factura
        $factura->update([
            'subtotal' => $factura->detalles()->sum('subtotal'),
            'impuestos' => $factura->detalles()->sum('impuesto'),
            'total' => $factura->detalles()->sum('total'),
        ]);

        return back()->with('success', 'Detalle agregado correctamente.');
    }

    public function destroy($id)
    {
        $detalle = DetalleFactura::findOrFail($id);
        $factura = $detalle->factura;

        $detalle->delete();

        $factura->update([
            'subtotal' => $factura->detalles()->sum('subtotal'),
            'impuestos' => $factura->detalles()->sum('impuesto'),
            'total' => $factura->detalles()->sum('total'),
        ]);

        return back()->with('success', 'Detalle eliminado correctamente.');
    }
}
