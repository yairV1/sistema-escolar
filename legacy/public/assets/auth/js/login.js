/* =============================================
   COLEGIO SAN CRISTÓBAL — LOGIN SCRIPT v2
   Sin selector de rol: campo único "usuario" que acepta
   código de estudiante o correo institucional. El backend
   determina el tipo de cuenta a partir de las credenciales.
   ============================================= */

document.addEventListener('DOMContentLoaded', () => {

    /* ==========================================
       REFERENCIAS AL DOM
       ========================================== */
    const loginForm = document.getElementById('loginForm');
    const btnLogin = document.getElementById('btnLogin');
    const togglePw = document.getElementById('togglePw');
    const passwordInput = document.getElementById('password');
    const usuarioInput = document.getElementById('usuario');
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
    const themeToggle = document.getElementById('themeToggle');

    /* ==========================================
       0. TEMA CLARO / OSCURO
       El estado inicial ya se fija en <head> (evita el parpadeo);
       aquí solo se engancha el click del botón.
       ========================================== */
    themeToggle?.addEventListener('click', () => {
        const current = document.documentElement.getAttribute('data-theme');
        const next = current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('cs-theme', next);
    });

    /* ==========================================
       1. MOSTRAR / OCULTAR CONTRASEÑA
       ========================================== */
    togglePw.addEventListener('click', () => {
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        togglePw.innerHTML = isPassword
            ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.6 21.6 0 0 1 5.06-6.06M9.9 4.24A10.94 10.94 0 0 1 12 4c7 0 11 8 11 8a21.6 21.6 0 0 1-2.66 3.79M14.12 14.12a3 3 0 1 1-4.24-4.24"/><path d="M1 1l22 22"/></svg>'
            : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
        togglePw.setAttribute('aria-label', isPassword ? 'Ocultar contraseña' : 'Mostrar contraseña');
    });

    /* ==========================================
       2. VALIDACIÓN EN TIEMPO REAL + LABELS FLOTANTES
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

    // Labels flotantes: se marcan como "arriba" si el input trae valor
    // (ej. autocompletado del navegador) al cargar o al perder el foco.
    [usuarioInput, passwordInput, recoveryEmail].forEach(input => {
        if (!input) return;
        syncFloatingLabel(input);
        input.addEventListener('input', () => syncFloatingLabel(input));
        input.addEventListener('blur', () => syncFloatingLabel(input));
    });

    function syncFloatingLabel(input) {
        const label = input.closest('.field')?.querySelector('label');
        if (!label) return;
        label.classList.toggle('up', input.value.trim().length > 0);
    }

    /* ==========================================
       3. ENVÍO DEL FORMULARIO
       Campo único: si el valor tiene forma de correo se valida
       como correo institucional; si no, se trata como código
       de estudiante/documento. El backend resuelve el tipo real
       de cuenta a partir de las credenciales recibidas.
       ========================================== */
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearErrors();

        const usuario = usuarioInput.value.trim();
        const password = passwordInput.value;
        let valid = true;

        // Validar usuario
        if (!usuario) {
            showFieldError(usuarioInput, usuarioError, 'Este campo es obligatorio');
            valid = false;
        } else if (usuario.includes('@') && !isEmail(usuario)) {
            // Si parece un intento de correo, exigir formato válido.
            // Si no tiene "@", se acepta como código de estudiante/documento.
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
                showToast('success', '¡Bienvenido! Redirigiendo al portal...');
                setTimeout(() => {
                    window.location.href = json.redirect || (window.BASE_URL_JS || '/colegio/');
                }, 1200);
            } else {
                showFieldError(passwordInput, passwordError, json.message || 'Usuario o contraseña incorrectos.');
                passwordInput.value = '';
                passwordInput.focus();
                syncFloatingLabel(passwordInput);
            }
        } catch (err) {
            setLoading(false);
            showToast('error', 'No se pudo conectar con el servidor. Intenta de nuevo.');
        }
    });

    /* ==========================================
       4. MODAL — RECUPERAR CONTRASEÑA
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
            recoveryEmail.classList.add('invalid');
            recoveryEmail.focus();
            return;
        }
        recoveryEmail.classList.remove('invalid');

        modalError.style.display = 'none';
        setModalRecoveryLoading(true);

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
                recoveryEmail.classList.remove('invalid');
                syncFloatingLabel(recoveryEmail);

                setTimeout(() => {
                    closeModal();
                    btnRecovery.style.display = 'flex';
                    setModalRecoveryLoading(false);
                    modalSuccess.style.display = 'none';
                }, 3000);
            } else {
                modalErrorText.textContent = json.message || 'No se pudo enviar el correo.';
                modalError.style.display = 'flex';
                setModalRecoveryLoading(false);
            }
        } catch (err) {
            modalErrorText.textContent = 'No se pudo conectar con el servidor.';
            modalError.style.display = 'flex';
            setModalRecoveryLoading(false);
        }
    });

    function setModalRecoveryLoading(state) {
        const label = btnRecovery.querySelector('.btn-label');
        const arrow = btnRecovery.querySelector('.btn-arrow');
        const spinner = btnRecovery.querySelector('.spinner');
        if (state) {
            label.textContent = 'Enviando...';
            arrow.style.display = 'none';
            spinner.style.display = 'inline-block';
            btnRecovery.disabled = true;
        } else {
            label.textContent = 'Enviar instrucciones';
            arrow.style.display = '';
            spinner.style.display = 'none';
            btnRecovery.disabled = false;
        }
    }

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
       5. ANIMACIÓN DE ENTRADA — elementos [data-anim]
       ========================================== */
    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const animElements = document.querySelectorAll('[data-anim]');

    animElements.forEach((el, i) => {
        if (prefersReduced) {
            el.style.opacity = '1';
            return;
        }
        const type = el.dataset.animType;
        const startTransform = type === 'slide' ? 'translateY(14px)'
            : type === 'zoom' ? 'scale(0.96)'
            : 'translateY(6px)';
        el.style.transform = startTransform;
        el.style.transition = 'opacity 620ms cubic-bezier(0.22,1,0.36,1), transform 620ms cubic-bezier(0.22,1,0.36,1)';
        setTimeout(() => {
            el.style.opacity = '1';
            el.style.transform = 'none';
        }, 90 + i * 90);
    });

    /* ==========================================
       6. RIPPLE EFFECT — botones
       ========================================== */
    document.querySelectorAll('.btn-primary, .btn-ghost').forEach(btn => {
        btn.addEventListener('click', (e) => {
            if (btn.disabled) return;
            const rect = btn.getBoundingClientRect();
            const ripple = document.createElement('span');
            const size = Math.max(rect.width, rect.height);
            ripple.className = 'ripple';
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
            ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';
            btn.appendChild(ripple);
            ripple.addEventListener('animationend', () => ripple.remove());
        });
    });

    /* ==========================================
       UTILIDADES
       ========================================== */
    function showFieldError(input, errorEl, msg) {
        input.classList.add('invalid');
        errorEl.innerHTML = msg
            ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v5M12 16h.01"/></svg><span>' + msg + '</span>'
            : '';
    }

    function clearFieldError(input, errorEl) {
        input.classList.remove('invalid');
        errorEl.innerHTML = '';
    }

    function clearErrors() {
        clearFieldError(usuarioInput, usuarioError);
        clearFieldError(passwordInput, passwordError);
    }

    function showToast(type, message) {
        if (typeof Toastify === 'undefined') return;
        Toastify({
            text: message,
            duration: 4000,
            gravity: 'top',
            position: 'right',
            offset: { y: 70 },
            close: true,
            stopOnFocus: true,
            escapeMarkup: true,
            className: type === 'success' ? 'toast-success' : 'toast-error',
        }).showToast();
    }

    function setLoading(state) {
        const btnLabel = btnLogin.querySelector('.btn-label');
        const btnArrow = btnLogin.querySelector('.btn-arrow');
        const btnSpinner = btnLogin.querySelector('.spinner');

        if (state) {
            btnLabel.textContent = 'Verificando...';
            btnArrow.style.display = 'none';
            btnSpinner.style.display = 'inline-block';
            btnLogin.disabled = true;
        } else {
            btnLabel.textContent = 'Ingresar al portal';
            btnArrow.style.display = '';
            btnSpinner.style.display = 'none';
            btnLogin.disabled = false;
        }
    }

    function isEmail(val) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
    }

});