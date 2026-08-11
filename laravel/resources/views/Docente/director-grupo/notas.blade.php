@extends('layouts.panel')

@section('title', 'Notas definitivas — '.$curso->nombre_curso)

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
            <a href="{{ route('director-grupo.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="h4 fw-semibold font-serif mb-0">{{ $curso->nombre_curso }}</h1>
                <div class="small text-secondary">{{ $periodo->nombre_periodo }} ({{ $periodo->anio_lectivo }})</div>
            </div>
        </div>

        <p class="text-secondary small mb-3" style="max-width:70ch;">
            <i class="fas fa-circle-info me-1"></i>
            Las celdas con <span class="badge text-bg-warning-subtle text-warning-emphasis">borde amarillo</span> tienen una nota digitada manualmente —
            no se recalculan al generar boletines. Borra el valor y guarda para volver al promedio calculado.
        </p>

        @if ($estudiantes->isEmpty() || $asignaciones->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-user-graduate"></i></div>
                <p class="mb-0">Este curso no tiene estudiantes matriculados o materias activas todavía.</p>
            </div>
        @else
            <form id="formNotasDirector" data-url="{{ route('director-grupo.guardar', [$curso, $periodo]) }}">
                <div class="table-responsive">
                    <table class="table table-hover align-middle notas-director-grid">
                        <thead>
                            <tr>
                                <th style="min-width:200px;">Estudiante</th>
                                @foreach ($asignaciones as $asignacion)
                                    <th class="text-center" style="min-width:110px;">{{ $asignacion->materia->nombre_materia ?? '—' }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($estudiantes as $estudiante)
                                <tr>
                                    <td>
                                        {{ trim($estudiante->usuario->nombres.' '.$estudiante->usuario->apellidos) }}
                                        <div class="text-secondary small"><code>{{ $estudiante->codigo_estudiante }}</code></div>
                                    </td>
                                    @foreach ($asignaciones as $asignacion)
                                        @php $detalle = $notasExistentes[$estudiante->id_estudiante][$asignacion->id_asignacion] ?? null; @endphp
                                        <td>
                                            <input type="number" class="form-control form-control-sm text-center{{ $detalle?->origen === 'manual' ? ' border-warning' : '' }}"
                                                   min="0" max="5" step="0.1"
                                                   data-id-estudiante="{{ $estudiante->id_estudiante }}"
                                                   data-id-asignacion="{{ $asignacion->id_asignacion }}"
                                                   data-origen="{{ $detalle?->origen }}"
                                                   value="{{ $detalle?->nota_definitiva }}">
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-primary" id="btnGuardarNotasDirector">
                        <i class="fas fa-save me-1"></i> Guardar notas definitivas
                    </button>
                </div>
            </form>
        @endif
    </div>
@endsection

@push('scripts')
    @vite('resources/js/pages/director-grupo/notas.js')
@endpush
