<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div class="row g-3 flex-grow-1 mb-0">
        @foreach ([
            ['icon' => 'fa-list-check', 'label' => 'Total', 'value' => $resumenTiposActividad['total'], 'color' => 'primary'],
            ['icon' => 'fa-circle-check', 'label' => 'Activos', 'value' => $resumenTiposActividad['activos'], 'color' => 'success'],
            ['icon' => 'fa-ban', 'label' => 'Inactivos', 'value' => $resumenTiposActividad['inactivos'], 'color' => 'secondary'],
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
    <button type="button" class="btn btn-primary btn-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#modalNuevoTipoActividad">
        <i class="fas fa-plus me-1"></i> Nuevo tipo
    </button>
</div>

<form method="GET" action="{{ route('calificaciones.index') }}" class="row g-2 mb-3 align-items-center" data-autosubmit-form>
    <input type="hidden" name="tab" value="tipos-actividad">
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
            <option value="activo" @selected(($filtros['estado'] ?? '') === 'activo')>Activo</option>
            <option value="inactivo" @selected(($filtros['estado'] ?? '') === 'inactivo')>Inactivo</option>
        </select>
    </div>
    <div class="col-md-2 d-grid">
        <a href="{{ route('calificaciones.index', ['tab' => 'tipos-actividad']) }}" class="btn btn-outline-secondary btn-sm">Limpiar filtros</a>
    </div>
</form>

@if ($tiposActividad->isEmpty())
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-list-check"></i></div>
        @if (($filtros['q'] ?? '') || ($filtros['estado'] ?? ''))
            <p class="mb-0">No hay tipos de actividad que coincidan con los filtros.</p>
        @else
            <p class="mb-2">Aún no hay tipos de actividad registrados.</p>
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoTipoActividad">
                Registrar el primero
            </button>
        @endif
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tiposActividad as $tipo)
                    <tr>
                        <td class="fw-semibold">{{ $tipo->nombre_tipo }}</td>
                        <td class="small text-secondary">{{ $tipo->descripcion ?: '—' }}</td>
                        <td>
                            <span class="badge text-bg-{{ $tipo->estado === 'activo' ? 'success' : 'secondary' }}">
                                {{ $tipo->estado === 'activo' ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-secondary" title="Editar"
                                    data-bs-toggle="modal" data-bs-target="#modalEditarTipoActividad{{ $tipo->id_tipo_actividad }}">
                                <i class="fas fa-pen"></i>
                            </button>
                            @if ($tipo->estado === 'activo')
                                <button type="button" class="btn btn-sm btn-outline-danger" data-desactivar
                                        data-url="{{ route('calificaciones.tipos-actividad.desactivar', $tipo) }}"
                                        data-nombre="{{ $tipo->nombre_tipo }}"
                                        title="Desactivar">
                                    <i class="fas fa-ban"></i>
                                </button>
                            @else
                                <button type="button" class="btn btn-sm btn-outline-success" data-activar
                                        data-url="{{ route('calificaciones.tipos-actividad.activar', $tipo) }}"
                                        data-nombre="{{ $tipo->nombre_tipo }}"
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

    {{ $tiposActividad->links('pagination::bootstrap-5') }}
@endif

@push('modals')
{{-- Modal: nuevo tipo de actividad --}}
<div class="modal fade" id="modalNuevoTipoActividad" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form data-crud-form data-url="{{ route('calificaciones.tipos-actividad.store') }}" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title font-serif">Nuevo tipo de actividad</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre *</label>
                        <input type="text" class="form-control" name="nombre_tipo" placeholder="Ej: Quiz, Examen, Taller" data-feedback="err-nt-nombre" required>
                        <div class="invalid-feedback" id="err-nt-nombre"></div>
                    </div>
                    <div class="mb-1">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" name="descripcion" rows="2" data-feedback="err-nt-descripcion"></textarea>
                        <div class="invalid-feedback" id="err-nt-descripcion"></div>
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

{{-- Modales: editar tipo de actividad (uno por fila) --}}
@foreach ($tiposActividad as $tipo)
    <div class="modal fade" id="modalEditarTipoActividad{{ $tipo->id_tipo_actividad }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form data-crud-form data-url="{{ route('calificaciones.tipos-actividad.update', $tipo) }}" novalidate>
                    <div class="modal-header">
                        <h5 class="modal-title font-serif">Editar tipo de actividad</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre *</label>
                            <input type="text" class="form-control" name="nombre_tipo" value="{{ $tipo->nombre_tipo }}"
                                   data-feedback="err-et-nombre-{{ $tipo->id_tipo_actividad }}" required>
                            <div class="invalid-feedback" id="err-et-nombre-{{ $tipo->id_tipo_actividad }}"></div>
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion" rows="2"
                                      data-feedback="err-et-descripcion-{{ $tipo->id_tipo_actividad }}">{{ $tipo->descripcion }}</textarea>
                            <div class="invalid-feedback" id="err-et-descripcion-{{ $tipo->id_tipo_actividad }}"></div>
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
