<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Ingresar — Colegio San Cristóbal</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/login.css" />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>

  <!-- ===== FONDO DECORATIVO ===== -->
  <div class="bg-panel">
    <div class="bg-shapes">
      <div class="bg-shape s1"></div>
      <div class="bg-shape s2"></div>
      <div class="bg-shape s3"></div>
      <div class="bg-shape s4"></div>
    </div>

    <div class="brand-side">
      <a href="../webSite/index.html" class="back-link">
        <i class="fas fa-arrow-left"></i> Volver al sitio
      </a>

      <div class="brand-content">
        <div class="brand-logo">
          <i class="fas fa-graduation-cap"></i>
        </div>
        <h1 class="brand-name">San <strong>Cristóbal</strong></h1>
        <p class="brand-tagline">Portal Académico Institucional</p>

        <div class="brand-features">
          <div class="feature-item">
            <div class="feature-icon"><i class="fas fa-book-open"></i></div>
            <div>
              <h4>Notas y boletines</h4>
              <p>Consulta tu rendimiento académico en tiempo real</p>
            </div>
          </div>
          <div class="feature-item">
            <div class="feature-icon"><i class="fas fa-calendar-check"></i></div>
            <div>
              <h4>Horarios y asistencia</h4>
              <p>Accede a tu horario y registro de asistencia</p>
            </div>
          </div>
          <div class="feature-item">
            <div class="feature-icon"><i class="fas fa-bell"></i></div>
            <div>
              <h4>Comunicados</h4>
              <p>Recibe notificaciones y circulares del colegio</p>
            </div>
          </div>
        </div>
      </div>

      <p class="brand-footer">© 2025 Colegio San Cristóbal · Bogotá, Colombia</p>
    </div>
  </div>

  <!-- ===== PANEL LOGIN ===== -->
  <div class="login-panel">
    <div class="login-box">

      <!-- Logo móvil -->
      <div class="mobile-logo">
        <div class="brand-logo small"><i class="fas fa-graduation-cap"></i></div>
        <span>San <strong>Cristóbal</strong></span>
      </div>

      <div class="login-header">
        <h2>Bienvenido de nuevo</h2>
        <p>Ingresa tus credenciales para acceder al portal</p>
      </div>

      <!-- Selector de perfil -->
      <div class="role-selector">
        <button class="role-btn active" data-role="estudiante">
          <i class="fas fa-user-graduate"></i>
          <span>Estudiante</span>
        </button>
        <button class="role-btn" data-role="acudiente">
          <i class="fas fa-user-friends"></i>
          <span>Acudiente</span>
        </button>
        <button class="role-btn" data-role="docente">
          <i class="fas fa-chalkboard-teacher"></i>
          <span>Docente</span>
        </button>
        <button class="role-btn" data-role="rector">
          <i class="fas fa-user-tie"></i>
          <span>Administrativo</span>
        </button>
        <button class="role-btn" data-role="rector">
          <i class="fas fa-user-tie"></i>
          <span>Directivo</span>
        </button>


      </div>

      <!-- Formulario -->
      <form class="login-form" id="loginForm" novalidate>

        <div class="form-group">
          <label for="usuario">
            <i class="fas fa-id-card"></i>
            <span id="usuarioLabel">Código de estudiante</span>
          </label>
          <div class="input-wrap">
            <input
              type="text"
              id="usuario"
              placeholder="Ej. 2024-EST-0042"
              autocomplete="username"
              required
            />
            <span class="input-icon"><i class="fas fa-user"></i></span>
          </div>
          <div class="field-error" id="usuarioError"></div>
        </div>

        <div class="form-group">
          <label for="password">
            <i class="fas fa-lock"></i>
            <span>Contraseña</span>
          </label>
          <div class="input-wrap">
            <input
              type="password"
              id="password"
              placeholder="••••••••"
              autocomplete="current-password"
              required
            />
            <button type="button" class="toggle-pw" id="togglePw" aria-label="Mostrar contraseña">
              <i class="fas fa-eye"></i>
            </button>
          </div>
          <div class="field-error" id="passwordError"></div>
        </div>

        <div class="form-options">
          <label class="checkbox-label">
            <input type="checkbox" id="remember" />
            <span class="checkbox-custom"></span>
            Recordarme
          </label>
          <a href="#" class="forgot-link" id="forgotLink">¿Olvidaste tu contraseña?</a>
        </div>

        <button type="submit" class="btn-login" id="btnLogin">
          <span class="btn-text">Ingresar al portal</span>
          <i class="fas fa-arrow-right btn-icon"></i>
          <span class="btn-spinner" style="display:none;"><i class="fas fa-spinner fa-spin"></i></span>
        </button>

        <div class="login-alert" id="loginAlert" style="display:none;"></div>

      </form>

      <div class="login-divider"><span>¿Eres nuevo?</span></div>

      <a href="index.html#registro" class="btn-registro">
        <i class="fas fa-user-plus"></i> Solicitar inscripción
      </a>

      <div class="login-help">
        <p>¿Problemas para ingresar?</p>
        <a href="index.html#contacto"><i class="fas fa-headset"></i> Contactar soporte</a>
      </div>

    </div>
  </div>

  <!-- ===== MODAL recuperar contraseña ===== -->
  <div class="modal-overlay" id="modalOverlay">
    <div class="modal" id="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
      <button class="modal-close" id="modalClose" aria-label="Cerrar"><i class="fas fa-times"></i></button>
      <div class="modal-icon"><i class="fas fa-key"></i></div>
      <h3 id="modalTitle">Recuperar contraseña</h3>
      <p>Ingresa tu correo institucional y te enviaremos instrucciones para restablecer tu contraseña.</p>
      <div class="form-group" style="margin-top:20px;">
        <label for="recoveryEmail"><i class="fas fa-envelope"></i> Correo institucional</label>
        <div class="input-wrap">
          <input type="email" id="recoveryEmail" placeholder="usuario@sancristobal.edu.co" />
          <span class="input-icon"><i class="fas fa-envelope"></i></span>
        </div>
      </div>
      <button class="btn-login" id="btnRecovery" style="margin-top:8px;">
        <span class="btn-text">Enviar instrucciones</span>
        <i class="fas fa-paper-plane btn-icon"></i>
      </button>
      <div class="modal-success" id="modalSuccess" style="display:none;">
        <i class="fas fa-check-circle"></i>
        <span>¡Correo enviado! Revisa tu bandeja de entrada.</span>
      </div>
    </div>
  </div>

  <script src="<?= BASE_URL ?>/ public/assets/auth/js/login.js"></script>
</body>
</html>