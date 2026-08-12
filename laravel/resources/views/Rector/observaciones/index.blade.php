@extends('layouts.rector')

@section('title', 'Observaciones')

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h1 class="h4 fw-semibold font-serif mb-0">Observaciones</h1>
            @if ($estudiantes->isEmpty() || $profesores->isEmpty())
                <button type="button" class="btn btn-primary btn-sm" disabled title="Necesitás al menos un estudiante y un profesor activos">
                    <i class="fas fa-plus me-1"></i> Nueva observación
                </button>
            @else
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalNuevaObservacion">
                    <i class="fas fa-plus me-1"></i> Nueva observación
                </button>
            @endif
        </div>

        <div class="row g-3 mb-3">
            @foreach ([
                ['icon' => 'fa-clipboard-list', 'label' => 'Total', 'value' => $resumen['total'], 'color' => 'primary'],
                ['icon' => 'fa-circle-check', 'label' => 'Activas', 'value' => $resumen['activas'], 'color' => 'success'],
                ['icon' => 'fa-triangle-exclamation', 'label' => 'Disciplinarias', 'value' => $resumen['disciplinarias'], 'color' => 'danger'],
                ['icon' => 'fa-star', 'label' => 'Positivas', 'value' => $resumen['positivas'], 'color' => 'warning'],
            ] as $stat)
                <div class="col-6 col-md-3">
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

        <form method="GET" action="{{ route('observaciones.index') }}" class="row g-2 mb-3 align-items-center" data-autosubmit-form>
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-body"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" name="q" value="{{ $filtros['q'] ?? '' }}"
                           placeholder="Buscar por estudiante o código..." data-autosubmit-debounce>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <select class="form-select" name="tipo" data-autosubmit>
                    <option value="">Todos los tipos</option>
                    <option value="academica" @selected(($filtros['tipo'] ?? '') === 'academica')>Académica</option>
                    <option value="disciplinaria" @selected(($filtros['tipo'] ?? '') === 'disciplinaria')>Disciplinaria</option>
                    <option value="convivencia" @selected(($filtros['tipo'] ?? '') === 'convivencia')>Convivencia</option>
                    <option value="positiva" @selected(($filtros['tipo'] ?? '') === 'positiva')>Positiva</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select class="form-select" name="estado" data-autosubmit>
                    <option value="">Todos los estados</option>
                    <option value="activa" @selected(($filtros['estado'] ?? '') === 'activa')>Activa</option>
                    <option value="archivada" @selected(($filtros['estado'] ?? '') === 'archivada')>Archivada</option>
                </select>
            </div>
            <div class="col-md-2 d-grid">
                <a href="{{ route('observaciones.index') }}" class="btn btn-outline-secondary btn-sm">Limpiar filtros</a>
            </div>
        </form>

        @if ($observaciones->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-clipboard-list"></i></div>
                @if (($filtros['q'] ?? '') || ($filtros['tipo'] ?? '') || ($filtros['estado'] ?? ''))
                    <p class="mb-0">No hay observaciones que coincidan con los filtros.</p>
                @else
                    <p class="mb-0">Aún no hay observaciones registradas.</p>
                @endif
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Estudiante</th>
                            <th>Tipo</th>
                            <th>Gravedad</th>
                            <th>Fecha</th>
                            <th>Registrado por</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $tipoColores = ['academica' => 'info', 'disciplinaria' => 'danger', 'convivencia' => 'warning', 'positiva' => 'success'];
                            $nivelColores = ['baja' => 'secondary', 'media' => 'warning', 'alta' => 'danger'];
                        @endphp
                        @foreach ($observaciones as $obs)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ trim($obs->estudiante->usuario->nombres.' '.$obs->estudiante->usuario->apellidos) }}</div>
                                    <div class="small text-secondary">{{ \Illuminate\Support\Str::limit($obs->descripcion, 60) }}</div>
                                </td>
                                <td><span class="badge text-bg-{{ $tipoColores[$obs->tipo_observacion] ?? 'secondary' }}">{{ ucfirst($obs->tipo_observacion) }}</span></td>
                                <td><span class="badge text-bg-{{ $nivelColores[$obs->nivel_gravedad] ?? 'secondary' }}">{{ ucfirst($obs->nivel_gravedad) }}</span></td>
                                <td>{{ \Illuminate\Support\Carbon::parse($obs->fecha)->format('d/m/Y') }}</td>
                                <td class="small">{{ trim($obs->profesor->usuario->nombres.' '.$obs->profesor->usuario->apellidos) }}</td>
                                <td>
                                    <span class="badge text-bg-{{ $obs->estado === 'activa' ? 'success' : 'secondary' }}">
                                        {{ $obs->estado === 'activa' ? 'Activa' : 'Archivada' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" title="Editar"
                                            data-bs-toggle="modal" data-bs-target="#modalEditarObservacion{{ $obs->id_observacion }}">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    @if ($obs->estado === 'activa')
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-desactivar
                                                data-url="{{ route('observaciones.desactivar', $obs) }}"
                                                data-nombre="esta observación"
                                                title="Archivar">
                                            <i class="fas fa-box-archive"></i>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-success" data-activar
                                                data-url="{{ route('observaciones.activar', $obs) }}"
                                                data-nombre="esta observación"
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

            {{ $observaciones->links('pagination::bootstrap-5') }}
        @endif
    </div>

@endsection

@push('modals')
    @php
        $tiposOpciones = ['academica' => 'Académica', 'disciplinaria' => 'Disciplinaria', 'convivencia' => 'Convivencia', 'positiva' => 'Positiva'];
        $nivelesOpciones = ['baja' => 'Baja', 'media' => 'Media', 'alta' => 'Alta'];
    @endphp

    {{-- Modal: nueva observación --}}
    <div class="modal fade" id="modalNuevaObservacion" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form data-crud-form data-url="{{ route('observaciones.store') }}" novalidate>
                    <div class="modal-header">
                        <h5 class="modal-title font-serif">Nueva observación</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Estudiante *</label>
                                <select class="form-select" name="id_estudiante" data-feedback="err-no-estudiante" required>
                                    <option value="" selected disabled>Selecciona...</option>
                                    @foreach ($estudiantes as $estudiante)
                                        <option value="{{ $estudiante->id_estudiante }}">
                                            {{ trim($estudiante->usuario->nombres.' '.$estudiante->usuario->apellidos) }} ({{ $estudiante->codigo_estudiante }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="err-no-estudiante"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Registrado por (profesor) *</label>
                                <select class="form-select" name="id_profesor" data-feedback="err-no-profesor" required>
                                    <option value="" selected disabled>Selecciona...</option>
                                    @foreach ($profesores as $profesor)
                                        <option value="{{ $profesor->id_profesor }}">{{ trim($profesor->usuario->nombres.' '.$profesor->usuario->apellidos) }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="err-no-profesor"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tipo *</label>
                                <select class="form-select" name="tipo_observacion" data-feedback="err-no-tipo" required>
                                    @foreach ($tiposOpciones as $valor => $etiqueta)
                                        <option value="{{ $valor }}">{{ $etiqueta }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="err-no-tipo"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Gravedad *</label>
                                <select class="form-select" name="nivel_gravedad" data-feedback="err-no-nivel" required>
                                    @foreach ($nivelesOpciones as $valor => $etiqueta)
                                        <option value="{{ $valor }}">{{ $etiqueta }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="err-no-nivel"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha *</label>
                                <input type="date" class="form-control" name="fecha" value="{{ now()->toDateString() }}" data-feedback="err-no-fecha" required>
                                <div class="invalid-feedback" id="err-no-fecha"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Descripción *</label>
                                <textarea class="form-control" name="descripcion" rows="3" data-feedback="err-no-descripcion" required></textarea>
                                <div class="invalid-feedback" id="err-no-descripcion"></div>
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

    {{-- Modales: editar observación (uno por fila) --}}
    @foreach ($observaciones as $obs)
        <div class="modal fade" id="modalEditarObservacion{{ $obs->id_observacion }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form data-crud-form data-url="{{ route('observaciones.update', $obs) }}" novalidate>
                        <div class="modal-header">
                            <h5 class="modal-title font-serif">Editar observación</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Estudiante *</label>
                                    <select class="form-select" name="id_estudiante" data-feedback="err-eo-estudiante-{{ $obs->id_observacion }}" required>
                                        @foreach ($estudiantes as $estudiante)
                                            <option value="{{ $estudiante->id_estudiante }}" @selected($obs->id_estudiante == $estudiante->id_estudiante)>
                                                {{ trim($estudiante->usuario->nombres.' '.$estudiante->usuario->apellidos) }} ({{ $estudiante->codigo_estudiante }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="err-eo-estudiante-{{ $obs->id_observacion }}"></div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Registrado por (profesor) *</label>
                                    <select class="form-select" name="id_profesor" data-feedback="err-eo-profesor-{{ $obs->id_observacion }}" required>
                                        @foreach ($profesores as $profesor)
                                            <option value="{{ $profesor->id_profesor }}" @selected($obs->id_profesor == $profesor->id_profesor)>{{ trim($profesor->usuario->nombres.' '.$profesor->usuario->apellidos) }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="err-eo-profesor-{{ $obs->id_observacion }}"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tipo *</label>
                                    <select class="form-select" name="tipo_observacion" data-feedback="err-eo-tipo-{{ $obs->id_observacion }}" required>
                                        @foreach ($tiposOpciones as $valor => $etiqueta)
                                            <option value="{{ $valor }}" @selected($obs->tipo_observacion === $valor)>{{ $etiqueta }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="err-eo-tipo-{{ $obs->id_observacion }}"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Gravedad *</label>
                                    <select class="form-select" name="nivel_gravedad" data-feedback="err-eo-nivel-{{ $obs->id_observacion }}" required>
                                        @foreach ($nivelesOpciones as $valor => $etiqueta)
                                            <option value="{{ $valor }}" @selected($obs->nivel_gravedad === $valor)>{{ $etiqueta }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="err-eo-nivel-{{ $obs->id_observacion }}"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Fecha *</label>
                                    <input type="date" class="form-control" name="fecha" value="{{ $obs->fecha }}"
                                           data-feedback="err-eo-fecha-{{ $obs->id_observacion }}" required>
                                    <div class="invalid-feedback" id="err-eo-fecha-{{ $obs->id_observacion }}"></div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Descripción *</label>
                                    <textarea class="form-control" name="descripcion" rows="3"
                                              data-feedback="err-eo-descripcion-{{ $obs->id_observacion }}" required>{{ $obs->descripcion }}</textarea>
                                    <div class="invalid-feedback" id="err-eo-descripcion-{{ $obs->id_observacion }}"></div>
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
@endpush

@push('scripts')
    @vite('resources/js/pages/observaciones/observaciones.js')
@endpush
