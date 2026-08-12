@extends('layouts.panel')

@section('title', 'Mis asignaturas')

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <h1 class="h4 fw-semibold font-serif mb-4">Mis asignaturas</h1>

        @if ($materias->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-book"></i></div>
                <p class="mb-0">Todavía no tienes asignaturas asignadas este año lectivo.</p>
            </div>
        @else
            <div class="row g-3">
                @foreach ($materias as $materia)
                    <div class="col-sm-6 col-xl-3">
                        <div class="card subject-card">
                            <div class="card-body">
                                <div class="subject-card__icon"><i class="fas {{ $materia->icono }}"></i></div>
                                <h3 class="h6 fw-semibold mb-1">{{ $materia->nombre }}</h3>
                                <div class="subject-card__teacher mb-3">
                                    <i class="fas fa-chalkboard-teacher"></i> {{ $materia->docente }}
                                </div>
                                <a href="{{ route('estudiante.notas') }}" class="btn btn-sm btn-outline-primary w-100">
                                    <i class="fas fa-star me-1"></i> Ver notas
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
