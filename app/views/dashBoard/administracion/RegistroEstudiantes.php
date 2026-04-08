<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Registro de Estudiante — Colegio San Cristóbal</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/css/admin.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/css/registro_estudiante.css" />
    
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>

<body>

    <?php include_once __DIR__ . '/../../layouts/administrativo/sidebar.php'; ?>

    <!-- Overlay móvil -->
    <div class="admin-overlay" id="adminOverlay"></div>

    <?php include_once __DIR__ . '/../../layouts/administrativo/nav.php'; ?>

    <!-- =====================================================
       CONTENIDO PRINCIPAL
       ===================================================== -->
    <main class="admin-main" id="adminMain">
        <div class="admin-container">

            <!-- ══════════════════════════════════════════
               ENCABEZADO DE PÁGINA
               ══════════════════════════════════════════ -->
            <section class="page-header reveal">
                <div class="ph-left">
                    <div class="ph-back">
                    </div>
                    <h1 class="ph-title"><i class="fas fa-user-plus"></i> Registro de Estudiante</h1>
                    <p class="ph-desc">Completa todos los pasos para registrar un nuevo estudiante en el sistema.</p>
                </div>
                <div class="ph-right">
                    <div class="ph-progress-info">
                        <span class="ph-step-label">Paso <span id="currentStepLabel">1</span> de 4</span>
                        <div class="ph-progress-bar">
                            <div class="ph-progress-fill" id="headerProgress" style="width: 25%"></div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ══════════════════════════════════════════
               WIZARD — INDICADOR DE PASOS
               ══════════════════════════════════════════ -->
            <div class="wizard-steps reveal">
                <div class="ws-track"></div>
                <div class="ws-fill" id="wsFill"></div>

                <div class="ws-step active" data-step="1">
                    <div class="ws-dot">
                        <i class="fas fa-user"></i>
                        <span class="ws-check"><i class="fas fa-check"></i></span>
                    </div>
                    <div class="ws-label">
                        <span class="ws-num">Paso 1</span>
                        <span class="ws-name">Datos personales</span>
                    </div>
                </div>

                <div class="ws-step" data-step="2">
                    <div class="ws-dot">
                        <i class="fas fa-map-marker-alt"></i>
                        <span class="ws-check"><i class="fas fa-check"></i></span>
                    </div>
                    <div class="ws-label">
                        <span class="ws-num">Paso 2</span>
                        <span class="ws-name">Datos de contacto</span>
                    </div>
                </div>

                <div class="ws-step" data-step="3">
                    <div class="ws-dot">
                        <i class="fas fa-graduation-cap"></i>
                        <span class="ws-check"><i class="fas fa-check"></i></span>
                    </div>
                    <div class="ws-label">
                        <span class="ws-num">Paso 3</span>
                        <span class="ws-name">Datos académicos</span>
                    </div>
                </div>

                <div class="ws-step" data-step="4">
                    <div class="ws-dot">
                        <i class="fas fa-users"></i>
                        <span class="ws-check"><i class="fas fa-check"></i></span>
                    </div>
                    <div class="ws-label">
                        <span class="ws-num">Paso 4</span>
                        <span class="ws-name">Acudiente</span>
                    </div>
                </div>
            </div>

            <!-- ══════════════════════════════════════════
               FORMULARIO WIZARD
               ══════════════════════════════════════════ -->
            <div class="wizard-body reveal">
                <form class="wizard-form" id="wizardForm" novalidate>

                    <!-- ─── PASO 1: Datos personales ─── -->
                    <div class="wf-panel active" id="panel-1">
                        <div class="wf-panel-header">
                            <div class="wfph-icon green"><i class="fas fa-user"></i></div>
                            <div>
                                <h2 class="wfph-title">Datos personales</h2>
                                <p class="wfph-desc">Información básica e identificación del estudiante.</p>
                            </div>
                        </div>

                        <div class="wf-grid">
                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="primerNombre">
                                    Primer nombre <span class="wf-req">*</span>
                                </label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-user wf-icon"></i>
                                    <input type="text" id="primerNombre" name="primerNombre"
                                        class="wf-input" placeholder="Ej. Juan" required />
                                </div>
                                <span class="wf-error" id="err-primerNombre"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="segundoNombre">
                                    Segundo nombre
                                </label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-user wf-icon"></i>
                                    <input type="text" id="segundoNombre" name="segundoNombre"
                                        class="wf-input" placeholder="Ej. Pablo (opcional)" />
                                </div>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="primerApellido">
                                    Primer apellido <span class="wf-req">*</span>
                                </label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-user wf-icon"></i>
                                    <input type="text" id="primerApellido" name="primerApellido"
                                        class="wf-input" placeholder="Ej. Suárez" required />
                                </div>
                                <span class="wf-error" id="err-primerApellido"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="segundoApellido">
                                    Segundo apellido
                                </label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-user wf-icon"></i>
                                    <input type="text" id="segundoApellido" name="segundoApellido"
                                        class="wf-input" placeholder="Ej. Gómez (opcional)" />
                                </div>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="tipoDoc">
                                    Tipo de documento <span class="wf-req">*</span>
                                </label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-id-card wf-icon"></i>
                                    <select id="tipoDoc" name="tipoDoc" class="wf-input wf-select" required>
                                        <option value="">Seleccionar…</option>
                                        <option value="TI">Tarjeta de identidad (TI)</option>
                                        <option value="RC">Registro civil (RC)</option>
                                        <option value="CC">Cédula de ciudadanía (CC)</option>
                                        <option value="PA">Pasaporte</option>
                                        <option value="PE">Permiso especial de permanencia</option>
                                    </select>
                                </div>
                                <span class="wf-error" id="err-tipoDoc"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="numDoc">
                                    Número de documento <span class="wf-req">*</span>
                                </label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-hashtag wf-icon"></i>
                                    <input type="text" id="numDoc" name="numDoc"
                                        class="wf-input" placeholder="Ej. 1234567890" required />
                                </div>
                                <span class="wf-error" id="err-numDoc"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="fechaNac">
                                    Fecha de nacimiento <span class="wf-req">*</span>
                                </label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-calendar wf-icon"></i>
                                    <input type="date" id="fechaNac" name="fechaNac"
                                        class="wf-input" required />
                                </div>
                                <span class="wf-error" id="err-fechaNac"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="genero">
                                    Género <span class="wf-req">*</span>
                                </label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-venus-mars wf-icon"></i>
                                    <select id="genero" name="genero" class="wf-input wf-select" required>
                                        <option value="">Seleccionar…</option>
                                        <option value="M">Masculino</option>
                                        <option value="F">Femenino</option>
                                        <option value="O">Otro</option>
                                        <option value="NR">Prefiero no decir</option>
                                    </select>
                                </div>
                                <span class="wf-error" id="err-genero"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="lugarNac">
                                    Lugar de nacimiento
                                </label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-map-pin wf-icon"></i>
                                    <input type="text" id="lugarNac" name="lugarNac"
                                        class="wf-input" placeholder="Ej. Bogotá, Cundinamarca" />
                                </div>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="nacionalidad">
                                    Nacionalidad
                                </label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-flag wf-icon"></i>
                                    <input type="text" id="nacionalidad" name="nacionalidad"
                                        class="wf-input" placeholder="Ej. Colombiana" value="Colombiana" />
                                </div>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="grupoSang">
                                    Grupo sanguíneo
                                </label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-tint wf-icon"></i>
                                    <select id="grupoSang" name="grupoSang" class="wf-input wf-select">
                                        <option value="">No sabe / No informa</option>
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                    </select>
                                </div>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="eps">
                                    EPS / Entidad de salud
                                </label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-hospital wf-icon"></i>
                                    <input type="text" id="eps" name="eps"
                                        class="wf-input" placeholder="Ej. Sura, Nueva EPS…" />
                                </div>
                            </div>
                        </div>

                        <!-- Alergias / condición especial -->
                        <div class="wf-group wf-full" style="margin-top: 4px;">
                            <label class="wf-label" for="condicion">
                                Condición médica o alergias relevantes
                            </label>
                            <div class="wf-input-wrap">
                                <i class="fas fa-notes-medical wf-icon wf-icon-top"></i>
                                <textarea id="condicion" name="condicion" class="wf-input wf-textarea"
                                    rows="3" placeholder="Describe alergias, discapacidades o condiciones que el colegio deba conocer. Deja en blanco si no aplica."></textarea>
                            </div>
                        </div>

                    </div><!-- /panel-1 -->

                    <!-- ─── PASO 2: Datos de contacto ─── -->
                    <div class="wf-panel" id="panel-2">
                        <div class="wf-panel-header">
                            <div class="wfph-icon blue"><i class="fas fa-map-marker-alt"></i></div>
                            <div>
                                <h2 class="wfph-title">Datos de contacto</h2>
                                <p class="wfph-desc">Dirección de residencia y medios de contacto del estudiante.</p>
                            </div>
                        </div>

                        <div class="wf-grid">
                            <div class="wf-group wf-col-full">
                                <label class="wf-label" for="direccion">
                                    Dirección de residencia <span class="wf-req">*</span>
                                </label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-home wf-icon"></i>
                                    <input type="text" id="direccion" name="direccion"
                                        class="wf-input" placeholder="Ej. Cra. 10 #45-23, Barrio Las Flores" required />
                                </div>
                                <span class="wf-error" id="err-direccion"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="barrio">
                                    Barrio
                                </label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-map wf-icon"></i>
                                    <input type="text" id="barrio" name="barrio"
                                        class="wf-input" placeholder="Ej. San Cristóbal Norte" />
                                </div>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="localidad">
                                    Localidad <span class="wf-req">*</span>
                                </label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-city wf-icon"></i>
                                    <select id="localidad" name="localidad" class="wf-input wf-select" required>
                                        <option value="">Seleccionar localidad…</option>
                                        <option>Usaquén</option>
                                        <option>Chapinero</option>
                                        <option>Santa Fe</option>
                                        <option>San Cristóbal</option>
                                        <option>Usme</option>
                                        <option>Tunjuelito</option>
                                        <option>Bosa</option>
                                        <option>Kennedy</option>
                                        <option>Fontibón</option>
                                        <option>Engativá</option>
                                        <option>Suba</option>
                                        <option>Barrios Unidos</option>
                                        <option>Teusaquillo</option>
                                        <option>Los Mártires</option>
                                        <option>Antonio Nariño</option>
                                        <option>Puente Aranda</option>
                                        <option>La Candelaria</option>
                                        <option>Rafael Uribe Uribe</option>
                                        <option>Ciudad Bolívar</option>
                                        <option>Sumapaz</option>
                                    </select>
                                </div>
                                <span class="wf-error" id="err-localidad"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="ciudad">
                                    Ciudad <span class="wf-req">*</span>
                                </label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-building wf-icon"></i>
                                    <input type="text" id="ciudad" name="ciudad"
                                        class="wf-input" placeholder="Ej. Bogotá" value="Bogotá" required />
                                </div>
                                <span class="wf-error" id="err-ciudad"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="estrato">
                                    Estrato socioeconómico
                                </label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-layer-group wf-icon"></i>
                                    <select id="estrato" name="estrato" class="wf-input wf-select">
                                        <option value="">No informa</option>
                                        <option value="1">Estrato 1</option>
                                        <option value="2">Estrato 2</option>
                                        <option value="3">Estrato 3</option>
                                        <option value="4">Estrato 4</option>
                                        <option value="5">Estrato 5</option>
                                        <option value="6">Estrato 6</option>
                                    </select>
                                </div>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="telEstudiante">
                                    Teléfono del estudiante
                                </label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-phone wf-icon"></i>
                                    <input type="tel" id="telEstudiante" name="telEstudiante"
                                        class="wf-input" placeholder="Ej. 3001234567" />
                                </div>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="emailEstudiante">
                                    Correo electrónico del estudiante
                                </label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-envelope wf-icon"></i>
                                    <input type="email" id="emailEstudiante" name="emailEstudiante"
                                        class="wf-input" placeholder="Ej. juan@correo.com" />
                                </div>
                                <span class="wf-error" id="err-emailEstudiante"></span>
                            </div>
                        </div>

                        <!-- Info adicional -->
                        <div class="wf-info-box blue">
                            <i class="fas fa-info-circle"></i>
                            <p>El correo del estudiante se usará para enviar notificaciones académicas y acceso al portal estudiantil.</p>
                        </div>

                    </div><!-- /panel-2 -->

                    <!-- ─── PASO 3: Datos académicos ─── -->
                    <div class="wf-panel" id="panel-3">
                        <div class="wf-panel-header">
                            <div class="wfph-icon gold"><i class="fas fa-graduation-cap"></i></div>
                            <div>
                                <h2 class="wfph-title">Datos académicos</h2>
                                <p class="wfph-desc">Grado, jornada y tipo de vinculación al colegio.</p>
                            </div>
                        </div>

                        <div class="wf-grid">
                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="tipoMatricula">
                                    Tipo de matrícula <span class="wf-req">*</span>
                                </label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-file-signature wf-icon"></i>
                                    <select id="tipoMatricula" name="tipoMatricula" class="wf-input wf-select" required>
                                        <option value="">Seleccionar…</option>
                                        <option value="nueva">Nueva matrícula</option>
                                        <option value="reingreso">Reingreso</option>
                                        <option value="traslado">Traslado externo</option>
                                        <option value="interno">Traslado interno (entre sedes)</option>
                                    </select>
                                </div>
                                <span class="wf-error" id="err-tipoMatricula"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="anioLectivo">
                                    Año lectivo <span class="wf-req">*</span>
                                </label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-calendar-alt wf-icon"></i>
                                    <select id="anioLectivo" name="anioLectivo" class="wf-input wf-select" required>
                                        <option value="2025" selected>2025</option>
                                        <option value="2026">2026</option>
                                    </select>
                                </div>
                                <span class="wf-error" id="err-anioLectivo"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="grado">
                                    Grado al que ingresa <span class="wf-req">*</span>
                                </label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-school wf-icon"></i>
                                    <select id="grado" name="grado" class="wf-input wf-select" required>
                                        <option value="">Seleccionar grado…</option>
                                        <optgroup label="Preescolar">
                                            <option value="PRE">Preescolar (Transición)</option>
                                        </optgroup>
                                        <optgroup label="Primaria">
                                            <option value="1">Primero (1°)</option>
                                            <option value="2">Segundo (2°)</option>
                                            <option value="3">Tercero (3°)</option>
                                            <option value="4">Cuarto (4°)</option>
                                            <option value="5">Quinto (5°)</option>
                                        </optgroup>
                                        <optgroup label="Bachillerato">
                                            <option value="6">Sexto (6°)</option>
                                            <option value="7">Séptimo (7°)</option>
                                            <option value="8">Octavo (8°)</option>
                                            <option value="9">Noveno (9°)</option>
                                            <option value="10">Décimo (10°)</option>
                                            <option value="11">Once (11°)</option>
                                        </optgroup>
                                    </select>
                                </div>
                                <span class="wf-error" id="err-grado"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="grupo">
                                    Grupo / Salón
                                </label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-door-open wf-icon"></i>
                                    <select id="grupo" name="grupo" class="wf-input wf-select">
                                        <option value="">Asignar después…</option>
                                        <option value="A">Grupo A</option>
                                        <option value="B">Grupo B</option>
                                        <option value="C">Grupo C</option>
                                        <option value="D">Grupo D</option>
                                    </select>
                                </div>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="jornada">
                                    Jornada <span class="wf-req">*</span>
                                </label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-clock wf-icon"></i>
                                    <select id="jornada" name="jornada" class="wf-input wf-select" required>
                                        <option value="">Seleccionar jornada…</option>
                                        <option value="manana">Mañana (6:30 a.m. – 12:30 p.m.)</option>
                                        <option value="tarde">Tarde (12:30 p.m. – 6:00 p.m.)</option>
                                        <option value="completa">Jornada completa</option>
                                        <option value="noche">Nocturna</option>
                                    </select>
                                </div>
                                <span class="wf-error" id="err-jornada"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="colegioAnterior">
                                    Colegio de procedencia
                                </label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-university wf-icon"></i>
                                    <input type="text" id="colegioAnterior" name="colegioAnterior"
                                        class="wf-input" placeholder="Ej. IED Simón Bolívar (si aplica)" />
                                </div>
                            </div>

                            <div class="wf-group wf-col-full">
                                <label class="wf-label" for="necesidadesEd">
                                    Necesidades educativas especiales (NEE)
                                </label>
                                <div class="wf-radio-group">
                                    <label class="wf-radio-item">
                                        <input type="radio" name="nee" value="no" checked />
                                        <span class="wf-radio-box"></span>
                                        No presenta NEE
                                    </label>
                                    <label class="wf-radio-item">
                                        <input type="radio" name="nee" value="si" />
                                        <span class="wf-radio-box"></span>
                                        Sí presenta NEE
                                    </label>
                                </div>
                            </div>

                            <!-- Campo condicional NEE -->
                            <div class="wf-group wf-col-full" id="neeDetalle" style="display:none;">
                                <label class="wf-label" for="neeDesc">
                                    Descripción de la NEE <span class="wf-req">*</span>
                                </label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-notes-medical wf-icon wf-icon-top"></i>
                                    <textarea id="neeDesc" name="neeDesc" class="wf-input wf-textarea"
                                        rows="3" placeholder="Describe el tipo de necesidad educativa especial y los apoyos requeridos…"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="wf-info-box green">
                            <i class="fas fa-info-circle"></i>
                            <p>El código único del estudiante será generado automáticamente al completar el registro.</p>
                        </div>

                    </div><!-- /panel-3 -->

                    <!-- ─── PASO 4: Datos del acudiente ─── -->
                    <div class="wf-panel" id="panel-4">
                        <div class="wf-panel-header">
                            <div class="wfph-icon teal"><i class="fas fa-users"></i></div>
                            <div>
                                <h2 class="wfph-title">Datos del acudiente</h2>
                                <p class="wfph-desc">Información del padre, madre o acudiente responsable del estudiante.</p>
                            </div>
                        </div>

                        <div class="wf-grid">
                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="acuNombres">
                                    Nombres completos <span class="wf-req">*</span>
                                </label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-user wf-icon"></i>
                                    <input type="text" id="acuNombres" name="acuNombres"
                                        class="wf-input" placeholder="Ej. María Fernanda Gómez" required />
                                </div>
                                <span class="wf-error" id="err-acuNombres"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="acuParentesco">
                                    Parentesco <span class="wf-req">*</span>
                                </label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-sitemap wf-icon"></i>
                                    <select id="acuParentesco" name="acuParentesco" class="wf-input wf-select" required>
                                        <option value="">Seleccionar…</option>
                                        <option value="madre">Madre</option>
                                        <option value="padre">Padre</option>
                                        <option value="abuelo">Abuelo/a</option>
                                        <option value="tio">Tío/a</option>
                                        <option value="hermano">Hermano/a mayor</option>
                                        <option value="acudiente">Acudiente legal</option>
                                        <option value="otro">Otro</option>
                                    </select>
                                </div>
                                <span class="wf-error" id="err-acuParentesco"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="acuTipoDoc">
                                    Tipo de documento <span class="wf-req">*</span>
                                </label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-id-card wf-icon"></i>
                                    <select id="acuTipoDoc" name="acuTipoDoc" class="wf-input wf-select" required>
                                        <option value="">Seleccionar…</option>
                                        <option value="CC">Cédula de ciudadanía (CC)</option>
                                        <option value="CE">Cédula de extranjería (CE)</option>
                                        <option value="PA">Pasaporte</option>
                                        <option value="PE">Permiso especial de permanencia</option>
                                    </select>
                                </div>
                                <span class="wf-error" id="err-acuTipoDoc"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="acuNumDoc">
                                    Número de documento <span class="wf-req">*</span>
                                </label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-hashtag wf-icon"></i>
                                    <input type="text" id="acuNumDoc" name="acuNumDoc"
                                        class="wf-input" placeholder="Ej. 52123456" required />
                                </div>
                                <span class="wf-error" id="err-acuNumDoc"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="acuTel">
                                    Teléfono principal <span class="wf-req">*</span>
                                </label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-phone wf-icon"></i>
                                    <input type="tel" id="acuTel" name="acuTel"
                                        class="wf-input" placeholder="Ej. 3109876543" required />
                                </div>
                                <span class="wf-error" id="err-acuTel"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="acuTel2">
                                    Teléfono alternativo
                                </label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-phone-alt wf-icon"></i>
                                    <input type="tel" id="acuTel2" name="acuTel2"
                                        class="wf-input" placeholder="Ej. 6012345678 (fijo)" />
                                </div>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="acuEmail">
                                    Correo electrónico <span class="wf-req">*</span>
                                </label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-envelope wf-icon"></i>
                                    <input type="email" id="acuEmail" name="acuEmail"
                                        class="wf-input" placeholder="Ej. maria@correo.com" required />
                                </div>
                                <span class="wf-error" id="err-acuEmail"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="acuOcupacion">
                                    Ocupación
                                </label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-briefcase wf-icon"></i>
                                    <input type="text" id="acuOcupacion" name="acuOcupacion"
                                        class="wf-input" placeholder="Ej. Docente, Independiente…" />
                                </div>
                            </div>

                            <div class="wf-group wf-col-full">
                                <label class="wf-label" for="acuDireccion">
                                    Dirección (si difiere a la del estudiante)
                                </label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-home wf-icon"></i>
                                    <input type="text" id="acuDireccion" name="acuDireccion"
                                        class="wf-input" placeholder="Dejar en blanco si es la misma del estudiante" />
                                </div>
                            </div>
                        </div>

                        <!-- Resumen antes de enviar -->
                        <div class="wf-summary" id="wfSummary">
                            <div class="wf-summary-header">
                                <i class="fas fa-clipboard-check"></i>
                                <span>Resumen del registro</span>
                            </div>
                            <div class="wf-summary-grid" id="summaryContent">
                                <!-- Generado por JS -->
                            </div>
                        </div>

                        <div class="wf-info-box green">
                            <i class="fas fa-shield-alt"></i>
                            <p>Al guardar el registro, el acudiente recibirá un correo con la confirmación y el código asignado al estudiante.</p>
                        </div>

                    </div><!-- /panel-4 -->

                    <!-- ─── Navegación del wizard ─── -->
                    <div class="wf-nav">
                        <button type="button" class="wf-btn-prev" id="btnPrev">
                            <i class="fas fa-arrow-left"></i> Anterior
                        </button>
                        <div class="wf-nav-center">
                            <span class="wf-dots">
                                <span class="wf-dot-ind active" data-step="1"></span>
                                <span class="wf-dot-ind" data-step="2"></span>
                                <span class="wf-dot-ind" data-step="3"></span>
                                <span class="wf-dot-ind" data-step="4"></span>
                            </span>
                        </div>
                        <button type="button" class="wf-btn-next" id="btnNext">
                            Siguiente <i class="fas fa-arrow-right"></i>
                        </button>
                        <button type="submit" class="wf-btn-submit" id="btnSubmit" style="display:none;">
                            <i class="fas fa-save"></i> Guardar registro
                        </button>
                    </div>

                </form>
            </div><!-- /wizard-body -->

        </div>
    </main>

    <!-- =====================================================
       MODAL ÉXITO
       ===================================================== -->
    <div class="modal-overlay" id="modalExito">
        <div class="modal-box modal-exito">
            <div class="me-anim">
                <div class="me-circle">
                    <i class="fas fa-check"></i>
                </div>
            </div>
            <h3>¡Registro exitoso!</h3>
            <p>El estudiante ha sido registrado correctamente en el sistema.</p>
            <div class="me-code">
                Código asignado: <strong id="codigoGenerado">—</strong>
            </div>
            <div class="me-btns">
                <button class="me-btn-sec" id="btnNuevoRegistro">
                    <i class="fas fa-plus"></i> Nuevo registro
                </button>
                <a href="#estudiantes" class="me-btn-pri">
                    <i class="fas fa-list"></i> Ver estudiantes
                </a>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/js/admin.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/js/registro_estudiante.js"></script>
</body>

</html>