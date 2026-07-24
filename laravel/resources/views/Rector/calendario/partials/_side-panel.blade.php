{{--
    Panel lateral (Offcanvas de Bootstrap, ya en el toolkit). Todo el
    contenido lo llena calendar-events.js: para un `evento` real, desde
    EventoController::show(); para una ocurrencia derivada (horario/
    actividad), directamente desde extendedProps (sin pegarle a show()).
--}}
<div class="offcanvas offcanvas-end calendario-panel" tabindex="-1" id="panelEvento" aria-labelledby="panelEventoTitulo">
    <div class="offcanvas-header">
        <div>
            <span class="badge calendario-panel-badge d-none" id="panelEventoCategoria"></span>
            <h5 class="offcanvas-title font-serif mt-1" id="panelEventoTitulo"></h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body">
        <div id="panelEventoCargando" class="text-center text-secondary py-5 d-none">
            <div class="spinner-border spinner-border-sm"></div> Cargando…
        </div>

        {{-- Evento real: detalle completo + acciones + colaboración --}}
        <div id="panelEventoContenido" class="d-none">
            <div class="d-flex flex-wrap gap-2 mb-3" id="panelEventoAcciones">
                <button type="button" class="btn btn-sm btn-primary d-none" data-panel-editar>
                    <i class="bi bi-pencil"></i> Editar
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary d-none" data-panel-duplicar>
                    <i class="bi bi-copy"></i> Duplicar
                </button>
                <div class="dropdown d-inline-block d-none" data-panel-estado-wrapper>
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        Cambiar estado
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" data-panel-estado="pendiente">Pendiente</a></li>
                        <li><a class="dropdown-item" href="#" data-panel-estado="completado">Completado</a></li>
                        <li><a class="dropdown-item" href="#" data-panel-estado="cancelado">Cancelado</a></li>
                    </ul>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger d-none" data-panel-eliminar>
                    <i class="bi bi-trash"></i> Eliminar
                </button>
            </div>

            <dl class="calendario-panel-detalle" id="panelEventoDetalle"></dl>

            <div class="mb-3">
                <h6 class="calendario-panel-subtitulo">Participantes</h6>
                <ul class="list-unstyled small" id="panelEventoParticipantes"></ul>
            </div>

            <div class="mb-3">
                <h6 class="calendario-panel-subtitulo">Adjuntos</h6>
                <ul class="list-unstyled small" id="panelEventoAdjuntos"></ul>
                <form id="panelEventoFormAdjunto" class="d-none" enctype="multipart/form-data">
                    <input type="file" class="form-control form-control-sm" name="adjunto">
                </form>
            </div>

            <div class="mb-3">
                <h6 class="calendario-panel-subtitulo">Comentarios</h6>
                <ul class="list-unstyled small" id="panelEventoComentarios"></ul>
                <form id="panelEventoFormComentario" class="d-none">
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" name="comentario" placeholder="Escribe un comentario..." maxlength="1000">
                        <button class="btn btn-primary" type="submit" aria-label="Enviar comentario"><i class="bi bi-send"></i></button>
                    </div>
                </form>
            </div>

            <div>
                <h6 class="calendario-panel-subtitulo">Historial</h6>
                <ul class="list-unstyled small text-secondary" id="panelEventoHistorial"></ul>
            </div>
        </div>

        {{-- Ocurrencia derivada (horario/actividad): detalle de solo lectura, sin pegarle a show() --}}
        <div id="panelEventoSoloLectura" class="d-none">
            <dl class="calendario-panel-detalle" id="panelEventoDetalleSoloLectura"></dl>
        </div>
    </div>
</div>
