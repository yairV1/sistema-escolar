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
        administracion: {
            label: 'Correo institucional',
            placeholder: 'Ej. administracion@sancristobal.edu.co'
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
        } else if (activeRole === 'docente' && !isEmail(usuario)) {
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

        // Simulación de llamada al servidor (1.8 segundos)
        await fakeApiCall(1800);

        setLoading(false);

        // Demo: credenciales de prueba
        // const credencialesDemo = {
        //     estudiante: { usuario: '2024-EST-0001', password: '123456' },
        //     docente: { usuario: 'docente@sancristobal.edu.co', password: '123456' },
        //     acudiente: { usuario: '80000000', password: '123456' },
        //     rector: { usuario: 'rector@sancristobal.edu.co', password: '123456' },
        //     // administracion: { usuario: 'administracion@sancristobal.edu.co', password: '123456' }
        // };

        const demo = credencialesDemo[activeRole];
        const esValido = usuario === demo.usuario && password === demo.password;

        if (esValido) {
            showAlert('success', `<i class="fas fa-check-circle"></i> ¡Bienvenido! Redirigiendo al portal...`);

            setTimeout(() => {
                window.location.href = '../../../../app/views/dashBoard/estudiante/inicio.html';
            }, 1500); // espera 1.5 segundos
        } else {
            showAlert('error', `<i class="fas fa-exclamation-circle"></i> Credenciales incorrectas. Verifica tu ${roleLabels[activeRole].label.toLowerCase()} y contraseña.`);
            passwordInput.value = '';
            passwordInput.focus();
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

        // Estado carga en modal
        btnRecovery.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
        btnRecovery.disabled = true;

        await fakeApiCall(1500);

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
    });

    function openModal() {
        modalOverlay.classList.add('open');
        document.body.style.overflow = 'hidden';
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