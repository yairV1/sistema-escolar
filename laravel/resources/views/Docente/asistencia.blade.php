@extends('layouts.panel')

@section('title', 'Asistencia')

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <h1 class="h4 fw-semibold font-serif mb-4">Asistencia</h1>

        @if ($asignaciones->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-clipboard-check"></i></div>
                <p class="mb-0">Todavía no tenés asignaciones activas.</p>
            </div>
        @else
            <p class="text-secondary small mb-3">Elegí un curso para tomar o revisar la asistencia.</p>
            <div class="row g-3">
                @foreach ($asignaciones as $asignacion)
                    <div class="col-md-6 col-xl-4">
                        <a href="{{ route('asistencia.show', $asignacion) }}" class="card h-100 text-decoration-none">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="kpi-icon bg-success-subtle text-success"><i class="fas fa-clipboard-check"></i></div>
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
