<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Listade de Docentes y Estudiantes — Colegio San Cristóbal</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/css/admin.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/css/listado.css" />
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
           PAGE HEADER
           ══════════════════════════════════════ -->
            <section class="page-header reveal">
                <div class="ph-left">
                    <h1 class="ph-title">
                        <i class="fas fa-list-alt"></i> Listados
                    </h1>
                    <p class="ph-desc">Consulta, filtra y gestiona los registros de estudiantes y docentes.</p>
                </div>
                <div class="ph-right ph-actions">
                    <button class="ph-btn-export" id="btnExport">
                        <i class="fas fa-file-export"></i> Exportar
                    </button>
                    <a href="<?= BASE_URL ?>RegistroEstudiantes" class="ph-btn-nuevo" id="btnNuevo">
                        <i class="fas fa-plus"></i> <span id="btnNuevoLabel">Nuevo estudiante</span>
                    </a>
                </div>
            </section>

            <!-- ══════════════════════════════════════
           TABS
           ══════════════════════════════════════ -->
            <div class="listado-tabs reveal">
                <div class="lt-tabs-bar">

                    <button class="lt-tab active" data-tab="estudiantes">
                        <i class="fas fa-user-graduate"></i>
                        <span>Estudiantes</span>
                        <span class="lt-tab-count" id="countEstudiantes">1,240</span>
                    </button>

                    <button class="lt-tab" data-tab="docentes">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>Docentes</span>
                        <span class="lt-tab-count" id="countDocentes">87</span>
                    </button>

                    <!-- Indicador deslizante -->
                    <div class="lt-tab-slider" id="tabSlider"></div>
                </div>
            </div>

            <!-- ══════════════════════════════════════
           PANEL — ESTUDIANTES
           ══════════════════════════════════════ -->
            <div class="lt-panel active reveal" id="panel-estudiantes">

                <!-- Barra de filtros -->
                <div class="lt-filters">
                    <div class="lt-search-wrap">
                        <i class="fas fa-search"></i>
                        <input
                            type="text"
                            id="searchEst"
                            class="lt-search"
                            placeholder="Buscar por nombre, código o grado…"
                            autocomplete="off" />
                        <button class="lt-search-clear" id="clearSearchEst" style="display:none;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="lt-filter-group">
                        <select class="lt-select" id="filtroGrado">
                            <option value="">Todos los grados</option>
                            <option>Preescolar</option>
                            <option>1°</option>
                            <option>2°</option>
                            <option>3°</option>
                            <option>4°</option>
                            <option>5°</option>
                            <option>6°</option>
                            <option>7°</option>
                            <option>8°</option>
                            <option>9°</option>
                            <option>10°</option>
                            <option>11°</option>
                        </select>
                        <select class="lt-select" id="filtroEstadoEst">
                            <option value="">Todos los estados</option>
                            <option value="activo">Activo</option>
                            <option value="riesgo">En riesgo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                        <select class="lt-select" id="filtroJornadaEst">
                            <option value="">Todas las jornadas</option>
                            <option value="manana">Mañana</option>
                            <option value="tarde">Tarde</option>
                        </select>
                    </div>
                    <div class="lt-view-toggle">
                        <button class="lt-view-btn active" id="viewTableEst" title="Vista tabla">
                            <i class="fas fa-table"></i>
                        </button>
                        <button class="lt-view-btn" id="viewCardEst" title="Vista tarjetas">
                            <i class="fas fa-th-large"></i>
                        </button>
                    </div>
                </div>

                <!-- Resumen rápido -->
                <div class="lt-summary-strip" id="summaryEst">
                    <div class="lss-item">
                        <strong id="totalEstMostrados">1,240</strong>
                        <span>Total</span>
                    </div>
                    <div class="lss-sep"></div>
                    <div class="lss-item green">
                        <strong>1,212</strong>
                        <span>Activos</span>
                    </div>
                    <div class="lss-sep"></div>
                    <div class="lss-item red">
                        <strong>8</strong>
                        <span>En riesgo</span>
                    </div>
                    <div class="lss-sep"></div>
                    <div class="lss-item gray">
                        <strong>20</strong>
                        <span>Inactivos</span>
                    </div>
                </div>

                <!-- VISTA TABLA de estudiantes -->
                <div class="lt-table-wrap" id="tableViewEst">
                    <table class="lt-table" id="tablaEstudiantes">
                        <thead>
                            <tr>
                                <th class="th-check">
                                    <label class="lt-check-all">
                                        <input type="checkbox" id="checkAllEst" />
                                        <span class="lt-checkmark"></span>
                                    </label>
                                </th>
                                <th class="th-sort" data-col="nombre">Estudiante <i class="fas fa-sort"></i></th>
                                <th class="th-sort" data-col="codigo">Código <i class="fas fa-sort"></i></th>
                                <th class="th-sort" data-col="grado">Grado <i class="fas fa-sort"></i></th>
                                <th class="th-sort" data-col="prom">Promedio <i class="fas fa-sort"></i></th>
                                <th class="th-sort" data-col="asist">Asistencia <i class="fas fa-sort"></i></th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="bodyEstudiantes">

                            <tr class="lt-row" data-grado="11" data-estado="activo" data-jornada="manana">
                                <td><label class="lt-check-row"><input type="checkbox" /><span class="lt-checkmark"></span></label></td>
                                <td>
                                    <div class="lt-user-cell">
                                        <div class="lt-avatar" style="background:#e8f5ee;color:#2d7a4f">JS</div>
                                        <div class="lt-user-info">
                                            <p class="lt-user-name">Juan Suárez</p>
                                            <p class="lt-user-sub">jsuarez@sancristobal.edu.co</p>
                                        </div>
                                    </div>
                                </td>
                                <td><code class="lt-code">2024-EST-0001</code></td>
                                <td><span class="lt-grado-pill">11°A</span></td>
                                <td><span class="lt-prom green">4.3</span></td>
                                <td>
                                    <div class="lt-asist-wrap">
                                        <div class="lt-asist-bar">
                                            <div class="lt-asist-fill" style="width:96%"></div>
                                        </div>
                                        <span>96%</span>
                                    </div>
                                </td>
                                <td><span class="lt-status activo">Activo</span></td>
                                <td>
                                    <div class="lt-actions">
                                        <button class="lt-act-btn" title="Ver perfil"><i class="fas fa-eye"></i></button>
                                        <button class="lt-act-btn" title="Editar"><i class="fas fa-pen"></i></button>
                                        <button class="lt-act-btn red" title="Desactivar"><i class="fas fa-ban"></i></button>
                                    </div>
                                </td>
                            </tr>

                            <tr class="lt-row" data-grado="11" data-estado="activo" data-jornada="manana">
                                <td><label class="lt-check-row"><input type="checkbox" /><span class="lt-checkmark"></span></label></td>
                                <td>
                                    <div class="lt-user-cell">
                                        <div class="lt-avatar" style="background:#eff6ff;color:#3182ce">LM</div>
                                        <div class="lt-user-info">
                                            <p class="lt-user-name">Laura Martínez</p>
                                            <p class="lt-user-sub">lmartinez@sancristobal.edu.co</p>
                                        </div>
                                    </div>
                                </td>
                                <td><code class="lt-code">2024-EST-0002</code></td>
                                <td><span class="lt-grado-pill">11°A</span></td>
                                <td><span class="lt-prom green">4.6</span></td>
                                <td>
                                    <div class="lt-asist-wrap">
                                        <div class="lt-asist-bar">
                                            <div class="lt-asist-fill" style="width:99%"></div>
                                        </div>
                                        <span>99%</span>
                                    </div>
                                </td>
                                <td><span class="lt-status activo">Activo</span></td>
                                <td>
                                    <div class="lt-actions">
                                        <button class="lt-act-btn" title="Ver perfil"><i class="fas fa-eye"></i></button>
                                        <button class="lt-act-btn" title="Editar"><i class="fas fa-pen"></i></button>
                                        <button class="lt-act-btn red" title="Desactivar"><i class="fas fa-ban"></i></button>
                                    </div>
                                </td>
                            </tr>

                            <tr class="lt-row riesgo" data-grado="8" data-estado="riesgo" data-jornada="manana">
                                <td><label class="lt-check-row"><input type="checkbox" /><span class="lt-checkmark"></span></label></td>
                                <td>
                                    <div class="lt-user-cell">
                                        <div class="lt-avatar" style="background:#fff5f5;color:#e53e3e">DG</div>
                                        <div class="lt-user-info">
                                            <p class="lt-user-name">Diego González <span class="lt-riesgo-tag">⚠ Riesgo</span></p>
                                            <p class="lt-user-sub">dgonzalez@sancristobal.edu.co</p>
                                        </div>
                                    </div>
                                </td>
                                <td><code class="lt-code">2024-EST-0187</code></td>
                                <td><span class="lt-grado-pill orange">8°A</span></td>
                                <td><span class="lt-prom red">2.9</span></td>
                                <td>
                                    <div class="lt-asist-wrap">
                                        <div class="lt-asist-bar">
                                            <div class="lt-asist-fill low" style="width:65%"></div>
                                        </div>
                                        <span class="red-text">65%</span>
                                    </div>
                                </td>
                                <td><span class="lt-status riesgo">En riesgo</span></td>
                                <td>
                                    <div class="lt-actions">
                                        <button class="lt-act-btn" title="Ver perfil"><i class="fas fa-eye"></i></button>
                                        <button class="lt-act-btn" title="Editar"><i class="fas fa-pen"></i></button>
                                        <button class="lt-act-btn red" title="Desactivar"><i class="fas fa-ban"></i></button>
                                    </div>
                                </td>
                            </tr>

                            <tr class="lt-row" data-grado="10" data-estado="activo" data-jornada="tarde">
                                <td><label class="lt-check-row"><input type="checkbox" /><span class="lt-checkmark"></span></label></td>
                                <td>
                                    <div class="lt-user-cell">
                                        <div class="lt-avatar" style="background:#fef3c7;color:#d97706">CP</div>
                                        <div class="lt-user-info">
                                            <p class="lt-user-name">Carlos Pérez</p>
                                            <p class="lt-user-sub">cperez@sancristobal.edu.co</p>
                                        </div>
                                    </div>
                                </td>
                                <td><code class="lt-code">2024-EST-0058</code></td>
                                <td><span class="lt-grado-pill blue">10°B</span></td>
                                <td><span class="lt-prom orange">3.4</span></td>
                                <td>
                                    <div class="lt-asist-wrap">
                                        <div class="lt-asist-bar">
                                            <div class="lt-asist-fill med" style="width:78%"></div>
                                        </div>
                                        <span>78%</span>
                                    </div>
                                </td>
                                <td><span class="lt-status activo">Activo</span></td>
                                <td>
                                    <div class="lt-actions">
                                        <button class="lt-act-btn" title="Ver perfil"><i class="fas fa-eye"></i></button>
                                        <button class="lt-act-btn" title="Editar"><i class="fas fa-pen"></i></button>
                                        <button class="lt-act-btn red" title="Desactivar"><i class="fas fa-ban"></i></button>
                                    </div>
                                </td>
                            </tr>

                            <tr class="lt-row" data-grado="9" data-estado="activo" data-jornada="manana">
                                <td><label class="lt-check-row"><input type="checkbox" /><span class="lt-checkmark"></span></label></td>
                                <td>
                                    <div class="lt-user-cell">
                                        <div class="lt-avatar" style="background:#faf5ff;color:#6b46c1">SR</div>
                                        <div class="lt-user-info">
                                            <p class="lt-user-name">Sofía Rojas</p>
                                            <p class="lt-user-sub">srojas@sancristobal.edu.co</p>
                                        </div>
                                    </div>
                                </td>
                                <td><code class="lt-code">2024-EST-0112</code></td>
                                <td><span class="lt-grado-pill purple">9°C</span></td>
                                <td><span class="lt-prom green">4.5</span></td>
                                <td>
                                    <div class="lt-asist-wrap">
                                        <div class="lt-asist-bar">
                                            <div class="lt-asist-fill" style="width:95%"></div>
                                        </div>
                                        <span>95%</span>
                                    </div>
                                </td>
                                <td><span class="lt-status activo">Activo</span></td>
                                <td>
                                    <div class="lt-actions">
                                        <button class="lt-act-btn" title="Ver perfil"><i class="fas fa-eye"></i></button>
                                        <button class="lt-act-btn" title="Editar"><i class="fas fa-pen"></i></button>
                                        <button class="lt-act-btn red" title="Desactivar"><i class="fas fa-ban"></i></button>
                                    </div>
                                </td>
                            </tr>

                            <tr class="lt-row" data-grado="7" data-estado="activo" data-jornada="tarde">
                                <td><label class="lt-check-row"><input type="checkbox" /><span class="lt-checkmark"></span></label></td>
                                <td>
                                    <div class="lt-user-cell">
                                        <div class="lt-avatar" style="background:#e0f2fe;color:#0891b2">AM</div>
                                        <div class="lt-user-info">
                                            <p class="lt-user-name">Ana Moreno</p>
                                            <p class="lt-user-sub">amoreno@sancristobal.edu.co</p>
                                        </div>
                                    </div>
                                </td>
                                <td><code class="lt-code">2024-EST-0234</code></td>
                                <td><span class="lt-grado-pill teal">7°B</span></td>
                                <td><span class="lt-prom green">4.1</span></td>
                                <td>
                                    <div class="lt-asist-wrap">
                                        <div class="lt-asist-bar">
                                            <div class="lt-asist-fill" style="width:91%"></div>
                                        </div>
                                        <span>91%</span>
                                    </div>
                                </td>
                                <td><span class="lt-status activo">Activo</span></td>
                                <td>
                                    <div class="lt-actions">
                                        <button class="lt-act-btn" title="Ver perfil"><i class="fas fa-eye"></i></button>
                                        <button class="lt-act-btn" title="Editar"><i class="fas fa-pen"></i></button>
                                        <button class="lt-act-btn red" title="Desactivar"><i class="fas fa-ban"></i></button>
                                    </div>
                                </td>
                            </tr>

                        </tbody>
                    </table>

                    <!-- Empty state -->
                    <div class="lt-empty" id="emptyEst" style="display:none;">
                        <i class="fas fa-user-graduate"></i>
                        <p>No se encontraron estudiantes con los filtros aplicados.</p>
                        <button class="lt-empty-reset" onclick="resetFiltrosEst()">Limpiar filtros</button>
                    </div>
                </div>

                <!-- Paginación -->
                <div class="lt-pagination" id="paginEst">
                    <div class="lt-page-info">
                        Mostrando <strong>1–6</strong> de <strong>1,240</strong> estudiantes
                    </div>
                    <div class="lt-page-btns">
                        <button class="lt-page-btn" disabled><i class="fas fa-chevron-left"></i></button>
                        <button class="lt-page-btn active">1</button>
                        <button class="lt-page-btn">2</button>
                        <button class="lt-page-btn">3</button>
                        <span class="lt-page-dots">…</span>
                        <button class="lt-page-btn">207</button>
                        <button class="lt-page-btn"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>

            </div><!-- /panel-estudiantes -->

            <!-- ══════════════════════════════════════
           PANEL — DOCENTES
           ══════════════════════════════════════ -->
            <div class="lt-panel" id="panel-docentes">

                <!-- Barra de filtros -->
                <div class="lt-filters">
                    <div class="lt-search-wrap">
                        <i class="fas fa-search"></i>
                        <input
                            type="text"
                            id="searchDoc"
                            class="lt-search"
                            placeholder="Buscar por nombre, área o código…"
                            autocomplete="off" />
                        <button class="lt-search-clear" id="clearSearchDoc" style="display:none;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="lt-filter-group">
                        <select class="lt-select" id="filtroArea">
                            <option value="">Todas las áreas</option>
                            <option value="matematicas">Matemáticas</option>
                            <option value="ciencias">Ciencias</option>
                            <option value="humanidades">Humanidades</option>
                            <option value="sociales">Ciencias Sociales</option>
                            <option value="ed_fisica">Ed. Física</option>
                            <option value="tecnologia">Tecnología</option>
                        </select>
                        <select class="lt-select" id="filtroEstadoDoc">
                            <option value="">Todos los estados</option>
                            <option value="activo">Activo</option>
                            <option value="licencia">En licencia</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                        <select class="lt-select" id="filtroContrato">
                            <option value="">Tipo contrato</option>
                            <option value="planta">Planta</option>
                            <option value="provisional">Provisional</option>
                            <option value="hora_catedra">Hora cátedra</option>
                        </select>
                    </div>
                    <div class="lt-view-toggle">
                        <button class="lt-view-btn active" id="viewTableDoc" title="Vista tabla">
                            <i class="fas fa-table"></i>
                        </button>
                        <button class="lt-view-btn" id="viewCardDoc" title="Vista tarjetas">
                            <i class="fas fa-th-large"></i>
                        </button>
                    </div>
                </div>

                <!-- Resumen rápido -->
                <div class="lt-summary-strip">
                    <div class="lss-item">
                        <strong>87</strong><span>Total</span>
                    </div>
                    <div class="lss-sep"></div>
                    <div class="lss-item green">
                        <strong>81</strong><span>Activos</span>
                    </div>
                    <div class="lss-sep"></div>
                    <div class="lss-item orange">
                        <strong>4</strong><span>Licencia</span>
                    </div>
                    <div class="lss-sep"></div>
                    <div class="lss-item gray">
                        <strong>2</strong><span>Inactivos</span>
                    </div>
                </div>

                <!-- VISTA TABLA de docentes -->
                <div class="lt-table-wrap" id="tableViewDoc">
                    <table class="lt-table" id="tablaDocentes">
                        <thead>
                            <tr>
                                <th class="th-check">
                                    <label class="lt-check-all">
                                        <input type="checkbox" id="checkAllDoc" />
                                        <span class="lt-checkmark"></span>
                                    </label>
                                </th>
                                <th class="th-sort" data-col="nombre">Docente <i class="fas fa-sort"></i></th>
                                <th class="th-sort" data-col="codigo">Código <i class="fas fa-sort"></i></th>
                                <th class="th-sort" data-col="area">Área <i class="fas fa-sort"></i></th>
                                <th>Grados</th>
                                <th class="th-sort" data-col="contrato">Contrato <i class="fas fa-sort"></i></th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="bodyDocentes">

                            <tr class="lt-row" data-area="matematicas" data-estado="activo" data-contrato="planta">
                                <td><label class="lt-check-row"><input type="checkbox" /><span class="lt-checkmark"></span></label></td>
                                <td>
                                    <div class="lt-user-cell">
                                        <div class="lt-avatar" style="background:#e8f5ee;color:#2d7a4f">RM</div>
                                        <div class="lt-user-info">
                                            <p class="lt-user-name">Ricardo Mendez</p>
                                            <p class="lt-user-sub">rmendez@sancristobal.edu.co</p>
                                        </div>
                                    </div>
                                </td>
                                <td><code class="lt-code">DOC-2019-001</code></td>
                                <td>
                                    <div class="lt-area-cell">
                                        <span class="lt-area-dot" style="background:#2d7a4f"></span>
                                        Matemáticas
                                    </div>
                                </td>
                                <td>
                                    <div class="lt-grados-chips">
                                        <span class="lt-chip">11°A</span>
                                        <span class="lt-chip">10°B</span>
                                        <span class="lt-chip">9°C</span>
                                    </div>
                                </td>
                                <td><span class="lt-contrato planta">Planta</span></td>
                                <td><span class="lt-status activo">Activo</span></td>
                                <td>
                                    <div class="lt-actions">
                                        <button class="lt-act-btn" title="Ver perfil"><i class="fas fa-eye"></i></button>
                                        <button class="lt-act-btn" title="Editar"><i class="fas fa-pen"></i></button>
                                        <button class="lt-act-btn red" title="Desactivar"><i class="fas fa-ban"></i></button>
                                    </div>
                                </td>
                            </tr>

                            <tr class="lt-row" data-area="ciencias" data-estado="activo" data-contrato="planta">
                                <td><label class="lt-check-row"><input type="checkbox" /><span class="lt-checkmark"></span></label></td>
                                <td>
                                    <div class="lt-user-cell">
                                        <div class="lt-avatar" style="background:#eff6ff;color:#3182ce">AT</div>
                                        <div class="lt-user-info">
                                            <p class="lt-user-name">Ana Torres</p>
                                            <p class="lt-user-sub">atorres@sancristobal.edu.co</p>
                                        </div>
                                    </div>
                                </td>
                                <td><code class="lt-code">DOC-2020-014</code></td>
                                <td>
                                    <div class="lt-area-cell">
                                        <span class="lt-area-dot" style="background:#3182ce"></span>
                                        Ciencias Naturales
                                    </div>
                                </td>
                                <td>
                                    <div class="lt-grados-chips">
                                        <span class="lt-chip">11°A</span>
                                        <span class="lt-chip">10°A</span>
                                    </div>
                                </td>
                                <td><span class="lt-contrato planta">Planta</span></td>
                                <td><span class="lt-status activo">Activo</span></td>
                                <td>
                                    <div class="lt-actions">
                                        <button class="lt-act-btn" title="Ver perfil"><i class="fas fa-eye"></i></button>
                                        <button class="lt-act-btn" title="Editar"><i class="fas fa-pen"></i></button>
                                        <button class="lt-act-btn red" title="Desactivar"><i class="fas fa-ban"></i></button>
                                    </div>
                                </td>
                            </tr>

                            <tr class="lt-row" data-area="humanidades" data-estado="licencia" data-contrato="provisional">
                                <td><label class="lt-check-row"><input type="checkbox" /><span class="lt-checkmark"></span></label></td>
                                <td>
                                    <div class="lt-user-cell">
                                        <div class="lt-avatar" style="background:#fff5f5;color:#e53e3e">CR</div>
                                        <div class="lt-user-info">
                                            <p class="lt-user-name">Carlos Ramírez</p>
                                            <p class="lt-user-sub">cramírez@sancristobal.edu.co</p>
                                        </div>
                                    </div>
                                </td>
                                <td><code class="lt-code">DOC-2021-022</code></td>
                                <td>
                                    <div class="lt-area-cell">
                                        <span class="lt-area-dot" style="background:#6b46c1"></span>
                                        Inglés
                                    </div>
                                </td>
                                <td>
                                    <div class="lt-grados-chips">
                                        <span class="lt-chip">11°A</span>
                                        <span class="lt-chip">11°B</span>
                                        <span class="lt-chip">10°A</span>
                                    </div>
                                </td>
                                <td><span class="lt-contrato provisional">Provisional</span></td>
                                <td><span class="lt-status licencia">Licencia</span></td>
                                <td>
                                    <div class="lt-actions">
                                        <button class="lt-act-btn" title="Ver perfil"><i class="fas fa-eye"></i></button>
                                        <button class="lt-act-btn" title="Editar"><i class="fas fa-pen"></i></button>
                                        <button class="lt-act-btn red" title="Desactivar"><i class="fas fa-ban"></i></button>
                                    </div>
                                </td>
                            </tr>

                            <tr class="lt-row" data-area="sociales" data-estado="activo" data-contrato="planta">
                                <td><label class="lt-check-row"><input type="checkbox" /><span class="lt-checkmark"></span></label></td>
                                <td>
                                    <div class="lt-user-cell">
                                        <div class="lt-avatar" style="background:#fff7ed;color:#dd6b20">LV</div>
                                        <div class="lt-user-info">
                                            <p class="lt-user-name">Luis Vargas</p>
                                            <p class="lt-user-sub">lvargas@sancristobal.edu.co</p>
                                        </div>
                                    </div>
                                </td>
                                <td><code class="lt-code">DOC-2018-007</code></td>
                                <td>
                                    <div class="lt-area-cell">
                                        <span class="lt-area-dot" style="background:#dd6b20"></span>
                                        Historia y Sociales
                                    </div>
                                </td>
                                <td>
                                    <div class="lt-grados-chips">
                                        <span class="lt-chip">10°A</span>
                                        <span class="lt-chip">9°A</span>
                                        <span class="lt-chip">8°B</span>
                                    </div>
                                </td>
                                <td><span class="lt-contrato planta">Planta</span></td>
                                <td><span class="lt-status activo">Activo</span></td>
                                <td>
                                    <div class="lt-actions">
                                        <button class="lt-act-btn" title="Ver perfil"><i class="fas fa-eye"></i></button>
                                        <button class="lt-act-btn" title="Editar"><i class="fas fa-pen"></i></button>
                                        <button class="lt-act-btn red" title="Desactivar"><i class="fas fa-ban"></i></button>
                                    </div>
                                </td>
                            </tr>

                            <tr class="lt-row" data-area="humanidades" data-estado="activo" data-contrato="planta">
                                <td><label class="lt-check-row"><input type="checkbox" /><span class="lt-checkmark"></span></label></td>
                                <td>
                                    <div class="lt-user-cell">
                                        <div class="lt-avatar" style="background:#faf5ff;color:#6b46c1">MC</div>
                                        <div class="lt-user-info">
                                            <p class="lt-user-name">María Castaño</p>
                                            <p class="lt-user-sub">mcastano@sancristobal.edu.co</p>
                                        </div>
                                    </div>
                                </td>
                                <td><code class="lt-code">DOC-2017-003</code></td>
                                <td>
                                    <div class="lt-area-cell">
                                        <span class="lt-area-dot" style="background:#6b46c1"></span>
                                        Español y Literatura
                                    </div>
                                </td>
                                <td>
                                    <div class="lt-grados-chips">
                                        <span class="lt-chip">11°A</span>
                                        <span class="lt-chip">11°B</span>
                                        <span class="lt-chip">10°C</span>
                                    </div>
                                </td>
                                <td><span class="lt-contrato planta">Planta</span></td>
                                <td><span class="lt-status activo">Activo</span></td>
                                <td>
                                    <div class="lt-actions">
                                        <button class="lt-act-btn" title="Ver perfil"><i class="fas fa-eye"></i></button>
                                        <button class="lt-act-btn" title="Editar"><i class="fas fa-pen"></i></button>
                                        <button class="lt-act-btn red" title="Desactivar"><i class="fas fa-ban"></i></button>
                                    </div>
                                </td>
                            </tr>

                            <tr class="lt-row" data-area="tecnologia" data-estado="activo" data-contrato="hora_catedra">
                                <td><label class="lt-check-row"><input type="checkbox" /><span class="lt-checkmark"></span></label></td>
                                <td>
                                    <div class="lt-user-cell">
                                        <div class="lt-avatar" style="background:#e0f2fe;color:#0891b2">NO</div>
                                        <div class="lt-user-info">
                                            <p class="lt-user-name">Natalia Ospina</p>
                                            <p class="lt-user-sub">nospina@sancristobal.edu.co</p>
                                        </div>
                                    </div>
                                </td>
                                <td><code class="lt-code">DOC-2023-045</code></td>
                                <td>
                                    <div class="lt-area-cell">
                                        <span class="lt-area-dot" style="background:#0891b2"></span>
                                        Tecnología e Informática
                                    </div>
                                </td>
                                <td>
                                    <div class="lt-grados-chips">
                                        <span class="lt-chip">9°A</span>
                                        <span class="lt-chip">9°B</span>
                                        <span class="lt-chip">8°A</span>
                                        <span class="lt-chip-more">+2</span>
                                    </div>
                                </td>
                                <td><span class="lt-contrato catedra">Hora cátedra</span></td>
                                <td><span class="lt-status activo">Activo</span></td>
                                <td>
                                    <div class="lt-actions">
                                        <button class="lt-act-btn" title="Ver perfil"><i class="fas fa-eye"></i></button>
                                        <button class="lt-act-btn" title="Editar"><i class="fas fa-pen"></i></button>
                                        <button class="lt-act-btn red" title="Desactivar"><i class="fas fa-ban"></i></button>
                                    </div>
                                </td>
                            </tr>

                        </tbody>
                    </table>

                    <!-- Empty state -->
                    <div class="lt-empty" id="emptyDoc" style="display:none;">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <p>No se encontraron docentes con los filtros aplicados.</p>
                        <button class="lt-empty-reset" onclick="resetFiltrosDoc()">Limpiar filtros</button>
                    </div>
                </div>

                <!-- Paginación -->
                <div class="lt-pagination" id="paginDoc">
                    <div class="lt-page-info">
                        Mostrando <strong>1–6</strong> de <strong>87</strong> docentes
                    </div>
                    <div class="lt-page-btns">
                        <button class="lt-page-btn" disabled><i class="fas fa-chevron-left"></i></button>
                        <button class="lt-page-btn active">1</button>
                        <button class="lt-page-btn">2</button>
                        <button class="lt-page-btn">3</button>
                        <button class="lt-page-btn"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>

            </div><!-- /panel-docentes -->

        </div>
    </main>

    <!-- Toast container -->
    <div id="toastContainer"></div>

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/js/admin.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/js/listado.js"></script>
</body>

</html>