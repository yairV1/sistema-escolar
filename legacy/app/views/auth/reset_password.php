<?php
$token = trim($_GET['token'] ?? '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Restablecer contraseña — Colegio San Cristóbal</title>
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
      <a href="<?= BASE_URL ?>login" class="back-link">
        <i class="fas fa-arrow-left"></i> Volver a iniciar sesión
      </a>

      <div class="brand-content">
        <div class="brand-logo">
          <i class="fas fa-graduation-cap"></i>
        </div>
        <h1 class="brand-name">San <strong>Cristóbal</strong></h1>
        <p class="brand-tagline">Portal Académico Institucional</p>
      </div>

      <p class="brand-footer">© 2025 Colegio San Cristóbal · Bogotá, Colombia</p>
    </div>
  </div>

  <!-- ===== PANEL RESTABLECER CONTRASEÑA ===== -->
  <div class="login-panel">
    <div class="login-box">

      <div class="mobile-logo">
        <div class="brand-logo small"><i class="fas fa-graduation-cap"></i></div>
        <span>San <strong>Cristóbal</strong></span>
      </div>

      <div class="login-header">
        <h2>Restablecer contraseña</h2>
        <p>Crea una nueva contraseña para tu cuenta</p>
      </div>

      <?php if ($token === ''): ?>
        <div class="login-alert error" style="display:flex;">
          <i class="fas fa-exclamation-circle"></i>
          Este enlace no incluye un token válido. Solicita uno nuevo desde la página de inicio de sesión.
        </div>
      <?php else: ?>

      <form class="login-form" id="resetForm" novalidate>
        <input type="hidden" id="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>" />

        <div class="form-group">
          <label for="password">
            <i class="fas fa-lock"></i>
            <span>Nueva contraseña</span>
          </label>
          <div class="input-wrap">
            <input type="password" id="password" placeholder="Mínimo 6 caracteres" autocomplete="new-password" required />
            <button type="button" class="toggle-pw" id="togglePw" aria-label="Mostrar contraseña">
              <i class="fas fa-eye"></i>
            </button>
          </div>
          <div class="field-error" id="passwordError"></div>
        </div>

        <div class="form-group">
          <label for="passwordConfirm">
            <i class="fas fa-lock"></i>
            <span>Confirmar contraseña</span>
          </label>
          <div class="input-wrap">
            <input type="password" id="passwordConfirm" placeholder="Repite la contraseña" autocomplete="new-password" required />
            <span class="input-icon"><i class="fas fa-lock"></i></span>
          </div>
          <div class="field-error" id="passwordConfirmError"></div>
        </div>

        <button type="submit" class="btn-login" id="btnReset">
          <span class="btn-text">Restablecer contraseña</span>
          <i class="fas fa-arrow-right btn-icon"></i>
          <span class="btn-spinner" style="display:none;"><i class="fas fa-spinner fa-spin"></i></span>
        </button>

        <div class="login-alert" id="resetAlert" style="display:none;"></div>
      </form>

      <?php endif; ?>

    </div>
  </div>

  <script>window.BASE_URL_JS = "<?= BASE_URL ?>";</script>
  <script src="<?= BASE_URL ?>/public/assets/auth/js/reset_password.js"></script>
</body>
</html>
