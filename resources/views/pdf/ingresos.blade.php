{{-- 
    ----------------------------------------------------------
    PLANTILLA PDF - REPORTE DE INGRESOS
    Este PDF es descargado desde /reportes/ingresos/pdf
    ----------------------------------------------------------
--}}

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ingresos</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 25px;
            color: #333;
        }

        /* Encabezado */
        .header {
            text-align: center;
            padding-bottom: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #2E7D32;
        }

        .hotel-name {
            font-size: 20px;
            color: #2E7D32;
            margin: 0;
        }

        /* Tabla */
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

        /* Footer */
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #666;
            font-size: 11px;
            border-top: 1px solid #ccc;
            padding-top: 8px;
        }
    </style>
</head>

<body>

    {{-- ENCABEZADO --}}
    <div class="header">
        <h1 class="hotel-name">Eco Hotel Villa del Sol</h1>
        <h2>Reporte de Ingresos</h2>
    </div>

    {{-- INFORMACIÓN DEL RANGO --}}
    <p>
        <strong>Desde:</strong> {{ $fechaInicio ?? '---' }} &nbsp;&nbsp;
        <strong>Hasta:</strong> {{ $fechaFin ?? '---' }}
    </p>

    <p>
        <strong>Total ingresos:</strong>
        ${{ number_format($totalIngresos, 0, ',', '.') }}
    </p>

    {{-- TABLA --}}
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>Fecha</th>
                <th>Total</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($facturas as $f)
                <tr>
                    <td>{{ $f->id }}</td>
                    <td>{{ $f->cliente->nombres }} {{ $f->cliente->apellidos }}</td>
                    <td>{{ \Carbon\Carbon::parse($f->fecha_emision)->format('Y-m-d') }}</td>
                    <td>${{ number_format($f->total, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- FOOTER --}}
    <div class="footer">
        Reporte generado automáticamente por el sistema del Eco Hotel Villa del Sol.<br>
        © {{ date('Y') }} — Todos los derechos reservados.
    </div>

</body>
</html>
