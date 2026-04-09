<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Mi Perfil — Colegio San Cristóbal</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/css/admin.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/css/perfil.css" />
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
           HEADER PERFIL
           ══════════════════════════════════════ -->
            <section class="perfil-hero reveal">
                <!-- Banner superior -->
                <div class="ph-banner">
                    <div class="ph-banner-overlay"></div>
                    <div class="ph-banner-shapes">
                        <div class="pbs pbs-1"></div>
                        <div class="pbs pbs-2"></div>
                        <div class="pbs pbs-3"></div>
                    </div>
                    <button class="ph-banner-edit" id="btnEditBanner" title="Cambiar imagen de portada">
                        <i class="fas fa-camera"></i> Cambiar portada
                    </button>
                    <input type="file" id="bannerInput" accept="image/*" class="hidden-input" />
                </div>

                <!-- Información principal -->
                <div class="ph-info">
                    <!-- Avatar -->
                    <div class="ph-avatar-wrap">
                        <div class="ph-avatar" id="phAvatar">
                            <span class="ph-avatar-initials" id="phInitials">RC</span>
                            <img id="phAvatarImg" src="" alt="" style="display:none;" />
                        </div>
                        <button class="ph-avatar-edit" id="btnEditAvatar" title="Cambiar foto">
                            <i class="fas fa-camera"></i>
                        </button>
                        <input type="file" id="avatarInput" accept="image/*" class="hidden-input" />
                    </div>

                    <!-- Datos -->
                    <div class="ph-datos">
                        <div class="ph-nombre-wrap">
                            <h1 class="ph-nombre" id="phNombre">Rosa Cardona Mejía</h1>
                            <span class="ph-badge rector">
                                <i class="fas fa-crown"></i> Rectora
                            </span>
                        </div>
                        <p class="ph-cargo" id="phCargo">Rectora · Colegio San Cristóbal · Bogotá D.C.</p>
                        <div class="ph-meta">
                            <span><i class="fas fa-envelope"></i> rcardona@sancristobal.edu.co</span>
                            <span><i class="fas fa-phone"></i> 310 000 0000</span>
                            <span><i class="fas fa-calendar-alt"></i> Vinculada desde 2018</span>
                        </div>
                    </div>

                    <!-- Acciones rápidas -->
                    <div class="ph-quick-actions">
                        <button class="pqa-btn" id="btnGuardarPerfil">
                            <i class="fas fa-save"></i> Guardar cambios
                        </button>
                    </div>
                </div>

                <!-- Stats rápidas -->
                <div class="ph-stats">
                    <div class="phs-item">
                        <strong>7</strong><span>Años en el cargo</span>
                    </div>
                    <div class="phs-sep"></div>
                    <div class="phs-item">
                        <strong>1,240</strong><span>Estudiantes</span>
                    </div>
                    <div class="phs-sep"></div>
                    <div class="phs-item">
                        <strong>87</strong><span>Docentes</span>
                    </div>
                    <div class="phs-sep"></div>
                    <div class="phs-item">
                        <strong>Activa</strong><span>Estado</span>
                    </div>
                </div>
            </section>

            <!-- ══════════════════════════════════════
           TABS
           ══════════════════════════════════════ -->
            <div class="perfil-tabs reveal">
                <div class="pt-bar">
                    <button class="pt-tab active" data-tab="perfil">
                        <i class="fas fa-user-circle"></i> Perfil
                    </button>
                    <button class="pt-tab" data-tab="seguridad">
                        <i class="fas fa-lock"></i> Seguridad
                    </button>
                    <button class="pt-tab" data-tab="notificaciones">
                        <i class="fas fa-bell"></i> Notificaciones
                    </button>
                    <div class="pt-slider" id="ptSlider"></div>
                </div>
            </div>

            <!-- ══════════════════════════════════════
           PANEL PERFIL
           ══════════════════════════════════════ -->
            <div class="perfil-panel active reveal" id="panel-perfil">
                <div class="perfil-grid">

                    <!-- Columna principal -->
                    <div class="perfil-main-col">

                        <!-- Datos personales -->
                        <div class="pf-card">
                            <div class="pf-card-header">
                                <div class="pfc-icon green"><i class="fas fa-user"></i></div>
                                <div>
                                    <h3>Datos personales</h3>
                                    <p>Información básica de tu cuenta</p>
                                </div>
                            </div>
                            <div class="pf-card-body">
                                <div class="pf-grid">
                                    <div class="pf-group pf-col-2">
                                        <label class="pf-label">Primer nombre <span class="pf-req">*</span></label>
                                        <div class="pf-input-wrap">
                                            <i class="fas fa-user pf-icon"></i>
                                            <input type="text" class="pf-input" id="pfPrimerNombre" value="Rosa" />
                                        </div>
                                    </div>
                                    <div class="pf-group pf-col-2">
                                        <label class="pf-label">Segundo nombre</label>
                                        <div class="pf-input-wrap">
                                            <i class="fas fa-user pf-icon"></i>
                                            <input type="text" class="pf-input" id="pfSegundoNombre" value="Elena" />
                                        </div>
                                    </div>
                                    <div class="pf-group pf-col-2">
                                        <label class="pf-label">Primer apellido <span class="pf-req">*</span></label>
                                        <div class="pf-input-wrap">
                                            <i class="fas fa-user pf-icon"></i>
                                            <input type="text" class="pf-input" id="pfPrimerApellido" value="Cardona" />
                                        </div>
                                    </div>
                                    <div class="pf-group pf-col-2">
                                        <label class="pf-label">Segundo apellido</label>
                                        <div class="pf-input-wrap">
                                            <i class="fas fa-user pf-icon"></i>
                                            <input type="text" class="pf-input" id="pfSegundoApellido" value="Mejía" />
                                        </div>
                                    </div>
                                    <div class="pf-group pf-col-2">
                                        <label class="pf-label">Tipo de documento</label>
                                        <div class="pf-input-wrap">
                                            <i class="fas fa-id-card pf-icon"></i>
                                            <select class="pf-input pf-select">
                                                <option selected>Cédula de ciudadanía (CC)</option>
                                                <option>Cédula de extranjería (CE)</option>
                                                <option>Pasaporte</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="pf-group pf-col-2">
                                        <label class="pf-label">Número de documento</label>
                                        <div class="pf-input-wrap">
                                            <i class="fas fa-hashtag pf-icon"></i>
                                            <input type="text" class="pf-input" value="52 890 123" />
                                        </div>
                                    </div>
                                    <div class="pf-group pf-col-2">
                                        <label class="pf-label">Fecha de nacimiento</label>
                                        <div class="pf-input-wrap">
                                            <i class="fas fa-calendar pf-icon"></i>
                                            <input type="date" class="pf-input" value="1978-03-15" />
                                        </div>
                                    </div>
                                    <div class="pf-group pf-col-2">
                                        <label class="pf-label">Género</label>
                                        <div class="pf-input-wrap">
                                            <i class="fas fa-venus-mars pf-icon"></i>
                                            <select class="pf-input pf-select">
                                                <option>Masculino</option>
                                                <option selected>Femenino</option>
                                                <option>Otro</option>
                                                <option>Prefiero no decir</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="pf-group pf-col-full">
                                        <label class="pf-label">Biografía / Presentación</label>
                                        <div class="pf-input-wrap">
                                            <i class="fas fa-align-left pf-icon pf-icon-top"></i>
                                            <textarea class="pf-input pf-textarea" rows="3" id="pfBio">Rectora del Colegio San Cristóbal desde 2018. Licenciada en Ciencias de la Educación con maestría en Gestión Educativa. Comprometida con la formación integral de la comunidad educativa.</textarea>
                                        </div>
                                        <span class="pf-char-hint" id="bioCharCount">193 / 300</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información de contacto -->
                        <div class="pf-card">
                            <div class="pf-card-header">
                                <div class="pfc-icon blue"><i class="fas fa-address-book"></i></div>
                                <div>
                                    <h3>Información de contacto</h3>
                                    <p>Teléfonos y correos electrónicos</p>
                                </div>
                            </div>
                            <div class="pf-card-body">
                                <div class="pf-grid">
                                    <div class="pf-group pf-col-2">
                                        <label class="pf-label">Correo personal</label>
                                        <div class="pf-input-wrap">
                                            <i class="fas fa-envelope pf-icon"></i>
                                            <input type="email" class="pf-input" value="rosa.cardona@gmail.com" />
                                        </div>
                                    </div>
                                    <div class="pf-group pf-col-2">
                                        <label class="pf-label">Correo institucional</label>
                                        <div class="pf-input-wrap pf-readonly-wrap">
                                            <i class="fas fa-envelope pf-icon"></i>
                                            <input type="email" class="pf-input pf-readonly" value="rcardona@sancristobal.edu.co" readonly />
                                            <span class="pf-readonly-badge"><i class="fas fa-lock"></i></span>
                                        </div>
                                        <small class="pf-hint">Solo puede cambiarlo el administrador del sistema.</small>
                                    </div>
                                    <div class="pf-group pf-col-2">
                                        <label class="pf-label">Teléfono personal</label>
                                        <div class="pf-input-wrap">
                                            <i class="fas fa-phone pf-icon"></i>
                                            <input type="tel" class="pf-input" value="310 000 0000" />
                                        </div>
                                    </div>
                                    <div class="pf-group pf-col-2">
                                        <label class="pf-label">Teléfono alternativo</label>
                                        <div class="pf-input-wrap">
                                            <i class="fas fa-phone-alt pf-icon"></i>
                                            <input type="tel" class="pf-input" value="" placeholder="Opcional" />
                                        </div>
                                    </div>
                                    <div class="pf-group pf-col-full">
                                        <label class="pf-label">Dirección de residencia</label>
                                        <div class="pf-input-wrap">
                                            <i class="fas fa-home pf-icon"></i>
                                            <input type="text" class="pf-input" value="Cra. 15 #72-45, Chapinero, Bogotá" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div><!-- /perfil-main-col -->

                    <!-- Columna lateral -->
                    <div class="perfil-side-col">

                        <!-- Foto de perfil -->
                        <div class="pf-card pf-card-foto">
                            <div class="pf-card-header">
                                <div class="pfc-icon purple"><i class="fas fa-camera"></i></div>
                                <div>
                                    <h3>Foto de perfil</h3>
                                    <p>Imagen que verá la comunidad</p>
                                </div>
                            </div>
                            <div class="pf-card-body pf-foto-body">
                                <div class="pf-foto-preview" id="pfFotoPreview">
                                    <span class="pf-foto-initials" id="pfFotoInitials">RC</span>
                                    <img id="pfFotoImg" src="" alt="" style="display:none;" />
                                </div>
                                <div class="pf-foto-actions">
                                    <button class="pf-foto-btn pri" id="btnFotoUpload">
                                        <i class="fas fa-upload"></i> Subir foto
                                    </button>
                                    <button class="pf-foto-btn sec" id="btnFotoRemove" style="display:none;">
                                        <i class="fas fa-trash"></i> Quitar
                                    </button>
                                </div>
                                <input type="file" id="fotoInput" accept="image/*" class="hidden-input" />
                                <p class="pf-foto-hint">
                                    <i class="fas fa-info-circle"></i>
                                    JPG, PNG o WebP. Máx. 2MB. Mínimo 200×200px.
                                </p>
                            </div>
                        </div>

                        <!-- Cargo e institución -->
                        <div class="pf-card">
                            <div class="pf-card-header">
                                <div class="pfc-icon gold"><i class="fas fa-building"></i></div>
                                <div>
                                    <h3>Cargo e institución</h3>
                                    <p>Datos laborales</p>
                                </div>
                            </div>
                            <div class="pf-card-body">
                                <div class="pf-group" style="margin-bottom:10px;">
                                    <label class="pf-label">Cargo actual</label>
                                    <div class="pf-input-wrap pf-readonly-wrap">
                                        <i class="fas fa-user-tie pf-icon"></i>
                                        <input type="text" class="pf-input pf-readonly" value="Rectora" readonly />
                                        <span class="pf-readonly-badge"><i class="fas fa-lock"></i></span>
                                    </div>
                                </div>
                                <div class="pf-group" style="margin-bottom:10px;">
                                    <label class="pf-label">Institución</label>
                                    <div class="pf-input-wrap">
                                        <i class="fas fa-school pf-icon"></i>
                                        <input type="text" class="pf-input" value="Colegio San Cristóbal" />
                                    </div>
                                </div>
                                <div class="pf-group" style="margin-bottom:10px;">
                                    <label class="pf-label">Fecha de vinculación</label>
                                    <div class="pf-input-wrap">
                                        <i class="fas fa-calendar-check pf-icon"></i>
                                        <input type="date" class="pf-input" value="2018-02-01" />
                                    </div>
                                </div>
                                <div class="pf-group" style="margin-bottom:10px;">
                                    <label class="pf-label">Tipo de contrato</label>
                                    <div class="pf-input-wrap">
                                        <i class="fas fa-file-contract pf-icon"></i>
                                        <select class="pf-input pf-select">
                                            <option selected>Docente de planta (oficial)</option>
                                            <option>Provisional</option>
                                            <option>Término fijo</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="pf-group" style="margin-bottom:10px;">
                                    <label class="pf-label">Escalafón docente</label>
                                    <div class="pf-input-wrap">
                                        <i class="fas fa-layer-group pf-icon"></i>
                                        <select class="pf-input pf-select">
                                            <option>Decreto 2277 (Antiguo)</option>
                                            <option selected>Decreto 1278 — Grado 3C</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="pf-group">
                                    <label class="pf-label">Sede / Localidad</label>
                                    <div class="pf-input-wrap">
                                        <i class="fas fa-map-marker-alt pf-icon"></i>
                                        <input type="text" class="pf-input" value="San Cristóbal — Bogotá D.C." />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Formación académica -->
                        <div class="pf-card">
                            <div class="pf-card-header">
                                <div class="pfc-icon teal"><i class="fas fa-graduation-cap"></i></div>
                                <div>
                                    <h3>Formación académica</h3>
                                    <p>Títulos y estudios</p>
                                </div>
                            </div>
                            <div class="pf-card-body">
                                <div class="pf-formacion-list" id="pfFormacionList">
                                    <div class="pf-formacion-item">
                                        <div class="pfi-icon"><i class="fas fa-university"></i></div>
                                        <div class="pfi-data">
                                            <p class="pfi-titulo">Maestría en Gestión Educativa</p>
                                            <p class="pfi-inst">Universidad de Los Andes · 2015</p>
                                        </div>
                                        <button class="pfi-del" title="Eliminar"><i class="fas fa-times"></i></button>
                                    </div>
                                    <div class="pf-formacion-item">
                                        <div class="pfi-icon"><i class="fas fa-graduation-cap"></i></div>
                                        <div class="pfi-data">
                                            <p class="pfi-titulo">Licenciatura en Ciencias de la Educación</p>
                                            <p class="pfi-inst">Universidad Pedagógica Nacional · 2001</p>
                                        </div>
                                        <button class="pfi-del" title="Eliminar"><i class="fas fa-times"></i></button>
                                    </div>
                                </div>
                                <button class="pf-add-btn" id="btnAddFormacion">
                                    <i class="fas fa-plus"></i> Agregar título
                                </button>
                            </div>
                        </div>

                    </div><!-- /perfil-side-col -->
                </div>
            </div><!-- /panel-perfil -->

            <!-- ══════════════════════════════════════
           PANEL SEGURIDAD
           ══════════════════════════════════════ -->
            <div class="perfil-panel reveal" id="panel-seguridad">
                <div class="seg-grid">

                    <!-- Cambiar contraseña -->
                    <div class="pf-card">
                        <div class="pf-card-header">
                            <div class="pfc-icon red"><i class="fas fa-key"></i></div>
                            <div>
                                <h3>Cambiar contraseña</h3>
                                <p>Usa una contraseña segura y única</p>
                            </div>
                        </div>
                        <div class="pf-card-body">
                            <div class="pf-group">
                                <label class="pf-label">Contraseña actual <span class="pf-req">*</span></label>
                                <div class="pf-input-wrap">
                                    <i class="fas fa-lock pf-icon"></i>
                                    <input type="password" class="pf-input" id="pwActual" placeholder="••••••••" />
                                    <button type="button" class="pf-toggle-pw" data-target="pwActual"><i class="fas fa-eye"></i></button>
                                </div>
                                <span class="pf-error" id="err-pwActual"></span>
                            </div>
                            <div class="pf-group">
                                <label class="pf-label">Nueva contraseña <span class="pf-req">*</span></label>
                                <div class="pf-input-wrap">
                                    <i class="fas fa-lock pf-icon"></i>
                                    <input type="password" class="pf-input" id="pwNueva" placeholder="Mínimo 8 caracteres" />
                                    <button type="button" class="pf-toggle-pw" data-target="pwNueva"><i class="fas fa-eye"></i></button>
                                </div>
                                <!-- Medidor de fortaleza -->
                                <div class="pw-strength-wrap" id="pwStrengthWrap" style="display:none;">
                                    <div class="pw-strength-bar">
                                        <div class="pw-strength-fill" id="pwStrengthFill"></div>
                                    </div>
                                    <span class="pw-strength-label" id="pwStrengthLabel">Débil</span>
                                </div>
                                <div class="pw-rules" id="pwRules">
                                    <span class="pw-rule" id="rule-len"><i class="fas fa-circle"></i> Mínimo 8 caracteres</span>
                                    <span class="pw-rule" id="rule-upper"><i class="fas fa-circle"></i> Una mayúscula</span>
                                    <span class="pw-rule" id="rule-num"><i class="fas fa-circle"></i> Un número</span>
                                    <span class="pw-rule" id="rule-special"><i class="fas fa-circle"></i> Un carácter especial</span>
                                </div>
                                <span class="pf-error" id="err-pwNueva"></span>
                            </div>
                            <div class="pf-group">
                                <label class="pf-label">Confirmar nueva contraseña <span class="pf-req">*</span></label>
                                <div class="pf-input-wrap">
                                    <i class="fas fa-lock pf-icon"></i>
                                    <input type="password" class="pf-input" id="pwConfirm" placeholder="Repite la nueva contraseña" />
                                    <button type="button" class="pf-toggle-pw" data-target="pwConfirm"><i class="fas fa-eye"></i></button>
                                </div>
                                <span class="pf-error" id="err-pwConfirm"></span>
                            </div>
                            <button class="pf-btn-primary" id="btnCambiarPw">
                                <i class="fas fa-save"></i> Actualizar contraseña
                            </button>
                        </div>
                    </div>

                    <!-- Sesiones activas -->
                    <div class="pf-card">
                        <div class="pf-card-header">
                            <div class="pfc-icon orange"><i class="fas fa-desktop"></i></div>
                            <div>
                                <h3>Sesiones activas</h3>
                                <p>Dispositivos donde has iniciado sesión</p>
                            </div>
                        </div>
                        <div class="pf-card-body">
                            <div class="sesion-list">
                                <div class="sesion-item current">
                                    <div class="si-icon"><i class="fas fa-laptop"></i></div>
                                    <div class="si-data">
                                        <p class="si-name">Este dispositivo <span class="si-current-badge">Actual</span></p>
                                        <p class="si-meta">Chrome 123 · Windows 11 · Bogotá, Colombia</p>
                                        <p class="si-time"><i class="fas fa-clock"></i> Ahora mismo</p>
                                    </div>
                                </div>
                                <div class="sesion-item">
                                    <div class="si-icon"><i class="fas fa-mobile-alt"></i></div>
                                    <div class="si-data">
                                        <p class="si-name">iPhone 15</p>
                                        <p class="si-meta">Safari · iOS 17 · Bogotá, Colombia</p>
                                        <p class="si-time"><i class="fas fa-clock"></i> Hace 2 horas</p>
                                    </div>
                                    <button class="si-close" title="Cerrar sesión"><i class="fas fa-times"></i></button>
                                </div>
                                <div class="sesion-item">
                                    <div class="si-icon"><i class="fas fa-tablet-alt"></i></div>
                                    <div class="si-data">
                                        <p class="si-name">iPad Pro</p>
                                        <p class="si-meta">Chrome · iPadOS 17 · Bogotá, Colombia</p>
                                        <p class="si-time"><i class="fas fa-clock"></i> Ayer, 3:40 pm</p>
                                    </div>
                                    <button class="si-close" title="Cerrar sesión"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                            <button class="pf-btn-danger" id="btnCerrarTodas">
                                <i class="fas fa-sign-out-alt"></i> Cerrar todas las demás sesiones
                            </button>
                        </div>
                    </div>

                    <!-- Autenticación en 2 pasos -->
                    <div class="pf-card seg-2fa">
                        <div class="pf-card-header">
                            <div class="pfc-icon green"><i class="fas fa-shield-alt"></i></div>
                            <div>
                                <h3>Autenticación en dos pasos</h3>
                                <p>Capa adicional de seguridad</p>
                            </div>
                            <label class="pf-toggle-big" style="margin-left:auto;">
                                <input type="checkbox" id="toggle2FA" />
                                <span class="ptb-track"><span class="ptb-thumb"></span></span>
                            </label>
                        </div>
                        <div class="pf-card-body">
                            <div class="twofa-info" id="twofaInfo">
                                <i class="fas fa-info-circle"></i>
                                <p>Con la autenticación en dos pasos, necesitarás ingresar un código de verificación enviado a tu correo cada vez que inicies sesión desde un dispositivo nuevo.</p>
                            </div>
                            <div class="twofa-active" id="twofaActive" style="display:none;">
                                <div class="tfa-status">
                                    <i class="fas fa-check-circle"></i>
                                    <span>2FA activa — Código enviado a rcardona@sancristobal.edu.co</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div><!-- /panel-seguridad -->

            <!-- ══════════════════════════════════════
           PANEL NOTIFICACIONES
           ══════════════════════════════════════ -->
            <div class="perfil-panel reveal" id="panel-notificaciones">
                <div class="noti-grid">

                    <!-- Notificaciones del sistema -->
                    <div class="pf-card">
                        <div class="pf-card-header">
                            <div class="pfc-icon purple"><i class="fas fa-bell"></i></div>
                            <div>
                                <h3>Notificaciones del sistema</h3>
                                <p>Elige qué quieres recibir</p>
                            </div>
                        </div>
                        <div class="pf-card-body">
                            <div class="noti-section-title">
                                <i class="fas fa-envelope"></i> Por correo electrónico
                            </div>
                            <div class="noti-list">
                                <div class="noti-item">
                                    <div class="ni-info">
                                        <p class="ni-title">Nuevas solicitudes de matrícula</p>
                                        <p class="ni-desc">Cuando llegue una nueva solicitud de inscripción</p>
                                    </div>
                                    <label class="ni-toggle">
                                        <input type="checkbox" checked />
                                        <span class="nit-track"><span class="nit-thumb"></span></span>
                                    </label>
                                </div>
                                <div class="noti-item">
                                    <div class="ni-info">
                                        <p class="ni-title">Observaciones disciplinarias</p>
                                        <p class="ni-desc">Observaciones graves que requieran atención del rector</p>
                                    </div>
                                    <label class="ni-toggle">
                                        <input type="checkbox" checked />
                                        <span class="nit-track"><span class="nit-thumb"></span></span>
                                    </label>
                                </div>
                                <div class="noti-item">
                                    <div class="ni-info">
                                        <p class="ni-title">Cambios en el sitio web</p>
                                        <p class="ni-desc">Cuando alguien edite y publique cambios en la landing</p>
                                    </div>
                                    <label class="ni-toggle">
                                        <input type="checkbox" checked />
                                        <span class="nit-track"><span class="nit-thumb"></span></span>
                                    </label>
                                </div>
                                <div class="noti-item">
                                    <div class="ni-info">
                                        <p class="ni-title">Reportes y boletines generados</p>
                                        <p class="ni-desc">Confirmación cuando se genere o envíe un boletín</p>
                                    </div>
                                    <label class="ni-toggle">
                                        <input type="checkbox" />
                                        <span class="nit-track"><span class="nit-thumb"></span></span>
                                    </label>
                                </div>
                                <div class="noti-item">
                                    <div class="ni-info">
                                        <p class="ni-title">Inicio de sesión desde nuevo dispositivo</p>
                                        <p class="ni-desc">Alerta de seguridad por acceso desde dispositivo no reconocido</p>
                                    </div>
                                    <label class="ni-toggle">
                                        <input type="checkbox" checked />
                                        <span class="nit-track"><span class="nit-thumb"></span></span>
                                    </label>
                                </div>
                            </div>

                            <div class="noti-section-title" style="margin-top:18px;">
                                <i class="fas fa-bell"></i> En el portal (push)
                            </div>
                            <div class="noti-list">
                                <div class="noti-item">
                                    <div class="ni-info">
                                        <p class="ni-title">Comunicados enviados</p>
                                        <p class="ni-desc">Confirmación de envío de comunicados a la comunidad</p>
                                    </div>
                                    <label class="ni-toggle">
                                        <input type="checkbox" checked />
                                        <span class="nit-track"><span class="nit-thumb"></span></span>
                                    </label>
                                </div>
                                <div class="noti-item">
                                    <div class="ni-info">
                                        <p class="ni-title">Actualizaciones del sistema</p>
                                        <p class="ni-desc">Avisos sobre mejoras y nuevas funcionalidades</p>
                                    </div>
                                    <label class="ni-toggle">
                                        <input type="checkbox" />
                                        <span class="nit-track"><span class="nit-thumb"></span></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Preferencias de horario -->
                    <div class="pf-card">
                        <div class="pf-card-header">
                            <div class="pfc-icon teal"><i class="fas fa-clock"></i></div>
                            <div>
                                <h3>Horario de notificaciones</h3>
                                <p>Cuándo recibir alertas</p>
                            </div>
                        </div>
                        <div class="pf-card-body">
                            <div class="pf-group">
                                <label class="pf-label">Zona horaria</label>
                                <div class="pf-input-wrap">
                                    <i class="fas fa-globe pf-icon"></i>
                                    <select class="pf-input pf-select">
                                        <option selected>América/Bogotá (UTC-5)</option>
                                        <option>América/Caracas (UTC-4)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="pf-group">
                                <label class="pf-label">No molestar</label>
                                <div class="noti-no-molestar">
                                    <label class="ni-toggle" style="margin-bottom:8px;">
                                        <input type="checkbox" id="toggleNoMolestar" />
                                        <span class="nit-track"><span class="nit-thumb"></span></span>
                                        <span style="font-size:0.82rem;color:var(--gris-texto);margin-left:8px;">Activar modo no molestar</span>
                                    </label>
                                    <div class="nm-horario" id="nmHorario" style="display:none;">
                                        <div class="pf-row">
                                            <div class="pf-group pf-col-2">
                                                <label class="pf-label">Desde</label>
                                                <input type="time" class="pf-input" value="21:00" />
                                            </div>
                                            <div class="pf-group pf-col-2">
                                                <label class="pf-label">Hasta</label>
                                                <input type="time" class="pf-input" value="06:00" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="pf-group">
                                <label class="pf-label">Frecuencia de resumen por correo</label>
                                <div class="pf-input-wrap">
                                    <i class="fas fa-calendar-alt pf-icon"></i>
                                    <select class="pf-input pf-select">
                                        <option>Tiempo real</option>
                                        <option selected>Diario (resumen a las 7am)</option>
                                        <option>Semanal (lunes 7am)</option>
                                        <option>Nunca</option>
                                    </select>
                                </div>
                            </div>
                            <button class="pf-btn-primary" style="margin-top:6px;" id="btnSaveNoti">
                                <i class="fas fa-save"></i> Guardar preferencias
                            </button>
                        </div>
                    </div>

                </div>
            </div><!-- /panel-notificaciones -->

        </div>
    </main>

    <!-- Modal agregar título académico -->
    <div class="modal-overlay" id="modalFormacion">
        <div class="mf-box">
            <div class="mf-header">
                <div class="mf-icon"><i class="fas fa-graduation-cap"></i></div>
                <div>
                    <h3>Agregar título académico</h3>
                    <p>Registra tu formación profesional</p>
                </div>
                <button class="mf-close" id="mfClose"><i class="fas fa-times"></i></button>
            </div>
            <div class="mf-body">
                <div class="pf-group">
                    <label class="pf-label">Título obtenido <span class="pf-req">*</span></label>
                    <div class="pf-input-wrap">
                        <i class="fas fa-certificate pf-icon"></i>
                        <input type="text" class="pf-input" id="mfTitulo" placeholder="Ej. Maestría en Gestión Educativa" />
                    </div>
                    <span class="pf-error" id="err-mfTitulo"></span>
                </div>
                <div class="pf-row">
                    <div class="pf-group pf-col-2">
                        <label class="pf-label">Institución <span class="pf-req">*</span></label>
                        <div class="pf-input-wrap">
                            <i class="fas fa-university pf-icon"></i>
                            <input type="text" class="pf-input" id="mfInst" placeholder="Nombre de la universidad" />
                        </div>
                    </div>
                    <div class="pf-group pf-col-2">
                        <label class="pf-label">Año de grado</label>
                        <div class="pf-input-wrap">
                            <i class="fas fa-calendar pf-icon"></i>
                            <input type="number" class="pf-input" id="mfAnio" placeholder="Ej. 2015" min="1970" max="2025" />
                        </div>
                    </div>
                </div>
                <div class="pf-group">
                    <label class="pf-label">Nivel</label>
                    <div class="pf-input-wrap">
                        <i class="fas fa-layer-group pf-icon"></i>
                        <select class="pf-input pf-select" id="mfNivel">
                            <option>Licenciatura</option>
                            <option>Especialización</option>
                            <option>Maestría</option>
                            <option>Doctorado</option>
                            <option>Postdoctorado</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="mf-footer">
                <button class="pf-btn-cancel" id="mfCancel">Cancelar</button>
                <button class="pf-btn-primary" id="mfConfirm"><i class="fas fa-plus"></i> Agregar</button>
            </div>
        </div>
    </div>

    <div id="toastContainer"></div>

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/js/admin.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/js/perfil.js"></script>
</body>

</html>