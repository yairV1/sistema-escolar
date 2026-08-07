@extends('layouts.panel')

@section('title', 'Estudiantes a cargo')

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <h1 class="h4 fw-semibold font-serif mb-4">Estudiantes a cargo</h1>

        @if ($hijos->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-user-graduate"></i></div>
                <p class="mb-0">Todavía no tienes estudiantes vinculados a tu cuenta.</p>
            </div>
        @else
            <div class="row g-3">
                @foreach ($hijos as $hijo)
                    <div class="col-md-6 col-xl-4">
                        <div class="card child-card">
                            <div class="card-body">
                                <div class="child-card__head mb-3">
                                    <span class="child-card__avatar">{{ mb_substr($hijo->nombres, 0, 1).mb_substr($hijo->apellidos, 0, 1) }}</span>
                                    <div>
                                        <h3 class="h6 fw-semibold mb-0">{{ $hijo->nombres }} {{ $hijo->apellidos }}</h3>
                                        <div class="small text-secondary">{{ $hijo->curso }} · <code>{{ $hijo->codigo }}</code></div>
                                    </div>
                                </div>

                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="{{ route('acudiente.horario', ['estudiante' => $hijo->id]) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-calendar-week me-1"></i> Horario
                                    </a>
                                    <a href="{{ route('acudiente.notas', ['estudiante' => $hijo->id]) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-star me-1"></i> Notas
                                    </a>
                                    <a href="{{ route('acudiente.asistencia', ['estudiante' => $hijo->id]) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-clipboard-check me-1"></i> Asistencia
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
