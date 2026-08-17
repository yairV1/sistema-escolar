@extends('layouts.docente')

@section('title', __('Mis cursos'))

@section('content')
<div class="container-fluid p-3 p-md-4">

    <div class="dash-welcome mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h1 class="h3 fw-semibold mb-1 font-serif">{{ __('Bienvenido, :nombre', ['nombre' => auth()->user()->nombres]) }} 👋</h1>
            <p class="mb-0 text-white-50">{{ __(auth()->user()->rolLabel) }} · {{ __('Año lectivo :anio', ['anio' => now()->year]) }}</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-4">
            <div class="kpi-card bg-body-tertiary border h-100">
                <div class="kpi-icon bg-primary-subtle text-primary mb-2">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div class="kpi-value">{{ $kpis['cursos'] }}</div>
                <div class="kpi-label">{{ __('Cursos a cargo') }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-4">
            <div class="kpi-card bg-body-tertiary border h-100">
                <div class="kpi-icon bg-info-subtle text-info mb-2">
                    <i class="fas fa-book"></i>
                </div>
                <div class="kpi-value">{{ $kpis['materias'] }}</div>
                <div class="kpi-label">{{ __('Asignaturas dictadas') }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-4">
            <div class="kpi-card bg-body-tertiary border h-100">
                <div class="kpi-icon bg-warning-subtle text-warning mb-2">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="kpi-value">{{ $kpis['estudiantes'] }}</div>
                <div class="kpi-label">{{ __('Estudiantes en total') }}</div>
            </div>
        </div>
    </div>

    <h2 class="h5 fw-semibold font-serif mb-3">{{ __('Mis asignaciones') }}</h2>

    @if ($asignaciones->isEmpty())
        <x-empty-state icon="fas fa-chalkboard-teacher"
                       :message="__('Todavía no tenés asignaciones activas. Contacta a coordinación académica si esto no es correcto.')" />
    @else
        @php
            // Mismo mecanismo que Rector/gestion-academica/partials/horario-grid.blade.php
            // (ver comentario ahí): cada materia se ancla a un slot fijo de la paleta
            // categórica (--cat-1..9) por id_materia, con overrides por nombre para las
            // más comunes — así el color de una materia es el mismo acá que en el
            // horario/calendario, sin importar la vista.
            $normalizar = fn ($texto) => str_replace(
                ['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'],
                mb_strtolower(trim($texto))
            );
            $patronesColor = [
                'educacion fisica' => 'var(--cat-2)',
                'matematic' => 'var(--cat-1)',
                'espanol' => 'var(--cat-6)',
                'lengua castellana' => 'var(--cat-6)',
                'quimic' => 'var(--cat-4)',
                'religion' => 'var(--cat-7)',
                'fisica' => 'var(--cat-8)',
                'ingles' => 'var(--cat-9)',
            ];
            $totalSlots = 9;
            $colorDeMateria = function ($asignacion) use ($normalizar, $patronesColor, $totalSlots) {
                $nombreNormalizado = $normalizar($asignacion->materia->nombre_materia);
                foreach ($patronesColor as $patron => $variable) {
                    if (str_contains($nombreNormalizado, $patron)) {
                        return $variable;
                    }
                }

                return 'var(--cat-'.(($asignacion->id_materia % $totalSlots) + 1).')';
            };
        @endphp

        <div class="row g-3">
            @foreach ($asignaciones as $asignacion)
                @php $colorMateria = $colorDeMateria($asignacion); @endphp
                <div class="col-md-6 col-xl-4">
                    <div class="dc-course-card">
                        <div class="dc-course-card-band" style="background: {{ $colorMateria }};">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <div class="dc-course-card-body">
                            <div class="dc-course-card-title">{{ $asignacion->materia->nombre_materia }}</div>
                            <div class="dc-course-card-sub">{{ $asignacion->curso->nombre_curso }}</div>
                            <div class="dc-course-card-meta">
                                <i class="fas fa-user-graduate"></i>
                                {{ $asignacion->estudiantesActivos }} {{ Str::plural('estudiante', $asignacion->estudiantesActivos) }}
                            </div>
                            <div class="dc-course-card-actions">
                                <a href="{{ route('asistencia.show', $asignacion) }}" class="btn btn-sm btn-outline-primary flex-fill">
                                    <i class="fas fa-clipboard-check me-1"></i> {{ __('Asistencia') }}
                                </a>
                                <a href="{{ route('calificaciones.asignaciones.show', $asignacion) }}" class="btn btn-sm btn-outline-primary flex-fill">
                                    <i class="fas fa-marker me-1"></i> {{ __('Calificaciones') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
