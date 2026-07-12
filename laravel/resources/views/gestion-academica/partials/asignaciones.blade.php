<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div class="row g-3 flex-grow-1 mb-0">
        @foreach ([
            ['label' => 'Total', 'value' => $resumenAsignaciones['total'], 'color' => 'primary'],
            ['label' => 'Activas', 'value' => $resumenAsignaciones['activas'], 'color' => 'success'],
            ['label' => 'Inactivas', 'value' => $resumenAsignaciones['inactivas'], 'color' => 'secondary'],
        ] as $stat)
            <div class="col-4">
                <div class="border rounded-3 p-3 bg-body-tertiary">
                    <div class="fs-4 fw-semibold text-{{ $stat['color'] }}">{{ $stat['value'] }}</div>
                    <div class="small text-secondary">{{ $stat['label'] }}</div>
                </div>
            </div>
        @endforeach
    </div>
    @if ($profesores->isEmpty() || $todasLasMaterias->isEmpty() || $todosLosCursos->isEmpty())
        <button type="button" class="btn btn-primary btn-sm text-nowrap" disabled
                title="Se necesita al menos un profesor, una materia y un curso activos">
            <i class="fas fa-plus me-1"></i> Nueva asignación
        </button>
    @else
        <button type="button" class="btn btn-primary btn-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#modalNuevaAsignacion">
            <i class="fas fa-plus me-1"></i> Nueva asignación
        </button>
    @endif
</div>

<form method="GET" action="{{ route('gestion-academica.index') }}" class="row g-2 mb-3 align-items-center" data-autosubmit-form>
    <input type="hidden" name="tab" value="asignaciones">
    <div class="col-6 col-md-4">
        <select class="form-select" name="curso" data-autosubmit>
            <option value="">Todos los cursos</option>
            @foreach ($todosLosCursos as $curso)
                <option value="{{ $curso->id_curso }}" @selected(($filtros['curso'] ?? '') == $curso->id_curso)>{{ $curso->nombre_curso }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-6 col-md-3">
        <select class="form-select" name="estado" data-autosubmit>
            <option value="">Todos los estados</option>
            <option value="activo" @selected(($filtros['estado'] ?? '') === 'activo')>Activa</option>
            <option value="inactivo" @selected(($filtros['estado'] ?? '') === 'inactivo')>Inactiva</option>
        </select>
    </div>
    <div class="col-md-2 d-grid">
        <a href="{{ route('gestion-academica.index', ['tab' => 'asignaciones']) }}" class="btn btn-outline-secondary btn-sm">Limpiar filtros</a>
    </div>
</form>

@if ($asignaciones->isEmpty())
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-diagram-project"></i></div>
        @if (($filtros['curso'] ?? '') || ($filtros['estado'] ?? ''))
            <p class="mb-0">No hay asignaciones que coincidan con los filtros.</p>
        @elseif ($profesores->isEmpty() || $todasLasMaterias->isEmpty() || $todosLosCursos->isEmpty())
            <p class="mb-0">Registra al menos un profesor, una materia y un curso activos para poder crear asignaciones.</p>
        @else
            <p class="mb-2">Aún no hay asignaciones registradas.</p>
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaAsignacion">
                Registrar la primera asignación
            </button>
        @endif
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Profesor</th>
                    <th>Materia</th>
                    <th>Curso</th>
                    <th>Año</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($asignaciones as $asignacion)
                    <tr>
                        <td>{{ trim($asignacion->profesor->usuario->nombres.' '.$asignacion->profesor->usuario->apellidos) }}</td>
                        <td>{{ $asignacion->materia->nombre_materia }}</td>
                        <td>{{ $asignacion->curso->nombre_curso }}</td>
                        <td>{{ $asignacion->anio_lectivo }}</td>
                        <td>
                            <span class="badge text-bg-{{ $asignacion->estado === 'activo' ? 'success' : 'secondary' }}">
                                {{ $asignacion->estado === 'activo' ? 'Activa' : 'Inactiva' }}
                            </span>
                        </td>
                        <td class="text-end">
                            @if ($asignacion->estado === 'activo')
                                <button type="button" class="btn btn-sm btn-outline-danger" data-desactivar
                                        data-url="{{ route('gestion-academica.asignaciones.desactivar', $asignacion) }}"
                                        data-nombre="la asignación de {{ $asignacion->materia->nombre_materia }} a {{ $asignacion->curso->nombre_curso }}"
                                        title="Desactivar">
                                    <i class="fas fa-ban"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $asignaciones->links('pagination::bootstrap-5') }}
@endif

{{-- Modal: nueva asignación --}}
<div class="modal fade" id="modalNuevaAsignacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form data-crud-form data-url="{{ route('gestion-academica.asignaciones.store') }}" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title font-serif">Nueva asignación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Profesor *</label>
                            <select class="form-select" name="id_profesor" data-feedback="err-na-profesor" required>
                                <option value="" selected disabled>Selecciona un profesor</option>
                                @foreach ($profesores as $profesor)
                                    <option value="{{ $profesor->id_profesor }}">{{ trim($profesor->usuario->nombres.' '.$profesor->usuario->apellidos) }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="err-na-profesor"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Materia *</label>
                            <select class="form-select" name="id_materia" data-feedback="err-na-materia" required>
                                <option value="" selected disabled>Selecciona una materia</option>
                                @foreach ($todasLasMaterias as $materia)
                                    <option value="{{ $materia->id_materia }}">{{ $materia->nombre_materia }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="err-na-materia"></div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Curso *</label>
                            <select class="form-select" name="id_curso" data-feedback="err-na-curso" required>
                                <option value="" selected disabled>Selecciona un curso</option>
                                @foreach ($todosLosCursos as $curso)
                                    <option value="{{ $curso->id_curso }}">{{ $curso->nombre_curso }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="err-na-curso"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Año lectivo *</label>
                            <input type="number" class="form-control" name="anio_lectivo" value="{{ date('Y') }}" data-feedback="err-na-anio" required>
                            <div class="invalid-feedback" id="err-na-anio"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
