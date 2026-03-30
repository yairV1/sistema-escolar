<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Panel Rector — Colegio San Cristóbal</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/css/admin.css" />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>

<body>

    <?php include_once __DIR__ . '/../../layouts/administrativo/Sidebar.php'; ?>

    <!-- Overlay móvil -->
    <div class="admin-overlay" id="adminOverlay"></div>

    <!-- =====================================================
       TOPBAR
       ===================================================== -->

    <?php include_once __DIR__ . '/../../layouts/administrativo/Nav.php'; ?>

    <!-- =====================================================
       CONTENIDO PRINCIPAL
       ===================================================== -->
    <main class="admin-main" id="adminMain">
        <div class="admin-container">

            <!-- ══════════════════════════════════════════
           BIENVENIDA
           ══════════════════════════════════════════ -->
            <section class="admin-welcome reveal">
                <div class="aw-left">
                    <div class="aw-tag"><span class="aw-dot"></span> Año lectivo 2025 · Período 1</div>
                    <h1>Bienvenida, <span class="aw-name">Rosa</span> 👋</h1>
                    <p>Rectora · Colegio San Cristóbal · Bogotá</p>
                    <div class="aw-meta">
                        <span><i class="fas fa-calendar-day"></i> <span id="adminFecha">—</span></span>
                        <span><i class="fas fa-clock"></i> <span id="adminHora">—</span></span>
                    </div>
                </div>
                <div class="aw-right">
                    <div class="aw-illus">
                        <div class="aw-circle c1"></div>
                        <div class="aw-circle c2"></div>
                        <i class="fas fa-school aw-icon"></i>
                    </div>
                </div>
            </section>

            <!-- ══════════════════════════════════════════
           KPIs PRINCIPALES
           ══════════════════════════════════════════ -->
            <section class="admin-kpis reveal">

                <div class="akpi-card" data-color="green">
                    <div class="akpi-top">
                        <div class="akpi-icon green"><i class="fas fa-user-graduate"></i></div>
                        <span class="akpi-trend up"><i class="fas fa-arrow-up"></i> +24 vs 2024</span>
                    </div>
                    <strong class="akpi-val" data-count="1240">1,240</strong>
                    <span class="akpi-label">Estudiantes matriculados</span>
                    <div class="akpi-bar">
                        <div class="akpi-fill green" style="width:82%"></div>
                    </div>
                </div>

                <div class="akpi-card" data-color="blue">
                    <div class="akpi-top">
                        <div class="akpi-icon blue"><i class="fas fa-chalkboard-teacher"></i></div>
                        <span class="akpi-trend up"><i class="fas fa-arrow-up"></i> +3 nuevos</span>
                    </div>
                    <strong class="akpi-val" data-count="87">87</strong>
                    <span class="akpi-label">Docentes activos</span>
                    <div class="akpi-bar">
                        <div class="akpi-fill blue" style="width:68%"></div>
                    </div>
                </div>

                <div class="akpi-card" data-color="gold">
                    <div class="akpi-top">
                        <div class="akpi-icon gold"><i class="fas fa-chart-line"></i></div>
                        <span class="akpi-trend up"><i class="fas fa-arrow-up"></i> +0.3 pts</span>
                    </div>
                    <strong class="akpi-val" data-count="4.1" data-decimals="1">4.1</strong>
                    <span class="akpi-label">Promedio institucional</span>
                    <div class="akpi-bar">
                        <div class="akpi-fill gold" style="width:82%"></div>
                    </div>
                </div>

                <div class="akpi-card" data-color="teal">
                    <div class="akpi-top">
                        <div class="akpi-icon teal"><i class="fas fa-user-check"></i></div>
                        <span class="akpi-trend up"><i class="fas fa-arrow-up"></i> Por encima del mín.</span>
                    </div>
                    <strong class="akpi-val" data-count="94" data-suffix="%">94%</strong>
                    <span class="akpi-label">Asistencia promedio</span>
                    <div class="akpi-bar">
                        <div class="akpi-fill teal" style="width:94%"></div>
                    </div>
                </div>

                <div class="akpi-card" data-color="orange">
                    <div class="akpi-top">
                        <div class="akpi-icon orange"><i class="fas fa-file-signature"></i></div>
                        <span class="akpi-trend new"><i class="fas fa-circle"></i> Nuevas</span>
                    </div>
                    <strong class="akpi-val" data-count="12">12</strong>
                    <span class="akpi-label">Solicitudes de matrícula</span>
                    <div class="akpi-bar">
                        <div class="akpi-fill orange" style="width:24%"></div>
                    </div>
                </div>

                <div class="akpi-card" data-color="red">
                    <div class="akpi-top">
                        <div class="akpi-icon red"><i class="fas fa-exclamation-triangle"></i></div>
                        <span class="akpi-trend down"><i class="fas fa-exclamation-circle"></i> Requieren atención</span>
                    </div>
                    <strong class="akpi-val" data-count="8">8</strong>
                    <span class="akpi-label">Estudiantes en riesgo</span>
                    <div class="akpi-bar">
                        <div class="akpi-fill red" style="width:10%"></div>
                    </div>
                </div>

            </section>

            <!-- ══════════════════════════════════════════
           FILA MID: Gráfica + Matrículas pendientes
           ══════════════════════════════════════════ -->
            <div class="admin-mid reveal">

                <!-- Estadísticas académicas por grado -->
                <div class="acard estadisticas-card">
                    <div class="acard-header">
                        <h2 class="acard-title"><i class="fas fa-chart-bar"></i> Promedio por grado</h2>
                        <select class="acard-select" id="gradoSelect">
                            <option>2025 — Período 1</option>
                            <option>2024 — Período 2</option>
                        </select>
                    </div>
                    <div class="chart-wrap">
                        <div class="chart-bars" id="chartBars">
                            <!-- Generado por JS -->
                        </div>
                        <div class="chart-labels" id="chartLabels"></div>
                    </div>
                </div>

                <!-- Matrículas pendientes -->
                <div class="acard matriculas-card">
                    <div class="acard-header">
                        <h2 class="acard-title"><i class="fas fa-file-signature"></i> Matrículas recientes</h2>
                        <span class="acard-badge orange">12 pendientes</span>
                    </div>
                    <ul class="matriculas-list">
                        <li class="mat-item">
                            <div class="mat-avatar" style="background:#e8f5ee;color:#2d7a4f">ES</div>
                            <div class="mat-data">
                                <p class="mat-name">Emilio Serna Ruiz</p>
                                <p class="mat-info">Grado 6° · Traslado · Colegio Andino</p>
                            </div>
                            <span class="mat-status pendiente">Pendiente</span>
                        </li>
                        <li class="mat-item">
                            <div class="mat-avatar" style="background:#eff6ff;color:#3182ce">VL</div>
                            <div class="mat-data">
                                <p class="mat-name">Valentina López Mora</p>
                                <p class="mat-info">Preescolar · Nueva matrícula</p>
                            </div>
                            <span class="mat-status pendiente">Pendiente</span>
                        </li>
                        <li class="mat-item">
                            <div class="mat-avatar" style="background:#fef3c7;color:#d97706">SG</div>
                            <div class="mat-data">
                                <p class="mat-name">Santiago García B.</p>
                                <p class="mat-info">Grado 9° · Reingreso</p>
                            </div>
                            <span class="mat-status revision">En revisión</span>
                        </li>
                        <li class="mat-item">
                            <div class="mat-avatar" style="background:#f0fff4;color:#2d7a4f">NR</div>
                            <div class="mat-data">
                                <p class="mat-name">Natalia Reyes Cano</p>
                                <p class="mat-info">Grado 11° · Traslado · IED Sur</p>
                            </div>
                            <span class="mat-status aprobada">Aprobada</span>
                        </li>
                        <li class="mat-item">
                            <div class="mat-avatar" style="background:#fff5f5;color:#e53e3e">JP</div>
                            <div class="mat-data">
                                <p class="mat-name">Juan Pablo Arango</p>
                                <p class="mat-info">Grado 7° · Nueva matrícula</p>
                            </div>
                            <span class="mat-status pendiente">Pendiente</span>
                        </li>
                    </ul>
                    <a href="#matriculas" class="acard-footer-link">Ver todas las solicitudes <i class="fas fa-arrow-right"></i></a>
                </div>

            </div>

            <!-- ══════════════════════════════════════════
           FILA BOTTOM: Listado docentes + Listado estudiantes + Comunicados
           ══════════════════════════════════════════ -->
            <div class="admin-bottom reveal">

                <!-- Listado de docentes -->
                <div class="acard docentes-card">
                    <div class="acard-header">
                        <h2 class="acard-title"><i class="fas fa-chalkboard-teacher"></i> Docentes activos</h2>
                        <a href="#docentes" class="acard-link">Ver todos <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="search-mini-wrap">
                        <i class="fas fa-search"></i>
                        <input type="text" class="search-mini" id="searchDocente" placeholder="Buscar docente…" />
                    </div>
                    <ul class="docentes-list" id="docentesList">
                        <li class="doc-item">
                            <div class="doc-avatar" style="background:#e8f5ee;color:#2d7a4f">RM</div>
                            <div class="doc-data">
                                <p class="doc-name">Ricardo Mendez</p>
                                <p class="doc-info">Matemáticas · 11°A, 10°B, 9°C</p>
                            </div>
                            <span class="doc-status activo">Activo</span>
                        </li>
                        <li class="doc-item">
                            <div class="doc-avatar" style="background:#eff6ff;color:#3182ce">AT</div>
                            <div class="doc-data">
                                <p class="doc-name">Ana Torres</p>
                                <p class="doc-info">Ciencias Naturales · 11°A, 10°A</p>
                            </div>
                            <span class="doc-status activo">Activo</span>
                        </li>
                        <li class="doc-item">
                            <div class="doc-avatar" style="background:#faf5ff;color:#6b46c1">MC</div>
                            <div class="doc-data">
                                <p class="doc-name">María Castaño</p>
                                <p class="doc-info">Español · 11°A, 11°B, 10°C</p>
                            </div>
                            <span class="doc-status activo">Activo</span>
                        </li>
                        <li class="doc-item">
                            <div class="doc-avatar" style="background:#fff7ed;color:#dd6b20">LV</div>
                            <div class="doc-data">
                                <p class="doc-name">Luis Vargas</p>
                                <p class="doc-info">Historia · 10°A, 9°A, 8°B</p>
                            </div>
                            <span class="doc-status activo">Activo</span>
                        </li>
                        <li class="doc-item">
                            <div class="doc-avatar" style="background:#fff5f5;color:#e53e3e">CR</div>
                            <div class="doc-data">
                                <p class="doc-name">Carlos Ramírez</p>
                                <p class="doc-info">Inglés · 11°A, 11°B, 10°A</p>
                            </div>
                            <span class="doc-status licencia">Licencia</span>
                        </li>
                        <li class="doc-item">
                            <div class="doc-avatar" style="background:#e0f2fe;color:#0891b2">JB</div>
                            <div class="doc-data">
                                <p class="doc-name">Jorge Betancur</p>
                                <p class="doc-info">Física · 11°A, 10°B</p>
                            </div>
                            <span class="doc-status activo">Activo</span>
                        </li>
                    </ul>
                </div>

                <!-- Listado de estudiantes -->
                <div class="acard estudiantes-card">
                    <div class="acard-header">
                        <h2 class="acard-title"><i class="fas fa-user-graduate"></i> Estudiantes — Muestra</h2>
                        <a href="#estudiantes" class="acard-link">Ver todos <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="search-mini-wrap">
                        <i class="fas fa-search"></i>
                        <input type="text" class="search-mini" id="searchEstudiante" placeholder="Buscar estudiante…" />
                    </div>
                    <ul class="estudiantes-list" id="estudiantesList">
                        <li class="est-item">
                            <div class="est-avatar" style="background:#e8f5ee;color:#2d7a4f">JS</div>
                            <div class="est-data">
                                <p class="est-name">Juan Suárez</p>
                                <p class="est-info">11°A · Cód. 2024-EST-0001</p>
                            </div>
                            <div class="est-stats">
                                <span class="est-prom green">4.3</span>
                                <span class="est-asist">96%</span>
                            </div>
                        </li>
                        <li class="est-item">
                            <div class="est-avatar" style="background:#eff6ff;color:#3182ce">LM</div>
                            <div class="est-data">
                                <p class="est-name">Laura Martínez</p>
                                <p class="est-info">11°A · Cód. 2024-EST-0002</p>
                            </div>
                            <div class="est-stats">
                                <span class="est-prom green">4.6</span>
                                <span class="est-asist">99%</span>
                            </div>
                        </li>
                        <li class="est-item">
                            <div class="est-avatar" style="background:#fef3c7;color:#d97706">CP</div>
                            <div class="est-data">
                                <p class="est-name">Carlos Pérez</p>
                                <p class="est-info">10°B · Cód. 2024-EST-0058</p>
                            </div>
                            <div class="est-stats">
                                <span class="est-prom orange">3.4</span>
                                <span class="est-asist">78%</span>
                            </div>
                        </li>
                        <li class="est-item">
                            <div class="est-avatar" style="background:#faf5ff;color:#6b46c1">SR</div>
                            <div class="est-data">
                                <p class="est-name">Sofía Rojas</p>
                                <p class="est-info">9°C · Cód. 2024-EST-0112</p>
                            </div>
                            <div class="est-stats">
                                <span class="est-prom green">4.5</span>
                                <span class="est-asist">95%</span>
                            </div>
                        </li>
                        <li class="est-item riesgo">
                            <div class="est-avatar" style="background:#fff5f5;color:#e53e3e">DG</div>
                            <div class="est-data">
                                <p class="est-name">Diego González <span class="riesgo-tag">⚠ Riesgo</span></p>
                                <p class="est-info">8°A · Cód. 2024-EST-0187</p>
                            </div>
                            <div class="est-stats">
                                <span class="est-prom red">2.9</span>
                                <span class="est-asist low">65%</span>
                            </div>
                        </li>
                        <li class="est-item">
                            <div class="est-avatar" style="background:#e0f2fe;color:#0891b2">AM</div>
                            <div class="est-data">
                                <p class="est-name">Ana Moreno</p>
                                <p class="est-info">7°B · Cód. 2024-EST-0234</p>
                            </div>
                            <div class="est-stats">
                                <span class="est-prom green">4.1</span>
                                <span class="est-asist">91%</span>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Comunicados -->
                <div class="acard comunicados-admin-card">
                    <div class="acard-header">
                        <h2 class="acard-title"><i class="fas fa-bullhorn"></i> Comunicados</h2>
                        <button class="btn-nuevo-comunic" id="btnNuevoComunic">
                            <i class="fas fa-plus"></i> Nuevo
                        </button>
                    </div>
                    <ul class="comunic-admin-list">
                        <li class="ca-item">
                            <div class="ca-dot red"></div>
                            <div class="ca-body">
                                <div class="ca-top">
                                    <span class="ca-tipo urgente">Urgente</span>
                                    <span class="ca-fecha">Hoy</span>
                                </div>
                                <p class="ca-titulo">Cambio de horario — Viernes 29 de marzo</p>
                                <p class="ca-dest"><i class="fas fa-users"></i> Toda la comunidad · Enviado</p>
                            </div>
                        </li>
                        <li class="ca-item">
                            <div class="ca-dot green"></div>
                            <div class="ca-body">
                                <div class="ca-top">
                                    <span class="ca-tipo evento">Evento</span>
                                    <span class="ca-fecha">Ayer</span>
                                </div>
                                <p class="ca-titulo">Festival de Ciencias 2025 — Inscripciones</p>
                                <p class="ca-dest"><i class="fas fa-user-graduate"></i> Estudiantes · Enviado</p>
                            </div>
                        </li>
                        <li class="ca-item">
                            <div class="ca-dot blue"></div>
                            <div class="ca-body">
                                <div class="ca-top">
                                    <span class="ca-tipo info">Informativo</span>
                                    <span class="ca-fecha">25 Mar</span>
                                </div>
                                <p class="ca-titulo">Entrega boletines período 1 — 5 de abril</p>
                                <p class="ca-dest"><i class="fas fa-users"></i> Padres de familia · Enviado</p>
                            </div>
                        </li>
                        <li class="ca-item borrador">
                            <div class="ca-dot gray"></div>
                            <div class="ca-body">
                                <div class="ca-top">
                                    <span class="ca-tipo borrador">Borrador</span>
                                    <span class="ca-fecha">22 Mar</span>
                                </div>
                                <p class="ca-titulo">Reunión de padres — Abril 2025</p>
                                <p class="ca-dest"><i class="fas fa-pen"></i> Sin enviar</p>
                            </div>
                        </li>
                    </ul>
                    <a href="#comunicados" class="acard-footer-link">Ver todos los comunicados <i class="fas fa-arrow-right"></i></a>
                </div>

            </div>

            <!-- ══════════════════════════════════════════
           REPORTES RÁPIDOS
           ══════════════════════════════════════════ -->
            <section class="reportes-section reveal">
                <div class="acard-header">
                    <h2 class="acard-title"><i class="fas fa-file-alt"></i> Reportes y boletines</h2>
                    <span class="acard-badge green">Período 1 · 2025</span>
                </div>
                <div class="reportes-grid">
                    <div class="reporte-card" data-reporte="academico">
                        <div class="rep-icon" style="background:#e8f5ee;color:#2d7a4f">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="rep-info">
                            <h3>Boletines académicos</h3>
                            <p>Notas por grado, materia y período</p>
                        </div>
                        <div class="rep-actions">
                            <button class="rep-btn preview"><i class="fas fa-eye"></i> Ver</button>
                            <button class="rep-btn download"><i class="fas fa-download"></i> Exportar</button>
                        </div>
                    </div>
                    <div class="reporte-card" data-reporte="asistencia">
                        <div class="rep-icon" style="background:#e0f2fe;color:#0891b2">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="rep-info">
                            <h3>Reporte de asistencia</h3>
                            <p>Resumen mensual y por grado</p>
                        </div>
                        <div class="rep-actions">
                            <button class="rep-btn preview"><i class="fas fa-eye"></i> Ver</button>
                            <button class="rep-btn download"><i class="fas fa-download"></i> Exportar</button>
                        </div>
                    </div>
                    <div class="reporte-card" data-reporte="docentes">
                        <div class="rep-icon" style="background:#faf5ff;color:#6b46c1">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div class="rep-info">
                            <h3>Gestión docente</h3>
                            <p>Carga académica y asistencia</p>
                        </div>
                        <div class="rep-actions">
                            <button class="rep-btn preview"><i class="fas fa-eye"></i> Ver</button>
                            <button class="rep-btn download"><i class="fas fa-download"></i> Exportar</button>
                        </div>
                    </div>
                    <div class="reporte-card" data-reporte="matriculas">
                        <div class="rep-icon" style="background:#fff7ed;color:#dd6b20">
                            <i class="fas fa-file-signature"></i>
                        </div>
                        <div class="rep-info">
                            <h3>Estado de matrículas</h3>
                            <p>Aprobadas, pendientes y rechazadas</p>
                        </div>
                        <div class="rep-actions">
                            <button class="rep-btn preview"><i class="fas fa-eye"></i> Ver</button>
                            <button class="rep-btn download"><i class="fas fa-download"></i> Exportar</button>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </main>

    <!-- Modal nuevo comunicado -->
    <div class="modal-overlay" id="modalComunic">
        <div class="modal-box">
            <button class="modal-close-btn" id="modalComunicClose"><i class="fas fa-times"></i></button>
            <div class="modal-icon-wrap"><i class="fas fa-bullhorn"></i></div>
            <h3>Nuevo comunicado</h3>
            <p>Redacta y envía un comunicado a la comunidad educativa.</p>
            <div class="modal-form">
                <div class="mf-group">
                    <label>Asunto</label>
                    <input type="text" placeholder="Ej. Reunión de padres — Abril 2025" />
                </div>
                <div class="mf-group">
                    <label>Destinatarios</label>
                    <select>
                        <option>Toda la comunidad</option>
                        <option>Solo estudiantes</option>
                        <option>Solo docentes</option>
                        <option>Solo padres de familia</option>
                        <option>Grado específico</option>
                    </select>
                </div>
                <div class="mf-group">
                    <label>Tipo</label>
                    <select>
                        <option>Informativo</option>
                        <option>Urgente</option>
                        <option>Evento</option>
                        <option>Académico</option>
                    </select>
                </div>
                <div class="mf-group">
                    <label>Mensaje</label>
                    <textarea rows="4" placeholder="Escribe el mensaje del comunicado…"></textarea>
                </div>
                <div class="mf-btns">
                    <button class="mf-btn-save">Guardar borrador</button>
                    <button class="mf-btn-send"><i class="fas fa-paper-plane"></i> Enviar ahora</button>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/js/admin.js"></script>
</body>

</html>