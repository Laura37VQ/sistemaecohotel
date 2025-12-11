{{-- 
    ----------------------------------------------------------
    PLANTILLA PDF - REPORTE DE CLIENTES
    Este archivo genera el reporte en PDF usando DOMPDF.
    Mantiene el mismo estilo visual que la factura del hotel.
    ----------------------------------------------------------
--}}

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Clientes</title>

    <style>
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 12px; 
            margin: 25px;
            color: #333;
        }

        /* Encabezado general del reporte */
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #2E7D32;
        }

        .hotel-name {
            color: #2E7D32;
            font-size: 20px;
            margin: 5px 0;
        }

        /* Tabla principal */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            border: 1px solid #bbb;
            padding: 6px 8px;
        }

        th {
            background: #C8E6C9;
            color: #1B5E20;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background: #F1F8E9;
        }

        /* Pie de página */
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 11px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
    </style>
</head>

<body>

    {{-- ENCABEZADO DEL DOCUMENTO --}}
    <div class="header">
        <h1 class="hotel-name">Eco Hotel Villa del Sol</h1>
        <h2>Reporte de Clientes</h2>
    </div>

    {{-- RESUMEN DE DATOS --}}
    <p>
        <strong>Total de clientes:</strong> {{ $totalClientes }} &nbsp;&nbsp;|
        <strong>Clientes con reservas:</strong> {{ $clientesConReservas }}
    </p>

    {{-- TABLA PRINCIPAL --}}
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre completo</th>
                <th>Correo</th>
                <th>Teléfono</th>
                <th>Reservas</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($clientes as $c)
                <tr>
                    <td>{{ $c->id }}</td>
                    <td>{{ $c->nombres }} {{ $c->apellidos }}</td>
                    <td>{{ $c->correo }}</td>
                    <td>{{ $c->telefono }}</td>
                    <td>{{ $c->reservas->count() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- PIE DE PÁGINA --}}
    <div class="footer">
        Reporte generado automáticamente por el sistema del Eco Hotel Villa del Sol.<br>
        © {{ date('Y') }} — Todos los derechos reservados.
    </div>

</body>
</html>
