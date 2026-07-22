<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h6 fw-semibold mb-0"><i class="fas fa-calendar-week text-primary me-1"></i> Horario semanal por curso</h2>
</div>

@if ($cursosParaHorario->isEmpty())
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-calendar-week"></i></div>
        <p class="mb-2">Todavía no hay cursos activos para armar un horario.</p>
        <a href="{{ route('gestion-academica.index', ['tab' => 'cursos']) }}" class="btn btn-sm btn-primary">Ir a Cursos</a>
    </div>
@else
    <form method="GET" action="{{ route('gestion-academica.index') }}" class="horario-toolbar" data-autosubmit-form>
        <input type="hidden" name="tab" value="horarios">
        <select class="form-select" name="curso" data-autosubmit>
            @foreach ($cursosParaHorario as $curso)
                <option value="{{ $curso->id_curso }}" @selected($curso->id_curso == $cursoHorarioId)>
                    {{ $curso->nombre_curso }} · {{ ucfirst($curso->jornada) }} · {{ $curso->anio_lectivo }}
                </option>
            @endforeach
        </select>
        <div class="ms-md-auto">
            @if ($asignacionesHorario->isEmpty())
                <button type="button" class="btn btn-primary btn-sm" disabled title="Asigná al menos una materia a este curso primero">
                    <i class="fas fa-plus me-1"></i> Agregar horario
                </button>
            @else
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoHorario">
                    <i class="fas fa-plus me-1"></i> Agregar horario
                </button>
            @endif
        </div>
    </form>

    @if ($asignacionesHorario->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-calendar-week"></i></div>
            <p class="mb-0">Asigná materias a este curso para poder armar el horario.</p>
        </div>
    @else
        @if ($horariosCurso->isEmpty())
            <p class="small text-secondary mb-2">Todavía no hay clases cargadas. Hacé click en cualquier celda para agregar una.</p>
        @endif
        @include('Rector.gestion-academica.partials.horario-grid', ['horarios' => $horariosCurso, 'puedeAgregar' => true])
    @endif

    @php
        $diasCompletos = ['lunes' => 'Lunes', 'martes' => 'Martes', 'miercoles' => 'Miércoles', 'jueves' => 'Jueves', 'viernes' => 'Viernes', 'sabado' => 'Sábado'];
        $cursoActual = $cursosParaHorario->firstWhere('id_curso', $cursoHorarioId);

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
                                <select class="form-select" name="id_asignacion" data-feedback="err-nhg-asignacion" required>
                                    <option value="" selected disabled>Selecciona...</option>
                                    @foreach ($asignacionesHorario as $asignacion)
                                        <option value="{{ $asignacion->id_asignacion }}">
                                            {{ $asignacion->materia->nombre_materia }} — {{ trim($asignacion->profesor->usuario->nombres.' '.$asignacion->profesor->usuario->apellidos) }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="err-nhg-asignacion"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Día *</label>
                                <select class="form-select" name="dia_semana" data-feedback="err-nhg-dia" required>
                                    @foreach ($diasCompletos as $valor => $etiqueta)
                                        <option value="{{ $valor }}">{{ $etiqueta }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="err-nhg-dia"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Salón</label>
                                <input type="text" class="form-control" name="salon" data-feedback="err-nhg-salon">
                                <div class="invalid-feedback" id="err-nhg-salon"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Hora inicio *</label>
                                <input type="time" class="form-control" name="hora_inicio" data-feedback="err-nhg-inicio" required>
                                <div class="invalid-feedback" id="err-nhg-inicio"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Hora fin *</label>
                                <input type="time" class="form-control" name="hora_fin" data-feedback="err-nhg-fin" required>
                                <div class="invalid-feedback" id="err-nhg-fin"></div>
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

    {{-- Modales: editar horario (uno por bloque) --}}
    @foreach ($horariosCurso as $horario)
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
                                    <div class="horario-modal__resumen-meta">
                                        {{ $profesorModal }}
                                        @if ($cursoActual) · {{ $cursoActual->nombre_curso }} @endif
                                    </div>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Materia / Profesor *</label>
                                    <select class="form-select" name="id_asignacion" data-feedback="err-ehg-asignacion-{{ $horario->id_horario }}" required>
                                        @foreach ($asignacionesHorario as $asignacion)
                                            <option value="{{ $asignacion->id_asignacion }}" @selected($horario->id_asignacion == $asignacion->id_asignacion)>
                                                {{ $asignacion->materia->nombre_materia }} — {{ trim($asignacion->profesor->usuario->nombres.' '.$asignacion->profesor->usuario->apellidos) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="err-ehg-asignacion-{{ $horario->id_horario }}"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Día *</label>
                                    <select class="form-select" name="dia_semana" data-feedback="err-ehg-dia-{{ $horario->id_horario }}" required>
                                        @foreach ($diasCompletos as $valor => $etiqueta)
                                            <option value="{{ $valor }}" @selected($horario->dia_semana === $valor)>{{ $etiqueta }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="err-ehg-dia-{{ $horario->id_horario }}"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Salón</label>
                                    <input type="text" class="form-control" name="salon" value="{{ $horario->salon }}" data-feedback="err-ehg-salon-{{ $horario->id_horario }}">
                                    <div class="invalid-feedback" id="err-ehg-salon-{{ $horario->id_horario }}"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Hora inicio *</label>
                                    <input type="time" class="form-control" name="hora_inicio" value="{{ substr($horario->hora_inicio, 0, 5) }}" data-feedback="err-ehg-inicio-{{ $horario->id_horario }}" required>
                                    <div class="invalid-feedback" id="err-ehg-inicio-{{ $horario->id_horario }}"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Hora fin *</label>
                                    <input type="time" class="form-control" name="hora_fin" value="{{ substr($horario->hora_fin, 0, 5) }}" data-feedback="err-ehg-fin-{{ $horario->id_horario }}" required>
                                    <div class="invalid-feedback" id="err-ehg-fin-{{ $horario->id_horario }}"></div>
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
@endif
