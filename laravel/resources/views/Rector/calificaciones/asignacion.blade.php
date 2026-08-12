@extends(in_array(auth()->user()?->rolSlug, ['admin', 'rector']) ? 'layouts.rector' : 'layouts.panel')

@section('title', $asignacion->materia->nombre_materia.' — '.$asignacion->curso->nombre_curso)

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
            <a href="{{ auth()->user()->tienePanelAdmin() ? route('gestion-academica.cursos.show', $asignacion->curso) : route('docente.dashboard') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="h4 fw-semibold font-serif mb-0">{{ $asignacion->materia->nombre_materia }}</h1>
                <div class="small text-secondary">
                    {{ $asignacion->curso->nombre_curso }} ·
                    {{ trim($asignacion->profesor->usuario->nombres.' '.$asignacion->profesor->usuario->apellidos) }} ·
                    {{ $asignacion->anio_lectivo }}
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('calificaciones.asignaciones.show', $asignacion) }}" class="row g-2 mb-3 align-items-center" data-autosubmit-form>
            <div class="col-6 col-md-4">
                <select class="form-select" name="periodo" data-autosubmit>
                    <option value="">Todos los periodos</option>
                    @foreach ($periodos as $periodo)
                        <option value="{{ $periodo->id_periodo }}" @selected($filtroPeriodo == $periodo->id_periodo)>{{ $periodo->nombre_periodo }} ({{ $periodo->anio_lectivo }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3 d-grid">
                <a href="{{ route('calificaciones.asignaciones.show', $asignacion) }}" class="btn btn-outline-secondary btn-sm">Limpiar filtro</a>
            </div>
            <div class="col-12 col-md-5 d-flex justify-content-md-end">
                @if ($periodos->isEmpty() || $tiposActividad->isEmpty())
                    <button type="button" class="btn btn-primary btn-sm" disabled title="Necesitás al menos un periodo y un tipo de actividad activos (Calificaciones → Periodos / Tipos de actividad)">
                        <i class="fas fa-plus me-1"></i> Nueva actividad
                    </button>
                @else
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalNuevaActividad">
                        <i class="fas fa-plus me-1"></i> Nueva actividad
                    </button>
                @endif
            </div>
        </form>

        @if ($actividades->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-clipboard-list"></i></div>
                @if ($periodos->isEmpty() || $tiposActividad->isEmpty())
                    <p class="mb-0">Registrá al menos un periodo y un tipo de actividad antes de crear actividades evaluativas.</p>
                @else
                    <p class="mb-0">Aún no hay actividades registradas para esta asignación.</p>
                @endif
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Actividad</th>
                            <th>Tipo</th>
                            <th>Periodo</th>
                            <th>%</th>
                            <th>Entrega</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $estadoColores = ['activa' => 'success', 'cerrada' => 'secondary', 'anulada' => 'danger']; @endphp
                        @foreach ($actividades as $actividad)
                            <tr>
                                <td class="fw-semibold">{{ $actividad->titulo }}</td>
                                <td>{{ $actividad->tipo->nombre_tipo }}</td>
                                <td>{{ $actividad->periodo->nombre_periodo }}</td>
                                <td>{{ rtrim(rtrim(number_format($actividad->porcentaje, 2), '0'), '.') }}%</td>
                                <td>{{ $actividad->fecha_entrega ? \Illuminate\Support\Carbon::parse($actividad->fecha_entrega)->format('d/m/Y') : '—' }}</td>
                                <td>
                                    <span class="badge text-bg-{{ $estadoColores[$actividad->estado] ?? 'secondary' }}">{{ ucfirst($actividad->estado) }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('calificaciones.actividades.notas', $actividad) }}" class="btn btn-sm btn-outline-primary" title="Calificar">
                                        <i class="fas fa-marker"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" title="Editar"
                                            data-bs-toggle="modal" data-bs-target="#modalEditarActividad{{ $actividad->id_actividad }}">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    @if ($actividad->estado === 'activa')
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-desactivar
                                                data-url="{{ route('calificaciones.actividades.desactivar', $actividad) }}"
                                                data-nombre="{{ $actividad->titulo }}"
                                                title="Anular">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-success" data-activar
                                                data-url="{{ route('calificaciones.actividades.activar', $actividad) }}"
                                                data-nombre="{{ $actividad->titulo }}"
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
        @endif
    </div>
@endsection

@push('modals')
    {{-- Modal: nueva actividad --}}
    <div class="modal fade" id="modalNuevaActividad" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form data-crud-form data-url="{{ route('calificaciones.actividades.store', $asignacion) }}" novalidate>
                    <div class="modal-header">
                        <h5 class="modal-title font-serif">Nueva actividad</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Título *</label>
                                <input type="text" class="form-control" name="titulo" placeholder="Ej: Quiz capítulo 3" data-feedback="err-na-titulo" required>
                                <div class="invalid-feedback" id="err-na-titulo"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Periodo *</label>
                                <select class="form-select" name="id_periodo" data-feedback="err-na-periodo" required>
                                    <option value="" selected disabled>Selecciona...</option>
                                    @foreach ($periodos as $periodo)
                                        <option value="{{ $periodo->id_periodo }}">{{ $periodo->nombre_periodo }} ({{ $periodo->anio_lectivo }})</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="err-na-periodo"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tipo *</label>
                                <select class="form-select" name="id_tipo_actividad" data-feedback="err-na-tipo" required>
                                    <option value="" selected disabled>Selecciona...</option>
                                    @foreach ($tiposActividad as $tipo)
                                        <option value="{{ $tipo->id_tipo_actividad }}">{{ $tipo->nombre_tipo }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="err-na-tipo"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Porcentaje *</label>
                                <input type="number" class="form-control" name="porcentaje" min="0" max="100" step="0.01" placeholder="Ej: 25" data-feedback="err-na-porcentaje" required>
                                <div class="invalid-feedback" id="err-na-porcentaje"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha de entrega</label>
                                <input type="date" class="form-control" name="fecha_entrega" data-feedback="err-na-fecha">
                                <div class="invalid-feedback" id="err-na-fecha"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Descripción</label>
                                <textarea class="form-control" name="descripcion" rows="2"></textarea>
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

    {{-- Modales: editar actividad (uno por fila) --}}
    @foreach ($actividades as $actividad)
        <div class="modal fade" id="modalEditarActividad{{ $actividad->id_actividad }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form data-crud-form data-url="{{ route('calificaciones.actividades.update', $actividad) }}" novalidate>
                        <div class="modal-header">
                            <h5 class="modal-title font-serif">Editar actividad</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Título *</label>
                                    <input type="text" class="form-control" name="titulo" value="{{ $actividad->titulo }}"
                                           data-feedback="err-ea-titulo-{{ $actividad->id_actividad }}" required>
                                    <div class="invalid-feedback" id="err-ea-titulo-{{ $actividad->id_actividad }}"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Periodo *</label>
                                    <select class="form-select" name="id_periodo" data-feedback="err-ea-periodo-{{ $actividad->id_actividad }}" required>
                                        @foreach ($periodos as $periodo)
                                            <option value="{{ $periodo->id_periodo }}" @selected($actividad->id_periodo == $periodo->id_periodo)>{{ $periodo->nombre_periodo }} ({{ $periodo->anio_lectivo }})</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="err-ea-periodo-{{ $actividad->id_actividad }}"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tipo *</label>
                                    <select class="form-select" name="id_tipo_actividad" data-feedback="err-ea-tipo-{{ $actividad->id_actividad }}" required>
                                        @foreach ($tiposActividad as $tipo)
                                            <option value="{{ $tipo->id_tipo_actividad }}" @selected($actividad->id_tipo_actividad == $tipo->id_tipo_actividad)>{{ $tipo->nombre_tipo }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="err-ea-tipo-{{ $actividad->id_actividad }}"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Porcentaje *</label>
                                    <input type="number" class="form-control" name="porcentaje" min="0" max="100" step="0.01"
                                           value="{{ $actividad->porcentaje }}" data-feedback="err-ea-porcentaje-{{ $actividad->id_actividad }}" required>
                                    <div class="invalid-feedback" id="err-ea-porcentaje-{{ $actividad->id_actividad }}"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Fecha de entrega</label>
                                    <input type="date" class="form-control" name="fecha_entrega" value="{{ $actividad->fecha_entrega }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Descripción</label>
                                    <textarea class="form-control" name="descripcion" rows="2">{{ $actividad->descripcion }}</textarea>
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
    @vite('resources/js/pages/calificaciones/asignacion.js')
@endpush
