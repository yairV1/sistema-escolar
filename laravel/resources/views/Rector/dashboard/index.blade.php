@extends('layouts.panel')

@section('title', 'Inicio')

@section('content')
    <div class="container-fluid p-3 p-md-4">

        <div class="dash-welcome mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <div class="small text-white-50 mb-1">
                    <i class="fas fa-circle me-1" style="font-size:.5rem;"></i> Año lectivo {{ now()->year }}
                </div>
                @php $institucionUsuario = auth()->user()->institucion; @endphp
                <h1 class="h3 fw-semibold mb-1 font-serif">Bienvenido, {{ auth()->user()->nombres }} 👋</h1>
                <p class="mb-0 text-white-50">
                    {{ auth()->user()->rolLabel }} ·
                    {{ $colegioConfiguracion->nombre_colegio ?? $institucionUsuario->nombre }} ·
                    {{ $institucionUsuario->ciudad ?? $colegioConfiguracion->ciudad }}
                </p>
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
                ['icon' => 'fa-star', 'color' => 'warning', 'label' => 'Promedio institucional', 'value' => $kpis['promedioInstitucional'] ? number_format($kpis['promedioInstitucional'], 1) : 'N/D'],
                ['icon' => 'fa-calendar-check', 'color' => 'success', 'label' => 'Asistencia promedio', 'value' => $kpis['asistenciaPromedio'] ? number_format($kpis['asistenciaPromedio'], 0).'%' : 'N/D'],
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
                            <x-empty-state icon="fas fa-chart-column"
                                           message="Aún no hay calificaciones registradas para este periodo." />
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
                            <x-empty-state icon="fas fa-file-signature"
                                           message="Aún no hay matrículas registradas."
                                           action-label="Registrar el primer estudiante"
                                           :action-url="route('registro.estudiantes.create')" />
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
                            <x-empty-state icon="fas fa-chalkboard-teacher"
                                           message="Aún no hay docentes registrados."
                                           action-label="Registrar el primer docente"
                                           :action-url="route('registro.docentes.create')" />
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
                            <x-empty-state icon="fas fa-user-graduate"
                                           message="Aún no hay estudiantes matriculados."
                                           action-label="Registrar el primer estudiante"
                                           :action-url="route('registro.estudiantes.create')" />
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
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="h6 fw-semibold mb-0"><i class="fas fa-bullhorn text-primary me-1"></i> Comunicados recientes</h2>
                            <a href="{{ route('comunicados.index') }}" class="small">Ver todos</a>
                        </div>

                        @if ($comunicadosRecientes->isEmpty())
                            <x-empty-state icon="fas fa-bullhorn"
                                           message="Aún no hay comunicados enviados."
                                           action-label="Redactar el primer comunicado"
                                           :action-url="route('comunicados.index')" class="flex-grow-1" />
                        @else
                            <ul class="list-group list-group-flush">
                                @foreach ($comunicadosRecientes as $comunicado)
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <div>
                                            <div class="fw-semibold">{{ $comunicado->titulo }}</div>
                                            <div class="small text-secondary">{{ \App\Modules\Comunicados\Models\Notificacion::TIPOS_LABELS[$comunicado->tipo_notificacion] ?? ucfirst($comunicado->tipo_notificacion) }}</div>
                                        </div>
                                        <span class="small text-secondary">{{ \Illuminate\Support\Carbon::parse($comunicado->fecha_envio)->diffForHumans() }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <h2 class="h6 fw-semibold mb-3"><i class="fas fa-chart-bar text-primary me-1"></i> Reportes institucionales</h2>
                        <p class="small text-secondary">Accesos directos a los reportes más consultados.</p>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item px-0">
                                <a href="{{ route('estadisticas') }}" class="d-flex justify-content-between align-items-center text-decoration-none">
                                    <span><i class="fas fa-chart-line text-secondary me-2"></i>Estadísticas completas</span>
                                    <i class="fas fa-chevron-right small text-secondary"></i>
                                </a>
                            </li>
                            <li class="list-group-item px-0">
                                <a href="{{ route('boletines.index') }}" class="d-flex justify-content-between align-items-center text-decoration-none">
                                    <span><i class="fas fa-file-lines text-secondary me-2"></i>Boletines por periodo</span>
                                    <i class="fas fa-chevron-right small text-secondary"></i>
                                </a>
                            </li>
                            <li class="list-group-item px-0">
                                <a href="{{ route('observaciones.index') }}" class="d-flex justify-content-between align-items-center text-decoration-none">
                                    <span><i class="fas fa-user-shield text-secondary me-2"></i>Observaciones de convivencia</span>
                                    <i class="fas fa-chevron-right small text-secondary"></i>
                                </a>
                            </li>
                        </ul>
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
