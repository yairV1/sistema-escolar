<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Registro de Docente — Colegio San Cristóbal</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/css/admin.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/css/registro_docente.css" />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>

<body>

    <?php include_once __DIR__ . '/../../layouts/administrativo/sidebar.php'; ?>

    <div class="admin-overlay" id="adminOverlay"></div>

    <?php include_once __DIR__ . '/../../layouts/administrativo/nav.php'; ?>

    <main class="admin-main" id="adminMain">
        <div class="admin-container">

            <!-- ══════════════════════════════════════════
               ENCABEZADO
               ══════════════════════════════════════════ -->
            <section class="page-header reveal">
                <div class="ph-left">
                    <div class="ph-back">
                        <a href="<?= BASE_URL ?>/RegistroDocentes" class="ph-back-btn">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <div class="ph-breadcrumb">
                            <span class="ph-root">Docentes</span>
                            <i class="fas fa-chevron-right"></i>
                            <span class="ph-current">Nuevo registro</span>
                        </div>
                    </div>
                    <h1 class="ph-title"><i class="fas fa-chalkboard-teacher"></i> Registro de Docente</h1>
                    <p class="ph-desc">Completa todos los pasos para registrar un nuevo docente en el sistema.</p>
                </div>
                <div class="ph-right">
                    <div class="ph-progress-info">
                        <span class="ph-step-label">Paso <span id="currentStepLabel">1</span> de 5</span>
                        <div class="ph-progress-bar">
                            <div class="ph-progress-fill" id="headerProgress" style="width: 20%"></div>
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
                        <span class="ws-name">Contacto</span>
                    </div>
                </div>

                <div class="ws-step" data-step="3">
                    <div class="ws-dot">
                        <i class="fas fa-graduation-cap"></i>
                        <span class="ws-check"><i class="fas fa-check"></i></span>
                    </div>
                    <div class="ws-label">
                        <span class="ws-num">Paso 3</span>
                        <span class="ws-name">Formación</span>
                    </div>
                </div>

                <div class="ws-step" data-step="4">
                    <div class="ws-dot">
                        <i class="fas fa-chalkboard"></i>
                        <span class="ws-check"><i class="fas fa-check"></i></span>
                    </div>
                    <div class="ws-label">
                        <span class="ws-num">Paso 4</span>
                        <span class="ws-name">Carga académica</span>
                    </div>
                </div>

                <div class="ws-step" data-step="5">
                    <div class="ws-dot">
                        <i class="fas fa-briefcase"></i>
                        <span class="ws-check"><i class="fas fa-check"></i></span>
                    </div>
                    <div class="ws-label">
                        <span class="ws-num">Paso 5</span>
                        <span class="ws-name">Vinculación</span>
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
                                <p class="wfph-desc">Información básica e identificación del docente.</p>
                            </div>
                        </div>

                        <div class="wf-grid">
                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="primerNombre">Primer nombre <span class="wf-req">*</span></label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-user wf-icon"></i>
                                    <input type="text" id="primerNombre" name="primerNombre" class="wf-input" placeholder="Ej. Ricardo" required />
                                </div>
                                <span class="wf-error" id="err-primerNombre"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="segundoNombre">Segundo nombre</label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-user wf-icon"></i>
                                    <input type="text" id="segundoNombre" name="segundoNombre" class="wf-input" placeholder="Opcional" />
                                </div>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="primerApellido">Primer apellido <span class="wf-req">*</span></label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-user wf-icon"></i>
                                    <input type="text" id="primerApellido" name="primerApellido" class="wf-input" placeholder="Ej. Méndez" required />
                                </div>
                                <span class="wf-error" id="err-primerApellido"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="segundoApellido">Segundo apellido</label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-user wf-icon"></i>
                                    <input type="text" id="segundoApellido" name="segundoApellido" class="wf-input" placeholder="Opcional" />
                                </div>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="tipoDoc">Tipo de documento <span class="wf-req">*</span></label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-id-card wf-icon"></i>
                                    <select id="tipoDoc" name="tipoDoc" class="wf-input wf-select" required>
                                        <option value="">Seleccionar…</option>
                                        <option value="CC">Cédula de ciudadanía (CC)</option>
                                        <option value="CE">Cédula de extranjería (CE)</option>
                                        <option value="PA">Pasaporte</option>
                                        <option value="PE">Permiso especial de permanencia</option>
                                    </select>
                                </div>
                                <span class="wf-error" id="err-tipoDoc"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="numDoc">Número de documento <span class="wf-req">*</span></label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-hashtag wf-icon"></i>
                                    <input type="text" id="numDoc" name="numDoc" class="wf-input" placeholder="Ej. 80123456" required />
                                </div>
                                <span class="wf-error" id="err-numDoc"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="fechaNac">Fecha de nacimiento <span class="wf-req">*</span></label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-calendar wf-icon"></i>
                                    <input type="date" id="fechaNac" name="fechaNac" class="wf-input" required />
                                </div>
                                <span class="wf-error" id="err-fechaNac"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="genero">Género <span class="wf-req">*</span></label>
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
                                <label class="wf-label" for="lugarNac">Lugar de nacimiento</label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-map-pin wf-icon"></i>
                                    <input type="text" id="lugarNac" name="lugarNac" class="wf-input" placeholder="Ej. Bogotá, Cundinamarca" />
                                </div>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="nacionalidad">Nacionalidad</label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-flag wf-icon"></i>
                                    <input type="text" id="nacionalidad" name="nacionalidad" class="wf-input" placeholder="Ej. Colombiana" value="Colombiana" />
                                </div>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="grupoSang">Grupo sanguíneo</label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-tint wf-icon"></i>
                                    <select id="grupoSang" name="grupoSang" class="wf-input wf-select">
                                        <option value="">No informa</option>
                                        <option>A+</option><option>A-</option>
                                        <option>B+</option><option>B-</option>
                                        <option>AB+</option><option>AB-</option>
                                        <option>O+</option><option>O-</option>
                                    </select>
                                </div>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="eps">EPS / Entidad de salud</label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-hospital wf-icon"></i>
                                    <input type="text" id="eps" name="eps" class="wf-input" placeholder="Ej. Sura, Nueva EPS…" />
                                </div>
                            </div>
                        </div>
                    </div><!-- /panel-1 -->

                    <!-- ─── PASO 2: Datos de contacto ─── -->
                    <div class="wf-panel" id="panel-2">
                        <div class="wf-panel-header">
                            <div class="wfph-icon blue"><i class="fas fa-map-marker-alt"></i></div>
                            <div>
                                <h2 class="wfph-title">Datos de contacto</h2>
                                <p class="wfph-desc">Dirección de residencia y medios de comunicación del docente.</p>
                            </div>
                        </div>

                        <div class="wf-grid">
                            <div class="wf-group wf-col-full">
                                <label class="wf-label" for="direccion">Dirección de residencia <span class="wf-req">*</span></label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-home wf-icon"></i>
                                    <input type="text" id="direccion" name="direccion" class="wf-input" placeholder="Ej. Cra. 7 #45-23, Barrio Chapinero" required />
                                </div>
                                <span class="wf-error" id="err-direccion"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="barrio">Barrio</label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-map wf-icon"></i>
                                    <input type="text" id="barrio" name="barrio" class="wf-input" placeholder="Ej. Chapinero Alto" />
                                </div>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="localidad">Localidad <span class="wf-req">*</span></label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-city wf-icon"></i>
                                    <select id="localidad" name="localidad" class="wf-input wf-select" required>
                                        <option value="">Seleccionar localidad…</option>
                                        <option>Usaquén</option><option>Chapinero</option>
                                        <option>Santa Fe</option><option>San Cristóbal</option>
                                        <option>Usme</option><option>Tunjuelito</option>
                                        <option>Bosa</option><option>Kennedy</option>
                                        <option>Fontibón</option><option>Engativá</option>
                                        <option>Suba</option><option>Barrios Unidos</option>
                                        <option>Teusaquillo</option><option>Los Mártires</option>
                                        <option>Antonio Nariño</option><option>Puente Aranda</option>
                                        <option>La Candelaria</option><option>Rafael Uribe Uribe</option>
                                        <option>Ciudad Bolívar</option><option>Sumapaz</option>
                                    </select>
                                </div>
                                <span class="wf-error" id="err-localidad"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="ciudad">Ciudad <span class="wf-req">*</span></label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-building wf-icon"></i>
                                    <input type="text" id="ciudad" name="ciudad" class="wf-input" value="Bogotá" required />
                                </div>
                                <span class="wf-error" id="err-ciudad"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="telPersonal">Teléfono personal <span class="wf-req">*</span></label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-phone wf-icon"></i>
                                    <input type="tel" id="telPersonal" name="telPersonal" class="wf-input" placeholder="Ej. 3001234567" required />
                                </div>
                                <span class="wf-error" id="err-telPersonal"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="telAlt">Teléfono alternativo</label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-phone-alt wf-icon"></i>
                                    <input type="tel" id="telAlt" name="telAlt" class="wf-input" placeholder="Ej. 6012345678 (fijo)" />
                                </div>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="emailPersonal">Correo personal <span class="wf-req">*</span></label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-envelope wf-icon"></i>
                                    <input type="email" id="emailPersonal" name="emailPersonal" class="wf-input" placeholder="Ej. r.mendez@correo.com" required />
                                </div>
                                <span class="wf-error" id="err-emailPersonal"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="emailInstitucional">Correo institucional</label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-envelope wf-icon"></i>
                                    <input type="email" id="emailInstitucional" name="emailInstitucional" class="wf-input" placeholder="Ej. r.mendez@sancristobal.edu.co" />
                                </div>
                            </div>

                            <!-- Contacto de emergencia -->
                            <div class="wf-group wf-col-full">
                                <div class="wf-section-divider">
                                    <i class="fas fa-exclamation-circle"></i> Contacto de emergencia
                                </div>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="emergNombre">Nombre completo <span class="wf-req">*</span></label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-user wf-icon"></i>
                                    <input type="text" id="emergNombre" name="emergNombre" class="wf-input" placeholder="Ej. Claudia Ramírez" required />
                                </div>
                                <span class="wf-error" id="err-emergNombre"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="emergParentesco">Parentesco <span class="wf-req">*</span></label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-sitemap wf-icon"></i>
                                    <select id="emergParentesco" name="emergParentesco" class="wf-input wf-select" required>
                                        <option value="">Seleccionar…</option>
                                        <option>Cónyuge / Pareja</option>
                                        <option>Madre</option><option>Padre</option>
                                        <option>Hijo/a</option><option>Hermano/a</option>
                                        <option>Amigo/a</option><option>Otro</option>
                                    </select>
                                </div>
                                <span class="wf-error" id="err-emergParentesco"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="emergTel">Teléfono de emergencia <span class="wf-req">*</span></label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-phone wf-icon"></i>
                                    <input type="tel" id="emergTel" name="emergTel" class="wf-input" placeholder="Ej. 3119876543" required />
                                </div>
                                <span class="wf-error" id="err-emergTel"></span>
                            </div>
                        </div>
                    </div><!-- /panel-2 -->

                    <!-- ─── PASO 3: Formación académica ─── -->
                    <div class="wf-panel" id="panel-3">
                        <div class="wf-panel-header">
                            <div class="wfph-icon gold"><i class="fas fa-graduation-cap"></i></div>
                            <div>
                                <h2 class="wfph-title">Formación académica</h2>
                                <p class="wfph-desc">Títulos, especialidades y experiencia docente previa.</p>
                            </div>
                        </div>

                        <div class="wf-grid">
                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="nivelEstudios">Nivel de estudios <span class="wf-req">*</span></label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-graduation-cap wf-icon"></i>
                                    <select id="nivelEstudios" name="nivelEstudios" class="wf-input wf-select" required>
                                        <option value="">Seleccionar…</option>
                                        <option value="normalista">Normalista Superior</option>
                                        <option value="licenciatura">Licenciatura</option>
                                        <option value="profesional">Profesional universitario</option>
                                        <option value="especializacion">Especialización</option>
                                        <option value="maestria">Maestría</option>
                                        <option value="doctorado">Doctorado</option>
                                        <option value="postdoctorado">Postdoctorado</option>
                                    </select>
                                </div>
                                <span class="wf-error" id="err-nivelEstudios"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="tituloObtenido">Título obtenido <span class="wf-req">*</span></label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-certificate wf-icon"></i>
                                    <input type="text" id="tituloObtenido" name="tituloObtenido" class="wf-input" placeholder="Ej. Licenciado en Matemáticas" required />
                                </div>
                                <span class="wf-error" id="err-tituloObtenido"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="institucionEgreso">Institución de egreso <span class="wf-req">*</span></label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-university wf-icon"></i>
                                    <input type="text" id="institucionEgreso" name="institucionEgreso" class="wf-input" placeholder="Ej. Universidad Nacional de Colombia" required />
                                </div>
                                <span class="wf-error" id="err-institucionEgreso"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="anioGrado">Año de graduación <span class="wf-req">*</span></label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-calendar-check wf-icon"></i>
                                    <input type="number" id="anioGrado" name="anioGrado" class="wf-input" placeholder="Ej. 2015" min="1970" max="2025" required />
                                </div>
                                <span class="wf-error" id="err-anioGrado"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="tarjetaProfesional">Tarjeta profesional / CONACES</label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-id-badge wf-icon"></i>
                                    <input type="text" id="tarjetaProfesional" name="tarjetaProfesional" class="wf-input" placeholder="Ej. TP-12345 (si aplica)" />
                                </div>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="registroSNP">Registro Secretaría de Educación</label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-file-alt wf-icon"></i>
                                    <input type="text" id="registroSNP" name="registroSNP" class="wf-input" placeholder="Ej. SED-2023-456 (si aplica)" />
                                </div>
                            </div>

                            <!-- Experiencia previa -->
                            <div class="wf-group wf-col-full">
                                <div class="wf-section-divider">
                                    <i class="fas fa-briefcase"></i> Experiencia docente previa
                                </div>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="aniosExperiencia">Años de experiencia <span class="wf-req">*</span></label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-history wf-icon"></i>
                                    <select id="aniosExperiencia" name="aniosExperiencia" class="wf-input wf-select" required>
                                        <option value="">Seleccionar…</option>
                                        <option value="0">Sin experiencia previa</option>
                                        <option value="1">Menos de 1 año</option>
                                        <option value="2">1 – 3 años</option>
                                        <option value="3">3 – 5 años</option>
                                        <option value="4">5 – 10 años</option>
                                        <option value="5">Más de 10 años</option>
                                    </select>
                                </div>
                                <span class="wf-error" id="err-aniosExperiencia"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="institucionAnterior">Institución anterior</label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-school wf-icon"></i>
                                    <input type="text" id="institucionAnterior" name="institucionAnterior" class="wf-input" placeholder="Ej. IED Simón Bolívar (si aplica)" />
                                </div>
                            </div>

                            <div class="wf-group wf-col-full">
                                <label class="wf-label" for="resumenExperiencia">Resumen de experiencia</label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-align-left wf-icon wf-icon-top"></i>
                                    <textarea id="resumenExperiencia" name="resumenExperiencia" class="wf-input wf-textarea" rows="3"
                                        placeholder="Describe brevemente tu trayectoria docente, logros o reconocimientos relevantes…"></textarea>
                                </div>
                            </div>

                            <!-- Documentos -->
                            <div class="wf-group wf-col-full">
                                <div class="wf-section-divider">
                                    <i class="fas fa-paperclip"></i> Documentos adjuntos
                                </div>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="docHojaVida">Hoja de vida (PDF)</label>
                                <div class="wf-file-wrap" id="dropHojaVida">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span class="wf-file-label">Arrastra o <strong>selecciona</strong> un archivo</span>
                                    <span class="wf-file-name" id="nameHojaVida">Sin archivo</span>
                                    <input type="file" id="docHojaVida" name="docHojaVida" accept=".pdf,.doc,.docx" class="wf-file-input" />
                                </div>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="docTitulos">Títulos académicos (PDF)</label>
                                <div class="wf-file-wrap" id="dropTitulos">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span class="wf-file-label">Arrastra o <strong>selecciona</strong> un archivo</span>
                                    <span class="wf-file-name" id="nameTitulos">Sin archivo</span>
                                    <input type="file" id="docTitulos" name="docTitulos" accept=".pdf" class="wf-file-input" />
                                </div>
                            </div>
                        </div>
                    </div><!-- /panel-3 -->

                    <!-- ─── PASO 4: Carga académica ─── -->
                    <div class="wf-panel" id="panel-4">
                        <div class="wf-panel-header">
                            <div class="wfph-icon teal"><i class="fas fa-chalkboard"></i></div>
                            <div>
                                <h2 class="wfph-title">Carga académica</h2>
                                <p class="wfph-desc">Materias, grados asignados y jornada de trabajo.</p>
                            </div>
                        </div>

                        <div class="wf-grid">
                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="areaEnsenanza">Área de enseñanza <span class="wf-req">*</span></label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-book wf-icon"></i>
                                    <select id="areaEnsenanza" name="areaEnsenanza" class="wf-input wf-select" required>
                                        <option value="">Seleccionar área…</option>
                                        <optgroup label="Ciencias">
                                            <option value="matematicas">Matemáticas</option>
                                            <option value="ciencias_nat">Ciencias Naturales</option>
                                            <option value="fisica">Física</option>
                                            <option value="quimica">Química</option>
                                            <option value="biologia">Biología</option>
                                        </optgroup>
                                        <optgroup label="Humanidades">
                                            <option value="español">Lengua Castellana (Español)</option>
                                            <option value="ingles">Inglés</option>
                                            <option value="frances">Francés</option>
                                            <option value="filosofia">Filosofía</option>
                                        </optgroup>
                                        <optgroup label="Sociales">
                                            <option value="historia">Historia</option>
                                            <option value="geografia">Geografía</option>
                                            <option value="sociales">Ciencias Sociales</option>
                                            <option value="religion">Educación Religiosa</option>
                                        </optgroup>
                                        <optgroup label="Otras áreas">
                                            <option value="ed_fisica">Educación Física</option>
                                            <option value="artes">Artes / Música</option>
                                            <option value="tecnologia">Tecnología e Informática</option>
                                            <option value="preescolar">Preescolar (todas las áreas)</option>
                                            <option value="primaria">Primaria (todas las áreas)</option>
                                        </optgroup>
                                    </select>
                                </div>
                                <span class="wf-error" id="err-areaEnsenanza"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="jornada">Jornada <span class="wf-req">*</span></label>
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

                            <!-- Grados asignados -->
                            <div class="wf-group wf-col-full">
                                <label class="wf-label">Grados asignados <span class="wf-req">*</span></label>
                                <div class="wf-checkbox-group" id="gradosGroup">
                                    <label class="wf-check-item"><input type="checkbox" name="grados" value="PRE" /><span class="wf-check-box"></span>Preescolar</label>
                                    <label class="wf-check-item"><input type="checkbox" name="grados" value="1" /><span class="wf-check-box"></span>1°</label>
                                    <label class="wf-check-item"><input type="checkbox" name="grados" value="2" /><span class="wf-check-box"></span>2°</label>
                                    <label class="wf-check-item"><input type="checkbox" name="grados" value="3" /><span class="wf-check-box"></span>3°</label>
                                    <label class="wf-check-item"><input type="checkbox" name="grados" value="4" /><span class="wf-check-box"></span>4°</label>
                                    <label class="wf-check-item"><input type="checkbox" name="grados" value="5" /><span class="wf-check-box"></span>5°</label>
                                    <label class="wf-check-item"><input type="checkbox" name="grados" value="6" /><span class="wf-check-box"></span>6°</label>
                                    <label class="wf-check-item"><input type="checkbox" name="grados" value="7" /><span class="wf-check-box"></span>7°</label>
                                    <label class="wf-check-item"><input type="checkbox" name="grados" value="8" /><span class="wf-check-box"></span>8°</label>
                                    <label class="wf-check-item"><input type="checkbox" name="grados" value="9" /><span class="wf-check-box"></span>9°</label>
                                    <label class="wf-check-item"><input type="checkbox" name="grados" value="10" /><span class="wf-check-box"></span>10°</label>
                                    <label class="wf-check-item"><input type="checkbox" name="grados" value="11" /><span class="wf-check-box"></span>11°</label>
                                </div>
                                <span class="wf-error" id="err-grados"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="horasSemana">Horas semanales <span class="wf-req">*</span></label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-hourglass-half wf-icon"></i>
                                    <input type="number" id="horasSemana" name="horasSemana" class="wf-input" placeholder="Ej. 24" min="1" max="40" required />
                                </div>
                                <span class="wf-error" id="err-horasSemana"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="directorGrupo">Director de grupo</label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-users wf-icon"></i>
                                    <select id="directorGrupo" name="directorGrupo" class="wf-input wf-select">
                                        <option value="">No asignado</option>
                                        <option>6°A</option><option>6°B</option><option>6°C</option>
                                        <option>7°A</option><option>7°B</option><option>7°C</option>
                                        <option>8°A</option><option>8°B</option>
                                        <option>9°A</option><option>9°B</option><option>9°C</option>
                                        <option>10°A</option><option>10°B</option>
                                        <option>11°A</option><option>11°B</option>
                                    </select>
                                </div>
                            </div>

                            <div class="wf-group wf-col-full">
                                <label class="wf-label" for="observacionesCarga">Observaciones de carga</label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-comment-alt wf-icon wf-icon-top"></i>
                                    <textarea id="observacionesCarga" name="observacionesCarga" class="wf-input wf-textarea" rows="2"
                                        placeholder="Anotaciones especiales sobre la asignación académica…"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="wf-info-box blue">
                            <i class="fas fa-info-circle"></i>
                            <p>Puedes modificar la asignación de grados y grupos más adelante desde el módulo de Gestión Docente.</p>
                        </div>
                    </div><!-- /panel-4 -->

                    <!-- ─── PASO 5: Vinculación laboral ─── -->
                    <div class="wf-panel" id="panel-5">
                        <div class="wf-panel-header">
                            <div class="wfph-icon orange"><i class="fas fa-briefcase"></i></div>
                            <div>
                                <h2 class="wfph-title">Vinculación laboral</h2>
                                <p class="wfph-desc">Tipo de contrato, cargo y datos administrativos.</p>
                            </div>
                        </div>

                        <div class="wf-grid">
                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="cargo">Cargo <span class="wf-req">*</span></label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-user-tie wf-icon"></i>
                                    <select id="cargo" name="cargo" class="wf-input wf-select" required>
                                        <option value="">Seleccionar…</option>
                                        <option value="docente">Docente</option>
                                        <option value="coord">Coordinador/a Académico/a</option>
                                        <option value="coord_conv">Coordinador/a de Convivencia</option>
                                        <option value="orientador">Orientador/a Escolar</option>
                                        <option value="directivo">Directivo Docente</option>
                                    </select>
                                </div>
                                <span class="wf-error" id="err-cargo"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="tipoContrato">Tipo de contrato <span class="wf-req">*</span></label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-file-contract wf-icon"></i>
                                    <select id="tipoContrato" name="tipoContrato" class="wf-input wf-select" required>
                                        <option value="">Seleccionar…</option>
                                        <option value="planta">Docente de planta (oficial)</option>
                                        <option value="provisional">Provisional</option>
                                        <option value="termino_fijo">Término fijo</option>
                                        <option value="termino_indef">Término indefinido</option>
                                        <option value="hora_catedra">Hora cátedra</option>
                                        <option value="prestacion">Prestación de servicios</option>
                                    </select>
                                </div>
                                <span class="wf-error" id="err-tipoContrato"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="fechaIngreso">Fecha de ingreso <span class="wf-req">*</span></label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-calendar-plus wf-icon"></i>
                                    <input type="date" id="fechaIngreso" name="fechaIngreso" class="wf-input" required />
                                </div>
                                <span class="wf-error" id="err-fechaIngreso"></span>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="fechaFinContrato">Fecha fin contrato</label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-calendar-times wf-icon"></i>
                                    <input type="date" id="fechaFinContrato" name="fechaFinContrato" class="wf-input" />
                                </div>
                                <small class="wf-hint">Dejar vacío si es indefinido o de planta</small>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="salario">Salario mensual (COP)</label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-dollar-sign wf-icon"></i>
                                    <input type="text" id="salario" name="salario" class="wf-input" placeholder="Ej. 3.500.000" />
                                </div>
                            </div>

                            <div class="wf-group wf-col-2">
                                <label class="wf-label" for="escalafon">Escalafón docente</label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-layer-group wf-icon"></i>
                                    <select id="escalafon" name="escalafon" class="wf-input wf-select">
                                        <option value="">No aplica / Sin clasificar</option>
                                        <option value="2277">Decreto 2277 (Antiguo)</option>
                                        <option value="1278_1A">Decreto 1278 — Grado 1A</option>
                                        <option value="1278_1B">Decreto 1278 — Grado 1B</option>
                                        <option value="1278_1C">Decreto 1278 — Grado 1C</option>
                                        <option value="1278_2A">Decreto 1278 — Grado 2A</option>
                                        <option value="1278_2B">Decreto 1278 — Grado 2B</option>
                                        <option value="1278_2C">Decreto 1278 — Grado 2C</option>
                                        <option value="1278_3A">Decreto 1278 — Grado 3A</option>
                                        <option value="1278_3B">Decreto 1278 — Grado 3B</option>
                                        <option value="1278_3C">Decreto 1278 — Grado 3C</option>
                                    </select>
                                </div>
                            </div>

                            <div class="wf-group wf-col-full">
                                <label class="wf-label" for="observacionesLab">Observaciones laborales</label>
                                <div class="wf-input-wrap">
                                    <i class="fas fa-comment-alt wf-icon wf-icon-top"></i>
                                    <textarea id="observacionesLab" name="observacionesLab" class="wf-input wf-textarea" rows="2"
                                        placeholder="Notas administrativas relevantes sobre la vinculación…"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Resumen final -->
                        <div class="wf-summary" id="wfSummary">
                            <div class="wf-summary-header">
                                <i class="fas fa-clipboard-check"></i>
                                <span>Resumen del registro</span>
                            </div>
                            <div class="wf-summary-grid" id="summaryContent"></div>
                        </div>

                        <div class="wf-info-box green">
                            <i class="fas fa-shield-alt"></i>
                            <p>Al guardar, se generará el código único del docente y se enviará una notificación al correo institucional registrado.</p>
                        </div>
                    </div><!-- /panel-5 -->

                    <!-- ─── Navegación ─── -->
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
                                <span class="wf-dot-ind" data-step="5"></span>
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

    <!-- Modal éxito -->
    <div class="modal-overlay" id="modalExito">
        <div class="modal-box modal-exito">
            <div class="me-anim">
                <div class="me-circle"><i class="fas fa-check"></i></div>
            </div>
            <h3>¡Docente registrado!</h3>
            <p>El docente ha sido vinculado correctamente al sistema del colegio.</p>
            <div class="me-code">
                Código asignado: <strong id="codigoGenerado">—</strong>
            </div>
            <div class="me-btns">
                <button class="me-btn-sec" id="btnNuevoRegistro">
                    <i class="fas fa-plus"></i> Nuevo registro
                </button>
                <a href="<?= BASE_URL ?>/RegistroDocentes" class="me-btn-pri">
                    <i class="fas fa-list"></i> Ver docentes
                </a>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/js/admin.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/js/registro_docente.js"></script>
</body>
</html>