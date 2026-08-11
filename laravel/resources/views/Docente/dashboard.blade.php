@extends('layouts.panel')

@section('title', 'Mis cursos')

@section('content')
<div class="container-fluid p-3 p-md-4">

    <div class="dash-welcome mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h1 class="h3 fw-semibold mb-1 font-serif">Bienvenido, {{ auth()->user()->nombres }} 👋</h1>
            <p class="mb-0 text-white-50">{{ auth()->user()->rolLabel }} · Año lectivo {{ now()->year }}</p>
        </div>
    </div>

    {{-- ---------- Próxima clase: lo primero que un docente quiere saber al entrar ---------- --}}
    @php
        $diasCompletos = ['lunes' => 'Lunes', 'martes' => 'Martes', 'miercoles' => 'Miércoles', 'jueves' => 'Jueves', 'viernes' => 'Viernes', 'sabado' => 'Sábado'];
        $diaHoySlug = match (now()->dayOfWeek) { 1, 2, 3, 4, 5, 6 => array_keys($diasCompletos)[now()->dayOfWeek - 1], default => null };
    @endphp
    <div class="card mb-4 border-0" style="background: linear-gradient(120deg, var(--cs-verde-claro) 0%, transparent 70%);">
        <div class="card-body d-flex flex-wrap align-items-center gap-3">
            <div class="kpi-icon bg-primary text-white" style="width:52px;height:52px;font-size:1.2rem;">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            @if ($proximaClase)
                <div class="flex-grow-1">
                    <p class="text-secondary small mb-1 text-uppercase fw-semibold" style="letter-spacing:.04em;">
                        {{ $proximaClase->dia_semana === $diaHoySlug ? 'Tu próxima clase es hoy' : 'Tu próxima clase' }}
                    </p>
                    <h2 class="h5 fw-semibold mb-0">
                        {{ $proximaClase->asignacion->materia->nombre_materia }}
                        <span class="text-secondary fw-normal">· {{ $proximaClase->asignacion->curso->nombre_curso }}</span>
                    </h2>
                </div>
                <div class="text-end">
                    <div class="h5 fw-semibold mb-0" style="font-variant-numeric: tabular-nums;">{{ substr($proximaClase->hora_inicio, 0, 5) }}–{{ substr($proximaClase->hora_fin, 0, 5) }}</div>
                    <div class="text-secondary small">
                        {{ $diasCompletos[$proximaClase->dia_semana] }}
                        @if ($proximaClase->salon) · Salón {{ $proximaClase->salon }} @endif
                    </div>
                </div>
            @else
                <div class="flex-grow-1">
                    <p class="text-secondary small mb-0">Todavía no tenés bloques de horario cargados.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- ---------- KPIs: los numéricos simples se quedan como número, promedio/asistencia suman una barra ---------- --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="kpi-card bg-body-tertiary border h-100">
                <div class="kpi-icon bg-primary-subtle text-primary mb-2"><i class="fas fa-chalkboard"></i></div>
                <div class="kpi-value">{{ $resumen['grupos'] }}</div>
                <div class="kpi-label">Grupos activos</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="kpi-card bg-body-tertiary border h-100">
                <div class="kpi-icon bg-info-subtle text-info mb-2"><i class="fas fa-user-graduate"></i></div>
                <div class="kpi-value">{{ $resumen['estudiantes'] }}</div>
                <div class="kpi-label">Estudiantes a cargo</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="kpi-card bg-body-tertiary border h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-icon bg-warning-subtle text-warning mb-2"><i class="fas fa-star"></i></div>
                    <span class="kpi-value">{{ $resumen['promedio'] ?: '—' }}</span>
                </div>
                <div class="kpi-label mb-2">Promedio general</div>
                @if ($resumen['promedio'])
                    <div class="progress" style="height:5px;">
                        <div class="progress-bar bg-warning" style="width: {{ min(100, $resumen['promedio'] / 5 * 100) }}%"></div>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="kpi-card bg-body-tertiary border h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-icon bg-success-subtle text-success mb-2"><i class="fas fa-clipboard-check"></i></div>
                    <span class="kpi-value">{{ $resumen['asistencia'] ? $resumen['asistencia'].'%' : '—' }}</span>
                </div>
                <div class="kpi-label mb-2">Asistencia promedio</div>
                @if ($resumen['asistencia'])
                    <div class="progress" style="height:5px;">
                        <div class="progress-bar bg-success" style="width: {{ $resumen['asistencia'] }}%"></div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- ---------- Mis asignaciones ---------- --}}
        <div class="col-lg-7">
            <h2 class="h5 fw-semibold font-serif mb-3">Mis asignaciones</h2>

            @if ($asignaciones->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                    <p class="mb-0">Todavía no tenés asignaciones activas. Contacta a coordinación académica si esto no es correcto.</p>
                </div>
            @else
                <p class="text-secondary small mb-3">
                    Para tomar asistencia o calificar, usá "Asistencia" y "Calificaciones" en el menú.
                </p>
                <div class="row g-3">
                    @foreach ($asignaciones as $asignacion)
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body d-flex align-items-center gap-3">
                                    <div class="kpi-icon bg-primary-subtle text-primary"><i class="fas fa-chalkboard"></i></div>
                                    <div class="flex-grow-1">
                                        <h3 class="h6 fw-semibold mb-1">{{ $asignacion->materia->nombre_materia }}</h3>
                                        <p class="text-secondary small mb-0">{{ $asignacion->curso->nombre_curso }}</p>
                                    </div>
                                    @if ($asignacion->en_riesgo > 0)
                                        <span class="badge text-bg-danger text-nowrap">{{ $asignacion->en_riesgo }} en riesgo</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ---------- Comunicados recientes ---------- --}}
        <div class="col-lg-5">
            <h2 class="h5 fw-semibold font-serif mb-3">Comunicados recientes</h2>
            <div class="card">
                <div class="card-body">
                    @if ($comunicadosRecientes->isEmpty())
                        <div class="empty-state py-3">
                            <div class="empty-icon"><i class="fas fa-bullhorn"></i></div>
                            <p class="mb-0">No has recibido comunicados todavía.</p>
                        </div>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($comunicadosRecientes as $comunicado)
                                <li class="list-group-item px-0 d-flex justify-content-between align-items-start gap-2">
                                    <div>
                                        @unless ($comunicado->leida)
                                            <span class="bg-primary rounded-circle d-inline-block me-1" style="width:6px;height:6px;"></span>
                                        @endunless
                                        <span class="fw-semibold">{{ $comunicado->titulo }}</span>
                                        <p class="text-secondary small mb-0">{{ \Illuminate\Support\Str::limit($comunicado->mensaje, 60) }}</p>
                                    </div>
                                    <span class="text-secondary small text-nowrap">{{ $comunicado->fecha_envio->format('d/m') }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <a href="{{ route('docente.comunicados') }}" class="btn btn-sm btn-outline-primary w-100 mt-2">Ver todos</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
