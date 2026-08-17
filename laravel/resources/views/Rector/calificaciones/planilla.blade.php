@extends(in_array(auth()->user()?->rolSlug, ['admin', 'rector']) ? 'layouts.rector' : (auth()->user()?->rolSlug === 'docente' ? 'layouts.docente' : 'layouts.panel'))

@section('title', 'Planilla — '.$asignacion->materia->nombre_materia.' — '.$asignacion->curso->nombre_curso)

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
            <a href="{{ route('calificaciones.asignaciones.show', $asignacion) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="h4 fw-semibold font-serif mb-0">Planilla — {{ $asignacion->materia->nombre_materia }}</h1>
                <div class="small text-secondary">
                    {{ $asignacion->curso->nombre_curso }} ·
                    {{ trim($asignacion->profesor->usuario->nombres.' '.$asignacion->profesor->usuario->apellidos) }} ·
                    {{ $asignacion->anio_lectivo }}
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('calificaciones.asignaciones.planilla', $asignacion) }}" class="row g-2 mb-3 align-items-center" data-autosubmit-form>
            <div class="col-6 col-md-4">
                <select class="form-select" name="periodo" data-autosubmit>
                    <option value="">Todos los periodos</option>
                    @foreach ($periodos as $periodo)
                        <option value="{{ $periodo->id_periodo }}" @selected($filtroPeriodo == $periodo->id_periodo)>{{ $periodo->nombre_periodo }} ({{ $periodo->anio_lectivo }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3 d-grid">
                <a href="{{ route('calificaciones.asignaciones.planilla', $asignacion) }}" class="btn btn-outline-secondary btn-sm">Limpiar filtro</a>
            </div>
            <div class="col-12 col-md-5 d-flex justify-content-md-end">
                <a href="{{ route('calificaciones.asignaciones.planilla.exportar', array_filter(['asignacion' => $asignacion->id_asignacion, 'periodo' => $filtroPeriodo])) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-file-excel me-1"></i> Exportar a Excel
                </a>
            </div>
        </form>

        @if ($actividades->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-table"></i></div>
                <p class="mb-0">
                    @if ($filtroPeriodo)
                        No hay actividades activas para el periodo seleccionado.
                    @else
                        Aún no hay actividades registradas para esta asignación.
                    @endif
                </p>
            </div>
        @elseif ($estudiantes->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-user-graduate"></i></div>
                <p class="mb-0">Este curso no tiene estudiantes matriculados activos todavía.</p>
            </div>
        @else
            @unless ($filtroPeriodo)
                <p class="small text-secondary mb-2">
                    <i class="fas fa-circle-info me-1"></i> Elegí un periodo puntual para ver la columna "Definitiva".
                </p>
            @endunless
            <form id="formPlanilla" novalidate>
                <div class="table-responsive planilla-scroll">
                    <table class="table table-hover align-middle table-bordered mb-0">
                        <thead>
                            <tr>
                                <th class="planilla-col-fija bg-body">Estudiante</th>
                                @foreach ($actividades as $actividad)
                                    <th class="text-center" style="min-width:130px;"
                                        data-id-actividad="{{ $actividad->id_actividad }}"
                                        data-notas-url="{{ route('calificaciones.actividades.notas.guardar', $actividad) }}"
                                        data-titulo="{{ $actividad->titulo }}">
                                        {{ $actividad->titulo }}
                                        <div class="small text-secondary fw-normal">
                                            {{ rtrim(rtrim(number_format($actividad->porcentaje, 2), '0'), '.') }}% · {{ $actividad->periodo->nombre_periodo }}
                                        </div>
                                    </th>
                                @endforeach
                                @if ($filtroPeriodo)
                                    <th class="text-center">Definitiva</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($estudiantes as $estudiante)
                                <tr>
                                    <td class="planilla-col-fija bg-body">
                                        <div class="fw-semibold">{{ trim($estudiante->usuario->nombres.' '.$estudiante->usuario->apellidos) }}</div>
                                        <div class="small text-secondary"><code>{{ $estudiante->codigo_estudiante }}</code></div>
                                    </td>
                                    @foreach ($actividades as $actividad)
                                        @php $valor = $notas[$estudiante->id_estudiante][$actividad->id_actividad] ?? null; @endphp
                                        <td class="text-center">
                                            <input type="number" class="form-control form-control-sm text-center mx-auto" style="max-width:90px;"
                                                   min="0" max="5" step="0.1"
                                                   data-id-estudiante="{{ $estudiante->id_estudiante }}"
                                                   data-id-actividad="{{ $actividad->id_actividad }}"
                                                   value="{{ $valor }}">
                                        </td>
                                    @endforeach
                                    @if ($filtroPeriodo)
                                        <td class="text-center fw-bold">
                                            {{ $definitivas[$estudiante->id_estudiante] !== null ? number_format($definitivas[$estudiante->id_estudiante], 2) : '—' }}
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-primary" id="btnGuardarPlanilla">
                        <i class="fas fa-save me-1"></i> Guardar cambios
                    </button>
                </div>
            </form>
        @endif
    </div>
@endsection

@push('styles')
    <style>
        .planilla-scroll { max-height: 70vh; overflow: auto; }
        .planilla-col-fija {
            position: sticky;
            left: 0;
            z-index: 2;
            box-shadow: 2px 0 0 rgba(0, 0, 0, .06);
        }
        .planilla-scroll thead th { position: sticky; top: 0; z-index: 3; background: var(--bs-body-bg); }
        .planilla-scroll thead th.planilla-col-fija { z-index: 4; }
    </style>
@endpush

@push('scripts')
    @vite('resources/js/pages/calificaciones/planilla.js')
@endpush
