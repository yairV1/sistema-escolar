@extends('layouts.panel')

@section('title', $curso->nombre_curso.' — Detalle del curso')

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
            <a href="{{ route('gestion-academica.cursos.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="h4 fw-semibold font-serif mb-0">{{ $curso->nombre_curso }}</h1>
                <div class="small text-secondary text-capitalize">
                    {{ $curso->nivel_academico }} · {{ $curso->jornada }} · {{ $curso->anio_lectivo }}
                    @if ($curso->director?->usuario)
                        · Director: {{ trim($curso->director->usuario->nombres.' '.$curso->director->usuario->apellidos) }}
                    @endif
                </div>
            </div>
            <span class="badge text-bg-{{ $curso->estado === 'activo' ? 'success' : 'secondary' }} ms-2">
                {{ $curso->estado === 'activo' ? 'Activo' : 'Inactivo' }}
            </span>
        </div>

        @php $profesoresUnicos = $asignaciones->pluck('id_profesor')->unique()->count(); @endphp
        <div class="row g-3 mb-4">
            @foreach ([
                ['icon' => 'fa-user-graduate', 'label' => 'Estudiantes matriculados', 'value' => $estudiantes->count(), 'color' => 'primary'],
                ['icon' => 'fa-book', 'label' => 'Materias asignadas', 'value' => $asignaciones->count(), 'color' => 'info'],
                ['icon' => 'fa-chalkboard-teacher', 'label' => 'Profesores', 'value' => $profesoresUnicos, 'color' => 'success'],
            ] as $stat)
                <div class="col-6 col-md-4">
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

        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h6 fw-semibold mb-3"><i class="fas fa-user-graduate text-primary me-1"></i> Estudiantes matriculados</h2>

                        @if ($estudiantes->isEmpty())
                            <div class="empty-state">
                                <div class="empty-icon"><i class="fas fa-user-graduate"></i></div>
                                <p class="mb-0">Aún no hay estudiantes matriculados en este curso.</p>
                            </div>
                        @else
                            <ul class="list-group list-group-flush">
                                @foreach ($estudiantes as $estudiante)
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <div>
                                            <div class="fw-semibold">{{ trim($estudiante->usuario->nombres.' '.$estudiante->usuario->apellidos) }}</div>
                                            <div class="small text-secondary"><code>{{ $estudiante->codigo_estudiante }}</code></div>
                                        </div>
                                        @if ($estudiante->estado_academico !== 'activo')
                                            <span class="badge text-bg-secondary">Inactivo</span>
                                        @elseif ($idsEnRiesgo->contains($estudiante->id_estudiante))
                                            <span class="badge text-bg-danger">En riesgo</span>
                                        @else
                                            <span class="badge text-bg-success">Activo</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="h6 fw-semibold mb-0"><i class="fas fa-diagram-project text-primary me-1"></i> Materias y profesores asignados</h2>
                            <a href="{{ route('boletines.index', ['curso' => $curso->id_curso]) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-file-lines me-1"></i> Boletines
                            </a>
                        </div>

                        @if ($asignaciones->isEmpty())
                            <div class="empty-state">
                                <div class="empty-icon"><i class="fas fa-diagram-project"></i></div>
                                <p class="mb-2">Este curso todavía no tiene materias asignadas.</p>
                                <a href="{{ route('gestion-academica.asignaciones.index', ['curso' => $curso->id_curso]) }}" class="btn btn-sm btn-primary">
                                    Ir a Asignaciones
                                </a>
                            </div>
                        @else
                            <ul class="list-group list-group-flush">
                                @foreach ($asignaciones as $asignacion)
                                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-semibold">{{ $asignacion->materia->nombre_materia }}</div>
                                            <div class="small text-secondary">{{ trim($asignacion->profesor->usuario->nombres.' '.$asignacion->profesor->usuario->apellidos) }}</div>
                                        </div>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('calificaciones.asignaciones.show', $asignacion) }}" class="btn btn-sm btn-outline-primary" title="Calificar">
                                                <i class="fas fa-marker"></i>
                                            </a>
                                            <a href="{{ route('asistencia.show', $asignacion) }}" class="btn btn-sm btn-outline-primary" title="Asistencia">
                                                <i class="fas fa-clipboard-check"></i>
                                            </a>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Horario semanal --}}
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3 horario-toolbar">
                    <h2 class="h6 fw-semibold mb-0"><i class="fas fa-calendar-week text-primary me-1"></i> Horario semanal</h2>
                    @if ($asignaciones->isEmpty())
                        <button type="button" class="btn btn-primary btn-sm" disabled title="Asigná al menos una materia primero">
                            <i class="fas fa-plus me-1"></i> Agregar horario
                        </button>
                    @else
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoHorario">
                            <i class="fas fa-plus me-1"></i> Agregar horario
                        </button>
                    @endif
                </div>

                @if ($asignaciones->isEmpty())
                    <div class="empty-state">
                        <div class="empty-icon"><i class="fas fa-calendar-week"></i></div>
                        <p class="mb-0">Asigná materias a este curso para poder armar el horario.</p>
                    </div>
                @else
                    @if ($horarios->isEmpty())
                        <p class="small text-secondary mb-2">Todavía no hay clases cargadas. Hacé click en cualquier celda para agregar una.</p>
                    @endif
                    @include('Rector.gestion-academica.partials.horario-grid', ['horarios' => $horarios, 'puedeAgregar' => true])
                @endif
            </div>
        </div>
    </div>

    @php
        $diasOpciones = ['lunes' => 'Lunes', 'martes' => 'Martes', 'miercoles' => 'Miércoles', 'jueves' => 'Jueves', 'viernes' => 'Viernes', 'sabado' => 'Sábado'];

        // Mismo criterio de color que horario-grid.blade.php, para que el punto
        // del modal coincida con el bloque que el usuario acaba de clickear.
        $normalizarModal = fn ($texto) => str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], mb_strtolower(trim($texto)));
        $patronesColorModal = [
            'educacion fisica' => 'var(--cat-2)', 'matematic' => 'var(--cat-1)', 'espanol' => 'var(--cat-6)',
            'lengua castellana' => 'var(--cat-6)', 'quimic' => 'var(--cat-4)', 'religion' => 'var(--cat-7)',
            'fisica' => 'var(--cat-8)', 'ingles' => 'var(--cat-9)',
        ];
        $colorMateriaModal = function ($idMateria, $nombre) use ($normalizarModal, $patronesColorModal) {
            $nombreNormalizado = $normalizarModal($nombre);
            foreach ($patronesColorModal as $patron => $variable) {
                if (str_contains($nombreNormalizado, $patron)) return $variable;
            }
            return 'var(--cat-'.(($idMateria % 8) + 1).')';
        };
    @endphp

    {{-- Modal: nuevo horario --}}
    <div class="modal fade" id="modalNuevoHorario" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form data-crud-form data-url="{{ route('gestion-academica.horarios.store') }}" novalidate>
                    <div class="modal-header">
                        <h5 class="modal-title font-serif">Nuevo horario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Materia / Profesor *</label>
                                <select class="form-select" name="id_asignacion" data-feedback="err-nh-asignacion" required>
                                    <option value="" selected disabled>Selecciona...</option>
                                    @foreach ($asignaciones as $asignacion)
                                        <option value="{{ $asignacion->id_asignacion }}">
                                            {{ $asignacion->materia->nombre_materia }} — {{ trim($asignacion->profesor->usuario->nombres.' '.$asignacion->profesor->usuario->apellidos) }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="err-nh-asignacion"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Día *</label>
                                <select class="form-select" name="dia_semana" data-feedback="err-nh-dia" required>
                                    @foreach ($diasOpciones as $valor => $etiqueta)
                                        <option value="{{ $valor }}">{{ $etiqueta }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="err-nh-dia"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Salón</label>
                                <input type="text" class="form-control" name="salon" data-feedback="err-nh-salon">
                                <div class="invalid-feedback" id="err-nh-salon"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Hora inicio *</label>
                                <input type="time" class="form-control" name="hora_inicio" data-feedback="err-nh-inicio" required>
                                <div class="invalid-feedback" id="err-nh-inicio"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Hora fin *</label>
                                <input type="time" class="form-control" name="hora_fin" data-feedback="err-nh-fin" required>
                                <div class="invalid-feedback" id="err-nh-fin"></div>
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

    {{-- Modales: editar horario (uno por fila) --}}
    @foreach ($horarios as $horario)
        @php
            $profesorModal = trim($horario->asignacion->profesor->usuario->nombres.' '.$horario->asignacion->profesor->usuario->apellidos);
            $colorModal = $colorMateriaModal($horario->asignacion->id_materia, $horario->asignacion->materia->nombre_materia);
        @endphp
        <div class="modal fade" id="modalEditarHorario{{ $horario->id_horario }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form data-crud-form data-url="{{ route('gestion-academica.horarios.update', $horario) }}" novalidate>
                        <div class="modal-header">
                            <h5 class="modal-title font-serif">Editar horario</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="horario-modal__resumen" style="--bloque-color: {{ $colorModal }};">
                                <span class="horario-modal__dot"></span>
                                <div>
                                    <div class="horario-modal__resumen-materia">{{ $horario->asignacion->materia->nombre_materia }}</div>
                                    <div class="horario-modal__resumen-meta">{{ $profesorModal }} · {{ $curso->nombre_curso }}</div>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Materia / Profesor *</label>
                                    <select class="form-select" name="id_asignacion" data-feedback="err-eh-asignacion-{{ $horario->id_horario }}" required>
                                        @foreach ($asignaciones as $asignacion)
                                            <option value="{{ $asignacion->id_asignacion }}" @selected($horario->id_asignacion == $asignacion->id_asignacion)>
                                                {{ $asignacion->materia->nombre_materia }} — {{ trim($asignacion->profesor->usuario->nombres.' '.$asignacion->profesor->usuario->apellidos) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="err-eh-asignacion-{{ $horario->id_horario }}"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Día *</label>
                                    <select class="form-select" name="dia_semana" data-feedback="err-eh-dia-{{ $horario->id_horario }}" required>
                                        @foreach ($diasOpciones as $valor => $etiqueta)
                                            <option value="{{ $valor }}" @selected($horario->dia_semana === $valor)>{{ $etiqueta }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="err-eh-dia-{{ $horario->id_horario }}"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Salón</label>
                                    <input type="text" class="form-control" name="salon" value="{{ $horario->salon }}" data-feedback="err-eh-salon-{{ $horario->id_horario }}">
                                    <div class="invalid-feedback" id="err-eh-salon-{{ $horario->id_horario }}"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Hora inicio *</label>
                                    <input type="time" class="form-control" name="hora_inicio" value="{{ substr($horario->hora_inicio, 0, 5) }}" data-feedback="err-eh-inicio-{{ $horario->id_horario }}" required>
                                    <div class="invalid-feedback" id="err-eh-inicio-{{ $horario->id_horario }}"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Hora fin *</label>
                                    <input type="time" class="form-control" name="hora_fin" value="{{ substr($horario->hora_fin, 0, 5) }}" data-feedback="err-eh-fin-{{ $horario->id_horario }}" required>
                                    <div class="invalid-feedback" id="err-eh-fin-{{ $horario->id_horario }}"></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-danger me-auto" data-desactivar
                                    data-url="{{ route('gestion-academica.horarios.desactivar', $horario) }}"
                                    data-nombre="esta clase"
                                    data-confirm-title="Eliminar clase"
                                    data-confirm-text="¿Está seguro que desea eliminar esta clase?"
                                    data-confirm-btn="Eliminar">
                                <i class="fas fa-trash me-1"></i> Eliminar clase
                            </button>
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@push('scripts')
    @vite('resources/js/pages/gestion-academica/curso-detalle.js')
@endpush
