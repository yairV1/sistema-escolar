<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Ingresar — Colegio San Cristóbal</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/login.css" />
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/login-alerts.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
  <script>
    // Fija el tema antes del primer paint para evitar el parpadeo.
    // Misma clave (cs-theme) que usa el sistema Laravel: si vienes de ahí,
    // se respeta la preferencia que ya elegiste.
    (function () {
      var stored = localStorage.getItem('cs-theme');
      var theme = stored || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
      document.documentElement.setAttribute('data-theme', theme);
    })();
  </script>
</head>
<body>

<div class="stage">

  <!-- ===== PANEL VISUAL — branding ===== -->
  <div class="visual">
    <div class="glow"></div>

    <div class="visual-top" data-anim data-anim-type="fade">
      <div class="brand-mark">
        <div class="logo-chip">
          <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" aria-hidden="true">
            <path d="M12 3 2 8l10 5 10-5-10-5Z"/>
            <path d="M6 10.5V16c0 1 2.7 2.5 6 2.5s6-1.5 6-2.5v-5.5"/>
          </svg>
        </div>
        <div class="brand-text">San Cristóbal<span>Portal académico</span></div>
      </div>
      <a href="<?= BASE_URL ?>" class="back-pill">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        Volver
      </a>
    </div>

    <div class="visual-mid">
      <div class="eyebrow" data-anim data-anim-type="fade">Sistema de gestión escolar</div>
      <h1 data-anim data-anim-type="slide">Todo tu colegio,<br><em>en un solo lugar</em></h1>
      <p class="lede" data-anim data-anim-type="slide">Notas, asistencia, comunicados y horarios — accede con tu cuenta institucional para continuar.</p>

      <div class="stat-row" data-anim data-anim-type="fade">
        <div class="stat"><b>1,240+</b><span>Estudiantes activos</span></div>
        <div class="stat"><b>98%</b><span>Asistencia digital</span></div>
        <div class="stat"><b>24/7</b><span>Acceso al portal</span></div>
      </div>
    </div>

    <svg class="illustration" viewBox="0 0 300 260" fill="none" aria-hidden="true" data-anim data-anim-type="fade">
      <circle cx="150" cy="130" r="110" fill="white" opacity="0.04"/>
      <rect x="70" y="90" width="160" height="115" rx="10" fill="white" opacity="0.08"/>
      <rect x="70" y="90" width="160" height="30" rx="10" fill="white" opacity="0.12"/>
      <circle cx="90" cy="105" r="5" fill="#a8e6c0" opacity="0.7"/>
      <rect x="90" y="135" width="90" height="7" rx="3.5" fill="white" opacity="0.18"/>
      <rect x="90" y="152" width="120" height="7" rx="3.5" fill="white" opacity="0.14"/>
      <rect x="90" y="169" width="70" height="7" rx="3.5" fill="white" opacity="0.14"/>
      <path d="M150 20 100 45l50 25 50-25-50-25Z" fill="white" opacity="0.1"/>
      <path d="M115 52v22c0 6 15 14 35 14s35-8 35-14V52" stroke="white" stroke-opacity="0.16" stroke-width="2"/>
    </svg>

    <div class="visual-foot">© 2025 Colegio San Cristóbal — Bogotá, Colombia</div>
  </div>

  <!-- ===== PANEL FORMULARIO ===== -->
  <div class="panel">
    <button type="button" class="theme-toggle" id="themeToggle" aria-label="Cambiar tema">
      <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/></svg>
      <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
    </button>
    <div class="card" data-anim data-anim-type="zoom">

      <div class="mobile-brand">
        <div class="logo-chip">
          <svg viewBox="0 0 24 24" fill="white" aria-hidden="true"><path d="M12 3 2 8l10 5 10-5-10-5Z"/></svg>
        </div>
        San Cristóbal
      </div>

      <div class="card-head">
        <h2>Bienvenido de nuevo</h2>
        <p>Ingresa tus credenciales para acceder al portal</p>
      </div>

      <form class="login-form" id="loginForm" novalidate>

        <div class="field">
          <svg class="ic-lead" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 9h18M8 4v5"/>
          </svg>
          <input
            type="text"
            id="usuario"
            placeholder=" "
            autocomplete="username"
            aria-describedby="usuarioError"
            required
          />
          <label for="usuario">Correo institucional</label>
          <div class="field-msg" id="usuarioError" role="alert"></div>
        </div>

        <div class="field">
          <svg class="ic-lead" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>
          </svg>
          <input
            type="password"
            id="password"
            placeholder=" "
            autocomplete="current-password"
            aria-describedby="passwordError"
            required
          />
          <label for="password">Contraseña</label>
          <button type="button" class="toggle-vis" id="togglePw" aria-label="Mostrar contraseña">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
              <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/>
            </svg>
          </button>
          <div class="field-msg" id="passwordError" role="alert"></div>
        </div>

        <div class="row-between">
          <label class="remember">
            <input type="checkbox" id="remember" />
            <span class="box">
              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 12l6 6L20 6"/></svg>
            </span>
            Recordarme
          </label>
          <a href="#" class="forgot" id="forgotLink">¿Olvidaste tu contraseña?</a>
        </div>

        <button type="submit" class="btn-primary" id="btnLogin">
          <span class="btn-label">Ingresar al portal</span>
          <svg class="btn-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M5 12h14M12 5l7 7-7 7"/>
          </svg>
          <span class="spinner" aria-hidden="true"></span>
        </button>

      </form>

      <div class="divider">¿Eres nuevo?</div>

      <a href="index.html#registro" class="btn-ghost">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
          <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/>
        </svg>
        Solicitar inscripción
      </a>

      <p class="help-line">¿Problemas para ingresar? <a href="index.html#contacto">Contactar soporte</a></p>

      <div style="text-align:center;">
        <span class="badge-note">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>
          </svg>
          Conexión segura
        </span>
      </div>

    </div>
  </div>

</div>

<!-- ===== MODAL — recuperar contraseña ===== -->
<div class="modal-overlay" id="modalOverlay">
  <div class="modal" id="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <button class="modal-close" id="modalClose" aria-label="Cerrar">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
    </button>
    <div class="modal-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
        <circle cx="7" cy="15" r="4"/><path d="M9.5 12.5 18 4l2 2-2 2 2 2-3 3"/>
      </svg>
    </div>
    <h3 id="modalTitle">Recuperar contraseña</h3>
    <p>Ingresa tu correo institucional y te enviaremos instrucciones para restablecer tu contraseña.</p>

    <div class="field">
      <svg class="ic-lead" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
        <rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>
      </svg>
      <input type="email" id="recoveryEmail" placeholder=" " aria-describedby="modalError" />
      <label for="recoveryEmail">Correo institucional</label>
    </div>

    <button class="btn-primary" id="btnRecovery" style="margin-top:16px;">
      <span class="btn-label">Enviar instrucciones</span>
      <svg class="btn-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4 20-7Z"/></svg>
      <span class="spinner" aria-hidden="true"></span>
    </button>

    <div class="modal-success" id="modalSuccess" role="status">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
      <span id="modalSuccessText">¡Correo enviado! Revisa tu bandeja de entrada.</span>
    </div>

    <div class="alert-box error" id="modalError" role="alert" style="display:none;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v5M12 16h.01"/></svg>
      <span id="modalErrorText"></span>
    </div>
  </div>
</div>

<script>window.BASE_URL_JS = "<?= BASE_URL ?>";</script>
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script src="<?= BASE_URL ?>/public/assets/auth/js/login.js"></script>
</body>
</html>