<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Matrículas — Colegio San Cristóbal</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/css/admin.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/css/matriculas.css" />
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
                    <h1 class="ph-title"><i class="fas fa-file-signature"></i> Gestión de Matrículas</h1>
                    <p class="ph-desc">Año lectivo 2025 · Revisa, aprueba o rechaza solicitudes de matrícula.</p>
                </div>
                <div class="ph-actions">
                    <button class="ph-btn-export" id="btnExportExcel">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                    <button class="ph-btn-export pdf" id="btnExportPdf">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                    <a href="<?= BASE_URL ?>/RegistroEstudiantes" class="ph-btn-nueva">
                        <i class="fas fa-plus"></i> Nueva matrícula
                    </a>
                </div>
            </section>

            <!-- ══ KPIs ══ -->
            <section class="mat-kpis reveal">

                <div class="mkpi-card total" data-filter="">
                    <div class="mkpi-icon"><i class="fas fa-file-signature"></i></div>
                    <div class="mkpi-info">
                        <strong class="mkpi-val" data-count="47">47</strong>
                        <span class="mkpi-label">Total solicitudes</span>
                    </div>
                    <div class="mkpi-bar-wrap"><div class="mkpi-bar-fill" style="width:100%"></div></div>
                </div>

                <div class="mkpi-card pendiente" data-filter="pendiente">
                    <div class="mkpi-icon"><i class="fas fa-clock"></i></div>
                    <div class="mkpi-info">
                        <strong class="mkpi-val" data-count="12">12</strong>
                        <span class="mkpi-label">Pendientes</span>
                    </div>
                    <div class="mkpi-bar-wrap"><div class="mkpi-bar-fill" style="width:26%"></div></div>
                </div>

                <div class="mkpi-card revision" data-filter="revision">
                    <div class="mkpi-icon"><i class="fas fa-search"></i></div>
                    <div class="mkpi-info">
                        <strong class="mkpi-val" data-count="8">8</strong>
                        <span class="mkpi-label">En revisión</span>
                    </div>
                    <div class="mkpi-bar-wrap"><div class="mkpi-bar-fill" style="width:17%"></div></div>
                </div>

                <div class="mkpi-card aprobada" data-filter="aprobada">
                    <div class="mkpi-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="mkpi-info">
                        <strong class="mkpi-val" data-count="22">22</strong>
                        <span class="mkpi-label">Aprobadas</span>
                    </div>
                    <div class="mkpi-bar-wrap"><div class="mkpi-bar-fill" style="width:47%"></div></div>
                </div>

                <div class="mkpi-card rechazada" data-filter="rechazada">
                    <div class="mkpi-icon"><i class="fas fa-times-circle"></i></div>
                    <div class="mkpi-info">
                        <strong class="mkpi-val" data-count="5">5</strong>
                        <span class="mkpi-label">Rechazadas</span>
                    </div>
                    <div class="mkpi-bar-wrap"><div class="mkpi-bar-fill" style="width:11%"></div></div>
                </div>

            </section>

            <!-- ══ FILTROS ══ -->
            <div class="mat-toolbar reveal">
                <div class="mat-search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" id="matSearch" class="mat-search"
                        placeholder="Buscar por nombre, código o documento…" />
                    <button class="mat-search-clear" id="matSearchClear" style="display:none;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mat-filters">
                    <select class="mat-filter" id="filtroEstado">
                        <option value="">Todos los estados</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="revision">En revisión</option>
                        <option value="aprobada">Aprobada</option>
                        <option value="rechazada">Rechazada</option>
                    </select>
                    <select class="mat-filter" id="filtroTipo">
                        <option value="">Todos los tipos</option>
                        <option value="nueva">Nueva matrícula</option>
                        <option value="reingreso">Reingreso</option>
                        <option value="traslado">Traslado</option>
                    </select>
                    <select class="mat-filter" id="filtroGrado">
                        <option value="">Todos los grados</option>
                        <option value="PRE">Preescolar</option>
                        <option value="1">1°</option><option value="2">2°</option>
                        <option value="3">3°</option><option value="4">4°</option>
                        <option value="5">5°</option><option value="6">6°</option>
                        <option value="7">7°</option><option value="8">8°</option>
                        <option value="9">9°</option><option value="10">10°</option>
                        <option value="11">11°</option>
                    </select>
                    <button class="mat-filter-clear" id="btnClearFilters">
                        <i class="fas fa-times"></i> Limpiar
                    </button>
                </div>
            </div>

            <!-- ══ TABLA ══ -->
            <div class="mat-table-card reveal">

                <div class="mat-table-header">
                    <span class="mat-count" id="matCount">Mostrando <strong>47</strong> solicitudes</span>
                    <div class="mat-view-toggle">
                        <button class="mvt-btn active" id="btnViewTable" title="Vista tabla">
                            <i class="fas fa-table"></i>
                        </button>
                        <button class="mvt-btn" id="btnViewCards" title="Vista tarjetas">
                            <i class="fas fa-th-large"></i>
                        </button>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="mat-table-wrap" id="viewTable">
                    <table class="mat-table" id="matTable">
                        <thead>
                            <tr>
                                <th class="th-check">
                                    <label class="mat-cb-wrap">
                                        <input type="checkbox" id="selectAll" />
                                        <span class="mat-cb"></span>
                                    </label>
                                </th>
                                <th class="sortable" data-col="codigo">Código <i class="fas fa-sort"></i></th>
                                <th class="sortable" data-col="nombre">Estudiante <i class="fas fa-sort"></i></th>
                                <th class="sortable" data-col="grado">Grado <i class="fas fa-sort"></i></th>
                                <th class="sortable" data-col="tipo">Tipo <i class="fas fa-sort"></i></th>
                                <th class="sortable" data-col="fecha">Fecha <i class="fas fa-sort"></i></th>
                                <th class="sortable" data-col="estado">Estado <i class="fas fa-sort"></i></th>
                                <th>Acudiente</th>
                                <th class="th-acciones">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="matTbody"></tbody>
                    </table>
                    <div class="mat-empty" id="matEmpty" style="display:none;">
                        <i class="fas fa-search"></i>
                        <p>No se encontraron matrículas con los filtros aplicados.</p>
                        <button class="mat-empty-btn" id="btnEmptyReset">Limpiar filtros</button>
                    </div>
                </div>

                <!-- Cards -->
                <div class="mat-cards-wrap" id="viewCards" style="display:none;">
                    <div class="mat-cards-grid" id="matCardsGrid"></div>
                </div>

                <!-- Paginación -->
                <div class="mat-pagination" id="matPagination">
                    <span class="mp-info" id="mpInfo">Página 1 de 3</span>
                    <div class="mp-btns">
                        <button class="mp-btn" id="mpPrev" disabled><i class="fas fa-chevron-left"></i></button>
                        <div class="mp-pages" id="mpPages"></div>
                        <button class="mp-btn" id="mpNext"><i class="fas fa-chevron-right"></i></button>
                    </div>
                    <select class="mp-per-page" id="mpPerPage">
                        <option value="10" selected>10 por página</option>
                        <option value="20">20 por página</option>
                        <option value="50">50 por página</option>
                    </select>
                </div>

            </div>

            <!-- Barra acciones en lote -->
            <div class="mat-bulk-bar" id="matBulkBar" style="display:none;">
                <span class="mbb-info"><strong id="mbbCount">0</strong> seleccionadas</span>
                <div class="mbb-actions">
                    <button class="mbb-btn approve" id="btnBulkApprove"><i class="fas fa-check"></i> Aprobar</button>
                    <button class="mbb-btn reject"  id="btnBulkReject"><i class="fas fa-times"></i> Rechazar</button>
                    <button class="mbb-btn cancel"  id="btnBulkCancel"><i class="fas fa-ban"></i> Cancelar</button>
                </div>
            </div>

        </div>
    </main>

    <!-- ══ MODAL DETALLE ══ -->
    <div class="modal-overlay" id="modalDetalle">
        <div class="modal-box modal-detalle">
            <button class="modal-close-btn" id="modalDetalleClose"><i class="fas fa-times"></i></button>

            <div class="md-header">
                <div class="md-avatar" id="mdAvatar">ES</div>
                <div class="md-info">
                    <h3 id="mdNombre">—</h3>
                    <p id="mdSub">—</p>
                </div>
                <span class="md-badge" id="mdBadge">—</span>
            </div>

            <div class="md-tabs">
                <button class="md-tab active" data-tab="estudiante"><i class="fas fa-user-graduate"></i> Estudiante</button>
                <button class="md-tab" data-tab="acudiente"><i class="fas fa-users"></i> Acudiente</button>
                <button class="md-tab" data-tab="academico"><i class="fas fa-graduation-cap"></i> Académico</button>
                <button class="md-tab" data-tab="historial"><i class="fas fa-history"></i> Historial</button>
            </div>

            <div class="md-tab-panel active" id="tab-estudiante">
                <div class="md-grid">
                    <div class="md-field"><span class="mdf-key">Nombre completo</span><span class="mdf-val" id="det-nombre">—</span></div>
                    <div class="md-field"><span class="mdf-key">Documento</span><span class="mdf-val" id="det-doc">—</span></div>
                    <div class="md-field"><span class="mdf-key">Fecha de nacimiento</span><span class="mdf-val" id="det-fechaNac">—</span></div>
                    <div class="md-field"><span class="mdf-key">Género</span><span class="mdf-val" id="det-genero">—</span></div>
                    <div class="md-field"><span class="mdf-key">Dirección</span><span class="mdf-val" id="det-dir">—</span></div>
                    <div class="md-field"><span class="mdf-key">Teléfono</span><span class="mdf-val" id="det-tel">—</span></div>
                    <div class="md-field"><span class="mdf-key">Correo</span><span class="mdf-val" id="det-email">—</span></div>
                    <div class="md-field"><span class="mdf-key">EPS</span><span class="mdf-val" id="det-eps">—</span></div>
                </div>
            </div>

            <div class="md-tab-panel" id="tab-acudiente">
                <div class="md-grid">
                    <div class="md-field"><span class="mdf-key">Nombre acudiente</span><span class="mdf-val" id="det-acuNombre">—</span></div>
                    <div class="md-field"><span class="mdf-key">Parentesco</span><span class="mdf-val" id="det-acuParent">—</span></div>
                    <div class="md-field"><span class="mdf-key">Documento</span><span class="mdf-val" id="det-acuDoc">—</span></div>
                    <div class="md-field"><span class="mdf-key">Teléfono</span><span class="mdf-val" id="det-acuTel">—</span></div>
                    <div class="md-field"><span class="mdf-key">Correo</span><span class="mdf-val" id="det-acuEmail">—</span></div>
                    <div class="md-field"><span class="mdf-key">Ocupación</span><span class="mdf-val" id="det-acuOcup">—</span></div>
                </div>
            </div>

            <div class="md-tab-panel" id="tab-academico">
                <div class="md-grid">
                    <div class="md-field"><span class="mdf-key">Grado solicitado</span><span class="mdf-val" id="det-grado">—</span></div>
                    <div class="md-field"><span class="mdf-key">Jornada</span><span class="mdf-val" id="det-jornada">—</span></div>
                    <div class="md-field"><span class="mdf-key">Tipo de matrícula</span><span class="mdf-val" id="det-tipo">—</span></div>
                    <div class="md-field"><span class="mdf-key">Colegio anterior</span><span class="mdf-val" id="det-colAnterior">—</span></div>
                    <div class="md-field"><span class="mdf-key">Año lectivo</span><span class="mdf-val" id="det-anio">—</span></div>
                    <div class="md-field"><span class="mdf-key">NEE</span><span class="mdf-val" id="det-nee">—</span></div>
                </div>
            </div>

            <div class="md-tab-panel" id="tab-historial">
                <ul class="md-timeline" id="det-timeline"></ul>
            </div>

            <div class="md-obs-wrap">
                <label class="md-obs-label"><i class="fas fa-comment-alt"></i> Observaciones</label>
                <textarea class="md-obs-input" id="detObs" rows="2" placeholder="Agrega una nota o justificación…"></textarea>
            </div>

            <div class="md-footer">
                <button class="md-btn-close" id="btnDetalleCerrar">Cerrar</button>
                <div class="md-footer-right" id="mdFooterActions">
                    <button class="md-btn-revision" id="btnDetalleRevision"><i class="fas fa-search"></i> En revisión</button>
                    <button class="md-btn-reject"   id="btnDetalleRechazar"><i class="fas fa-times"></i> Rechazar</button>
                    <button class="md-btn-approve"  id="btnDetalleAprobar"><i class="fas fa-check"></i> Aprobar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal confirmación -->
    <div class="modal-overlay" id="modalConfirm">
        <div class="modal-box modal-confirm">
            <div class="mc-icon" id="mcIcon"><i class="fas fa-question-circle"></i></div>
            <h3 id="mcTitle">¿Confirmar acción?</h3>
            <p id="mcMsg">Esta acción cambiará el estado de la matrícula.</p>
            <div class="mc-btns">
                <button class="mc-btn-cancel" id="btnConfirmCancel">Cancelar</button>
                <button class="mc-btn-ok"     id="btnConfirmOk">Confirmar</button>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/js/admin.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/js/matriculas.js"></script>
</body>
</html>