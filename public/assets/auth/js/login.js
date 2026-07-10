/* =============================================
   COLEGIO SAN CRISTÓBAL — LOGIN SCRIPT
   ============================================= */

document.addEventListener('DOMContentLoaded', () => {

    /* ==========================================
       REFERENCIAS AL DOM
       ========================================== */
    const loginForm = document.getElementById('loginForm');
    const btnLogin = document.getElementById('btnLogin');
    const loginAlert = document.getElementById('loginAlert');
    const togglePw = document.getElementById('togglePw');
    const passwordInput = document.getElementById('password');
    const usuarioInput = document.getElementById('usuario');
    const usuarioLabel = document.getElementById('usuarioLabel');
    const usuarioError = document.getElementById('usuarioError');
    const passwordError = document.getElementById('passwordError');
    const forgotLink = document.getElementById('forgotLink');
    const modalOverlay = document.getElementById('modalOverlay');
    const modalClose = document.getElementById('modalClose');
    const btnRecovery = document.getElementById('btnRecovery');
    const recoveryEmail = document.getElementById('recoveryEmail');
    const modalSuccess = document.getElementById('modalSuccess');
    const modalSuccessText = document.getElementById('modalSuccessText');
    const modalError = document.getElementById('modalError');
    const modalErrorText = document.getElementById('modalErrorText');

    /* ==========================================
       1. SELECTOR DE ROL — cambia placeholder y label
       ========================================== */
    const roleLabels = {
        estudiante: {
            label: 'Código de estudiante',
            placeholder: 'Ej. 2024-EST-0042'
        },
        docente: {
            label: 'Correo institucional',
            placeholder: 'Ej. docente@sancristobal.edu.co'
        },
        acudiente: {
            label: 'Número de documento',
            placeholder: 'Ej. 80123456'
        },
        rector: {
            label: 'Correo institucional',
            placeholder: 'Ej. rector@sancristobal.edu.co'
        },
        admin: {
            label: 'Correo institucional',
            placeholder: 'Ej. administrativo@sancristobal.edu.co'
        }

    };

    let activeRole = 'estudiante';

    document.querySelectorAll('.role-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.role-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            activeRole = btn.dataset.role;

            const config = roleLabels[activeRole];
            usuarioLabel.textContent = config.label;
            usuarioInput.placeholder = config.placeholder;
            usuarioInput.value = '';
            clearErrors();
            hideAlert();
        });
    });

    /* ==========================================
       2. MOSTRAR / OCULTAR CONTRASEÑA
       ========================================== */
    togglePw.addEventListener('click', () => {
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        togglePw.querySelector('i').className = isPassword
            ? 'fas fa-eye-slash'
            : 'fas fa-eye';
        togglePw.setAttribute('aria-label', isPassword ? 'Ocultar contraseña' : 'Mostrar contraseña');
    });

    /* ==========================================
       3. VALIDACIÓN EN TIEMPO REAL
       ========================================== */
    usuarioInput.addEventListener('input', () => {
        if (usuarioInput.value.trim()) {
            clearFieldError(usuarioInput, usuarioError);
        }
    });

    passwordInput.addEventListener('input', () => {
        if (passwordInput.value.trim()) {
            clearFieldError(passwordInput, passwordError);
        }
    });

    /* ==========================================
       4. ENVÍO DEL FORMULARIO
       ========================================== */
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        hideAlert();
        clearErrors();

        const usuario = usuarioInput.value.trim();
        const password = passwordInput.value;
        let valid = true;

        // Validar usuario
        if (!usuario) {
            showFieldError(usuarioInput, usuarioError, 'Este campo es obligatorio');
            valid = false;
        } else if (['docente', 'rector', 'admin'].includes(activeRole) && !isEmail(usuario)) {
            showFieldError(usuarioInput, usuarioError, 'Ingresa un correo institucional válido');
            valid = false;
        }

        // Validar contraseña
        if (!password) {
            showFieldError(passwordInput, passwordError, 'Ingresa tu contraseña');
            valid = false;
        } else if (password.length < 6) {
            showFieldError(passwordInput, passwordError, 'Mínimo 6 caracteres');
            valid = false;
        }

        if (!valid) return;

        // Estado de carga
        setLoading(true);

        try {
            const body = new URLSearchParams({ usuario, password });
            const res = await fetch((window.BASE_URL_JS || '/colegio/') + 'api/auth/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body
            });
            const json = await res.json();

            setLoading(false);

            if (json.success) {
                showAlert('success', `<i class="fas fa-check-circle"></i> ¡Bienvenido! Redirigiendo al portal...`);
                setTimeout(() => {
                    window.location.href = json.redirect || (window.BASE_URL_JS || '/colegio/');
                }, 1200);
            } else {
                showAlert('error', `<i class="fas fa-exclamation-circle"></i> ${json.message || 'Credenciales incorrectas.'}`);
                passwordInput.value = '';
                passwordInput.focus();
            }
        } catch (err) {
            setLoading(false);
            showAlert('error', `<i class="fas fa-exclamation-circle"></i> No se pudo conectar con el servidor. Intenta de nuevo.`);
        }
    });

    /* ==========================================
       5. MODAL — RECUPERAR CONTRASEÑA
       ========================================== */
    forgotLink.addEventListener('click', (e) => {
        e.preventDefault();
        openModal();
    });

    modalClose.addEventListener('click', closeModal);

    modalOverlay.addEventListener('click', (e) => {
        if (e.target === modalOverlay) closeModal();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modalOverlay.classList.contains('open')) closeModal();
    });

    btnRecovery.addEventListener('click', async () => {
        const email = recoveryEmail.value.trim();
        if (!email || !isEmail(email)) {
            recoveryEmail.style.borderColor = '#e53e3e';
            recoveryEmail.style.boxShadow = '0 0 0 3px rgba(229,62,62,0.10)';
            recoveryEmail.focus();
            return;
        }

        modalError.style.display = 'none';
        btnRecovery.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
        btnRecovery.disabled = true;

        try {
            const body = new URLSearchParams({ correo: email });
            const res = await fetch((window.BASE_URL_JS || '/colegio/') + 'api/auth/recuperar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body
            });
            const json = await res.json();

            if (json.success) {
                modalSuccessText.textContent = json.message || '¡Correo enviado! Revisa tu bandeja de entrada.';
                btnRecovery.style.display = 'none';
                modalSuccess.style.display = 'flex';
                recoveryEmail.value = '';
                recoveryEmail.style.borderColor = '';
                recoveryEmail.style.boxShadow = '';

                setTimeout(() => {
                    closeModal();
                    btnRecovery.style.display = 'flex';
                    btnRecovery.innerHTML = '<span class="btn-text">Enviar instrucciones</span><i class="fas fa-paper-plane btn-icon"></i>';
                    btnRecovery.disabled = false;
                    modalSuccess.style.display = 'none';
                }, 3000);
            } else {
                modalErrorText.textContent = json.message || 'No se pudo enviar el correo.';
                modalError.style.display = 'block';
                btnRecovery.innerHTML = '<span class="btn-text">Enviar instrucciones</span><i class="fas fa-paper-plane btn-icon"></i>';
                btnRecovery.disabled = false;
            }
        } catch (err) {
            modalErrorText.textContent = 'No se pudo conectar con el servidor.';
            modalError.style.display = 'block';
            btnRecovery.innerHTML = '<span class="btn-text">Enviar instrucciones</span><i class="fas fa-paper-plane btn-icon"></i>';
            btnRecovery.disabled = false;
        }
    });

    function openModal() {
        modalOverlay.classList.add('open');
        document.body.style.overflow = 'hidden';
        modalError.style.display = 'none';
        setTimeout(() => recoveryEmail.focus(), 200);
    }

    function closeModal() {
        modalOverlay.classList.remove('open');
        document.body.style.overflow = '';
    }

    /* ==========================================
       6. ANIMACIÓN DE ENTRADA — elementos
       ========================================== */
    const animElements = document.querySelectorAll(
        '.login-header, .role-selector, .form-group, .form-options, .btn-login, .login-divider, .btn-registro, .login-help'
    );

    animElements.forEach((el, i) => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(16px)';
        el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
        setTimeout(() => {
            el.style.opacity = '1';
            el.style.transform = 'none';
        }, 80 + i * 60);
    });

    /* ==========================================
       UTILIDADES
       ========================================== */
    function showFieldError(input, errorEl, msg) {
        input.classList.add('error');
        errorEl.textContent = msg;
    }

    function clearFieldError(input, errorEl) {
        input.classList.remove('error');
        errorEl.textContent = '';
    }

    function clearErrors() {
        clearFieldError(usuarioInput, usuarioError);
        clearFieldError(passwordInput, passwordError);
    }

    function showAlert(type, html) {
        loginAlert.innerHTML = html;
        loginAlert.className = `login-alert ${type}`;
        loginAlert.style.display = 'flex';
        loginAlert.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function hideAlert() {
        loginAlert.style.display = 'none';
        loginAlert.className = 'login-alert';
    }

    function setLoading(state) {
        const btnText = btnLogin.querySelector('.btn-text');
        const btnIcon = btnLogin.querySelector('.btn-icon');
        const btnSpinner = btnLogin.querySelector('.btn-spinner');

        if (state) {
            btnText.textContent = 'Verificando...';
            btnIcon.style.display = 'none';
            btnSpinner.style.display = 'inline';
            btnLogin.disabled = true;
        } else {
            btnText.textContent = 'Ingresar al portal';
            btnIcon.style.display = '';
            btnSpinner.style.display = 'none';
            btnLogin.disabled = false;
        }
    }

    function isEmail(val) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
    }

    function fakeApiCall(ms) {
        return new Promise(res => setTimeout(res, ms));
    }

});