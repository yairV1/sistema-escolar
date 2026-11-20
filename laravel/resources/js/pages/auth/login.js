import { toast } from '../../components/alerts/toast';
import { confirmLogout } from '../../components/alerts/sweetAlert';
import {
    wireLiveValidation,
    setFieldError,
    clearFormErrors,
    applyServerErrors,
} from '../../components/forms/validation';
import { appUrl } from '../../core/csrf';

function setLoading(button, loading) {
    button.classList.toggle('btn-loading', loading);
    button.disabled = loading;
}

/**
 * Botón "Mostrar/Ocultar" dentro del campo de contraseña + avatar animado.
 * El avatar refleja lo que pasa en el formulario:
 *   mira  → escribiendo el usuario (sigue el texto con la mirada)
 *   tapa  → escribiendo la contraseña oculta (se tapa los ojos)
 *   espia → la contraseña está visible (baja las manos y mira)
 *   reposo → nada enfocado (parpadea de vez en cuando)
 */
function initPasswordYAvatar(form, usuario, password) {
    const toggle = form.querySelector('#togglePassword');
    const mono = document.getElementById('authMono');

    const actualizarMono = () => {
        if (!mono) return;
        const activo = document.activeElement;
        let estado = 'reposo';
        if (password.type === 'text' && (activo === password || activo === toggle)) estado = 'espia';
        else if (activo === password || activo === toggle) estado = 'tapa';
        else if (activo === usuario) estado = 'mira';
        mono.dataset.estado = estado;

        // La mirada se desplaza según cuánto se ha escrito en el usuario.
        const avance = Math.min(usuario.value.length, 30) / 30;
        mono.style.setProperty('--mirada', `${(avance * 2 - 1) * 1.2}px`);
    };

    toggle?.addEventListener('click', () => {
        const visible = password.type === 'text';
        password.type = visible ? 'password' : 'text';
        toggle.textContent = visible ? 'Mostrar' : 'Ocultar';
        toggle.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
        toggle.setAttribute('aria-pressed', String(!visible));
        password.focus();
        actualizarMono();
    });

    [usuario, password, toggle].forEach((el) => {
        el?.addEventListener('focus', actualizarMono);
        el?.addEventListener('blur', () => setTimeout(actualizarMono, 0));
    });
    usuario.addEventListener('input', actualizarMono);
}

function initLoginForm() {
    const form = document.getElementById('loginForm');
    if (!form) return;

    const usuario = form.querySelector('#usuario');
    const password = form.querySelector('#password');
    const remember = form.querySelector('#remember');
    const submitBtn = form.querySelector('#btnLogin');

    initPasswordYAvatar(form, usuario, password);
    wireLiveValidation(usuario);
    wireLiveValidation(password);

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearFormErrors(form);

        let hasError = false;
        if (!usuario.value.trim()) {
            setFieldError(usuario, 'Ingresa tu correo o número de documento.');
            hasError = true;
        }
        if (password.value.length < 6) {
            setFieldError(password, 'La contraseña debe tener al menos 6 caracteres.');
            hasError = true;
        }
        if (hasError) return;

        setLoading(submitBtn, true);

        try {
            const { data } = await window.axios.post('/login', {
                usuario: usuario.value.trim(),
                password: password.value,
                remember: remember?.checked ?? false,
            });

            toast.success(data.message || '¡Bienvenido! Redirigiendo...');
            setTimeout(() => {
                window.location.href = data.redirect || appUrl('/');
            }, 900);
        } catch (error) {
            const response = error.response;
            if (response?.status === 422 && response.data.errors) {
                applyServerErrors(form, response.data.errors);
            } else if (response?.data?.message) {
                setFieldError(password, response.data.message);
                password.value = '';
                password.focus();
            } else {
                toast.error('No se pudo conectar con el servidor. Intenta de nuevo.');
            }
        } finally {
            setLoading(submitBtn, false);
        }
    });
}

function initForgotPasswordForm() {
    const form = document.getElementById('forgotPasswordForm');
    if (!form) return;

    const email = form.querySelector('#recoveryEmail');
    const submitBtn = form.querySelector('#btnRecovery');
    const successBox = document.getElementById('forgotSuccess');

    wireLiveValidation(email);

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearFormErrors(form);

        if (!email.value.trim()) {
            setFieldError(email, 'Ingresa tu correo institucional.');
            return;
        }

        setLoading(submitBtn, true);

        try {
            const { data } = await window.axios.post('/forgot-password', {
                correo: email.value.trim(),
            });

            successBox.textContent = data.message;
            successBox.classList.remove('d-none');
            form.classList.add('d-none');
        } catch (error) {
            const response = error.response;
            if (response?.status === 422 && response.data.errors) {
                applyServerErrors(form, response.data.errors);
            } else {
                toast.error('No se pudo enviar el correo. Intenta de nuevo.');
            }
        } finally {
            setLoading(submitBtn, false);
        }
    });

    // Restaura el modal a su estado inicial cada vez que se abre
    document.getElementById('forgotPasswordModal')?.addEventListener('shown.bs.modal', () => {
        form.classList.remove('d-none');
        successBox.classList.add('d-none');
        form.reset();
        clearFormErrors(form);
    });
}

function initLogoutConfirmation() {
    document.querySelectorAll('[data-logout]').forEach((trigger) => {
        trigger.addEventListener('click', async (event) => {
            event.preventDefault();
            const result = await confirmLogout();
            if (result.isConfirmed) {
                trigger.closest('form')?.submit();
            }
        });
    });
}

initLoginForm();
initForgotPasswordForm();
initLogoutConfirmation();
