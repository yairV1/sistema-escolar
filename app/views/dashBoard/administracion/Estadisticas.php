<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Estadísticas — Colegio San Cristóbal</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/css/admin.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/css/estadisticas.css" />
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
                    <div class="ph-breadcrumb-bar">
                        <span class="ph-root"><i class="fas fa-home"></i> Panel</span>
                        <i class="fas fa-chevron-right"></i>
                        <span class="ph-current">Estadísticas</span>
                    </div>
                    <h1 class="ph-title"><i class="fas fa-chart-bar"></i> Estadísticas Académicas</h1>
                    <p class="ph-desc">Análisis de rendimiento, asistencia y riesgo por grado y área.</p>
                </div>
                <div class="ph-actions">
                    <div class="est-filter-group">
                        <select class="est-filter-select" id="filtroAnio">
                            <option value="2025" selected>2025</option>
                            <option value="2024">2024</option>
                        </select>
                        <select class="est-filter-select" id="filtroPeriodo">
                            <option value="1" selected>Período 1</option>
                            <option value="2">Período 2</option>
                            <option value="3">Período 3</option>
                            <option value="4">Período 4</option>
                        </select>
                        <button class="est-btn-aplicar" id="btnAplicar">
                            <i class="fas fa-sync-alt"></i> Aplicar
                        </button>
                    </div>
                    <button class="ph-btn-export" id="btnExportPdf">
                        <i class="fas fa-file-pdf"></i> Exportar
                    </button>
                </div>
            </section>

            <!-- ══ KPIs ══ -->
            <section class="est-kpis reveal">
                <div class="ekpi-card green">
                    <div class="ekpi-icon"><i class="fas fa-chart-line"></i></div>
                    <div class="ekpi-body">
                        <strong class="ekpi-val" id="kpiPromInst">4.1</strong>
                        <span class="ekpi-label">Promedio institucional</span>
                        <span class="ekpi-trend up"><i class="fas fa-arrow-up"></i> +0.3 vs período anterior</span>
                    </div>
                </div>
                <div class="ekpi-card blue">
                    <div class="ekpi-icon"><i class="fas fa-user-check"></i></div>
                    <div class="ekpi-body">
                        <strong class="ekpi-val" id="kpiAsistencia">94%</strong>
                        <span class="ekpi-label">Asistencia promedio</span>
                        <span class="ekpi-trend up"><i class="fas fa-arrow-up"></i> +1.2% vs período anterior</span>
                    </div>
                </div>
                <div class="ekpi-card red">
                    <div class="ekpi-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="ekpi-body">
                        <strong class="ekpi-val" id="kpiRiesgo">8</strong>
                        <span class="ekpi-label">Estudiantes en riesgo</span>
                        <span class="ekpi-trend down"><i class="fas fa-arrow-down"></i> -2 vs período anterior</span>
                    </div>
                </div>
                <div class="ekpi-card gold">
                    <div class="ekpi-icon"><i class="fas fa-trophy"></i></div>
                    <div class="ekpi-body">
                        <strong class="ekpi-val" id="kpiDestacados">187</strong>
                        <span class="ekpi-label">Estudiantes destacados</span>
                        <span class="ekpi-trend up"><i class="fas fa-arrow-up"></i> +14 vs período anterior</span>
                    </div>
                </div>
            </section>

            <!-- ══ FILA PRINCIPAL: barras + dona ══ -->
            <div class="est-row-main reveal">

                <!-- Barras horizontales -->
                <div class="est-card">
                    <div class="est-card-header">
                        <div class="est-card-title">
                            <i class="fas fa-chart-bar"></i>
                            <span>Promedio académico por grado</span>
                        </div>
                        <select class="est-mini-select" id="selectBarras">
                            <option value="promedio">Promedio</option>
                            <option value="asistencia">Asistencia %</option>
                        </select>
                    </div>
                    <!-- Las barras se generan completamente por JS -->
                    <div id="barrasContainer"></div>
                    <div class="est-barras-legend">
                        <span class="ebl-item green"><span class="ebl-dot"></span>≥ 4.3 Excelente</span>
                        <span class="ebl-item lime"><span class="ebl-dot"></span>≥ 4.0 Bueno</span>
                        <span class="ebl-item gold"><span class="ebl-dot"></span>≥ 3.5 Aceptable</span>
                        <span class="ebl-item red"><span class="ebl-dot"></span>&lt; 3.5 En riesgo</span>
                    </div>
                </div>

                <!-- Dona rendimiento -->
                <div class="est-card est-dona-card">
                    <div class="est-card-header">
                        <div class="est-card-title">
                            <i class="fas fa-chart-pie"></i>
                            <span>Distribución por rendimiento</span>
                        </div>
                    </div>
                    <div class="est-dona-outer">
                        <div class="est-dona-wrap">
                            <canvas id="donaRendimiento" width="220" height="220"></canvas>
                            <div class="est-dona-center">
                                <strong id="donaCenterVal">1,240</strong>
                                <span>estudiantes</span>
                            </div>
                        </div>
                    </div>
                    <ul class="est-dona-legend" id="donaLegend"></ul>
                </div>

            </div>

            <!-- ══ FILA SECUNDARIA: áreas + 2 donas ══ -->
            <div class="est-row-sec reveal">

                <!-- Rendimiento por área -->
                <div class="est-card">
                    <div class="est-card-header">
                        <div class="est-card-title">
                            <i class="fas fa-book-open"></i>
                            <span>Rendimiento por área</span>
                        </div>
                        <select class="est-mini-select" id="selectGradoArea">
                            <option value="all">Todos los grados</option>
                            <option value="6">Grado 6°</option>
                            <option value="7">Grado 7°</option>
                            <option value="8">Grado 8°</option>
                            <option value="9">Grado 9°</option>
                            <option value="10">Grado 10°</option>
                            <option value="11">Grado 11°</option>
                        </select>
                    </div>
                    <div id="areasList"></div>
                </div>

                <!-- Dona riesgo -->
                <div class="est-card est-dona-card-sm">
                    <div class="est-card-header">
                        <div class="est-card-title">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Riesgo académico</span>
                        </div>
                    </div>
                    <div class="est-dona-outer sm">
                        <div class="est-dona-wrap sm">
                            <canvas id="donaRiesgo" width="180" height="180"></canvas>
                            <div class="est-dona-center sm">
                                <strong id="riesgoCenterVal">8</strong>
                                <span>en riesgo</span>
                            </div>
                        </div>
                    </div>
                    <ul class="est-dona-legend" id="riesgoLegend"></ul>
                </div>

                <!-- Dona género -->
                <div class="est-card est-dona-card-sm">
                    <div class="est-card-header">
                        <div class="est-card-title">
                            <i class="fas fa-venus-mars"></i>
                            <span>Por género</span>
                        </div>
                    </div>
                    <div class="est-dona-outer sm">
                        <div class="est-dona-wrap sm">
                            <canvas id="donaGenero" width="180" height="180"></canvas>
                            <div class="est-dona-center sm">
                                <strong id="generoCenterVal">1,240</strong>
                                <span>total</span>
                            </div>
                        </div>
                    </div>
                    <ul class="est-dona-legend" id="generoLegend"></ul>
                </div>

            </div>

            <!-- ══ TABLA DETALLE POR GRADO ══ -->
            <div class="est-card reveal">
                <div class="est-card-header">
                    <div class="est-card-title">
                        <i class="fas fa-table"></i>
                        <span>Detalle académico por grado</span>
                    </div>
                    <div class="est-card-actions">
                        <input type="text" class="est-search-inline" id="tablaSearch" placeholder="Buscar grado…" />
                        <button class="ph-btn-export" id="btnExportTabla">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                    </div>
                </div>
                <div class="est-tabla-wrap">
                    <table class="est-tabla">
                        <thead>
                            <tr>
                                <th>Grado</th>
                                <th>Estudiantes</th>
                                <th>Promedio</th>
                                <th>Asistencia</th>
                                <th>Aprobados</th>
                                <th>En riesgo</th>
                                <th>Mejor área</th>
                                <th>Área crítica</th>
                                <th>Tendencia</th>
                            </tr>
                        </thead>
                        <tbody id="tablaGradosTbody"></tbody>
                    </table>
                </div>
            </div>

            <!-- ══ ESTUDIANTES EN RIESGO ══ -->
            <div class="est-card reveal">
                <div class="est-card-header">
                    <div class="est-card-title">
                        <i class="fas fa-user-times"></i>
                        <span>Estudiantes en riesgo académico</span>
                    </div>
                    <span class="est-badge-red" id="riesgoBadge">8 estudiantes</span>
                </div>
                <div class="est-riesgo-grid" id="riesgoList"></div>
            </div>

        </div>
    </main>

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/js/admin.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/js/estadisticas.js"></script>
</body>
</html>