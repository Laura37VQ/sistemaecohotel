<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura {{ $factura->prefijo }}{{ $factura->numero_factura }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; margin: 30px; }
        .header { text-align: center; border-bottom: 2px solid #2e7d32; padding-bottom: 10px; margin-bottom: 25px; }
        .logo { max-height: 80px; margin-bottom: 10px; }
        h2 { color: #2e7d32; margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f4f4f4; font-weight: bold; }
        .info { margin-bottom: 15px; line-height: 1.6; }
        .totales { width: 50%; float: right; margin-top: 20px; }
        .totales td { border: none; padding: 4px 8px; }
        .totales tr td:first-child { text-align: right; font-weight: bold; }
        .footer { clear: both; text-align: center; margin-top: 60px; font-size: 11px; color: #555; border-top: 1px solid #ccc; padding-top: 10px; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        @if($hotel && $hotel->logo)
            <img src="{{ public_path('storage/' . $hotel->logo) }}" class="logo">
        @endif
        <h2>{{ $hotel->nombre ?? 'EcoHotel Villa del Sol' }}</h2>
        <p>
            NIT: {{ $hotel->nit ?? '----' }} |
            {{ $hotel->direccion ?? 'Cabrera, Santander' }} |
            Tel: {{ $hotel->telefono ?? '---' }}
        </p>
        <p>Email: {{ $hotel->email ?? 'info@ecohotelvilladelsol.com' }} |
           {{ $hotel->ciudad ?? 'Cabrera' }}, {{ $hotel->pais ?? 'Colombia' }}</p>
    </div>

    <h3>Factura de venta: <strong>{{ $factura->prefijo }}{{ $factura->numero_factura }}</strong></h3>

    <div class="info">
        <p><strong>Cliente:</strong> {{ $cliente->nombres }} {{ $cliente->apellidos }}</p>
        <p><strong>Documento:</strong> {{ $cliente->documento_identidad }}</p>
        <p><strong>Correo:</strong> {{ $cliente->correo }}</p>
        <p><strong>Fecha de emisión:</strong> {{ \Carbon\Carbon::parse($factura->fecha_emision)->format('d/m/Y H:i') }}</p>
        <p><strong>Método de pago:</strong> {{ $factura->metodo_pago }}</p>
        <p><strong>Estado:</strong> {{ $factura->estado }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Descripción</th>
                <th>Cantidad</th>
                <th>Valor unitario</th>
                <th>Subtotal</th>
                <th>IVA (19%)</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($detalles as $d)
            <tr>
                <td>{{ $d->descripcion }}</td>
                <td>{{ $d->cantidad }}</td>
                <td class="text-right">${{ number_format($d->precio_unitario, 0, ',', '.') }}</td>
                <td class="text-right">${{ number_format($d->subtotal, 0, ',', '.') }}</td>
                <td class="text-right">${{ number_format($d->impuesto, 0, ',', '.') }}</td>
                <td class="text-right">${{ number_format($d->total, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totales">
        <tr>
            <td>Subtotal:</td>
            <td>${{ number_format($factura->subtotal, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>IVA (19%):</td>
            <td>${{ number_format($factura->impuestos, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Total a pagar:</strong></td>
            <td><strong>${{ number_format($factura->total, 0, ',', '.') }}</strong></td>
        </tr>
    </table>

    <div class="footer">
        Factura generada automáticamente por el sistema de reservas del {{ $hotel->nombre ?? 'EcoHotel Villa del Sol' }}.<br>
        No requiere firma física. Emitida conforme a la normatividad DIAN vigente.
    </div>
</body>
</html>
