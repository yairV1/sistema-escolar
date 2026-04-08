<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Comunicados — Colegio San Cristóbal</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/css/admin.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/css/comunicados.css" />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>

<body>

    <?php include_once __DIR__ . '/../../layouts/administrativo/sidebar.php'; ?>
    <div class="admin-overlay" id="adminOverlay"></div>
    <?php include_once __DIR__ . '/../../layouts/administrativo/nav.php'; ?>

    <main class="admin-main" id="adminMain">
        <div class="admin-container">

            <!-- ══ ENCABEZADO ══ -->
            <section class="page-header reveal">
                <div class="ph-left">
                    <h1 class="ph-title"><i class="fas fa-bullhorn"></i> Comunicados</h1>
                    <p class="ph-desc">Redacta, gestiona y envía comunicados a la comunidad educativa.</p>
                </div>
                <div class="ph-actions">
                    <button class="ph-btn-nueva" id="btnNuevoComunicado">
                        <i class="fas fa-plus"></i> Nuevo comunicado
                    </button>
                </div>
            </section>

            <!-- ══ KPIs ══ -->
            <section class="com-kpis reveal">
                <div class="ckpi-card" data-filter="">
                    <div class="ckpi-icon total"><i class="fas fa-bullhorn"></i></div>
                    <div class="ckpi-info">
                        <strong class="ckpi-val" id="kpiTotal">24</strong>
                        <span class="ckpi-label">Total</span>
                    </div>
                </div>
                <div class="ckpi-card" data-filter="enviado">
                    <div class="ckpi-icon enviado"><i class="fas fa-paper-plane"></i></div>
                    <div class="ckpi-info">
                        <strong class="ckpi-val" id="kpiEnviados">18</strong>
                        <span class="ckpi-label">Enviados</span>
                    </div>
                </div>
                <div class="ckpi-card" data-filter="borrador">
                    <div class="ckpi-icon borrador"><i class="fas fa-pen"></i></div>
                    <div class="ckpi-info">
                        <strong class="ckpi-val" id="kpiBorradores">4</strong>
                        <span class="ckpi-label">Borradores</span>
                    </div>
                </div>
                <div class="ckpi-card" data-filter="urgente">
                    <div class="ckpi-icon urgente"><i class="fas fa-exclamation-circle"></i></div>
                    <div class="ckpi-info">
                        <strong class="ckpi-val" id="kpiUrgentes">2</strong>
                        <span class="ckpi-label">Urgentes</span>
                    </div>
                </div>
                <div class="ckpi-card" data-filter="programado">
                    <div class="ckpi-icon programado"><i class="fas fa-clock"></i></div>
                    <div class="ckpi-info">
                        <strong class="ckpi-val" id="kpiProgramados">2</strong>
                        <span class="ckpi-label">Programados</span>
                    </div>
                </div>
            </section>

            <!-- ══ TOOLBAR ══ -->
            <div class="com-toolbar reveal">
                <div class="com-search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" id="comSearch" class="com-search" placeholder="Buscar comunicado…" />
                    <button class="com-search-clear" id="comSearchClear" style="display:none;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="com-filters">
                    <select class="com-filter" id="filtroTipo">
                        <option value="">Todos los tipos</option>
                        <option value="urgente">Urgente</option>
                        <option value="informativo">Informativo</option>
                        <option value="evento">Evento</option>
                        <option value="academico">Académico</option>
                    </select>
                    <select class="com-filter" id="filtroEstado">
                        <option value="">Todos los estados</option>
                        <option value="enviado">Enviado</option>
                        <option value="borrador">Borrador</option>
                        <option value="programado">Programado</option>
                    </select>
                    <select class="com-filter" id="filtroDestinatario">
                        <option value="">Todos los destinatarios</option>
                        <option value="comunidad">Toda la comunidad</option>
                        <option value="docentes">Docentes</option>
                        <option value="directivos">Directivos</option>
                        <option value="grado">Por grado</option>
                    </select>
                    <button class="com-filter-clear" id="btnClearFilters">
                        <i class="fas fa-times"></i> Limpiar
                    </button>
                </div>
            </div>

            <!-- ══ LISTADO DE COMUNICADOS ══ -->
            <div class="com-lista-card reveal">
                <div class="com-lista-header">
                    <span class="com-count" id="comCount">Mostrando <strong>24</strong> comunicados</span>
                    <div class="com-tabs" id="comTabs">
                        <button class="com-tab active" data-tab="todos">Todos</button>
                        <button class="com-tab" data-tab="enviado">Enviados</button>
                        <button class="com-tab" data-tab="borrador">Borradores</button>
                        <button class="com-tab" data-tab="programado">Programados</button>
                    </div>
                </div>

                <div class="com-lista" id="comLista">
                    <!-- Generado por JS -->
                </div>

                <div class="com-empty" id="comEmpty" style="display:none;">
                    <i class="fas fa-bullhorn"></i>
                    <p>No se encontraron comunicados con los filtros aplicados.</p>
                    <button class="com-empty-btn" id="btnEmptyReset">Limpiar filtros</button>
                </div>
            </div>

        </div>
    </main>

    <!-- ══════════════════════════════════════
         MODAL EDITOR DE COMUNICADO
    ══════════════════════════════════════ -->
    <div class="modal-overlay" id="modalEditor">
        <div class="modal-box modal-editor">
            <button class="modal-close-btn" id="modalEditorClose"><i class="fas fa-times"></i></button>

            <div class="me-header">
                <div class="me-header-icon"><i class="fas fa-bullhorn"></i></div>
                <div>
                    <h3 id="editorTitle">Nuevo comunicado</h3>
                    <p id="editorSubtitle">Completa el formulario y previsualiza antes de enviar</p>
                </div>
            </div>

            <!-- Tabs editor / preview -->
            <div class="me-tabs">
                <button class="me-tab active" data-etab="editor">
                    <i class="fas fa-edit"></i> Redactar
                </button>
                <button class="me-tab" data-etab="preview">
                    <i class="fas fa-eye"></i> Vista previa
                </button>
                <button class="me-tab" data-etab="permisos">
                    <i class="fas fa-lock"></i> Permisos
                </button>
            </div>

            <!-- ── Tab: Editor ── -->
            <div class="me-panel active" id="etab-editor">
                <div class="me-form">

                    <div class="me-row-2">
                        <!-- Tipo -->
                        <div class="me-group">
                            <label class="me-label">Tipo <span class="me-req">*</span></label>
                            <div class="me-tipo-group" id="tipoGroup">
                                <label class="me-tipo-item">
                                    <input type="radio" name="tipo" value="informativo" checked />
                                    <span class="me-tipo-btn informativo">
                                        <i class="fas fa-info-circle"></i> Informativo
                                    </span>
                                </label>
                                <label class="me-tipo-item">
                                    <input type="radio" name="tipo" value="urgente" />
                                    <span class="me-tipo-btn urgente">
                                        <i class="fas fa-exclamation-circle"></i> Urgente
                                    </span>
                                </label>
                                <label class="me-tipo-item">
                                    <input type="radio" name="tipo" value="evento" />
                                    <span class="me-tipo-btn evento">
                                        <i class="fas fa-calendar-star"></i> Evento
                                    </span>
                                </label>
                                <label class="me-tipo-item">
                                    <input type="radio" name="tipo" value="academico" />
                                    <span class="me-tipo-btn academico">
                                        <i class="fas fa-graduation-cap"></i> Académico
                                    </span>
                                </label>
                            </div>
                            <span class="me-error" id="err-tipo"></span>
                        </div>
                    </div>

                    <!-- Asunto -->
                    <div class="me-group">
                        <label class="me-label" for="comAsunto">Asunto <span class="me-req">*</span></label>
                        <div class="me-input-wrap">
                            <i class="fas fa-heading me-icon"></i>
                            <input type="text" id="comAsunto" class="me-input"
                                placeholder="Ej. Reunión de padres — Abril 2025" maxlength="120" />
                        </div>
                        <div class="me-char-count"><span id="asuntoCount">0</span>/120</div>
                        <span class="me-error" id="err-asunto"></span>
                    </div>

                    <!-- Destinatarios -->
                    <div class="me-group">
                        <label class="me-label">Destinatarios <span class="me-req">*</span></label>
                        <div class="me-dest-grid">
                            <label class="me-dest-item">
                                <input type="checkbox" name="dest" value="comunidad" id="destComunidad" />
                                <span class="me-dest-btn">
                                    <i class="fas fa-users"></i> Toda la comunidad
                                </span>
                            </label>
                            <label class="me-dest-item">
                                <input type="checkbox" name="dest" value="docentes" id="destDocentes" />
                                <span class="me-dest-btn">
                                    <i class="fas fa-chalkboard-teacher"></i> Docentes
                                </span>
                            </label>
                            <label class="me-dest-item">
                                <input type="checkbox" name="dest" value="directivos" id="destDirectivos" />
                                <span class="me-dest-btn">
                                    <i class="fas fa-user-tie"></i> Directivos
                                </span>
                            </label>
                            <label class="me-dest-item">
                                <input type="checkbox" name="dest" value="grado" id="destGrado" />
                                <span class="me-dest-btn">
                                    <i class="fas fa-graduation-cap"></i> Por grado
                                </span>
                            </label>
                        </div>
                        <span class="me-error" id="err-dest"></span>
                    </div>

                    <!-- Selector de grados (condicional) -->
                    <div class="me-group" id="gradoSelectorWrap" style="display:none;">
                        <label class="me-label">Seleccionar grados</label>
                        <div class="me-grado-grid">
                            <label class="me-grado-item"><input type="checkbox" name="gradosDest" value="PRE" /><span>Preescolar</span></label>
                            <label class="me-grado-item"><input type="checkbox" name="gradosDest" value="1"   /><span>1°</span></label>
                            <label class="me-grado-item"><input type="checkbox" name="gradosDest" value="2"   /><span>2°</span></label>
                            <label class="me-grado-item"><input type="checkbox" name="gradosDest" value="3"   /><span>3°</span></label>
                            <label class="me-grado-item"><input type="checkbox" name="gradosDest" value="4"   /><span>4°</span></label>
                            <label class="me-grado-item"><input type="checkbox" name="gradosDest" value="5"   /><span>5°</span></label>
                            <label class="me-grado-item"><input type="checkbox" name="gradosDest" value="6"   /><span>6°</span></label>
                            <label class="me-grado-item"><input type="checkbox" name="gradosDest" value="7"   /><span>7°</span></label>
                            <label class="me-grado-item"><input type="checkbox" name="gradosDest" value="8"   /><span>8°</span></label>
                            <label class="me-grado-item"><input type="checkbox" name="gradosDest" value="9"   /><span>9°</span></label>
                            <label class="me-grado-item"><input type="checkbox" name="gradosDest" value="10"  /><span>10°</span></label>
                            <label class="me-grado-item"><input type="checkbox" name="gradosDest" value="11"  /><span>11°</span></label>
                        </div>
                    </div>

                    <!-- Mensaje -->
                    <div class="me-group">
                        <label class="me-label" for="comMensaje">Mensaje <span class="me-req">*</span></label>
                        <!-- Toolbar editor -->
                        <div class="me-toolbar">
                            <button type="button" class="me-tb-btn" data-cmd="bold" title="Negrita"><i class="fas fa-bold"></i></button>
                            <button type="button" class="me-tb-btn" data-cmd="italic" title="Cursiva"><i class="fas fa-italic"></i></button>
                            <button type="button" class="me-tb-btn" data-cmd="underline" title="Subrayado"><i class="fas fa-underline"></i></button>
                            <div class="me-tb-divider"></div>
                            <button type="button" class="me-tb-btn" data-cmd="insertUnorderedList" title="Lista"><i class="fas fa-list-ul"></i></button>
                            <button type="button" class="me-tb-btn" data-cmd="insertOrderedList" title="Lista numerada"><i class="fas fa-list-ol"></i></button>
                            <div class="me-tb-divider"></div>
                            <button type="button" class="me-tb-btn" data-cmd="justifyLeft"   title="Izquierda"><i class="fas fa-align-left"></i></button>
                            <button type="button" class="me-tb-btn" data-cmd="justifyCenter" title="Centro"><i class="fas fa-align-center"></i></button>
                            <button type="button" class="me-tb-btn" data-cmd="justifyRight"  title="Derecha"><i class="fas fa-align-right"></i></button>
                        </div>
                        <div id="comMensaje" class="me-editor" contenteditable="true"
                            data-placeholder="Escribe el contenido del comunicado aquí…"></div>
                        <span class="me-error" id="err-mensaje"></span>
                    </div>

                    <!-- Programar envío -->
                    <div class="me-group">
                        <label class="me-label">
                            <label class="me-toggle-wrap">
                                <input type="checkbox" id="toggleProgramar" />
                                <span class="me-toggle"></span>
                            </label>
                            Programar envío para fecha específica
                        </label>
                        <div class="me-programar-wrap" id="programarWrap" style="display:none;">
                            <div class="me-input-wrap">
                                <i class="fas fa-calendar me-icon"></i>
                                <input type="datetime-local" id="fechaProgramada" class="me-input" />
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ── Tab: Vista previa ── -->
            <div class="me-panel" id="etab-preview">
                <div class="me-preview-wrap">
                    <div class="me-preview-card" id="previewCard">
                        <div class="mpv-header" id="pvHeader">
                            <div class="mpv-tipo" id="pvTipo">Informativo</div>
                            <div class="mpv-fecha">Vista previa · <span id="pvFecha">—</span></div>
                        </div>
                        <div class="mpv-body">
                            <h2 class="mpv-asunto" id="pvAsunto">Sin asunto</h2>
                            <div class="mpv-dest" id="pvDest">
                                <i class="fas fa-users"></i> <span>Sin destinatarios</span>
                            </div>
                            <div class="mpv-mensaje" id="pvMensaje">
                                <p style="color:#b0bfb0;font-style:italic">El contenido del comunicado aparecerá aquí…</p>
                            </div>
                        </div>
                        <div class="mpv-footer">
                            <div class="mpv-firma">
                                <strong>Rosa Cardona</strong>
                                <span>Rectora · Colegio San Cristóbal</span>
                            </div>
                            <div class="mpv-logo">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Tab: Permisos ── -->
            <div class="me-panel" id="etab-permisos">
                <div class="me-permisos-wrap">

                    <div class="mp-info-box">
                        <i class="fas fa-info-circle"></i>
                        <p>Define quién puede ver y modificar este comunicado después de creado.</p>
                    </div>

                    <!-- Visibilidad -->
                    <div class="mp-section">
                        <h4 class="mp-section-title"><i class="fas fa-eye"></i> Visibilidad</h4>
                        <div class="mp-radio-group">
                            <label class="mp-radio-item">
                                <input type="radio" name="visibilidad" value="todos" checked />
                                <span class="mp-radio-box"></span>
                                <div>
                                    <strong>Todos los administradores</strong>
                                    <p>Cualquier usuario con rol administrativo puede verlo</p>
                                </div>
                            </label>
                            <label class="mp-radio-item">
                                <input type="radio" name="visibilidad" value="autor" />
                                <span class="mp-radio-box"></span>
                                <div>
                                    <strong>Solo el autor</strong>
                                    <p>Visible únicamente para quien lo creó</p>
                                </div>
                            </label>
                            <label class="mp-radio-item">
                                <input type="radio" name="visibilidad" value="roles" />
                                <span class="mp-radio-box"></span>
                                <div>
                                    <strong>Roles específicos</strong>
                                    <p>Elige qué roles pueden ver este comunicado</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Edición -->
                    <div class="mp-section">
                        <h4 class="mp-section-title"><i class="fas fa-edit"></i> ¿Quién puede editar?</h4>
                        <div class="mp-check-group">
                            <label class="mp-check-item">
                                <input type="checkbox" name="puedeEditar" value="rector" checked disabled />
                                <span class="mp-check-box"></span>
                                <div>
                                    <strong>Rector/a</strong>
                                    <span class="mp-check-badge siempre">Siempre</span>
                                </div>
                            </label>
                            <label class="mp-check-item">
                                <input type="checkbox" name="puedeEditar" value="coord" />
                                <span class="mp-check-box"></span>
                                <div>
                                    <strong>Coordinadores</strong>
                                    <span class="mp-check-badge">Opcional</span>
                                </div>
                            </label>
                            <label class="mp-check-item">
                                <input type="checkbox" name="puedeEditar" value="secretaria" />
                                <span class="mp-check-box"></span>
                                <div>
                                    <strong>Secretaría</strong>
                                    <span class="mp-check-badge">Opcional</span>
                                </div>
                            </label>
                            <label class="mp-check-item">
                                <input type="checkbox" name="puedeEditar" value="docente_lider" />
                                <span class="mp-check-box"></span>
                                <div>
                                    <strong>Docente líder</strong>
                                    <span class="mp-check-badge">Opcional</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Envío -->
                    <div class="mp-section">
                        <h4 class="mp-section-title"><i class="fas fa-paper-plane"></i> ¿Quién puede enviar?</h4>
                        <div class="mp-check-group">
                            <label class="mp-check-item">
                                <input type="checkbox" name="puedeEnviar" value="rector" checked disabled />
                                <span class="mp-check-box"></span>
                                <div>
                                    <strong>Rector/a</strong>
                                    <span class="mp-check-badge siempre">Siempre</span>
                                </div>
                            </label>
                            <label class="mp-check-item">
                                <input type="checkbox" name="puedeEnviar" value="coord" />
                                <span class="mp-check-box"></span>
                                <div>
                                    <strong>Coordinadores</strong>
                                    <span class="mp-check-badge">Opcional</span>
                                </div>
                            </label>
                            <label class="mp-check-item">
                                <input type="checkbox" name="puedeEnviar" value="secretaria" />
                                <span class="mp-check-box"></span>
                                <div>
                                    <strong>Secretaría</strong>
                                    <span class="mp-check-badge">Opcional</span>
                                </div>
                            </label>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Footer del modal -->
            <div class="me-footer">
                <button class="me-btn-cancel" id="btnEditorCancelar">Cancelar</button>
                <div class="me-footer-right">
                    <button class="me-btn-draft" id="btnGuardarBorrador">
                        <i class="fas fa-save"></i> Guardar borrador
                    </button>
                    <button class="me-btn-preview-quick" id="btnPreviewQuick">
                        <i class="fas fa-eye"></i> Previsualizar
                    </button>
                    <button class="me-btn-send" id="btnEnviarComunicado">
                        <i class="fas fa-paper-plane"></i> Enviar ahora
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal confirmación envío -->
    <div class="modal-overlay" id="modalConfirm">
        <div class="modal-box modal-confirm-com">
            <div class="mcc-icon" id="mccIcon"><i class="fas fa-paper-plane"></i></div>
            <h3 id="mccTitle">¿Enviar comunicado?</h3>
            <p id="mccMsg">Esta acción enviará el comunicado a los destinatarios seleccionados.</p>
            <div class="mcc-dest-preview" id="mccDestPreview"></div>
            <div class="mcc-btns">
                <button class="mcc-btn-cancel" id="btnConfirmCancel">Cancelar</button>
                <button class="mcc-btn-ok" id="btnConfirmOk">
                    <i class="fas fa-paper-plane"></i> Confirmar envío
                </button>
            </div>
        </div>
    </div>

    <!-- Modal éxito -->
    <div class="modal-overlay" id="modalExito">
        <div class="modal-box modal-exito-com">
            <div class="mex-circle"><i class="fas fa-check"></i></div>
            <h3 id="mexTitle">¡Comunicado enviado!</h3>
            <p id="mexMsg">El comunicado fue enviado correctamente a los destinatarios.</p>
            <div class="mex-btns">
                <button class="mex-btn-nuevo" id="btnMexNuevo">
                    <i class="fas fa-plus"></i> Nuevo comunicado
                </button>
                <button class="mex-btn-ok" id="btnMexOk">
                    <i class="fas fa-check"></i> Listo
                </button>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/js/admin.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/js/comunicados.js"></script>
</body>
</html>