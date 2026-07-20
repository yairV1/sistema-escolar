<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div class="row g-3 flex-grow-1 mb-0">
        @foreach ([
            ['icon' => 'fa-calendar-days', 'label' => 'Total', 'value' => $resumenPeriodos['total'], 'color' => 'primary'],
            ['icon' => 'fa-circle-check', 'label' => 'Activos', 'value' => $resumenPeriodos['activos'], 'color' => 'success'],
            ['icon' => 'fa-lock', 'label' => 'Cerrados', 'value' => $resumenPeriodos['cerrados'], 'color' => 'secondary'],
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
    <button type="button" class="btn btn-primary btn-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#modalNuevoPeriodo">
        <i class="fas fa-plus me-1"></i> Nuevo periodo
    </button>
</div>

<form method="GET" action="{{ route('calificaciones.index') }}" class="row g-2 mb-3 align-items-center" data-autosubmit-form>
    <input type="hidden" name="tab" value="periodos">
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
            <option value="pendiente" @selected(($filtros['estado'] ?? '') === 'pendiente')>Pendiente</option>
            <option value="activo" @selected(($filtros['estado'] ?? '') === 'activo')>Activo</option>
            <option value="cerrado" @selected(($filtros['estado'] ?? '') === 'cerrado')>Cerrado</option>
        </select>
    </div>
    <div class="col-md-2 d-grid">
        <a href="{{ route('calificaciones.index', ['tab' => 'periodos']) }}" class="btn btn-outline-secondary btn-sm">Limpiar filtros</a>
    </div>
</form>

@if ($periodos->isEmpty())
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-calendar-days"></i></div>
        @if (($filtros['q'] ?? '') || ($filtros['estado'] ?? ''))
            <p class="mb-0">No hay periodos que coincidan con los filtros.</p>
        @else
            <p class="mb-2">Aún no hay periodos académicos registrados.</p>
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoPeriodo">
                Registrar el primer periodo
            </button>
        @endif
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Periodo</th>
                    <th>Año</th>
                    <th>Fechas</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @php $estadoColores = ['activo' => 'success', 'cerrado' => 'secondary', 'pendiente' => 'warning']; @endphp
                @foreach ($periodos as $periodo)
                    <tr>
                        <td class="fw-semibold">{{ $periodo->nombre_periodo }}</td>
                        <td>{{ $periodo->anio_lectivo }}</td>
                        <td class="small">
                            {{ \Illuminate\Support\Carbon::parse($periodo->fecha_inicio)->format('d/m/Y') }}
                            –
                            {{ \Illuminate\Support\Carbon::parse($periodo->fecha_fin)->format('d/m/Y') }}
                        </td>
                        <td>
                            <span class="badge text-bg-{{ $estadoColores[$periodo->estado] ?? 'secondary' }}">{{ ucfirst($periodo->estado) }}</span>
                        </td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-secondary" title="Editar"
                                    data-bs-toggle="modal" data-bs-target="#modalEditarPeriodo{{ $periodo->id_periodo }}">
                                <i class="fas fa-pen"></i>
                            </button>
                            @if ($periodo->estado === 'activo')
                                <button type="button" class="btn btn-sm btn-outline-danger" data-desactivar
                                        data-url="{{ route('calificaciones.periodos.desactivar', $periodo) }}"
                                        data-nombre="{{ $periodo->nombre_periodo }}"
                                        title="Cerrar">
                                    <i class="fas fa-lock"></i>
                                </button>
                            @else
                                <button type="button" class="btn btn-sm btn-outline-success" data-activar
                                        data-url="{{ route('calificaciones.periodos.activar', $periodo) }}"
                                        data-nombre="{{ $periodo->nombre_periodo }}"
                                        title="Activar">
                                    <i class="fas fa-rotate-left"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $periodos->links('pagination::bootstrap-5') }}
@endif

{{-- Modal: nuevo periodo --}}
<div class="modal fade" id="modalNuevoPeriodo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form data-crud-form data-url="{{ route('calificaciones.periodos.store') }}" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title font-serif">Nuevo periodo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nombre *</label>
                            <input type="text" class="form-control" name="nombre_periodo" placeholder="Ej: Periodo 1" data-feedback="err-np-nombre" required>
                            <div class="invalid-feedback" id="err-np-nombre"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Año lectivo *</label>
                            <input type="number" class="form-control" name="anio_lectivo" value="{{ date('Y') }}" data-feedback="err-np-anio" required>
                            <div class="invalid-feedback" id="err-np-anio"></div>
                        </div>
                        <div class="col-md-6"></div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha inicio *</label>
                            <input type="date" class="form-control" name="fecha_inicio" data-feedback="err-np-inicio" required>
                            <div class="invalid-feedback" id="err-np-inicio"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha fin *</label>
                            <input type="date" class="form-control" name="fecha_fin" data-feedback="err-np-fin" required>
                            <div class="invalid-feedback" id="err-np-fin"></div>
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

{{-- Modales: editar periodo (uno por fila) --}}
@foreach ($periodos as $periodo)
    <div class="modal fade" id="modalEditarPeriodo{{ $periodo->id_periodo }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form data-crud-form data-url="{{ route('calificaciones.periodos.update', $periodo) }}" novalidate>
                    <div class="modal-header">
                        <h5 class="modal-title font-serif">Editar periodo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Nombre *</label>
                                <input type="text" class="form-control" name="nombre_periodo" value="{{ $periodo->nombre_periodo }}"
                                       data-feedback="err-ep-nombre-{{ $periodo->id_periodo }}" required>
                                <div class="invalid-feedback" id="err-ep-nombre-{{ $periodo->id_periodo }}"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Año lectivo *</label>
                                <input type="number" class="form-control" name="anio_lectivo" value="{{ $periodo->anio_lectivo }}"
                                       data-feedback="err-ep-anio-{{ $periodo->id_periodo }}" required>
                                <div class="invalid-feedback" id="err-ep-anio-{{ $periodo->id_periodo }}"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Estado</label>
                                <select class="form-select" name="estado">
                                    <option value="pendiente" @selected($periodo->estado === 'pendiente')>Pendiente</option>
                                    <option value="activo" @selected($periodo->estado === 'activo')>Activo</option>
                                    <option value="cerrado" @selected($periodo->estado === 'cerrado')>Cerrado</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha inicio *</label>
                                <input type="date" class="form-control" name="fecha_inicio" value="{{ $periodo->fecha_inicio }}"
                                       data-feedback="err-ep-inicio-{{ $periodo->id_periodo }}" required>
                                <div class="invalid-feedback" id="err-ep-inicio-{{ $periodo->id_periodo }}"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha fin *</label>
                                <input type="date" class="form-control" name="fecha_fin" value="{{ $periodo->fecha_fin }}"
                                       data-feedback="err-ep-fin-{{ $periodo->id_periodo }}" required>
                                <div class="invalid-feedback" id="err-ep-fin-{{ $periodo->id_periodo }}"></div>
                            </div>
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
