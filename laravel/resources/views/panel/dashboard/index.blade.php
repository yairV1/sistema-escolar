@extends('layouts.panel')

@section('title', 'Inicio')

@section('content')
    <div class="container-fluid p-3 p-md-4">

        <div class="dash-welcome mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <div class="small text-white-50 mb-1">
                    <i class="fas fa-circle me-1" style="font-size:.5rem;"></i> Año lectivo {{ now()->year }}
                </div>
                <h1 class="h3 fw-semibold mb-1 font-serif">Bienvenido, {{ auth()->user()->nombres }} 👋</h1>
                <p class="mb-0 text-white-50">{{ auth()->user()->rolLabel }} · Colegio San Cristóbal · Bogotá</p>
            </div>
            <div class="text-white-50 small text-end">
                <div id="dashFecha">—</div>
                <div id="dashHora">—</div>
            </div>
        </div>

        @php
            $kpiCards = [
                ['icon' => 'fa-user-graduate', 'color' => 'primary', 'label' => 'Estudiantes matriculados', 'value' => $kpis['estudiantesMatriculados']],
                ['icon' => 'fa-chalkboard-teacher', 'color' => 'info', 'label' => 'Docentes activos', 'value' => $kpis['docentesActivos']],
                ['icon' => 'fa-star', 'color' => 'warning', 'label' => 'Promedio institucional', 'value' => $kpis['promedioInstitucional'] ? number_format($kpis['promedioInstitucional'], 1) : '—'],
                ['icon' => 'fa-calendar-check', 'color' => 'success', 'label' => 'Asistencia promedio', 'value' => $kpis['asistenciaPromedio'] ? number_format($kpis['asistenciaPromedio'], 0).'%' : '—'],
                ['icon' => 'fa-file-signature', 'color' => 'secondary', 'label' => 'Solicitudes de matrícula', 'value' => $kpis['solicitudesPendientes']],
                ['icon' => 'fa-triangle-exclamation', 'color' => 'danger', 'label' => 'Estudiantes en riesgo', 'value' => $kpis['estudiantesEnRiesgo']],
            ];
        @endphp

        <div class="row g-3 mb-4">
            @foreach ($kpiCards as $card)
                <div class="col-6 col-lg-4 col-xl-2">
                    <div class="kpi-card bg-body-tertiary border h-100">
                        <div class="kpi-icon bg-{{ $card['color'] }}-subtle text-{{ $card['color'] }} mb-2">
                            <i class="fas {{ $card['icon'] }}"></i>
                        </div>
                        <div class="kpi-value">{{ $card['value'] }}</div>
                        <div class="kpi-label">{{ $card['label'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h6 fw-semibold mb-3"><i class="fas fa-chart-column text-primary me-1"></i> Promedio por grado</h2>

                        @if ($promedioPorGrado->isEmpty())
                            <div class="empty-state">
                                <div class="empty-icon"><i class="fas fa-chart-column"></i></div>
                                <p class="mb-0">Aún no hay notas registradas para calcular promedios por grado.</p>
                            </div>
                        @else
                            @php $max = $promedioPorGrado->max('promedio') ?: 5; @endphp
                            <div class="chart-bars">
                                @foreach ($promedioPorGrado as $grado)
                                    <div class="chart-bar bg-primary"
                                         style="height: {{ max(4, ($grado->promedio / $max) * 100) }}%"
                                         title="{{ $grado->nombre_curso }}: {{ number_format($grado->promedio, 1) }}"></div>
                                @endforeach
                            </div>
                            <div class="chart-labels">
                                @foreach ($promedioPorGrado as $grado)
                                    <span>{{ $grado->nombre_curso }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h6 fw-semibold mb-3"><i class="fas fa-file-signature text-primary me-1"></i> Matrículas recientes</h2>

                        @if ($matriculasRecientes->isEmpty())
                            <div class="empty-state">
                                <div class="empty-icon"><i class="fas fa-file-signature"></i></div>
                                <p class="mb-2">Aún no hay matrículas registradas.</p>
                                <a href="{{ config('legacy.url') }}RegistroEstudiantes" class="btn btn-sm btn-primary">
                                    Registrar el primer estudiante
                                </a>
                            </div>
                        @else
                            <ul class="list-group list-group-flush">
                                @foreach ($matriculasRecientes as $matricula)
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <div>
                                            <div class="fw-semibold">
                                                {{ trim($matricula->estudiante->usuario->nombres.' '.$matricula->estudiante->usuario->apellidos) }}
                                            </div>
                                            <div class="small text-secondary">{{ $matricula->curso->nombre_curso ?? '—' }}</div>
                                        </div>
                                        <span class="badge text-bg-{{ $matricula->estado_matricula === 'activa' ? 'success' : ($matricula->estado_matricula === 'pendiente' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($matricula->estado_matricula) }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h6 fw-semibold mb-3"><i class="fas fa-chalkboard-teacher text-primary me-1"></i> Docentes activos</h2>

                        @if ($docentesLista->isEmpty())
                            <div class="empty-state">
                                <div class="empty-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                                <p class="mb-2">Aún no hay docentes registrados.</p>
                                <a href="{{ config('legacy.url') }}RegistroDocentes" class="btn btn-sm btn-primary">
                                    Registrar el primer docente
                                </a>
                            </div>
                        @else
                            <ul class="list-group list-group-flush">
                                @foreach ($docentesLista as $docente)
                                    <li class="list-group-item px-0">
                                        <div class="fw-semibold">
                                            {{ trim($docente->usuario->nombres.' '.$docente->usuario->apellidos) }}
                                        </div>
                                        <div class="small text-secondary">
                                            {{ $docente->asignaciones->pluck('materia.nombre_materia')->filter()->unique()->join(', ') ?: 'Sin asignaturas asignadas' }}
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h6 fw-semibold mb-3"><i class="fas fa-user-graduate text-primary me-1"></i> Estudiantes</h2>

                        @if ($estudiantesMuestra->isEmpty())
                            <div class="empty-state">
                                <div class="empty-icon"><i class="fas fa-user-graduate"></i></div>
                                <p class="mb-2">Aún no hay estudiantes matriculados.</p>
                                <a href="{{ config('legacy.url') }}RegistroEstudiantes" class="btn btn-sm btn-primary">
                                    Registrar el primer estudiante
                                </a>
                            </div>
                        @else
                            <ul class="list-group list-group-flush">
                                @foreach ($estudiantesMuestra as $estudiante)
                                    <li class="list-group-item px-0">
                                        <div class="fw-semibold">
                                            {{ trim($estudiante->usuario->nombres.' '.$estudiante->usuario->apellidos) }}
                                        </div>
                                        <div class="small text-secondary">
                                            {{ $estudiante->matriculas->first()->curso->nombre_curso ?? 'Sin curso asignado' }}
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body empty-state">
                        <div class="empty-icon"><i class="fas fa-bullhorn"></i></div>
                        <p class="mb-0">Comunicados llega en una fase posterior de la migración.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body empty-state">
                        <div class="empty-icon"><i class="fas fa-chart-bar"></i></div>
                        <p class="mb-0">Reportes y estadísticas llegan en una fase posterior de la migración.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var DIAS = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
            var MESES = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

            function actualizarReloj() {
                var ahora = new Date();
                var fechaEl = document.getElementById('dashFecha');
                var horaEl = document.getElementById('dashHora');
                if (fechaEl) fechaEl.textContent = DIAS[ahora.getDay()] + ', ' + ahora.getDate() + ' de ' + MESES[ahora.getMonth()] + ' de ' + ahora.getFullYear();
                if (horaEl) {
                    var h = ahora.getHours();
                    var ampm = h >= 12 ? 'pm' : 'am';
                    var h12 = h % 12 || 12;
                    var m = String(ahora.getMinutes()).padStart(2, '0');
                    horaEl.textContent = h12 + ':' + m + ' ' + ampm;
                }
            }

            actualizarReloj();
            setInterval(actualizarReloj, 30000);
        });
    </script>
@endpush
