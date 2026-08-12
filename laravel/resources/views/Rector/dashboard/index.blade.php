@extends('layouts.rector')

@section('title', 'Inicio')

@section('content')
    <div class="container-fluid p-3 p-md-4">

        @php $institucionUsuario = auth()->user()->institucion; @endphp
        <div class="dash-welcome dash-welcome-compact mb-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h1 class="h5 fw-semibold mb-1 font-serif">Bienvenido, {{ auth()->user()->nombres }} 👋</h1>
                <p class="mb-0 small text-white-50">
                    {{ auth()->user()->rolLabel }} ·
                    {{ $institucionUsuario->nombre ?? $colegioConfiguracion->nombre_colegio }} ·
                    {{ $institucionUsuario->ciudad ?? $colegioConfiguracion->ciudad }} ·
                    Año lectivo {{ now()->year }}
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

        <div class="row g-3 mb-3">
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

        <div class="row g-3 mb-3">
            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-body">
                        @php
                            $hoyCal = now();
                            $inicioMesCal = $hoyCal->copy()->startOfMonth();
                            $primerDiaSemanaCal = $inicioMesCal->dayOfWeekIso;
                            $diasEnMesCal = $inicioMesCal->daysInMonth;
                        @endphp
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="h5 fw-semibold mb-0 text-capitalize">{{ $hoyCal->translatedFormat('F Y') }}</h2>
                            <a href="{{ route('calendario.index') }}" class="small">Ver calendario completo</a>
                        </div>
                        <div class="cal-big-grid">
                            @foreach (['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $diaSemana)
                                <span class="cal-big-dow">{{ $diaSemana }}</span>
                            @endforeach
                            @for ($i = 1; $i < $primerDiaSemanaCal; $i++)
                                <span></span>
                            @endfor
                            @for ($dia = 1; $dia <= $diasEnMesCal; $dia++)
                                <span class="cal-big-day {{ $dia === $hoyCal->day ? 'is-today' : '' }}">{{ $dia }}</span>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5 d-flex flex-column gap-3">
                @php
                    $inicialesPerfil = collect(explode(' ', trim(auth()->user()->nombres.' '.auth()->user()->apellidos)))
                        ->filter()
                        ->map(fn ($parte) => mb_strtoupper(mb_substr($parte, 0, 1)))
                        ->join('');
                    $inicialesPerfil = mb_substr($inicialesPerfil, 0, 2) ?: '?';
                @endphp
                <div class="card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="td-avatar flex-shrink-0" style="width:52px;height:52px;font-size:1rem;">
                            @if (auth()->user()->fotoPerfilUrl)
                                <img src="{{ auth()->user()->fotoPerfilUrl }}" alt="{{ auth()->user()->nombres }}">
                            @else
                                {{ $inicialesPerfil }}
                            @endif
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="fw-semibold text-truncate">{{ trim(auth()->user()->nombres.' '.auth()->user()->apellidos) }}</div>
                            <div class="small text-secondary text-truncate">{{ auth()->user()->rolLabel }} · {{ $institucionUsuario->nombre ?? $colegioConfiguracion->nombre_colegio }}</div>
                        </div>
                        <a href="{{ route('perfil.show') }}" class="btn btn-sm btn-outline-secondary flex-shrink-0">Ver perfil</a>
                    </div>
                </div>

                <div class="card flex-grow-1">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="h6 fw-semibold mb-0"><i class="fas fa-calendar-day text-primary me-1"></i> Próximos eventos</h2>
                            <a href="{{ route('calendario.index') }}" class="small">Ver todo</a>
                        </div>

                        @if ($proximosEventos->isEmpty())
                            <x-empty-state icon="fas fa-calendar-day" message="No hay eventos próximos programados." />
                        @else
                            <ul class="list-group list-group-flush">
                                @foreach ($proximosEventos as $evento)
                                    @php
                                        $colorEvento = $evento->color_override ?? $evento->categoria->color ?? 'var(--sb-primary)';
                                        $fechaEvento = $evento->fecha_inicio;
                                        $etiquetaFecha = $fechaEvento->isToday() ? 'Hoy' : ($fechaEvento->isTomorrow() ? 'Mañana' : $fechaEvento->translatedFormat('d M'));
                                    @endphp
                                    <li class="list-group-item d-flex align-items-center gap-3 px-0">
                                        <span class="rounded-circle flex-shrink-0" style="width:9px;height:9px;background:{{ $colorEvento }};"></span>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold">{{ $evento->titulo }}</div>
                                            <div class="small text-secondary">{{ $evento->categoria->nombre ?? 'Evento' }}</div>
                                        </div>
                                        <div class="text-end small text-secondary">
                                            <div class="fw-semibold">{{ $etiquetaFecha }}</div>
                                            <div>{{ $evento->todo_el_dia ? 'Todo el día' : substr($evento->hora_inicio ?? '', 0, 5) }}</div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="h6 fw-semibold mb-0"><i class="fas fa-graduation-cap text-primary me-1"></i> Cursos</h2>
                            <a href="{{ route('gestion-academica.cursos.index') }}" class="small">Ver todos</a>
                        </div>

                        @if ($promedioPorGrado->isEmpty())
                            <x-empty-state icon="fas fa-graduation-cap"
                                           message="Aún no hay cursos con calificaciones registradas para este periodo." />
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr class="text-secondary small">
                                            <th class="fw-semibold">Curso</th>
                                            <th class="fw-semibold text-end">Estudiantes</th>
                                            <th class="fw-semibold text-end">Promedio</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($promedioPorGrado as $grado)
                                            <tr>
                                                <td class="fw-semibold">{{ $grado->nombre_curso }}</td>
                                                <td class="text-end">{{ $grado->total_estudiantes }}</td>
                                                <td class="text-end">{{ number_format($grado->promedio, 1) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                        <h2 class="h6 fw-semibold mb-3 align-self-start"><i class="fas fa-calendar-check text-primary me-1"></i> Asistencia promedio</h2>
                        @if ($kpis['asistenciaPromedio'])
                            <x-stat-ring :value="$kpis['asistenciaPromedio']" label="Asistencia institucional" class="my-2" />
                        @else
                            <x-empty-state icon="fas fa-calendar-check" message="Aún no hay registros de asistencia." class="flex-grow-1" />
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h6 fw-semibold mb-2"><i class="fas fa-file-signature text-primary me-1"></i> Matrículas recientes</h2>

                        @if ($matriculasRecientes->isEmpty())
                            <x-empty-state icon="fas fa-file-signature"
                                           message="Aún no hay matrículas registradas."
                                           action-label="Registrar el primer estudiante"
                                           :action-url="route('registro.estudiantes.create')" />
                        @else
                            <ul class="list-group list-group-flush dash-list-scroll">
                                @foreach ($matriculasRecientes as $matricula)
                                    <li class="list-group-item px-0">
                                        <div class="fw-semibold text-truncate">
                                            {{ trim($matricula->estudiante->usuario->nombres.' '.$matricula->estudiante->usuario->apellidos) }}
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="small text-secondary text-truncate">{{ $matricula->curso->nombre_curso ?? '—' }}</span>
                                            <span class="badge text-bg-{{ $matricula->estado_matricula === 'activa' ? 'success' : ($matricula->estado_matricula === 'pendiente' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst($matricula->estado_matricula) }}
                                            </span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h6 fw-semibold mb-2"><i class="fas fa-chalkboard-teacher text-primary me-1"></i> Docentes activos</h2>

                        @if ($docentesLista->isEmpty())
                            <x-empty-state icon="fas fa-chalkboard-teacher"
                                           message="Aún no hay docentes registrados."
                                           action-label="Registrar el primer docente"
                                           :action-url="route('registro.docentes.create')" />
                        @else
                            <ul class="list-group list-group-flush dash-list-scroll">
                                @foreach ($docentesLista as $docente)
                                    <li class="list-group-item px-0">
                                        <div class="fw-semibold text-truncate">
                                            {{ trim($docente->usuario->nombres.' '.$docente->usuario->apellidos) }}
                                        </div>
                                        <div class="small text-secondary text-truncate">
                                            {{ $docente->asignaciones->pluck('materia.nombre_materia')->filter()->unique()->join(', ') ?: 'Sin asignaturas' }}
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h6 fw-semibold mb-2"><i class="fas fa-user-graduate text-primary me-1"></i> Estudiantes</h2>

                        @if ($estudiantesMuestra->isEmpty())
                            <x-empty-state icon="fas fa-user-graduate"
                                           message="Aún no hay estudiantes matriculados."
                                           action-label="Registrar el primer estudiante"
                                           :action-url="route('registro.estudiantes.create')" />
                        @else
                            <ul class="list-group list-group-flush dash-list-scroll">
                                @foreach ($estudiantesMuestra as $estudiante)
                                    <li class="list-group-item px-0">
                                        <div class="fw-semibold text-truncate">
                                            {{ trim($estudiante->usuario->nombres.' '.$estudiante->usuario->apellidos) }}
                                        </div>
                                        <div class="small text-secondary text-truncate">
                                            {{ $estudiante->matriculas->first()->curso->nombre_curso ?? 'Sin curso asignado' }}
                                        </div>
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
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h2 class="h6 fw-semibold mb-0"><i class="fas fa-bullhorn text-primary me-1"></i> Comunicados recientes</h2>
                            <a href="{{ route('comunicados.index') }}" class="small">Ver todos</a>
                        </div>

                        @if ($comunicadosRecientes->isEmpty())
                            <x-empty-state icon="fas fa-bullhorn"
                                           message="Aún no hay comunicados enviados."
                                           action-label="Redactar el primer comunicado"
                                           :action-url="route('comunicados.index')" class="flex-grow-1" />
                        @else
                            <ul class="list-group list-group-flush dash-list-scroll">
                                @foreach ($comunicadosRecientes as $comunicado)
                                    <li class="list-group-item px-0">
                                        <div class="fw-semibold text-truncate">{{ $comunicado->titulo }}</div>
                                        <div class="d-flex justify-content-between align-items-center small text-secondary">
                                            <span class="text-truncate">{{ \App\Modules\Comunicados\Models\Notificacion::TIPOS_LABELS[$comunicado->tipo_notificacion] ?? ucfirst($comunicado->tipo_notificacion) }}</span>
                                            <span class="flex-shrink-0 ms-2">{{ \Illuminate\Support\Carbon::parse($comunicado->fecha_envio)->diffForHumans(null, true) }}</span>
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
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h2 class="h6 fw-semibold mb-3"><i class="fas fa-chart-bar text-primary me-1"></i> Reportes institucionales</h2>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <a href="{{ route('estadisticas') }}" class="text-decoration-none">
                                    <div class="kpi-card bg-body-tertiary border h-100">
                                        <div class="kpi-icon bg-primary-subtle text-primary mb-2">
                                            <i class="fas fa-chart-line"></i>
                                        </div>
                                        <div class="fw-semibold">Estadísticas completas</div>
                                        <div class="kpi-label">Indicadores institucionales</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="{{ route('boletines.index') }}" class="text-decoration-none">
                                    <div class="kpi-card bg-body-tertiary border h-100">
                                        <div class="kpi-icon bg-info-subtle text-info mb-2">
                                            <i class="fas fa-file-lines"></i>
                                        </div>
                                        <div class="fw-semibold">Boletines por periodo</div>
                                        <div class="kpi-label">Generar y consultar</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="{{ route('observaciones.index') }}" class="text-decoration-none">
                                    <div class="kpi-card bg-body-tertiary border h-100">
                                        <div class="kpi-icon bg-warning-subtle text-warning mb-2">
                                            <i class="fas fa-user-shield"></i>
                                        </div>
                                        <div class="fw-semibold">Observaciones de convivencia</div>
                                        <div class="kpi-label">Seguimiento disciplinario</div>
                                    </div>
                                </a>
                            </div>
                        </div>
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
