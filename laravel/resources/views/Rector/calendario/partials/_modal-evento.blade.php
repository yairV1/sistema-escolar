{{--
    Modal único reutilizado para crear y editar eventos (a diferencia de
    Materias/Horarios, los eventos no se renderizan como filas server-side
    — viven dentro de FullCalendar). calendar-modal.js decide el título, fija
    data-url dinámicamente y pre-rellena los campos al editar. El envío en
    sí sigue usando initCrudForms() de listActions.js sin cambios.
--}}
<div class="modal fade" id="modalEvento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form data-crud-form data-url="{{ route('calendario.store') }}" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title font-serif" id="modalEventoTitulo">Nuevo evento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Título *</label>
                            <input type="text" class="form-control" name="titulo" data-feedback="err-ev-titulo" required>
                            <div class="invalid-feedback" id="err-ev-titulo"></div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Categoría *</label>
                            <select class="form-select" name="id_categoria" data-feedback="err-ev-categoria" required>
                                @foreach ($categoriasCreables as $categoria)
                                    <option value="{{ $categoria->id_categoria }}">{{ $categoria->nombre }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="err-ev-categoria"></div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Prioridad *</label>
                            <select class="form-select" name="prioridad" data-feedback="err-ev-prioridad" required>
                                <option value="baja">Baja</option>
                                <option value="media" selected>Media</option>
                                <option value="alta">Alta</option>
                                <option value="urgente">Urgente</option>
                            </select>
                            <div class="invalid-feedback" id="err-ev-prioridad"></div>
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="evTodoElDia" name="todo_el_dia" value="1" data-evento-todo-dia>
                                <label class="form-check-label" for="evTodoElDia">Todo el día</label>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label">Fecha inicio *</label>
                            <input type="date" class="form-control" name="fecha_inicio" data-feedback="err-ev-fecha-inicio" required>
                            <div class="invalid-feedback" id="err-ev-fecha-inicio"></div>
                        </div>
                        <div class="col-6 col-md-3" data-evento-campo-hora>
                            <label class="form-label">Hora inicio</label>
                            <input type="time" class="form-control" name="hora_inicio" data-feedback="err-ev-hora-inicio">
                            <div class="invalid-feedback" id="err-ev-hora-inicio"></div>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Fecha fin *</label>
                            <input type="date" class="form-control" name="fecha_fin" data-feedback="err-ev-fecha-fin" required>
                            <div class="invalid-feedback" id="err-ev-fecha-fin"></div>
                        </div>
                        <div class="col-6 col-md-3" data-evento-campo-hora>
                            <label class="form-label">Hora fin</label>
                            <input type="time" class="form-control" name="hora_fin" data-feedback="err-ev-hora-fin">
                            <div class="invalid-feedback" id="err-ev-hora-fin"></div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion" rows="2" data-feedback="err-ev-descripcion"></textarea>
                            <div class="invalid-feedback" id="err-ev-descripcion"></div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Salón</label>
                            <input type="text" class="form-control" name="salon" data-feedback="err-ev-salon">
                            <div class="invalid-feedback" id="err-ev-salon"></div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Ubicación</label>
                            <input type="text" class="form-control" name="ubicacion" data-feedback="err-ev-ubicacion">
                            <div class="invalid-feedback" id="err-ev-ubicacion"></div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Visibilidad *</label>
                            <select class="form-select" name="visibilidad" data-feedback="err-ev-visibilidad" data-evento-visibilidad required>
                                <option value="privado" selected>Privado (solo yo)</option>
                                <option value="publico">Público</option>
                                <option value="compartido">Compartido con personas específicas</option>
                            </select>
                            <div class="invalid-feedback" id="err-ev-visibilidad"></div>
                        </div>

                        <div class="col-12" data-evento-campo-participantes hidden>
                            <label class="form-label">Compartir con *</label>
                            <select class="form-select" name="participantes[]" data-feedback="err-ev-participantes" multiple size="5">
                                @foreach ($personalParaCompartir as $persona)
                                    <option value="{{ $persona->id_usuario }}">{{ trim($persona->nombres.' '.$persona->apellidos) }} — {{ $persona->rolLabel }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Ctrl/Cmd + clic para elegir varias personas.</div>
                            <div class="invalid-feedback" id="err-ev-participantes"></div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Repetir</label>
                            <select class="form-select" name="tipo_recurrencia" data-feedback="err-ev-recurrencia" data-evento-tipo-recurrencia>
                                <option value="ninguna" selected>No se repite</option>
                                <option value="diaria">Diariamente</option>
                                <option value="semanal">Semanalmente</option>
                                <option value="mensual">Mensualmente</option>
                            </select>
                            <div class="invalid-feedback" id="err-ev-recurrencia"></div>
                        </div>

                        <div class="col-12" data-evento-campos-recurrencia hidden>
                            <div class="row g-3 align-items-end">
                                <div class="col-6 col-md-4">
                                    <label class="form-label">Cada</label>
                                    <input type="number" class="form-control" name="intervalo_recurrencia" min="1" max="52" value="1">
                                </div>
                                <div class="col-6 col-md-4">
                                    <label class="form-label">Hasta</label>
                                    <input type="date" class="form-control" name="fecha_fin_recurrencia">
                                </div>
                                <div class="col-12" data-evento-dias-semana hidden>
                                    <label class="form-label d-block">Días</label>
                                    @foreach (['lunes' => 'L', 'martes' => 'M', 'miercoles' => 'X', 'jueves' => 'J', 'viernes' => 'V', 'sabado' => 'S'] as $valor => $etiqueta)
                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" class="form-check-input" name="dias_semana_recurrencia[]" value="{{ $valor }}" id="evDia{{ $valor }}">
                                            <label class="form-check-label" for="evDia{{ $valor }}">{{ $etiqueta }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label d-block">Recordarme</label>
                            @foreach (config('calendario.recordatorio_opciones_minutos') as $minutos => $etiqueta)
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" class="form-check-input" name="recordatorio_minutos_antes[]" value="{{ $minutos }}" id="evRecordatorio{{ $minutos }}">
                                    <label class="form-check-label" for="evRecordatorio{{ $minutos }}">{{ $etiqueta }}</label>
                                </div>
                            @endforeach
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
