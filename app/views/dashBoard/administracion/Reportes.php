<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reportes y Boletines — Colegio San Cristóbal</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/css/admin.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/css/reportes.css" />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>

<body>

    <!-- Sidebar y Nav van con PHP includes -->
    <?php include_once __DIR__ . '/../../layouts/administrativo/sidebar.php'; ?>
    <div class="admin-overlay" id="adminOverlay"></div>
    <?php include_once __DIR__ . '/../../layouts/administrativo/nav.php'; ?>

    <main class="admin-main" id="adminMain">
        <div class="admin-container">

            <!-- ══════════════════════════════════════
           HEADER
           ══════════════════════════════════════ -->
            <section class="page-header reveal">
                <div class="ph-left">
                    <h1 class="ph-title"><i class="fas fa-file-alt"></i> Reportes y Boletines</h1>
                    <p class="ph-desc">Genera, descarga y comparte boletines individuales o masivos por grado y período.</p>
                </div>
                <div class="ph-actions">
                    <button class="ph-btn-sec" id="btnGenMasivo">
                        <i class="fas fa-layer-group"></i> Generar masivo
                    </button>
                    <button class="ph-btn-pri" id="btnNuevoReporte">
                        <i class="fas fa-plus"></i> Nuevo reporte
                    </button>
                </div>
            </section>

            <!-- ══════════════════════════════════════
           TABS
           ══════════════════════════════════════ -->
            <div class="rep-tabs reveal">
                <div class="rt-bar">
                    <button class="rt-tab active" data-tab="boletines">
                        <i class="fas fa-file-invoice"></i>
                        <span>Boletines académicos</span>
                    </button>
                    <button class="rt-tab" data-tab="asistencia">
                        <i class="fas fa-user-check"></i>
                        <span>Asistencia</span>
                    </button>
                    <button class="rt-tab" data-tab="rendimiento">
                        <i class="fas fa-chart-bar"></i>
                        <span>Rendimiento institucional</span>
                    </button>
                    <button class="rt-tab" data-tab="docentes">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>Docentes</span>
                    </button>
                    <div class="rt-slider" id="rtSlider"></div>
                </div>
            </div>

            <!-- ══════════════════════════════════════
           PANEL BOLETINES
           ══════════════════════════════════════ -->
            <div class="rep-panel active" id="panel-boletines">

                <!-- Filtros -->
                <div class="rep-filters reveal">
                    <div class="rf-group">
                        <label class="rf-label"><i class="fas fa-calendar-alt"></i> Período</label>
                        <select class="rf-select" id="filPeriodo">
                            <option value="1">2025 — Período 1</option>
                            <option value="2">2024 — Período 2</option>
                            <option value="3">2024 — Período 1</option>
                        </select>
                    </div>
                    <div class="rf-group">
                        <label class="rf-label"><i class="fas fa-layer-group"></i> Grado</label>
                        <select class="rf-select" id="filGrado">
                            <option value="">Todos los grados</option>
                            <option value="PRE">Preescolar</option>
                            <option value="1">1°</option>
                            <option value="2">2°</option>
                            <option value="3">3°</option>
                            <option value="4">4°</option>
                            <option value="5">5°</option>
                            <option value="6">6°</option>
                            <option value="7">7°</option>
                            <option value="8">8°</option>
                            <option value="9">9°</option>
                            <option value="10">10°</option>
                            <option value="11">11°</option>
                        </select>
                    </div>
                    <div class="rf-group">
                        <label class="rf-label"><i class="fas fa-search"></i> Buscar estudiante</label>
                        <div class="rf-search-wrap">
                            <i class="fas fa-search"></i>
                            <input type="text" id="filBuscar" class="rf-search" placeholder="Nombre o código…" />
                            <button class="rf-clear" id="rfClear" style="display:none;"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                    <button class="rf-btn-apply" id="btnFiltrar">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                    <button class="rf-btn-reset" id="btnReset">
                        <i class="fas fa-undo"></i> Limpiar
                    </button>
                </div>

                <!-- Acciones masivas -->
                <div class="rep-bulk reveal">
                    <div class="rb-left">
                        <label class="rb-check-all">
                            <input type="checkbox" id="checkAllBol" />
                            <span class="rb-checkmark"></span>
                            Seleccionar todos
                        </label>
                        <span class="rb-selected" id="rbSelectedCount" style="display:none;">
                            <strong id="selectedNum">0</strong> seleccionados
                        </span>
                    </div>
                    <div class="rb-actions" id="rbActions" style="display:none;">
                        <button class="rb-btn green" id="btnDescMasivo">
                            <i class="fas fa-download"></i> Descargar selección
                        </button>
                        <button class="rb-btn blue" id="btnEnvMasivo">
                            <i class="fas fa-paper-plane"></i> Enviar por correo
                        </button>
                        <button class="rb-btn gray" id="btnImpMasivo">
                            <i class="fas fa-print"></i> Imprimir
                        </button>
                    </div>
                </div>

                <!-- Grid de boletines individuales -->
                <div class="rep-grid" id="boletinesGrid">

                    <!-- Card 1 -->
                    <div class="bol-card reveal" data-grado="11" data-codigo="2024-EST-0001">
                        <div class="bol-select-wrap">
                            <label class="bol-check">
                                <input type="checkbox" class="bol-checkbox" />
                                <span class="bol-checkmark"></span>
                            </label>
                        </div>
                        <div class="bol-header">
                            <div class="bol-avatar" style="background:#e8f5ee;color:#2d7a4f">JS</div>
                            <div class="bol-info">
                                <h3 class="bol-name">Juan Suárez</h3>
                                <p class="bol-grado">Grado 11°A · Cód. 2024-EST-0001</p>
                            </div>
                            <div class="bol-prom-badge green">4.3</div>
                        </div>

                        <div class="bol-materias">
                            <div class="bol-mat-row">
                                <span class="bm-nombre">Matemáticas</span>
                                <div class="bm-bar-wrap">
                                    <div class="bm-bar">
                                        <div class="bm-fill" style="width:90%;background:#2d7a4f"></div>
                                    </div>
                                </div>
                                <span class="bm-nota green">4.5</span>
                            </div>
                            <div class="bol-mat-row">
                                <span class="bm-nombre">Ciencias</span>
                                <div class="bm-bar-wrap">
                                    <div class="bm-bar">
                                        <div class="bm-fill" style="width:84%;background:#3182ce"></div>
                                    </div>
                                </div>
                                <span class="bm-nota blue">4.2</span>
                            </div>
                            <div class="bol-mat-row">
                                <span class="bm-nombre">Español</span>
                                <div class="bm-bar-wrap">
                                    <div class="bm-bar">
                                        <div class="bm-fill" style="width:80%;background:#6b46c1"></div>
                                    </div>
                                </div>
                                <span class="bm-nota purple">4.0</span>
                            </div>
                            <div class="bol-mat-row">
                                <span class="bm-nombre">Historia</span>
                                <div class="bm-bar-wrap">
                                    <div class="bm-bar">
                                        <div class="bm-fill" style="width:76%;background:#dd6b20"></div>
                                    </div>
                                </div>
                                <span class="bm-nota orange">3.8</span>
                            </div>
                            <div class="bol-mat-row">
                                <span class="bm-nombre">Inglés</span>
                                <div class="bm-bar-wrap">
                                    <div class="bm-bar">
                                        <div class="bm-fill" style="width:72%;background:#e53e3e"></div>
                                    </div>
                                </div>
                                <span class="bm-nota red">3.6</span>
                            </div>
                        </div>

                        <div class="bol-footer">
                            <div class="bol-meta">
                                <span><i class="fas fa-user-check"></i> 96% asistencia</span>
                                <span><i class="fas fa-calendar"></i> Período 1 · 2025</span>
                            </div>
                            <div class="bol-btns">
                                <button class="bol-btn-ver" data-estudiante="Juan Suárez" data-codigo="2024-EST-0001">
                                    <i class="fas fa-eye"></i> Ver
                                </button>
                                <button class="bol-btn-desc" data-nombre="Juan_Suarez">
                                    <i class="fas fa-download"></i>
                                </button>
                                <button class="bol-btn-env" data-email="jsuarez@sancristobal.edu.co">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2 -->
                    <div class="bol-card reveal" data-grado="11" data-codigo="2024-EST-0002">
                        <div class="bol-select-wrap">
                            <label class="bol-check"><input type="checkbox" class="bol-checkbox" /><span class="bol-checkmark"></span></label>
                        </div>
                        <div class="bol-header">
                            <div class="bol-avatar" style="background:#eff6ff;color:#3182ce">LM</div>
                            <div class="bol-info">
                                <h3 class="bol-name">Laura Martínez</h3>
                                <p class="bol-grado">Grado 11°A · Cód. 2024-EST-0002</p>
                            </div>
                            <div class="bol-prom-badge green">4.6</div>
                        </div>
                        <div class="bol-materias">
                            <div class="bol-mat-row"><span class="bm-nombre">Matemáticas</span>
                                <div class="bm-bar-wrap">
                                    <div class="bm-bar">
                                        <div class="bm-fill" style="width:94%;background:#2d7a4f"></div>
                                    </div>
                                </div><span class="bm-nota green">4.7</span>
                            </div>
                            <div class="bol-mat-row"><span class="bm-nombre">Ciencias</span>
                                <div class="bm-bar-wrap">
                                    <div class="bm-bar">
                                        <div class="bm-fill" style="width:92%;background:#3182ce"></div>
                                    </div>
                                </div><span class="bm-nota blue">4.6</span>
                            </div>
                            <div class="bol-mat-row"><span class="bm-nombre">Español</span>
                                <div class="bm-bar-wrap">
                                    <div class="bm-bar">
                                        <div class="bm-fill" style="width:90%;background:#6b46c1"></div>
                                    </div>
                                </div><span class="bm-nota purple">4.5</span>
                            </div>
                            <div class="bol-mat-row"><span class="bm-nombre">Historia</span>
                                <div class="bm-bar-wrap">
                                    <div class="bm-bar">
                                        <div class="bm-fill" style="width:88%;background:#dd6b20"></div>
                                    </div>
                                </div><span class="bm-nota orange">4.4</span>
                            </div>
                            <div class="bol-mat-row"><span class="bm-nombre">Inglés</span>
                                <div class="bm-bar-wrap">
                                    <div class="bm-bar">
                                        <div class="bm-fill" style="width:86%;background:#0891b2"></div>
                                    </div>
                                </div><span class="bm-nota teal">4.3</span>
                            </div>
                        </div>
                        <div class="bol-footer">
                            <div class="bol-meta">
                                <span><i class="fas fa-user-check"></i> 99% asistencia</span>
                                <span><i class="fas fa-calendar"></i> Período 1 · 2025</span>
                            </div>
                            <div class="bol-btns">
                                <button class="bol-btn-ver" data-estudiante="Laura Martínez" data-codigo="2024-EST-0002"><i class="fas fa-eye"></i> Ver</button>
                                <button class="bol-btn-desc" data-nombre="Laura_Martinez"><i class="fas fa-download"></i></button>
                                <button class="bol-btn-env" data-email="lmartinez@sancristobal.edu.co"><i class="fas fa-paper-plane"></i></button>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3 — RIESGO -->
                    <div class="bol-card riesgo reveal" data-grado="8" data-codigo="2024-EST-0187">
                        <div class="bol-select-wrap">
                            <label class="bol-check"><input type="checkbox" class="bol-checkbox" /><span class="bol-checkmark"></span></label>
                        </div>
                        <div class="bol-riesgo-banner"><i class="fas fa-exclamation-triangle"></i> Estudiante en riesgo académico</div>
                        <div class="bol-header">
                            <div class="bol-avatar" style="background:#fff5f5;color:#e53e3e">DG</div>
                            <div class="bol-info">
                                <h3 class="bol-name">Diego González</h3>
                                <p class="bol-grado">Grado 8°A · Cód. 2024-EST-0187</p>
                            </div>
                            <div class="bol-prom-badge red">2.9</div>
                        </div>
                        <div class="bol-materias">
                            <div class="bol-mat-row"><span class="bm-nombre">Matemáticas</span>
                                <div class="bm-bar-wrap">
                                    <div class="bm-bar">
                                        <div class="bm-fill" style="width:50%;background:#e53e3e"></div>
                                    </div>
                                </div><span class="bm-nota red">2.5</span>
                            </div>
                            <div class="bol-mat-row"><span class="bm-nombre">Ciencias</span>
                                <div class="bm-bar-wrap">
                                    <div class="bm-bar">
                                        <div class="bm-fill" style="width:58%;background:#dd6b20"></div>
                                    </div>
                                </div><span class="bm-nota orange">2.9</span>
                            </div>
                            <div class="bol-mat-row"><span class="bm-nombre">Español</span>
                                <div class="bm-bar-wrap">
                                    <div class="bm-bar">
                                        <div class="bm-fill" style="width:64%;background:#6b46c1"></div>
                                    </div>
                                </div><span class="bm-nota orange">3.2</span>
                            </div>
                            <div class="bol-mat-row"><span class="bm-nombre">Historia</span>
                                <div class="bm-bar-wrap">
                                    <div class="bm-bar">
                                        <div class="bm-fill" style="width:56%;background:#dd6b20"></div>
                                    </div>
                                </div><span class="bm-nota red">2.8</span>
                            </div>
                            <div class="bol-mat-row"><span class="bm-nombre">Inglés</span>
                                <div class="bm-bar-wrap">
                                    <div class="bm-bar">
                                        <div class="bm-fill" style="width:52%;background:#e53e3e"></div>
                                    </div>
                                </div><span class="bm-nota red">2.6</span>
                            </div>
                        </div>
                        <div class="bol-footer">
                            <div class="bol-meta">
                                <span class="low"><i class="fas fa-user-times"></i> 65% asistencia</span>
                                <span><i class="fas fa-calendar"></i> Período 1 · 2025</span>
                            </div>
                            <div class="bol-btns">
                                <button class="bol-btn-ver" data-estudiante="Diego González" data-codigo="2024-EST-0187"><i class="fas fa-eye"></i> Ver</button>
                                <button class="bol-btn-desc" data-nombre="Diego_Gonzalez"><i class="fas fa-download"></i></button>
                                <button class="bol-btn-env" data-email="dgonzalez@sancristobal.edu.co"><i class="fas fa-paper-plane"></i></button>
                            </div>
                        </div>
                    </div>

                    <!-- Card 4 -->
                    <div class="bol-card reveal" data-grado="9" data-codigo="2024-EST-0112">
                        <div class="bol-select-wrap">
                            <label class="bol-check"><input type="checkbox" class="bol-checkbox" /><span class="bol-checkmark"></span></label>
                        </div>
                        <div class="bol-header">
                            <div class="bol-avatar" style="background:#faf5ff;color:#6b46c1">SR</div>
                            <div class="bol-info">
                                <h3 class="bol-name">Sofía Rojas</h3>
                                <p class="bol-grado">Grado 9°C · Cód. 2024-EST-0112</p>
                            </div>
                            <div class="bol-prom-badge green">4.5</div>
                        </div>
                        <div class="bol-materias">
                            <div class="bol-mat-row"><span class="bm-nombre">Matemáticas</span>
                                <div class="bm-bar-wrap">
                                    <div class="bm-bar">
                                        <div class="bm-fill" style="width:92%;background:#2d7a4f"></div>
                                    </div>
                                </div><span class="bm-nota green">4.6</span>
                            </div>
                            <div class="bol-mat-row"><span class="bm-nombre">Ciencias</span>
                                <div class="bm-bar-wrap">
                                    <div class="bm-bar">
                                        <div class="bm-fill" style="width:88%;background:#3182ce"></div>
                                    </div>
                                </div><span class="bm-nota blue">4.4</span>
                            </div>
                            <div class="bol-mat-row"><span class="bm-nombre">Español</span>
                                <div class="bm-bar-wrap">
                                    <div class="bm-bar">
                                        <div class="bm-fill" style="width:86%;background:#6b46c1"></div>
                                    </div>
                                </div><span class="bm-nota purple">4.3</span>
                            </div>
                            <div class="bol-mat-row"><span class="bm-nombre">Historia</span>
                                <div class="bm-bar-wrap">
                                    <div class="bm-bar">
                                        <div class="bm-fill" style="width:90%;background:#dd6b20"></div>
                                    </div>
                                </div><span class="bm-nota orange">4.5</span>
                            </div>
                            <div class="bol-mat-row"><span class="bm-nombre">Inglés</span>
                                <div class="bm-bar-wrap">
                                    <div class="bm-bar">
                                        <div class="bm-fill" style="width:84%;background:#0891b2"></div>
                                    </div>
                                </div><span class="bm-nota teal">4.2</span>
                            </div>
                        </div>
                        <div class="bol-footer">
                            <div class="bol-meta">
                                <span><i class="fas fa-user-check"></i> 95% asistencia</span>
                                <span><i class="fas fa-calendar"></i> Período 1 · 2025</span>
                            </div>
                            <div class="bol-btns">
                                <button class="bol-btn-ver" data-estudiante="Sofía Rojas" data-codigo="2024-EST-0112"><i class="fas fa-eye"></i> Ver</button>
                                <button class="bol-btn-desc" data-nombre="Sofia_Rojas"><i class="fas fa-download"></i></button>
                                <button class="bol-btn-env" data-email="srojas@sancristobal.edu.co"><i class="fas fa-paper-plane"></i></button>
                            </div>
                        </div>
                    </div>

                    <!-- Card 5 -->
                    <div class="bol-card reveal" data-grado="7" data-codigo="2024-EST-0234">
                        <div class="bol-select-wrap">
                            <label class="bol-check"><input type="checkbox" class="bol-checkbox" /><span class="bol-checkmark"></span></label>
                        </div>
                        <div class="bol-header">
                            <div class="bol-avatar" style="background:#e0f2fe;color:#0891b2">AM</div>
                            <div class="bol-info">
                                <h3 class="bol-name">Ana Moreno</h3>
                                <p class="bol-grado">Grado 7°B · Cód. 2024-EST-0234</p>
                            </div>
                            <div class="bol-prom-badge green">4.1</div>
                        </div>
                        <div class="bol-materias">
                            <div class="bol-mat-row"><span class="bm-nombre">Matemáticas</span>
                                <div class="bm-bar-wrap">
                                    <div class="bm-bar">
                                        <div class="bm-fill" style="width:82%;background:#2d7a4f"></div>
                                    </div>
                                </div><span class="bm-nota green">4.1</span>
                            </div>
                            <div class="bol-mat-row"><span class="bm-nombre">Ciencias</span>
                                <div class="bm-bar-wrap">
                                    <div class="bm-bar">
                                        <div class="bm-fill" style="width:84%;background:#3182ce"></div>
                                    </div>
                                </div><span class="bm-nota blue">4.2</span>
                            </div>
                            <div class="bol-mat-row"><span class="bm-nombre">Español</span>
                                <div class="bm-bar-wrap">
                                    <div class="bm-bar">
                                        <div class="bm-fill" style="width:80%;background:#6b46c1"></div>
                                    </div>
                                </div><span class="bm-nota purple">4.0</span>
                            </div>
                            <div class="bol-mat-row"><span class="bm-nombre">Historia</span>
                                <div class="bm-bar-wrap">
                                    <div class="bm-bar">
                                        <div class="bm-fill" style="width:78%;background:#dd6b20"></div>
                                    </div>
                                </div><span class="bm-nota orange">3.9</span>
                            </div>
                            <div class="bol-mat-row"><span class="bm-nombre">Inglés</span>
                                <div class="bm-bar-wrap">
                                    <div class="bm-bar">
                                        <div class="bm-fill" style="width:82%;background:#0891b2"></div>
                                    </div>
                                </div><span class="bm-nota teal">4.1</span>
                            </div>
                        </div>
                        <div class="bol-footer">
                            <div class="bol-meta">
                                <span><i class="fas fa-user-check"></i> 91% asistencia</span>
                                <span><i class="fas fa-calendar"></i> Período 1 · 2025</span>
                            </div>
                            <div class="bol-btns">
                                <button class="bol-btn-ver" data-estudiante="Ana Moreno" data-codigo="2024-EST-0234"><i class="fas fa-eye"></i> Ver</button>
                                <button class="bol-btn-desc" data-nombre="Ana_Moreno"><i class="fas fa-download"></i></button>
                                <button class="bol-btn-env" data-email="amoreno@sancristobal.edu.co"><i class="fas fa-paper-plane"></i></button>
                            </div>
                        </div>
                    </div>

                    <!-- Card 6 -->
                    <div class="bol-card reveal" data-grado="10" data-codigo="2024-EST-0058">
                        <div class="bol-select-wrap">
                            <label class="bol-check"><input type="checkbox" class="bol-checkbox" /><span class="bol-checkmark"></span></label>
                        </div>
                        <div class="bol-header">
                            <div class="bol-avatar" style="background:#fef3c7;color:#d97706">CP</div>
                            <div class="bol-info">
                                <h3 class="bol-name">Carlos Pérez</h3>
                                <p class="bol-grado">Grado 10°B · Cód. 2024-EST-0058</p>
                            </div>
                            <div class="bol-prom-badge orange">3.4</div>
                        </div>
                        <div class="bol-materias">
                            <div class="bol-mat-row"><span class="bm-nombre">Matemáticas</span>
                                <div class="bm-bar-wrap">
                                    <div class="bm-bar">
                                        <div class="bm-fill" style="width:66%;background:#dd6b20"></div>
                                    </div>
                                </div><span class="bm-nota orange">3.3</span>
                            </div>
                            <div class="bol-mat-row"><span class="bm-nombre">Ciencias</span>
                                <div class="bm-bar-wrap">
                                    <div class="bm-bar">
                                        <div class="bm-fill" style="width:70%;background:#3182ce"></div>
                                    </div>
                                </div><span class="bm-nota orange">3.5</span>
                            </div>
                            <div class="bol-mat-row"><span class="bm-nombre">Español</span>
                                <div class="bm-bar-wrap">
                                    <div class="bm-bar">
                                        <div class="bm-fill" style="width:72%;background:#6b46c1"></div>
                                    </div>
                                </div><span class="bm-nota orange">3.6</span>
                            </div>
                            <div class="bol-mat-row"><span class="bm-nombre">Historia</span>
                                <div class="bm-bar-wrap">
                                    <div class="bm-bar">
                                        <div class="bm-fill" style="width:64%;background:#dd6b20"></div>
                                    </div>
                                </div><span class="bm-nota orange">3.2</span>
                            </div>
                            <div class="bol-mat-row"><span class="bm-nombre">Inglés</span>
                                <div class="bm-bar-wrap">
                                    <div class="bm-bar">
                                        <div class="bm-fill" style="width:68%;background:#dd6b20"></div>
                                    </div>
                                </div><span class="bm-nota orange">3.4</span>
                            </div>
                        </div>
                        <div class="bol-footer">
                            <div class="bol-meta">
                                <span class="low"><i class="fas fa-user-check"></i> 78% asistencia</span>
                                <span><i class="fas fa-calendar"></i> Período 1 · 2025</span>
                            </div>
                            <div class="bol-btns">
                                <button class="bol-btn-ver" data-estudiante="Carlos Pérez" data-codigo="2024-EST-0058"><i class="fas fa-eye"></i> Ver</button>
                                <button class="bol-btn-desc" data-nombre="Carlos_Perez"><i class="fas fa-download"></i></button>
                                <button class="bol-btn-env" data-email="cperez@sancristobal.edu.co"><i class="fas fa-paper-plane"></i></button>
                            </div>
                        </div>
                    </div>

                </div><!-- /boletinesGrid -->

                <!-- Paginación -->
                <div class="rep-pagination reveal">
                    <span class="rp-info">Mostrando <strong>1–6</strong> de <strong>1,240</strong> boletines</span>
                    <div class="rp-btns">
                        <button class="rp-btn" disabled><i class="fas fa-chevron-left"></i></button>
                        <button class="rp-btn active">1</button>
                        <button class="rp-btn">2</button>
                        <button class="rp-btn">3</button>
                        <span class="rp-dots">…</span>
                        <button class="rp-btn">207</button>
                        <button class="rp-btn"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>

            </div><!-- /panel-boletines -->

            <!-- ══════════════════════════════════════
           PANEL ASISTENCIA
           ══════════════════════════════════════ -->
            <div class="rep-panel" id="panel-asistencia">
                <div class="rep-coming reveal">
                    <div class="rc-icon teal"><i class="fas fa-user-check"></i></div>
                    <h3>Reportes de Asistencia</h3>
                    <p>Resumen mensual por grado, ausencias justificadas e injustificadas y ranking de asistencia.</p>
                    <div class="rc-btns">
                        <button class="rc-btn green" onclick="showToast('Generando reporte de asistencia…','success')">
                            <i class="fas fa-file-export"></i> Generar reporte
                        </button>
                        <button class="rc-btn gray" onclick="showToast('Exportando a Excel…','info')">
                            <i class="fas fa-file-excel"></i> Exportar Excel
                        </button>
                    </div>
                </div>
            </div>

            <!-- ══════════════════════════════════════
           PANEL RENDIMIENTO
           ══════════════════════════════════════ -->
            <div class="rep-panel" id="panel-rendimiento">
                <div class="rep-coming reveal">
                    <div class="rc-icon gold"><i class="fas fa-chart-bar"></i></div>
                    <h3>Rendimiento Institucional</h3>
                    <p>Estadísticas comparativas por grado, período y docente. Identifica áreas de mejora y fortalezas académicas.</p>
                    <div class="rc-btns">
                        <button class="rc-btn green" onclick="showToast('Generando análisis institucional…','success')">
                            <i class="fas fa-chart-line"></i> Ver análisis
                        </button>
                        <button class="rc-btn gray" onclick="showToast('Exportando estadísticas…','info')">
                            <i class="fas fa-download"></i> Exportar PDF
                        </button>
                    </div>
                </div>
            </div>

            <!-- ══════════════════════════════════════
           PANEL DOCENTES
           ══════════════════════════════════════ -->
            <div class="rep-panel" id="panel-docentes">
                <div class="rep-coming reveal">
                    <div class="rc-icon purple"><i class="fas fa-chalkboard-teacher"></i></div>
                    <h3>Gestión Docente</h3>
                    <p>Carga académica, asistencia laboral, evaluación de desempeño y registro de novedades por docente.</p>
                    <div class="rc-btns">
                        <button class="rc-btn green" onclick="showToast('Generando reporte docente…','success')">
                            <i class="fas fa-file-alt"></i> Generar reporte
                        </button>
                        <button class="rc-btn gray" onclick="showToast('Exportando a PDF…','info')">
                            <i class="fas fa-download"></i> Exportar PDF
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- ══════════════════════════════════════
       MODAL — BOLETÍN INDIVIDUAL
       ══════════════════════════════════════ -->
    <div class="modal-overlay" id="modalBoletin">
        <div class="modal-boletin">

            <!-- Controles del modal -->
            <div class="mb-controls">
                <div class="mb-ctrl-left">
                    <button class="mb-ctrl-btn" id="mbPrint" title="Imprimir">
                        <i class="fas fa-print"></i> Imprimir
                    </button>
                    <button class="mb-ctrl-btn green" id="mbDownload" title="Descargar PDF">
                        <i class="fas fa-download"></i> Descargar PDF
                    </button>
                    <button class="mb-ctrl-btn blue" id="mbSend" title="Enviar por correo">
                        <i class="fas fa-paper-plane"></i> Enviar
                    </button>
                </div>
                <button class="mb-close" id="mbClose"><i class="fas fa-times"></i></button>
            </div>

            <!-- Boletín imprimible -->
            <div class="mb-boletin printable" id="boletinPrintable">

                <!-- Encabezado institucional -->
                <div class="mb-inst-header">
                    <div class="mb-inst-logo">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="mb-inst-info">
                        <h1>Colegio San Cristóbal</h1>
                        <p>Bogotá D.C., Colombia · Resolución de Aprobación No. 1234 de 1985</p>
                        <p>NIT: 800.123.456-7 · Tel: (601) 234-5678</p>
                    </div>
                    <div class="mb-inst-period">
                        <span class="mb-period-label">BOLETÍN DE NOTAS</span>
                        <span class="mb-period-val">Período 1 · 2025</span>
                    </div>
                </div>

                <!-- Datos del estudiante -->
                <div class="mb-student-info">
                    <div class="mb-si-grid">
                        <div class="mb-si-item">
                            <span class="mb-si-label">Estudiante</span>
                            <span class="mb-si-val" id="mbNombre">Juan Suárez</span>
                        </div>
                        <div class="mb-si-item">
                            <span class="mb-si-label">Código</span>
                            <span class="mb-si-val" id="mbCodigo">2024-EST-0001</span>
                        </div>
                        <div class="mb-si-item">
                            <span class="mb-si-label">Grado</span>
                            <span class="mb-si-val">11°A</span>
                        </div>
                        <div class="mb-si-item">
                            <span class="mb-si-label">Director de grupo</span>
                            <span class="mb-si-val">Prof. Ricardo Méndez</span>
                        </div>
                        <div class="mb-si-item">
                            <span class="mb-si-label">Jornada</span>
                            <span class="mb-si-val">Mañana</span>
                        </div>
                        <div class="mb-si-item">
                            <span class="mb-si-label">Fecha emisión</span>
                            <span class="mb-si-val" id="mbFecha">28 de marzo de 2025</span>
                        </div>
                    </div>
                </div>

                <!-- Tabla de notas -->
                <div class="mb-notas-section">
                    <h2 class="mb-section-title">Calificaciones por asignatura</h2>
                    <table class="mb-notas-table">
                        <thead>
                            <tr>
                                <th>Asignatura</th>
                                <th>Docente</th>
                                <th>Corte 1</th>
                                <th>Corte 2</th>
                                <th>Corte 3</th>
                                <th>Final</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="mb-mat-name"><span class="mb-mat-dot" style="background:#2d7a4f"></span>Matemáticas</td>
                                <td>Prof. R. Méndez</td>
                                <td>4.6</td>
                                <td>4.4</td>
                                <td>4.5</td>
                                <td class="mb-final green">4.5</td>
                                <td><span class="mb-est aprobado">Aprobado</span></td>
                            </tr>
                            <tr>
                                <td class="mb-mat-name"><span class="mb-mat-dot" style="background:#3182ce"></span>Ciencias Naturales</td>
                                <td>Prof. A. Torres</td>
                                <td>4.1</td>
                                <td>4.3</td>
                                <td>4.2</td>
                                <td class="mb-final blue">4.2</td>
                                <td><span class="mb-est aprobado">Aprobado</span></td>
                            </tr>
                            <tr>
                                <td class="mb-mat-name"><span class="mb-mat-dot" style="background:#6b46c1"></span>Español y Literatura</td>
                                <td>Prof. M. Castaño</td>
                                <td>3.8</td>
                                <td>4.0</td>
                                <td>4.2</td>
                                <td class="mb-final purple">4.0</td>
                                <td><span class="mb-est aprobado">Aprobado</span></td>
                            </tr>
                            <tr>
                                <td class="mb-mat-name"><span class="mb-mat-dot" style="background:#dd6b20"></span>Historia y Sociales</td>
                                <td>Prof. L. Vargas</td>
                                <td>3.9</td>
                                <td>3.7</td>
                                <td>3.8</td>
                                <td class="mb-final orange">3.8</td>
                                <td><span class="mb-est aprobado">Aprobado</span></td>
                            </tr>
                            <tr>
                                <td class="mb-mat-name"><span class="mb-mat-dot" style="background:#e53e3e"></span>Inglés</td>
                                <td>Prof. C. Ramírez</td>
                                <td>3.5</td>
                                <td>3.7</td>
                                <td>3.6</td>
                                <td class="mb-final orange">3.6</td>
                                <td><span class="mb-est aprobado">Aprobado</span></td>
                            </tr>
                            <tr>
                                <td class="mb-mat-name"><span class="mb-mat-dot" style="background:#0891b2"></span>Física</td>
                                <td>Prof. J. Betancur</td>
                                <td>4.4</td>
                                <td>4.2</td>
                                <td>4.3</td>
                                <td class="mb-final teal">4.3</td>
                                <td><span class="mb-est aprobado">Aprobado</span></td>
                            </tr>
                            <tr>
                                <td class="mb-mat-name"><span class="mb-mat-dot" style="background:#c8a84b"></span>Educación Física</td>
                                <td>Prof. H. Sánchez</td>
                                <td>4.9</td>
                                <td>4.8</td>
                                <td>4.7</td>
                                <td class="mb-final gold">4.8</td>
                                <td><span class="mb-est aprobado">Aprobado</span></td>
                            </tr>
                            <tr>
                                <td class="mb-mat-name"><span class="mb-mat-dot" style="background:#7c3aed"></span>Tecnología e Informática</td>
                                <td>Prof. N. Ospina</td>
                                <td>4.0</td>
                                <td>4.2</td>
                                <td>4.1</td>
                                <td class="mb-final violet">4.1</td>
                                <td><span class="mb-est aprobado">Aprobado</span></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="mb-prom-row">
                                <td colspan="5"><strong>Promedio general del período</strong></td>
                                <td class="mb-final green"><strong>4.3</strong></td>
                                <td><span class="mb-est destacado">Destacado</span></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Asistencia y observaciones -->
                <div class="mb-bottom-grid">
                    <div class="mb-asistencia">
                        <h2 class="mb-section-title">Registro de asistencia</h2>
                        <div class="mb-asist-stats">
                            <div class="mb-asist-item green"><strong>22</strong><span>Asistencias</span></div>
                            <div class="mb-asist-item red"><strong>1</strong><span>Inasistencias</span></div>
                            <div class="mb-asist-item orange"><strong>0</strong><span>Tardanzas</span></div>
                            <div class="mb-asist-pct"><strong>96%</strong><span>Total</span></div>
                        </div>
                        <div class="mb-asist-bar-wrap">
                            <div class="mb-asist-bar">
                                <div class="mb-asist-fill" style="width:96%">96%</div>
                            </div>
                            <span>Mínimo requerido: 80%</span>
                        </div>
                    </div>

                    <div class="mb-observaciones">
                        <h2 class="mb-section-title">Observaciones del director de grupo</h2>
                        <p class="mb-obs-text">Juan demuestra un excelente desempeño académico y actitudinal. Participa activamente en clase, cumple puntualmente con sus responsabilidades y muestra liderazgo positivo con sus compañeros. Se recomienda continuar con el mismo nivel de compromiso durante el siguiente período.</p>
                        <div class="mb-firma-wrap">
                            <div class="mb-firma">
                                <div class="mb-firma-line"></div>
                                <span>Director de Grupo</span>
                                <span class="mb-firma-name">Prof. Ricardo Méndez</span>
                            </div>
                            <div class="mb-firma">
                                <div class="mb-firma-line"></div>
                                <span>Rector(a)</span>
                                <span class="mb-firma-name">Rosa Cardona</span>
                            </div>
                            <div class="mb-firma">
                                <div class="mb-firma-line"></div>
                                <span>Acudiente / Padre de familia</span>
                                <span class="mb-firma-name">&nbsp;</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pie del boletín -->
                <div class="mb-footer-doc">
                    <p>Este boletín es un documento oficial del Colegio San Cristóbal. Cualquier alteración invalida el documento.</p>
                    <p>Generado el <span id="mbFechaGen">28 de marzo de 2025</span> · Sistema de Gestión Académica v1.0</p>
                </div>

            </div><!-- /printable -->
        </div>
    </div>

    <!-- Toast container -->
    <div id="toastContainer"></div>

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/js/admin.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/js/reportes.js"></script>
</body>

</html>