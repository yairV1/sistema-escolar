<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Boletín — {{ trim($boletin->estudiante->usuario->nombres.' '.$boletin->estudiante->usuario->apellidos) }}</title>
    <style>
        @page { margin: 100px 40px 60px 40px; }
        body { font-family: 'Helvetica', Arial, sans-serif; font-size: 12px; color: #212529; }
        header { position: fixed; top: -80px; left: 0; right: 0; height: 70px; border-bottom: 2px solid #212529; padding-bottom: 8px; }
        header .logo-colegio { float: left; height: 50px; margin-right: 12px; }
        header .datos-colegio { overflow: hidden; }
        footer { position: fixed; bottom: -45px; left: 0; right: 0; height: 30px; font-size: 9px; color: #6c757d; text-align: center; border-top: 1px solid #dee2e6; padding-top: 6px; }
        .colegio-nombre { font-size: 16px; font-weight: bold; margin: 0; }
        .colegio-datos { font-size: 9px; color: #6c757d; margin: 2px 0 0 0; }
        h1.titulo { font-size: 15px; text-align: center; margin: 0 0 14px 0; text-transform: uppercase; letter-spacing: 1px; }
        table.datos-estudiante { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        table.datos-estudiante td { padding: 3px 0; font-size: 11px; }
        table.datos-estudiante td.label { color: #6c757d; width: 130px; }
        table.notas { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table.notas th, table.notas td { border: 1px solid #ced4da; padding: 6px 8px; font-size: 11px; }
        table.notas th { background-color: #f1f3f5; text-align: left; font-weight: bold; }
        table.notas td.nota { text-align: center; font-weight: bold; width: 80px; }
        table.resumen { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table.resumen td { width: 50%; border: 1px solid #ced4da; padding: 10px; text-align: center; }
        table.resumen .valor { font-size: 20px; font-weight: bold; display: block; }
        table.resumen .etiqueta { font-size: 9px; color: #6c757d; text-transform: uppercase; }
        .observaciones { border: 1px solid #ced4da; padding: 10px; font-size: 11px; }
        .observaciones h2 { font-size: 11px; margin: 0 0 6px 0; text-transform: uppercase; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 9px; text-transform: uppercase; color: #fff; }
        .badge-borrador { background-color: #6c757d; }
        .badge-publicado { background-color: #198754; }
        .badge-anulado { background-color: #dc3545; }
        table.escala-legend { width: 100%; border-collapse: collapse; margin-bottom: 14px; font-size: 9px; color: #495057; }
        table.escala-legend td { padding: 2px 6px 2px 0; }
        table.firmas { width: 100%; border-collapse: collapse; margin-top: 40px; }
        table.firmas td { width: 33.33%; text-align: center; font-size: 10px; padding-top: 4px; border-top: 1px solid #212529; }
        table.firmas .nombre-firma { height: 24px; }
    </style>
</head>
<body>
    <header>
        @if ($logoBase64 ?? null)
            <img src="{{ $logoBase64 }}" class="logo-colegio" alt="Logo">
        @endif
        <div class="datos-colegio">
            <p class="colegio-nombre">{{ $colegio->nombre_colegio ?? 'Institución Educativa' }}</p>
            <p class="colegio-datos">
                {{ $colegio->direccion ?? '' }}{{ $colegio->ciudad ? ' · '.$colegio->ciudad : '' }}
                @if ($colegio->telefono) · Tel. {{ $colegio->telefono }} @endif
            </p>
        </div>
    </header>

    <footer>
        Boletín generado el {{ now()->format('d/m/Y H:i') }} — {{ $boletin->estudiante->codigo_estudiante }}
    </footer>

    <h1 class="titulo">Boletín de calificaciones — {{ $boletin->periodo->nombre_periodo }} ({{ $boletin->periodo->anio_lectivo }})</h1>

    <table class="datos-estudiante">
        <tr>
            <td class="label">Estudiante</td>
            <td>{{ trim($boletin->estudiante->usuario->nombres.' '.$boletin->estudiante->usuario->apellidos) }}</td>
            <td class="label">Código</td>
            <td>{{ $boletin->estudiante->codigo_estudiante }}</td>
        </tr>
        <tr>
            <td class="label">Curso</td>
            <td>{{ $curso->nombre_curso ?? '—' }}</td>
            <td class="label">Estado del boletín</td>
            <td>
                @php $badgeClase = ['borrador' => 'badge-borrador', 'publicado' => 'badge-publicado', 'anulado' => 'badge-anulado']; @endphp
                <span class="badge {{ $badgeClase[$boletin->estado] ?? 'badge-borrador' }}">{{ ucfirst($boletin->estado) }}</span>
            </td>
        </tr>
    </table>

    <table class="notas">
        <thead>
            <tr>
                <th>Materia</th>
                <th>Observación del docente</th>
                <th class="nota">Nota definitiva</th>
                <th class="nota">Escala</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($boletin->detalle as $detalle)
                <tr>
                    <td>{{ $detalle->asignacion->materia->nombre_materia ?? '—' }}</td>
                    <td>
                        @if ($detalle->tipo_observacion)
                            <strong>{{ ucfirst($detalle->tipo_observacion) }}:</strong> {{ $detalle->observacion_materia }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="nota">{{ $detalle->nota_definitiva ?? '—' }}</td>
                    @php $escalaDetalle = $detalle->nota_definitiva !== null ? ($escalas ?? collect())->first(fn ($e) => $detalle->nota_definitiva >= $e->valor_min && $detalle->nota_definitiva <= $e->valor_max) : null; @endphp
                    <td class="nota">{{ $escalaDetalle->sigla ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="4">Sin materias registradas.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if (($escalas ?? collect())->isNotEmpty())
        <table class="escala-legend">
            <tr>
                @foreach ($escalas as $escala)
                    <td><strong>{{ $escala->sigla }}</strong> = {{ $escala->etiqueta }} ({{ $escala->valor_min }}–{{ $escala->valor_max }})</td>
                @endforeach
            </tr>
        </table>
    @endif

    <table class="resumen">
        <tr>
            <td>
                <span class="valor">{{ $boletin->promedio_general ?? '—' }}</span>
                <span class="etiqueta">Promedio general</span>
            </td>
            <td>
                <span class="valor">{{ $boletin->puesto_curso ?? '—' }}</span>
                <span class="etiqueta">Puesto en el curso</span>
            </td>
        </tr>
    </table>

    @if ($boletin->observaciones_gral)
        <div class="observaciones">
            <h2>Observaciones del director de grupo</h2>
            <p style="margin: 0;">{{ $boletin->observaciones_gral }}</p>
        </div>
    @endif

    <table class="firmas">
        <tr>
            <td>
                <div class="nombre-firma">{{ $colegio->nombre_rector ?? '' }}</div>
                RECTOR
            </td>
            <td>
                <div class="nombre-firma"></div>
                SECRETARÍA ACADÉMICA
            </td>
            <td>
                <div class="nombre-firma">{{ $curso?->director?->usuario ? trim($curso->director->usuario->nombres.' '.$curso->director->usuario->apellidos) : '' }}</div>
                DIRECTOR DE GRUPO
            </td>
        </tr>
    </table>
</body>
</html>
