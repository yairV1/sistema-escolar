@extends('layouts.panel')

@section('title', 'Calificar: '.$actividad->titulo)

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
            <a href="{{ route('calificaciones.asignaciones.show', $actividad->asignacion) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="h4 fw-semibold font-serif mb-0">{{ $actividad->titulo }}</h1>
                <div class="small text-secondary">
                    {{ $actividad->asignacion->materia->nombre_materia }} · {{ $actividad->asignacion->curso->nombre_curso }} ·
                    {{ $actividad->periodo->nombre_periodo }} · {{ $actividad->tipo->nombre_tipo }}
                </div>
            </div>
        </div>

        @if ($estudiantes->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-user-graduate"></i></div>
                <p class="mb-0">Este curso no tiene estudiantes matriculados activos todavía.</p>
            </div>
        @else
            <form id="formNotas" data-url="{{ route('calificaciones.actividades.notas.guardar', $actividad) }}">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Estudiante</th>
                                <th>Código</th>
                                <th style="width:140px;">Nota (0.0 – 5.0)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($estudiantes as $estudiante)
                                @php $notaExistente = $notasExistentes->get($estudiante->id_estudiante); @endphp
                                <tr>
                                    <td>{{ trim($estudiante->usuario->nombres.' '.$estudiante->usuario->apellidos) }}</td>
                                    <td><code>{{ $estudiante->codigo_estudiante }}</code></td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm" min="0" max="5" step="0.1"
                                               data-id-estudiante="{{ $estudiante->id_estudiante }}"
                                               value="{{ $notaExistente?->nota }}">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-primary" id="btnGuardarNotas">
                        <i class="fas fa-save me-1"></i> Guardar notas
                    </button>
                </div>
            </form>
        @endif
    </div>
@endsection

@push('scripts')
    @vite('resources/js/pages/calificaciones/notas.js')
@endpush
