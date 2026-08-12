@extends('layouts.rector')

@section('title', 'Estadísticas')

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <h1 class="h4 fw-semibold font-serif mb-3">Estadísticas</h1>

        <div class="row g-3 mb-4">
            @foreach ([
                ['icon' => 'fa-user-graduate', 'label' => 'Estudiantes activos', 'value' => $resumen['estudiantes'], 'color' => 'primary'],
                ['icon' => 'fa-chalkboard-teacher', 'label' => 'Docentes activos', 'value' => $resumen['docentes'], 'color' => 'info'],
                ['icon' => 'fa-school', 'label' => 'Cursos activos', 'value' => $resumen['cursos'], 'color' => 'success'],
                ['icon' => 'fa-triangle-exclamation', 'label' => 'Estudiantes en riesgo', 'value' => $resumen['en_riesgo'], 'color' => 'danger'],
            ] as $stat)
                <div class="col-6 col-md-3">
                    <div class="kpi-card bg-body-tertiary border h-100">
                        <div class="kpi-icon bg-{{ $stat['color'] }}-subtle text-{{ $stat['color'] }} mb-2">
                            <i class="fas {{ $stat['icon'] }}"></i>
                        </div>
                        <div class="kpi-value">{{ $stat['value'] }}</div>
                        <div class="kpi-label">{{ $stat['label'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="kpi-card bg-body-tertiary border h-100">
                    <div class="kpi-icon bg-warning-subtle text-warning mb-2"><i class="fas fa-star"></i></div>
                    <div class="kpi-value">{{ $promedioInstitucional ?: '—' }}</div>
                    <div class="kpi-label">Promedio institucional (boletines publicados)</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="kpi-card bg-body-tertiary border h-100">
                    <div class="kpi-icon bg-success-subtle text-success mb-2"><i class="fas fa-clipboard-check"></i></div>
                    <div class="kpi-value">{{ $asistenciaPromedio ?: '—' }}%</div>
                    <div class="kpi-label">Asistencia promedio institucional</div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h6 fw-semibold mb-3"><i class="fas fa-chart-simple text-primary me-1"></i> Distribución de notas definitivas</h2>
                        @php
                            $totalNotas = array_sum($distribucionNotas) ?: 1;
                            $rangos = [
                                'bajo' => ['label' => 'Bajo (< 3.0)', 'color' => 'danger'],
                                'basico' => ['label' => 'Básico (3.0 – 3.9)', 'color' => 'warning'],
                                'alto' => ['label' => 'Alto (4.0 – 4.4)', 'color' => 'info'],
                                'superior' => ['label' => 'Superior (≥ 4.5)', 'color' => 'success'],
                            ];
                        @endphp
                        @foreach ($rangos as $clave => $info)
                            @php $pct = round($distribucionNotas[$clave] / $totalNotas * 100); @endphp
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span>{{ $info['label'] }}</span>
                                    <span>{{ $distribucionNotas[$clave] }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-{{ $info['color'] }}" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h6 fw-semibold mb-3"><i class="fas fa-ranking-star text-primary me-1"></i> Ranking de cursos por promedio</h2>
                        @if ($rankingCursos->isEmpty())
                            <div class="empty-state">
                                <div class="empty-icon"><i class="fas fa-ranking-star"></i></div>
                                <p class="mb-0">Todavía no hay boletines publicados para calcular el ranking.</p>
                            </div>
                        @else
                            <ul class="list-group list-group-flush">
                                @foreach ($rankingCursos as $i => $fila)
                                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                        <span><span class="fw-semibold">#{{ $i + 1 }}</span> {{ $fila->nombre_curso }}</span>
                                        <span class="badge text-bg-primary">{{ round($fila->promedio, 2) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
