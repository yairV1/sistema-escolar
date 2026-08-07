@extends('layouts.panel')

@section('title', 'Cursos')

@section('content')
<div class="container-fluid p-3 p-md-4">
    <h1 class="h4 fw-semibold font-serif mb-3">Cursos</h1>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div class="row g-3 flex-grow-1 mb-0">
            @foreach ([
                ['icon' => 'fa-chalkboard', 'label' => 'Total', 'value' => $resumenCursos['total'], 'color' => 'primary'],
                ['icon' => 'fa-circle-check', 'label' => 'Activos', 'value' => $resumenCursos['activos'], 'color' => 'success'],
                ['icon' => 'fa-ban', 'label' => 'Inactivos', 'value' => $resumenCursos['inactivos'], 'color' => 'secondary'],
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
        <button type="button" class="btn btn-primary btn-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#modalNuevoCurso">
            <i class="fas fa-plus me-1"></i> Nuevo curso
        </button>
    </div>

    <form method="GET" action="{{ route('gestion-academica.cursos.index') }}" class="row g-2 mb-3 align-items-center" data-autosubmit-form>
        <div class="col-12 col-md-4">
            <div class="input-group">
                <span class="input-group-text bg-body"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" name="q" value="{{ $filtros['q'] ?? '' }}"
                       placeholder="Buscar por nombre..." data-autosubmit-debounce>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <select class="form-select" name="nivel" data-autosubmit>
                <option value="">Todos los niveles</option>
                <option value="preescolar" @selected(($filtros['nivel'] ?? '') === 'preescolar')>Preescolar</option>
                <option value="primaria" @selected(($filtros['nivel'] ?? '') === 'primaria')>Primaria</option>
                <option value="secundaria" @selected(($filtros['nivel'] ?? '') === 'secundaria')>Secundaria</option>
                <option value="media" @selected(($filtros['nivel'] ?? '') === 'media')>Media</option>
            </select>
        </div>
        <div class="col-6 col-md-3">
            <select class="form-select" name="estado" data-autosubmit>
                <option value="">Todos los estados</option>
                <option value="activo" @selected(($filtros['estado'] ?? '') === 'activo')>Activo</option>
                <option value="inactivo" @selected(($filtros['estado'] ?? '') === 'inactivo')>Inactivo</option>
            </select>
        </div>
        <div class="col-md-2 d-grid">
            <a href="{{ route('gestion-academica.cursos.index') }}" class="btn btn-outline-secondary btn-sm">Limpiar filtros</a>
        </div>
    </form>

    @if ($cursos->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-chalkboard"></i></div>
            @if (($filtros['q'] ?? '') || ($filtros['nivel'] ?? '') || ($filtros['estado'] ?? ''))
                <p class="mb-0">No hay cursos que coincidan con los filtros.</p>
            @else
                <p class="mb-2">Aún no hay cursos registrados.</p>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoCurso">
                    Registrar el primer curso
                </button>
            @endif
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Curso</th>
                        <th>Nivel</th>
                        <th>Jornada</th>
                        <th>Año</th>
                        <th>Director de grupo</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cursos as $curso)
                        <tr>
                            <td class="fw-semibold">
                                <a href="{{ route('gestion-academica.cursos.show', $curso) }}">{{ $curso->nombre_curso }}</a>
                            </td>
                            <td class="text-capitalize">{{ $curso->nivel_academico }}</td>
                            <td class="text-capitalize">{{ $curso->jornada }}</td>
                            <td>{{ $curso->anio_lectivo }}</td>
                            <td>
                                @if ($curso->director?->usuario)
                                    {{ trim($curso->director->usuario->nombres.' '.$curso->director->usuario->apellidos) }}
                                @else
                                    <span class="text-secondary">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge text-bg-{{ $curso->estado === 'activo' ? 'success' : 'secondary' }}">
                                    {{ $curso->estado === 'activo' ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-secondary" title="Editar"
                                        data-bs-toggle="modal" data-bs-target="#modalEditarCurso{{ $curso->id_curso }}">
                                    <i class="fas fa-pen"></i>
                                </button>
                                @if ($curso->estado === 'activo')
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-desactivar
                                            data-url="{{ route('gestion-academica.cursos.desactivar', $curso) }}"
                                            data-nombre="{{ $curso->nombre_curso }}"
                                            title="Desactivar">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                @else
                                    <button type="button" class="btn btn-sm btn-outline-success" data-activar
                                            data-url="{{ route('gestion-academica.cursos.activar', $curso) }}"
                                            data-nombre="{{ $curso->nombre_curso }}"
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

        {{ $cursos->links('pagination::bootstrap-5') }}
    @endif

    @php
        $nivelesOpciones = ['preescolar' => 'Preescolar', 'primaria' => 'Primaria', 'secundaria' => 'Secundaria', 'media' => 'Media'];
        $jornadasOpciones = ['manana' => 'Mañana', 'tarde' => 'Tarde', 'noche' => 'Noche', 'unica' => 'Única'];
    @endphp

    {{-- Modal: nuevo curso --}}
    <div class="modal fade" id="modalNuevoCurso" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form data-crud-form data-url="{{ route('gestion-academica.cursos.store') }}" novalidate>
                    <div class="modal-header">
                        <h5 class="modal-title font-serif">Nuevo curso</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre *</label>
                                <input type="text" class="form-control" name="nombre_curso" placeholder="Ej: 6A" data-feedback="err-nc-nombre" required>
                                <div class="invalid-feedback" id="err-nc-nombre"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Año lectivo *</label>
                                <input type="number" class="form-control" name="anio_lectivo" value="{{ date('Y') }}" data-feedback="err-nc-anio" required>
                                <div class="invalid-feedback" id="err-nc-anio"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nivel académico *</label>
                                <select class="form-select" name="nivel_academico" data-feedback="err-nc-nivel" required>
                                    @foreach ($nivelesOpciones as $valor => $etiqueta)
                                        <option value="{{ $valor }}">{{ $etiqueta }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="err-nc-nivel"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Jornada *</label>
                                <select class="form-select" name="jornada" data-feedback="err-nc-jornada" required>
                                    @foreach ($jornadasOpciones as $valor => $etiqueta)
                                        <option value="{{ $valor }}">{{ $etiqueta }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="err-nc-jornada"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Director de grupo</label>
                                <select class="form-select" name="id_director_grupo" data-feedback="err-nc-director">
                                    <option value="">Sin asignar</option>
                                    @foreach ($profesores as $profesor)
                                        <option value="{{ $profesor->id_profesor }}">{{ trim($profesor->usuario->nombres.' '.$profesor->usuario->apellidos) }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="err-nc-director"></div>
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

    {{-- Modales: editar curso (uno por fila) --}}
    @foreach ($cursos as $curso)
        <div class="modal fade" id="modalEditarCurso{{ $curso->id_curso }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form data-crud-form data-url="{{ route('gestion-academica.cursos.update', $curso) }}" novalidate>
                        <div class="modal-header">
                            <h5 class="modal-title font-serif">Editar curso</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nombre *</label>
                                    <input type="text" class="form-control" name="nombre_curso" value="{{ $curso->nombre_curso }}"
                                           data-feedback="err-ec-nombre-{{ $curso->id_curso }}" required>
                                    <div class="invalid-feedback" id="err-ec-nombre-{{ $curso->id_curso }}"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Año lectivo *</label>
                                    <input type="number" class="form-control" name="anio_lectivo" value="{{ $curso->anio_lectivo }}"
                                           data-feedback="err-ec-anio-{{ $curso->id_curso }}" required>
                                    <div class="invalid-feedback" id="err-ec-anio-{{ $curso->id_curso }}"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nivel académico *</label>
                                    <select class="form-select" name="nivel_academico" data-feedback="err-ec-nivel-{{ $curso->id_curso }}" required>
                                        @foreach ($nivelesOpciones as $valor => $etiqueta)
                                            <option value="{{ $valor }}" @selected($curso->nivel_academico === $valor)>{{ $etiqueta }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="err-ec-nivel-{{ $curso->id_curso }}"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Jornada *</label>
                                    <select class="form-select" name="jornada" data-feedback="err-ec-jornada-{{ $curso->id_curso }}" required>
                                        @foreach ($jornadasOpciones as $valor => $etiqueta)
                                            <option value="{{ $valor }}" @selected($curso->jornada === $valor)>{{ $etiqueta }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="err-ec-jornada-{{ $curso->id_curso }}"></div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Director de grupo</label>
                                    <select class="form-select" name="id_director_grupo" data-feedback="err-ec-director-{{ $curso->id_curso }}">
                                        <option value="">Sin asignar</option>
                                        @foreach ($profesores as $profesor)
                                            <option value="{{ $profesor->id_profesor }}" @selected($curso->id_director_grupo == $profesor->id_profesor)>
                                                {{ trim($profesor->usuario->nombres.' '.$profesor->usuario->apellidos) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="err-ec-director-{{ $curso->id_curso }}"></div>
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
</div>
@endsection

@push('scripts')
    @vite('resources/js/pages/gestion-academica/gestion-academica.js')
@endpush
