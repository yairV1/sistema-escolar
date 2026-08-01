@extends('layouts.panel')

@section('title', 'Boletín — '.trim($boletin->estudiante->usuario->nombres.' '.$boletin->estudiante->usuario->apellidos))

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
            <a href="{{ route('boletines.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="h4 fw-semibold font-serif mb-0">{{ trim($boletin->estudiante->usuario->nombres.' '.$boletin->estudiante->usuario->apellidos) }}</h1>
                <div class="small text-secondary">
                    <code>{{ $boletin->estudiante->codigo_estudiante }}</code> · {{ $boletin->periodo->nombre_periodo }} ({{ $boletin->periodo->anio_lectivo }})
                </div>
            </div>
            @php $estadoColores = ['borrador' => 'secondary', 'publicado' => 'success', 'anulado' => 'danger']; @endphp
            <span class="badge text-bg-{{ $estadoColores[$boletin->estado] ?? 'secondary' }} ms-2">{{ ucfirst($boletin->estado) }}</span>
            <a href="{{ route('boletines.pdf', $boletin) }}" class="btn btn-sm btn-outline-primary ms-auto">
                <i class="fas fa-file-pdf me-1"></i> Descargar PDF
            </a>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="kpi-card bg-body-tertiary border h-100">
                    <div class="kpi-icon bg-primary-subtle text-primary mb-2"><i class="fas fa-star"></i></div>
                    <div class="kpi-value">{{ $boletin->promedio_general ?? '—' }}</div>
                    <div class="kpi-label">Promedio general</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card bg-body-tertiary border h-100">
                    <div class="kpi-icon bg-info-subtle text-info mb-2"><i class="fas fa-ranking-star"></i></div>
                    <div class="kpi-value">{{ $boletin->puesto_curso ?? '—' }}</div>
                    <div class="kpi-label">Puesto en el curso</div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Materia</th>
                        <th>Profesor</th>
                        <th>Nota definitiva</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($boletin->detalle as $detalle)
                        <tr>
                            <td class="fw-semibold">{{ $detalle->asignacion->materia->nombre_materia }}</td>
                            <td>{{ trim($detalle->asignacion->profesor->usuario->nombres.' '.$detalle->asignacion->profesor->usuario->apellidos) }}</td>
                            <td>{{ $detalle->nota_definitiva ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-secondary py-4">Sin materias registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($boletin->observaciones_gral)
            <div class="card mt-3">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-2">Observaciones del director de grupo</h2>
                    <p class="mb-0 small">{{ $boletin->observaciones_gral }}</p>
                </div>
            </div>
        @endif
    </div>
@endsection
