/* =============================================
   COLEGIO SAN CRISTÓBAL — RESTABLECER CONTRASEÑA
   ============================================= */

document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('resetForm');
  if (!form) return; // token ausente: la vista solo muestra la alerta de enlace inválido

  const btn = document.getElementById('btnReset');
  const alertBox = document.getElementById('resetAlert');
  const tokenInput = document.getElementById('token');
  const passwordInput = document.getElementById('password');
  const passwordConfirmInput = document.getElementById('passwordConfirm');
  const passwordError = document.getElementById('passwordError');
  const passwordConfirmError = document.getElementById('passwordConfirmError');
  const togglePw = document.getElementById('togglePw');

  togglePw?.addEventListener('click', () => {
    const isPassword = passwordInput.type === 'password';
    passwordInput.type = isPassword ? 'text' : 'password';
    togglePw.querySelector('i').className = isPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
  });

  function showAlert(type, html) {
    alertBox.innerHTML = html;
    alertBox.className = `login-alert ${type}`;
    alertBox.style.display = 'flex';
  }

  function setLoading(state) {
    const btnText = btn.querySelector('.btn-text');
    const btnIcon = btn.querySelector('.btn-icon');
    const btnSpinner = btn.querySelector('.btn-spinner');
    btnText.textContent = state ? 'Guardando...' : 'Restablecer contraseña';
    btnIcon.style.display = state ? 'none' : '';
    btnSpinner.style.display = state ? 'inline' : 'none';
    btn.disabled = state;
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    passwordError.textContent = '';
    passwordConfirmError.textContent = '';
    alertBox.style.display = 'none';

    const password = passwordInput.value;
    const confirm = passwordConfirmInput.value;
    let valid = true;

    if (password.length < 6) {
      passwordError.textContent = 'Mínimo 6 caracteres';
      valid = false;
    }
    if (password !== confirm) {
      passwordConfirmError.textContent = 'Las contraseñas no coinciden';
      valid = false;
    }
    if (!valid) return;

    setLoading(true);
    try {
      const body = new URLSearchParams({ token: tokenInput.value, password });
      const res = await fetch((window.BASE_URL_JS || '/colegio/') + 'api/auth/restablecer', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body
      });
      const json = await res.json();
      setLoading(false);

      if (json.success) {
        showAlert('success', `<i class="fas fa-check-circle"></i> ${json.message}`);
        form.reset();
        setTimeout(() => {
          window.location.href = (window.BASE_URL_JS || '/colegio/') + 'login';
        }, 1800);
      } else {
        showAlert('error', `<i class="fas fa-exclamation-circle"></i> ${json.message}`);
      }
    } catch (err) {
      setLoading(false);
      showAlert('error', '<i class="fas fa-exclamation-circle"></i> No se pudo conectar con el servidor.');
    }
  });
});
