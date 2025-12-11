{{-- 
    ----------------------------------------------------------
    PLANTILLA PDF - REPORTE DE OCUPACIÓN
    Diseño uniforme con los demás reportes del sistema.
    ----------------------------------------------------------
--}}

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ocupación</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 25px;
            color: #333;
        }

        /* Encabezado con estilo corporativo */
        .header {
            text-align: center;
            margin-bottom: 18px;
            padding-bottom: 10px;
            border-bottom: 2px solid #2E7D32;
        }

        .hotel-name {
            font-size: 20px;
            font-weight: bold;
            color: #2E7D32;
            margin: 0;
        }

        /* Sección de información */
        .info {
            font-size: 13px;
            margin-bottom: 12px;
        }
        .info strong {
            color: #1B5E20;
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
            text-align: left;
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
            margin-top: 28px;
            text-align: center;
            font-size: 11px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 8px;
        }
    </style>
</head>

<body>

    {{-- Encabezado del documento --}}
    <div class="header">
        <h1 class="hotel-name">Eco Hotel Villa del Sol</h1>
        <h2>Reporte de Ocupación</h2>
    </div>

    {{-- Información general del reporte --}}
    <div class="info">
        <p>
            <strong>Desde:</strong> {{ $fechaInicio ?? '---' }} &nbsp;&nbsp;
            <strong>Hasta:</strong> {{ $fechaFin ?? '---' }}
        </p>

        <p>
            <strong>Tasa de ocupación:</strong> {{ $tasaOcupacion }}% <br>
            <strong>Total habitaciones:</strong> {{ $totalHabitaciones }}
        </p>
    </div>

    {{-- Tabla con las reservas encontradas --}}
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>Habitación</th>
                <th>Ingreso</th>
                <th>Salida</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($reservas as $r)
                <tr>
                    <td>{{ $r->id }}</td>
                    <td>{{ $r->usuario->nombres }} {{ $r->usuario->apellidos }}</td>
                    <td>{{ $r->habitacion->numero_habitacion }}</td>
                    <td>{{ \Carbon\Carbon::parse($r->fecha_ingreso)->format('Y-m-d') }}</td>
                    <td>{{ \Carbon\Carbon::parse($r->fecha_salida)->format('Y-m-d') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center; padding:10px;">
                        No hay reservas registradas para este rango.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pie de página --}}
    <div class="footer">
        Reporte generado automáticamente por el sistema del Eco Hotel Villa del Sol.<br>
        © {{ date('Y') }} — Todos los derechos reservados.
    </div>

</body>
</html>
