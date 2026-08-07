<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div class="row g-3 flex-grow-1 mb-0">
        @foreach ([
            ['icon' => 'fa-diagram-project', 'label' => 'Total', 'value' => $resumenAreas['total'], 'color' => 'primary'],
            ['icon' => 'fa-circle-check', 'label' => 'Activas', 'value' => $resumenAreas['activas'], 'color' => 'success'],
            ['icon' => 'fa-ban', 'label' => 'Inactivas', 'value' => $resumenAreas['inactivas'], 'color' => 'secondary'],
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
    <button type="button" class="btn btn-primary btn-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#modalNuevaArea">
        <i class="fas fa-plus me-1"></i> Nueva área
    </button>
</div>

<form method="GET" action="{{ route('gestion-academica.materias.index') }}" class="row g-2 mb-3 align-items-center" data-autosubmit-form>
    <input type="hidden" name="tab" value="areas">
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
        <a href="{{ route('gestion-academica.materias.index', ['tab' => 'areas']) }}" class="btn btn-outline-secondary btn-sm">Limpiar filtros</a>
    </div>
</form>

@if ($areas->isEmpty())
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-diagram-project"></i></div>
        @if (($filtros['q'] ?? '') || ($filtros['estado'] ?? ''))
            <p class="mb-0">No hay áreas que coincidan con los filtros.</p>
        @else
            <p class="mb-2">Aún no hay áreas registradas.</p>
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaArea">
                Registrar la primera área
            </button>
        @endif
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Área</th>
                    <th>Descripción</th>
                    <th>Materias</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($areas as $area)
                    <tr>
                        <td class="fw-semibold">{{ $area->nombre_area }}</td>
                        <td class="small text-secondary">{{ $area->descripcion ?: '—' }}</td>
                        <td>{{ $area->materias()->count() }}</td>
                        <td>
                            <span class="badge text-bg-{{ $area->estado === 'activo' ? 'success' : 'secondary' }}">
                                {{ $area->estado === 'activo' ? 'Activa' : 'Inactiva' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-secondary" title="Editar"
                                    data-bs-toggle="modal" data-bs-target="#modalEditarArea{{ $area->id_area }}">
                                <i class="fas fa-pen"></i>
                            </button>
                            @if ($area->estado === 'activo')
                                <button type="button" class="btn btn-sm btn-outline-danger" data-desactivar
                                        data-url="{{ route('gestion-academica.materias.areas.desactivar', $area) }}"
                                        data-nombre="{{ $area->nombre_area }}"
                                        title="Desactivar">
                                    <i class="fas fa-ban"></i>
                                </button>
                            @else
                                <button type="button" class="btn btn-sm btn-outline-success" data-activar
                                        data-url="{{ route('gestion-academica.materias.areas.activar', $area) }}"
                                        data-nombre="{{ $area->nombre_area }}"
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

    {{ $areas->links('pagination::bootstrap-5') }}
@endif

{{-- Modal: nueva área --}}
<div class="modal fade" id="modalNuevaArea" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form data-crud-form data-url="{{ route('gestion-academica.materias.areas.store') }}" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title font-serif">Nueva área</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre *</label>
                        <input type="text" class="form-control" name="nombre_area" placeholder="Ej: Ciencias Naturales" data-feedback="err-na-nombre" required>
                        <div class="invalid-feedback" id="err-na-nombre"></div>
                    </div>
                    <div class="mb-1">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" name="descripcion" rows="2" data-feedback="err-na-descripcion"></textarea>
                        <div class="invalid-feedback" id="err-na-descripcion"></div>
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

{{-- Modales: editar área (una por fila) --}}
@foreach ($areas as $area)
    <div class="modal fade" id="modalEditarArea{{ $area->id_area }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form data-crud-form data-url="{{ route('gestion-academica.materias.areas.update', $area) }}" novalidate>
                    <div class="modal-header">
                        <h5 class="modal-title font-serif">Editar área</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre *</label>
                            <input type="text" class="form-control" name="nombre_area" value="{{ $area->nombre_area }}"
                                   data-feedback="err-ea-nombre-{{ $area->id_area }}" required>
                            <div class="invalid-feedback" id="err-ea-nombre-{{ $area->id_area }}"></div>
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion" rows="2"
                                      data-feedback="err-ea-descripcion-{{ $area->id_area }}">{{ $area->descripcion }}</textarea>
                            <div class="invalid-feedback" id="err-ea-descripcion-{{ $area->id_area }}"></div>
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
