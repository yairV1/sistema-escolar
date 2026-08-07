@extends('layouts.panel')

@section('title', 'Inicio')

@section('content')
    <div class="container-fluid p-3 p-md-4">

        <div class="dash-welcome mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <span class="role-badge role-badge--estudiante mb-2"><i class="fas fa-user-graduate"></i> Estudiante</span>
                <h1 class="h3 fw-semibold mb-1 font-serif">Hola, {{ auth()->user()->nombres }} 👋</h1>
                <p class="mb-0 text-white-50">{{ ucfirst(now()->locale('es')->isoFormat('dddd, D [de] MMMM')) }}</p>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-4">
                <div class="kpi-card bg-body-tertiary border h-100">
                    <div class="kpi-icon bg-primary-subtle text-primary mb-2"><i class="fas fa-star"></i></div>
                    <div class="kpi-value">{{ number_format($promedioGeneral, 1) }}</div>
                    <div class="kpi-label">Promedio general</div>
                </div>
            </div>
            <div class="col-6 col-lg-4">
                <div class="kpi-card bg-body-tertiary border h-100">
                    <div class="kpi-icon bg-warning-subtle text-warning mb-2"><i class="fas fa-list-check"></i></div>
                    <div class="kpi-value">{{ $tareasPendientes->count() }}</div>
                    <div class="kpi-label">Tareas pendientes</div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="kpi-card bg-body-tertiary border h-100">
                    <div class="kpi-icon bg-info-subtle text-info mb-2"><i class="fas fa-clock"></i></div>
                    <div class="kpi-value">
                        {{ $clasesHoy->isNotEmpty() ? substr($clasesHoy->first()->hora_inicio, 0, 5) : '—' }}
                    </div>
                    <div class="kpi-label">
                        {{ $clasesHoy->isNotEmpty() ? 'Próxima clase: '.$clasesHoy->first()->asignacion->materia->nombre_materia : 'Sin clases hoy' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h6 fw-semibold mb-3"><i class="fas fa-calendar-day text-primary me-1"></i> Clases de hoy</h2>

                        @if ($clasesHoy->isEmpty())
                            <div class="empty-state">
                                <div class="empty-icon"><i class="fas fa-mug-hot"></i></div>
                                <p class="mb-0">No tienes clases programadas hoy.</p>
                            </div>
                        @else
                            <ul class="list-group list-group-flush">
                                @foreach ($clasesHoy as $clase)
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <div>
                                            <div class="fw-semibold">{{ $clase->asignacion->materia->nombre_materia }}</div>
                                            <div class="small text-secondary">{{ trim($clase->asignacion->profesor->usuario->nombres.' '.$clase->asignacion->profesor->usuario->apellidos) }} · {{ $clase->salon }}</div>
                                        </div>
                                        <span class="badge text-bg-secondary">{{ substr($clase->hora_inicio, 0, 5) }}–{{ substr($clase->hora_fin, 0, 5) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h6 fw-semibold mb-3"><i class="fas fa-list-check text-primary me-1"></i> Entregas pendientes</h2>

                        @if ($tareasPendientes->isEmpty())
                            <div class="empty-state">
                                <div class="empty-icon"><i class="fas fa-circle-check"></i></div>
                                <p class="mb-0">Estás al día, no tienes entregas pendientes.</p>
                            </div>
                        @else
                            <ul class="list-group list-group-flush">
                                @foreach ($tareasPendientes as $tarea)
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <div>
                                            <div class="fw-semibold">{{ $tarea->titulo }}</div>
                                            <div class="small text-secondary">{{ $tarea->materia }}</div>
                                        </div>
                                        <span class="small text-secondary">{{ $tarea->fecha_entrega }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mt-1">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="h6 fw-semibold mb-0"><i class="fas fa-bullhorn text-primary me-1"></i> Comunicados recientes</h2>
                            <a href="{{ route('estudiante.comunicados') }}" class="small">Ver todos</a>
                        </div>

                        @if ($comunicados->isEmpty())
                            <div class="empty-state">
                                <div class="empty-icon"><i class="fas fa-bullhorn"></i></div>
                                <p class="mb-0">No hay comunicados por ahora.</p>
                            </div>
                        @else
                            <div class="announcement-list">
                                @foreach ($comunicados as $comunicado)
                                    <a href="{{ route('estudiante.comunicados') }}" class="announcement-item {{ $comunicado->leido ? '' : 'announcement-item--unread' }}">
                                        @if (! $comunicado->leido)
                                            <span class="announcement-item__dot"></span>
                                        @endif
                                        <div>
                                            <div class="announcement-item__title">{{ $comunicado->titulo }}</div>
                                            <div class="announcement-item__body">{{ \Illuminate\Support\Str::limit($comunicado->mensaje, 90) }}</div>
                                            <div class="announcement-item__meta">{{ $comunicado->fecha }}</div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
