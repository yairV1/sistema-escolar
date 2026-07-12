import { toast } from '../../components/alerts/toast';
import { confirmLogout } from '../../components/alerts/sweetAlert';
import { initPasswordToggle } from '../../components/forms/passwordToggle';
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

function initLoginForm() {
    const form = document.getElementById('loginForm');
    if (!form) return;

    const usuario = form.querySelector('#usuario');
    const password = form.querySelector('#password');
    const remember = form.querySelector('#remember');
    const submitBtn = form.querySelector('#btnLogin');

    initPasswordToggle(document.getElementById('togglePassword'), password);
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
