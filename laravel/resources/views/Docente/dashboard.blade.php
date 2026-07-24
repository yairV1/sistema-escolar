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

    <h2 class="h5 fw-semibold font-serif mb-3">Mis asignaciones</h2>

    @if ($asignaciones->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-chalkboard-teacher"></i></div>
            <p class="mb-0">Todavía no tenés asignaciones activas. Contacta a coordinación académica si esto no es correcto.</p>
        </div>
    @else
        <div class="row g-3">
            @foreach ($asignaciones as $asignacion)
                <div class="col-md-6 col-xl-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h3 class="h6 fw-semibold mb-1">{{ $asignacion->materia->nombre_materia }}</h3>
                            <p class="text-secondary small mb-3">{{ $asignacion->curso->nombre_curso }}</p>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="{{ route('asistencia.show', $asignacion) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-clipboard-check me-1"></i> Asistencia
                                </a>
                                <a href="{{ route('calificaciones.asignaciones.show', $asignacion) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-marker me-1"></i> Calificaciones
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
