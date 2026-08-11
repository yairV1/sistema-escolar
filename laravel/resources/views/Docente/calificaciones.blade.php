@extends('layouts.panel')

@section('title', 'Calificaciones')

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <h1 class="h4 fw-semibold font-serif mb-4">Calificaciones</h1>

        @if ($asignaciones->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-marker"></i></div>
                <p class="mb-0">Todavía no tenés asignaciones activas.</p>
            </div>
        @else
            <p class="text-secondary small mb-3">Elegí un curso para calificar actividades u observaciones.</p>
            <div class="row g-3">
                @foreach ($asignaciones as $asignacion)
                    <div class="col-md-6 col-xl-4">
                        <a href="{{ route('calificaciones.asignaciones.show', $asignacion) }}" class="card h-100 text-decoration-none">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="kpi-icon bg-primary-subtle text-primary"><i class="fas fa-marker"></i></div>
                                <div>
                                    <h2 class="h6 fw-semibold mb-1 text-body">{{ $asignacion->materia->nombre_materia }}</h2>
                                    <p class="text-secondary small mb-0">{{ $asignacion->curso->nombre_curso }}</p>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
