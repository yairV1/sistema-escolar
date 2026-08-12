<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div class="row g-3 flex-grow-1 mb-0">
        @foreach ([
            ['icon' => 'fa-book', 'label' => 'Total', 'value' => $resumenMaterias['total'], 'color' => 'primary'],
            ['icon' => 'fa-circle-check', 'label' => 'Activas', 'value' => $resumenMaterias['activas'], 'color' => 'success'],
            ['icon' => 'fa-ban', 'label' => 'Inactivas', 'value' => $resumenMaterias['inactivas'], 'color' => 'secondary'],
        ] as $stat)
            <div class="col-4">
                <div class="kpi-card bg-body-tertiary border h-100">
                    <div class="kpi-icon bg-{{ $stat['color'] }}-subtle text-{{ $stat['color'] }} mb-2">
                        <i class="fas {{ $stat['icon'] }}"></i>
                    </div>
                    <div class="kpi-value">{{ $stat['value'] }}</div>
                    <div class="kpi-label">{{ $stat['label'] }}</div>
                </div>
            </div>
        @endforeach
    </div>
    <button type="button" class="btn btn-primary btn-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#modalNuevaMateria">
        <i class="fas fa-plus me-1"></i> Nueva asignatura
    </button>
</div>

<form method="GET" action="{{ route('gestion-academica.materias.index') }}" class="row g-2 mb-3 align-items-center" data-autosubmit-form>
    <div class="col-12 col-md-5">
        <div class="input-group">
            <span class="input-group-text bg-body"><i class="fas fa-search"></i></span>
            <input type="text" class="form-control" name="q" value="{{ $filtros['q'] ?? '' }}"
                   placeholder="Buscar por nombre..." data-autosubmit-debounce>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <select class="form-select" name="estado" data-autosubmit>
            <option value="">Todos los estados</option>
            <option value="activo" @selected(($filtros['estado'] ?? '') === 'activo')>Activa</option>
            <option value="inactivo" @selected(($filtros['estado'] ?? '') === 'inactivo')>Inactiva</option>
        </select>
    </div>
    <div class="col-md-2 d-grid">
        <a href="{{ route('gestion-academica.materias.index') }}" class="btn btn-outline-secondary btn-sm">Limpiar filtros</a>
    </div>
</form>

@if ($materias->isEmpty())
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-book"></i></div>
        @if (($filtros['q'] ?? '') || ($filtros['estado'] ?? ''))
            <p class="mb-0">No hay asignaturas que coincidan con los filtros.</p>
        @else
            <p class="mb-2">Aún no hay asignaturas registradas.</p>
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaMateria">
                Registrar la primera asignatura
            </button>
        @endif
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Asignatura</th>
                    <th>Intensidad horaria</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($materias as $materia)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $materia->nombre_materia }}</div>
                            @if ($materia->padre)
                                <div class="small text-secondary">↳ Sub-asignatura de {{ $materia->padre->nombre_materia }}</div>
                            @elseif ($materia->descripcion)
                                <div class="small text-secondary">{{ \Illuminate\Support\Str::limit($materia->descripcion, 80) }}</div>
                            @endif
                        </td>
                        <td>{{ $materia->intensidad_horaria }} h/semana</td>
                        <td>
                            <span class="badge text-bg-{{ $materia->estado === 'activo' ? 'success' : 'secondary' }}">
                                {{ $materia->estado === 'activo' ? 'Activa' : 'Inactiva' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-secondary" title="Editar"
                                    data-bs-toggle="modal" data-bs-target="#modalEditarMateria{{ $materia->id_materia }}">
                                <i class="fas fa-pen"></i>
                            </button>
                            @if ($materia->estado === 'activo')
                                <button type="button" class="btn btn-sm btn-outline-danger" data-desactivar
                                        data-url="{{ route('gestion-academica.materias.desactivar', $materia) }}"
                                        data-nombre="{{ $materia->nombre_materia }}"
                                        title="Desactivar">
                                    <i class="fas fa-ban"></i>
                                </button>
                            @else
                                <button type="button" class="btn btn-sm btn-outline-success" data-activar
                                        data-url="{{ route('gestion-academica.materias.activar', $materia) }}"
                                        data-nombre="{{ $materia->nombre_materia }}"
                                        title="Reactivar">
                                    <i class="fas fa-rotate-left"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $materias->links('pagination::bootstrap-5') }}
@endif

@push('modals')
{{-- Modal: nueva asignatura --}}
<div class="modal fade" id="modalNuevaMateria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form data-crud-form data-url="{{ route('gestion-academica.materias.store') }}" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title font-serif">Nueva asignatura</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre *</label>
                        <input type="text" class="form-control" name="nombre_materia" data-feedback="err-nm-nombre" required>
                        <div class="invalid-feedback" id="err-nm-nombre"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" name="descripcion" rows="2" data-feedback="err-nm-descripcion"></textarea>
                        <div class="invalid-feedback" id="err-nm-descripcion"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Asignatura padre</label>
                        <select class="form-select" name="id_materia_padre" data-feedback="err-nm-padre">
                            <option value="">Ninguna (asignatura principal)</option>
                            @foreach ($materiasPadre as $padre)
                                <option value="{{ $padre->id_materia }}">{{ $padre->nombre_materia }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="err-nm-padre"></div>
                        <div class="form-text">Si eliges una, esta asignatura queda agrupada como sub-asignatura de esa.</div>
                    </div>
                    <div class="mb-1">
                        <label class="form-label">Intensidad horaria (h/semana) *</label>
                        <input type="number" class="form-control" name="intensidad_horaria" min="1" max="40" value="4" data-feedback="err-nm-intensidad" required>
                        <div class="invalid-feedback" id="err-nm-intensidad"></div>
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

{{-- Modales: editar asignatura (uno por fila) --}}
@foreach ($materias as $materia)
    <div class="modal fade" id="modalEditarMateria{{ $materia->id_materia }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form data-crud-form data-url="{{ route('gestion-academica.materias.update', $materia) }}" novalidate>
                    <div class="modal-header">
                        <h5 class="modal-title font-serif">Editar asignatura</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre *</label>
                            <input type="text" class="form-control" name="nombre_materia" value="{{ $materia->nombre_materia }}"
                                   data-feedback="err-em-nombre-{{ $materia->id_materia }}" required>
                            <div class="invalid-feedback" id="err-em-nombre-{{ $materia->id_materia }}"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion" rows="2"
                                      data-feedback="err-em-descripcion-{{ $materia->id_materia }}">{{ $materia->descripcion }}</textarea>
                            <div class="invalid-feedback" id="err-em-descripcion-{{ $materia->id_materia }}"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Asignatura padre</label>
                            <select class="form-select" name="id_materia_padre" data-feedback="err-em-padre-{{ $materia->id_materia }}">
                                <option value="">Ninguna (asignatura principal)</option>
                                @foreach ($materiasPadre->reject(fn ($p) => $p->id_materia === $materia->id_materia) as $padre)
                                    <option value="{{ $padre->id_materia }}" @selected($materia->id_materia_padre === $padre->id_materia)>{{ $padre->nombre_materia }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="err-em-padre-{{ $materia->id_materia }}"></div>
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Intensidad horaria (h/semana) *</label>
                            <input type="number" class="form-control" name="intensidad_horaria" min="1" max="40"
                                   value="{{ $materia->intensidad_horaria }}" data-feedback="err-em-intensidad-{{ $materia->id_materia }}" required>
                            <div class="invalid-feedback" id="err-em-intensidad-{{ $materia->id_materia }}"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endpush
