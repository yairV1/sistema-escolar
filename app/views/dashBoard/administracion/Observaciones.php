<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Observaciones — Colegio San Cristóbal</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/css/admin.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/css/observaciones.css" />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>

<body>

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
                    <h1 class="ph-title"><i class="fas fa-clipboard-list"></i> Observaciones</h1>
                    <p class="ph-desc">Registro de anotaciones académicas, disciplinarias y de seguimiento por estudiante.</p>
                </div>
                <div class="ph-actions">
                    <button class="ph-btn-pri" id="btnNuevaObs">
                        <i class="fas fa-plus"></i> Nueva observación
                    </button>
                </div>
            </section>

            <!-- ══════════════════════════════════════
           RESUMEN KPIs
           ══════════════════════════════════════ -->
            <section class="obs-kpis reveal">
                <div class="ok-card">
                    <div class="ok-icon purple"><i class="fas fa-clipboard-list"></i></div>
                    <div class="ok-data">
                        <strong class="ok-val" data-count="148">148</strong>
                        <span class="ok-label">Total observaciones</span>
                    </div>
                </div>
                <div class="ok-card">
                    <div class="ok-icon red"><i class="fas fa-exclamation-circle"></i></div>
                    <div class="ok-data">
                        <strong class="ok-val" data-count="34">34</strong>
                        <span class="ok-label">Disciplinarias</span>
                    </div>
                </div>
                <div class="ok-card">
                    <div class="ok-icon green"><i class="fas fa-star"></i></div>
                    <div class="ok-data">
                        <strong class="ok-val" data-count="52">52</strong>
                        <span class="ok-label">Reconocimientos</span>
                    </div>
                </div>
                <div class="ok-card">
                    <div class="ok-icon orange"><i class="fas fa-users"></i></div>
                    <div class="ok-data">
                        <strong class="ok-val" data-count="18">18</strong>
                        <span class="ok-label">Citaciones acudiente</span>
                    </div>
                </div>
                <div class="ok-card">
                    <div class="ok-icon blue"><i class="fas fa-user-graduate"></i></div>
                    <div class="ok-data">
                        <strong class="ok-val" data-count="67">67</strong>
                        <span class="ok-label">Estudiantes con obs.</span>
                    </div>
                </div>
            </section>

            <!-- ══════════════════════════════════════
           LAYOUT PRINCIPAL: Lista + Detalle
           ══════════════════════════════════════ -->
            <div class="obs-layout reveal">

                <!-- ── PANEL IZQUIERDO: Buscador + lista ── -->
                <aside class="obs-sidebar">

                    <!-- Búsqueda de estudiante -->
                    <div class="obs-search-box">
                        <div class="osb-wrap">
                            <i class="fas fa-search"></i>
                            <input type="text" id="obsSearch" class="osb-input"
                                placeholder="Buscar estudiante…" autocomplete="off" />
                            <button class="osb-clear" id="obsClear" style="display:none;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="osb-filters">
                            <select class="osb-select" id="obsFilGrado">
                                <option value="">Todos los grados</option>
                                <option value="11">11°</option>
                                <option value="10">10°</option>
                                <option value="9">9°</option>
                                <option value="8">8°</option>
                                <option value="7">7°</option>
                            </select>
                            <select class="osb-select" id="obsFilTipo">
                                <option value="">Todos los tipos</option>
                                <option value="academica">Académica</option>
                                <option value="disciplinaria">Disciplinaria</option>
                                <option value="asistencia">Asistencia</option>
                                <option value="psicosocial">Psicosocial</option>
                                <option value="positiva">Reconocimiento</option>
                                <option value="citacion">Citación</option>
                            </select>
                        </div>
                    </div>

                    <!-- Lista de estudiantes con observaciones -->
                    <ul class="obs-est-list" id="obsEstList">

                        <li class="obs-est-item active" data-id="1" data-grado="11">
                            <div class="oei-avatar" style="background:#e8f5ee;color:#2d7a4f">JS</div>
                            <div class="oei-info">
                                <p class="oei-name">Juan Suárez</p>
                                <p class="oei-meta">11°A · 5 observaciones</p>
                            </div>
                            <div class="oei-badges">
                                <span class="oei-badge red" title="Disciplinarias">2</span>
                                <span class="oei-badge green" title="Reconocimientos">2</span>
                            </div>
                        </li>

                        <li class="obs-est-item" data-id="2" data-grado="11">
                            <div class="oei-avatar" style="background:#eff6ff;color:#3182ce">LM</div>
                            <div class="oei-info">
                                <p class="oei-name">Laura Martínez</p>
                                <p class="oei-meta">11°A · 3 observaciones</p>
                            </div>
                            <div class="oei-badges">
                                <span class="oei-badge green" title="Reconocimientos">3</span>
                            </div>
                        </li>

                        <li class="obs-est-item" data-id="3" data-grado="8">
                            <div class="oei-avatar" style="background:#fff5f5;color:#e53e3e">DG</div>
                            <div class="oei-info">
                                <p class="oei-name">Diego González</p>
                                <p class="oei-meta">8°A · 9 observaciones <span class="riesgo-tag">⚠ Riesgo</span></p>
                            </div>
                            <div class="oei-badges">
                                <span class="oei-badge red" title="Disciplinarias">5</span>
                                <span class="oei-badge orange" title="Citaciones">2</span>
                            </div>
                        </li>

                        <li class="obs-est-item" data-id="4" data-grado="9">
                            <div class="oei-avatar" style="background:#faf5ff;color:#6b46c1">SR</div>
                            <div class="oei-info">
                                <p class="oei-name">Sofía Rojas</p>
                                <p class="oei-meta">9°C · 2 observaciones</p>
                            </div>
                            <div class="oei-badges">
                                <span class="oei-badge green" title="Reconocimientos">1</span>
                                <span class="oei-badge blue" title="Académicas">1</span>
                            </div>
                        </li>

                        <li class="obs-est-item" data-id="5" data-grado="10">
                            <div class="oei-avatar" style="background:#fef3c7;color:#d97706">CP</div>
                            <div class="oei-info">
                                <p class="oei-name">Carlos Pérez</p>
                                <p class="oei-meta">10°B · 6 observaciones</p>
                            </div>
                            <div class="oei-badges">
                                <span class="oei-badge red" title="Disciplinarias">3</span>
                                <span class="oei-badge orange" title="Asistencia">2</span>
                            </div>
                        </li>

                        <li class="obs-est-item" data-id="6" data-grado="7">
                            <div class="oei-avatar" style="background:#e0f2fe;color:#0891b2">AM</div>
                            <div class="oei-info">
                                <p class="oei-name">Ana Moreno</p>
                                <p class="oei-meta">7°B · 1 observación</p>
                            </div>
                            <div class="oei-badges">
                                <span class="oei-badge green" title="Reconocimientos">1</span>
                            </div>
                        </li>

                    </ul>
                </aside>

                <!-- ── PANEL DERECHO: Detalle del estudiante ── -->
                <div class="obs-detail" id="obsDetail">

                    <!-- Header del estudiante seleccionado -->
                    <div class="od-header">
                        <div class="od-student">
                            <div class="od-avatar" style="background:#e8f5ee;color:#2d7a4f" id="detAvatar">JS</div>
                            <div class="od-student-info">
                                <h2 id="detNombre">Juan Suárez</h2>
                                <p id="detMeta">Grado 11°A · Cód. 2024-EST-0001</p>
                            </div>
                        </div>
                        <div class="od-actions">
                            <!-- Toggle vista -->
                            <div class="od-view-toggle">
                                <button class="od-view-btn active" id="btnTimeline" title="Vista timeline">
                                    <i class="fas fa-stream"></i> Timeline
                                </button>
                                <button class="od-view-btn" id="btnTabla" title="Vista tabla">
                                    <i class="fas fa-table"></i> Tabla
                                </button>
                            </div>
                            <button class="od-btn-add" id="btnAddObs">
                                <i class="fas fa-plus"></i> Agregar
                            </button>
                        </div>
                    </div>

                    <!-- Resumen rápido del estudiante -->
                    <div class="od-summary">
                        <div class="ods-item purple">
                            <strong>5</strong><span>Total</span>
                        </div>
                        <div class="ods-item red">
                            <strong>2</strong><span>Disciplinarias</span>
                        </div>
                        <div class="ods-item green">
                            <strong>2</strong><span>Reconocimientos</span>
                        </div>
                        <div class="ods-item blue">
                            <strong>1</strong><span>Académica</span>
                        </div>
                        <div class="ods-item orange">
                            <strong>0</strong><span>Citaciones</span>
                        </div>
                    </div>

                    <!-- ═══════════════════════════════
               VISTA TIMELINE
               ═══════════════════════════════ -->
                    <div class="od-timeline-view active" id="timelineView">

                        <!-- Grupo de fecha -->
                        <div class="tl-group">
                            <div class="tl-date-label">Marzo 2025</div>

                            <div class="tl-item positiva">
                                <div class="tl-dot"><i class="fas fa-star"></i></div>
                                <div class="tl-connector"></div>
                                <div class="tl-card">
                                    <div class="tl-card-header">
                                        <div class="tl-tipo-badge positiva">
                                            <i class="fas fa-star"></i> Reconocimiento
                                        </div>
                                        <span class="tl-fecha">28 Mar 2025</span>
                                        <div class="tl-card-menu">
                                            <button class="tl-menu-btn" title="Opciones"><i class="fas fa-ellipsis-v"></i></button>
                                        </div>
                                    </div>
                                    <p class="tl-texto">Juan obtuvo el primer lugar en la Olimpiada Regional de Matemáticas 2025. Demostró excelente preparación, trabajo en equipo y representó con orgullo a la institución.</p>
                                    <div class="tl-card-footer">
                                        <div class="tl-autor">
                                            <div class="tl-autor-av" style="background:#e8f5ee;color:#2d7a4f">RM</div>
                                            <div>
                                                <p class="tl-autor-name">Prof. Ricardo Méndez</p>
                                                <p class="tl-autor-role">Matemáticas</p>
                                            </div>
                                        </div>
                                        <div class="tl-card-tags">
                                            <span class="tl-tag">Matemáticas</span>
                                            <span class="tl-tag">Logro académico</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tl-item disciplinaria">
                                <div class="tl-dot"><i class="fas fa-exclamation-circle"></i></div>
                                <div class="tl-connector"></div>
                                <div class="tl-card">
                                    <div class="tl-card-header">
                                        <div class="tl-tipo-badge disciplinaria">
                                            <i class="fas fa-exclamation-circle"></i> Disciplinaria
                                        </div>
                                        <span class="tl-fecha">20 Mar 2025</span>
                                        <div class="tl-card-menu">
                                            <button class="tl-menu-btn" title="Opciones"><i class="fas fa-ellipsis-v"></i></button>
                                        </div>
                                    </div>
                                    <p class="tl-texto">El estudiante fue reportado por uso de teléfono móvil durante la clase de Inglés sin autorización del docente. Se le llamó la atención de manera verbal y se le recordaron las normas del manual de convivencia.</p>
                                    <div class="tl-card-footer">
                                        <div class="tl-autor">
                                            <div class="tl-autor-av" style="background:#fff5f5;color:#e53e3e">CR</div>
                                            <div>
                                                <p class="tl-autor-name">Prof. Carlos Ramírez</p>
                                                <p class="tl-autor-role">Inglés</p>
                                            </div>
                                        </div>
                                        <div class="tl-card-tags">
                                            <span class="tl-tag">Normas</span>
                                            <span class="tl-tag">Amonestación verbal</span>
                                        </div>
                                    </div>
                                    <div class="tl-acudiente-notif">
                                        <i class="fas fa-envelope-open-text"></i>
                                        Acudiente notificado el 20 Mar 2025
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="tl-group">
                            <div class="tl-date-label">Febrero 2025</div>

                            <div class="tl-item academica">
                                <div class="tl-dot"><i class="fas fa-book-open"></i></div>
                                <div class="tl-connector"></div>
                                <div class="tl-card">
                                    <div class="tl-card-header">
                                        <div class="tl-tipo-badge academica">
                                            <i class="fas fa-book-open"></i> Académica
                                        </div>
                                        <span class="tl-fecha">18 Feb 2025</span>
                                        <div class="tl-card-menu">
                                            <button class="tl-menu-btn"><i class="fas fa-ellipsis-v"></i></button>
                                        </div>
                                    </div>
                                    <p class="tl-texto">Juan no entregó el taller de Ciencias Naturales en la fecha establecida. Al consultarle, mencionó dificultades para entender el tema de ecosistemas. Se programó sesión de refuerzo para la siguiente semana.</p>
                                    <div class="tl-card-footer">
                                        <div class="tl-autor">
                                            <div class="tl-autor-av" style="background:#eff6ff;color:#3182ce">AT</div>
                                            <div>
                                                <p class="tl-autor-name">Prof. Ana Torres</p>
                                                <p class="tl-autor-role">Ciencias Naturales</p>
                                            </div>
                                        </div>
                                        <div class="tl-card-tags">
                                            <span class="tl-tag">Tarea pendiente</span>
                                            <span class="tl-tag">Refuerzo</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tl-item positiva">
                                <div class="tl-dot"><i class="fas fa-star"></i></div>
                                <div class="tl-connector"></div>
                                <div class="tl-card">
                                    <div class="tl-card-header">
                                        <div class="tl-tipo-badge positiva">
                                            <i class="fas fa-star"></i> Reconocimiento
                                        </div>
                                        <span class="tl-fecha">5 Feb 2025</span>
                                        <div class="tl-card-menu">
                                            <button class="tl-menu-btn"><i class="fas fa-ellipsis-v"></i></button>
                                        </div>
                                    </div>
                                    <p class="tl-texto">Juan ayudó voluntariamente a un compañero nuevo a integrarse al grupo, mostrando valores de solidaridad y liderazgo positivo. Es un ejemplo de convivencia escolar.</p>
                                    <div class="tl-card-footer">
                                        <div class="tl-autor">
                                            <div class="tl-autor-av" style="background:#faf5ff;color:#6b46c1">MC</div>
                                            <div>
                                                <p class="tl-autor-name">Prof. María Castaño</p>
                                                <p class="tl-autor-role">Directora de grupo</p>
                                            </div>
                                        </div>
                                        <div class="tl-card-tags">
                                            <span class="tl-tag">Convivencia</span>
                                            <span class="tl-tag">Liderazgo</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tl-item disciplinaria">
                                <div class="tl-dot"><i class="fas fa-exclamation-circle"></i></div>
                                <div class="tl-connector"></div>
                                <div class="tl-card">
                                    <div class="tl-card-header">
                                        <div class="tl-tipo-badge disciplinaria">
                                            <i class="fas fa-exclamation-circle"></i> Disciplinaria
                                        </div>
                                        <span class="tl-fecha">1 Feb 2025</span>
                                        <div class="tl-card-menu">
                                            <button class="tl-menu-btn"><i class="fas fa-ellipsis-v"></i></button>
                                        </div>
                                    </div>
                                    <p class="tl-texto">El estudiante llegó tarde a 3 clases consecutivas sin justificación durante la semana. Se conversó con él y se envió comunicado al acudiente para informar la situación.</p>
                                    <div class="tl-card-footer">
                                        <div class="tl-autor">
                                            <div class="tl-autor-av" style="background:#faf5ff;color:#6b46c1">MC</div>
                                            <div>
                                                <p class="tl-autor-name">Prof. María Castaño</p>
                                                <p class="tl-autor-role">Directora de grupo</p>
                                            </div>
                                        </div>
                                        <div class="tl-card-tags">
                                            <span class="tl-tag">Tardanza</span>
                                            <span class="tl-tag">Notificado</span>
                                        </div>
                                    </div>
                                    <div class="tl-acudiente-notif">
                                        <i class="fas fa-envelope-open-text"></i>
                                        Acudiente notificado el 2 Feb 2025
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div><!-- /timelineView -->

                    <!-- ═══════════════════════════════
               VISTA TABLA
               ═══════════════════════════════ -->
                    <div class="od-tabla-view" id="tablaView">
                        <div class="ot-filters">
                            <select class="ot-select" id="otFilTipo">
                                <option value="">Todos los tipos</option>
                                <option value="academica">Académica</option>
                                <option value="disciplinaria">Disciplinaria</option>
                                <option value="asistencia">Asistencia</option>
                                <option value="psicosocial">Psicosocial</option>
                                <option value="positiva">Reconocimiento</option>
                                <option value="citacion">Citación</option>
                            </select>
                            <select class="ot-select" id="otFilPeriodo">
                                <option value="">Todos los períodos</option>
                                <option value="1">Período 1 · 2025</option>
                                <option value="2">Período 2 · 2024</option>
                            </select>
                            <button class="ot-export-btn" id="btnExportObs">
                                <i class="fas fa-file-export"></i> Exportar
                            </button>
                        </div>
                        <div class="ot-table-wrap">
                            <table class="ot-table">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Descripción</th>
                                        <th>Registrado por</th>
                                        <th>Acudiente</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="obsTableBody">
                                    <tr data-tipo="positiva">
                                        <td class="ot-fecha">28 Mar 2025</td>
                                        <td><span class="ot-tipo positiva"><i class="fas fa-star"></i> Reconocimiento</span></td>
                                        <td class="ot-desc">Primer lugar Olimpiada Regional de Matemáticas 2025.</td>
                                        <td>
                                            <div class="ot-autor">
                                                <div class="ot-autor-av" style="background:#e8f5ee;color:#2d7a4f">RM</div>
                                                Prof. R. Méndez
                                            </div>
                                        </td>
                                        <td><span class="ot-notif no"><i class="fas fa-minus"></i></span></td>
                                        <td>
                                            <div class="ot-actions">
                                                <button class="ot-btn" title="Ver"><i class="fas fa-eye"></i></button>
                                                <button class="ot-btn" title="Editar"><i class="fas fa-pen"></i></button>
                                                <button class="ot-btn red" title="Eliminar"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr data-tipo="disciplinaria">
                                        <td class="ot-fecha">20 Mar 2025</td>
                                        <td><span class="ot-tipo disciplinaria"><i class="fas fa-exclamation-circle"></i> Disciplinaria</span></td>
                                        <td class="ot-desc">Uso de celular sin autorización en clase de Inglés.</td>
                                        <td>
                                            <div class="ot-autor">
                                                <div class="ot-autor-av" style="background:#fff5f5;color:#e53e3e">CR</div>
                                                Prof. C. Ramírez
                                            </div>
                                        </td>
                                        <td><span class="ot-notif si"><i class="fas fa-check"></i> 20 Mar</span></td>
                                        <td>
                                            <div class="ot-actions">
                                                <button class="ot-btn" title="Ver"><i class="fas fa-eye"></i></button>
                                                <button class="ot-btn" title="Editar"><i class="fas fa-pen"></i></button>
                                                <button class="ot-btn red" title="Eliminar"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr data-tipo="academica">
                                        <td class="ot-fecha">18 Feb 2025</td>
                                        <td><span class="ot-tipo academica"><i class="fas fa-book-open"></i> Académica</span></td>
                                        <td class="ot-desc">No entregó taller de Ciencias en fecha establecida.</td>
                                        <td>
                                            <div class="ot-autor">
                                                <div class="ot-autor-av" style="background:#eff6ff;color:#3182ce">AT</div>
                                                Prof. A. Torres
                                            </div>
                                        </td>
                                        <td><span class="ot-notif no"><i class="fas fa-minus"></i></span></td>
                                        <td>
                                            <div class="ot-actions">
                                                <button class="ot-btn"><i class="fas fa-eye"></i></button>
                                                <button class="ot-btn"><i class="fas fa-pen"></i></button>
                                                <button class="ot-btn red"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr data-tipo="positiva">
                                        <td class="ot-fecha">5 Feb 2025</td>
                                        <td><span class="ot-tipo positiva"><i class="fas fa-star"></i> Reconocimiento</span></td>
                                        <td class="ot-desc">Apoyo a compañero nuevo. Valores de solidaridad y liderazgo.</td>
                                        <td>
                                            <div class="ot-autor">
                                                <div class="ot-autor-av" style="background:#faf5ff;color:#6b46c1">MC</div>
                                                Prof. M. Castaño
                                            </div>
                                        </td>
                                        <td><span class="ot-notif no"><i class="fas fa-minus"></i></span></td>
                                        <td>
                                            <div class="ot-actions">
                                                <button class="ot-btn"><i class="fas fa-eye"></i></button>
                                                <button class="ot-btn"><i class="fas fa-pen"></i></button>
                                                <button class="ot-btn red"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr data-tipo="disciplinaria">
                                        <td class="ot-fecha">1 Feb 2025</td>
                                        <td><span class="ot-tipo disciplinaria"><i class="fas fa-exclamation-circle"></i> Disciplinaria</span></td>
                                        <td class="ot-desc">3 tardanzas consecutivas sin justificación.</td>
                                        <td>
                                            <div class="ot-autor">
                                                <div class="ot-autor-av" style="background:#faf5ff;color:#6b46c1">MC</div>
                                                Prof. M. Castaño
                                            </div>
                                        </td>
                                        <td><span class="ot-notif si"><i class="fas fa-check"></i> 2 Feb</span></td>
                                        <td>
                                            <div class="ot-actions">
                                                <button class="ot-btn"><i class="fas fa-eye"></i></button>
                                                <button class="ot-btn"><i class="fas fa-pen"></i></button>
                                                <button class="ot-btn red"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div><!-- /tablaView -->

                </div><!-- /obs-detail -->
            </div><!-- /obs-layout -->

        </div>
    </main>

    <!-- ══════════════════════════════════════
       MODAL NUEVA OBSERVACIÓN
       ══════════════════════════════════════ -->
    <div class="modal-overlay" id="modalObs">
        <div class="modal-obs-box">
            <div class="mob-header">
                <div class="mob-icon" id="mobIconWrap">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div>
                    <h3 id="mobTitle">Nueva observación</h3>
                    <p id="mobSubtitle">Completa los campos para registrar la anotación.</p>
                </div>
                <button class="mob-close" id="mobClose"><i class="fas fa-times"></i></button>
            </div>

            <form class="mob-form" id="obsForm" novalidate>

                <div class="mob-grid">

                    <!-- Estudiante -->
                    <div class="mob-group mob-col-full">
                        <label class="mob-label">Estudiante <span class="mob-req">*</span></label>
                        <div class="mob-input-wrap">
                            <i class="fas fa-user-graduate mob-icon-f"></i>
                            <select class="mob-input mob-select" id="obsEstudiante" required>
                                <option value="">Seleccionar estudiante…</option>
                                <option value="1">Juan Suárez — 11°A</option>
                                <option value="2">Laura Martínez — 11°A</option>
                                <option value="3">Diego González — 8°A</option>
                                <option value="4">Sofía Rojas — 9°C</option>
                                <option value="5">Carlos Pérez — 10°B</option>
                                <option value="6">Ana Moreno — 7°B</option>
                            </select>
                        </div>
                        <span class="mob-error" id="err-obsEstudiante"></span>
                    </div>

                    <!-- Tipo -->
                    <div class="mob-group mob-col-2">
                        <label class="mob-label">Tipo de observación <span class="mob-req">*</span></label>
                        <div class="mob-tipo-grid" id="mobTipoGrid">
                            <button type="button" class="mob-tipo-btn" data-tipo="academica">
                                <i class="fas fa-book-open"></i><span>Académica</span>
                            </button>
                            <button type="button" class="mob-tipo-btn" data-tipo="disciplinaria">
                                <i class="fas fa-exclamation-circle"></i><span>Disciplinaria</span>
                            </button>
                            <button type="button" class="mob-tipo-btn" data-tipo="asistencia">
                                <i class="fas fa-user-clock"></i><span>Asistencia</span>
                            </button>
                            <button type="button" class="mob-tipo-btn" data-tipo="psicosocial">
                                <i class="fas fa-brain"></i><span>Psicosocial</span>
                            </button>
                            <button type="button" class="mob-tipo-btn" data-tipo="positiva">
                                <i class="fas fa-star"></i><span>Reconocimiento</span>
                            </button>
                            <button type="button" class="mob-tipo-btn" data-tipo="citacion">
                                <i class="fas fa-users"></i><span>Citación</span>
                            </button>
                        </div>
                        <span class="mob-error" id="err-obsTipo"></span>
                    </div>

                    <!-- Fecha -->
                    <div class="mob-group mob-col-2">
                        <label class="mob-label">Fecha <span class="mob-req">*</span></label>
                        <div class="mob-input-wrap">
                            <i class="fas fa-calendar mob-icon-f"></i>
                            <input type="date" id="obsFecha" class="mob-input" required />
                        </div>
                        <span class="mob-error" id="err-obsFecha"></span>
                    </div>

                    <!-- Materia relacionada -->
                    <div class="mob-group mob-col-2">
                        <label class="mob-label">Materia relacionada</label>
                        <div class="mob-input-wrap">
                            <i class="fas fa-book mob-icon-f"></i>
                            <select class="mob-input mob-select" id="obsMateria">
                                <option value="">General / Sin materia</option>
                                <option>Matemáticas</option>
                                <option>Ciencias Naturales</option>
                                <option>Español y Literatura</option>
                                <option>Historia y Sociales</option>
                                <option>Inglés</option>
                                <option>Física</option>
                                <option>Educación Física</option>
                                <option>Tecnología e Informática</option>
                            </select>
                        </div>
                    </div>

                    <!-- Período -->
                    <div class="mob-group mob-col-2">
                        <label class="mob-label">Período académico <span class="mob-req">*</span></label>
                        <div class="mob-input-wrap">
                            <i class="fas fa-calendar-alt mob-icon-f"></i>
                            <select class="mob-input mob-select" id="obsPeriodo" required>
                                <option value="">Seleccionar…</option>
                                <option value="1" selected>Período 1 · 2025</option>
                                <option value="2">Período 2 · 2025</option>
                            </select>
                        </div>
                        <span class="mob-error" id="err-obsPeriodo"></span>
                    </div>

                    <!-- Descripción -->
                    <div class="mob-group mob-col-full">
                        <label class="mob-label">Descripción <span class="mob-req">*</span></label>
                        <div class="mob-input-wrap">
                            <i class="fas fa-align-left mob-icon-f" style="top:12px;"></i>
                            <textarea id="obsDescripcion" class="mob-input mob-textarea" rows="4"
                                placeholder="Describe detalladamente la observación, el contexto y las acciones tomadas…"
                                required></textarea>
                        </div>
                        <div class="mob-char-count"><span id="charCount">0</span> / 500 caracteres</div>
                        <span class="mob-error" id="err-obsDescripcion"></span>
                    </div>

                    <!-- Notificar acudiente -->
                    <div class="mob-group mob-col-full">
                        <label class="mob-toggle-label">
                            <div class="mob-toggle-info">
                                <i class="fas fa-envelope"></i>
                                <div>
                                    <p class="mob-toggle-title">Notificar al acudiente</p>
                                    <p class="mob-toggle-sub">Se enviará un correo automático con la observación registrada</p>
                                </div>
                            </div>
                            <div class="mob-toggle-wrap">
                                <input type="checkbox" id="obsNotificar" />
                                <span class="mob-toggle-track">
                                    <span class="mob-toggle-thumb"></span>
                                </span>
                            </div>
                        </label>
                    </div>

                    <!-- Tags -->
                    <div class="mob-group mob-col-full">
                        <label class="mob-label">Etiquetas (opcional)</label>
                        <div class="mob-tags-wrap" id="mobTagsWrap">
                            <div class="mob-tags-input-row">
                                <input type="text" id="mobTagInput" class="mob-tag-input"
                                    placeholder="Escribe y presiona Enter…" maxlength="20" />
                            </div>
                            <div class="mob-tags-list" id="mobTagsList"></div>
                            <div class="mob-tags-suggest">
                                <span class="mob-tag-sug" data-tag="Comportamiento">Comportamiento</span>
                                <span class="mob-tag-sug" data-tag="Rendimiento">Rendimiento</span>
                                <span class="mob-tag-sug" data-tag="Tardanza">Tardanza</span>
                                <span class="mob-tag-sug" data-tag="Logro">Logro</span>
                                <span class="mob-tag-sug" data-tag="Refuerzo">Refuerzo</span>
                                <span class="mob-tag-sug" data-tag="Citación">Citación</span>
                            </div>
                        </div>
                    </div>

                </div><!-- /mob-grid -->

                <div class="mob-footer">
                    <button type="button" class="mob-btn-cancel" id="mobCancel">Cancelar</button>
                    <button type="submit" class="mob-btn-save" id="mobSave">
                        <i class="fas fa-save"></i> Guardar observación
                    </button>
                </div>

            </form>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toastContainer"></div>

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/js/admin.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/js/observaciones.js"></script>
</body>

</html>