import { toast } from '../../components/alerts/toast';
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

const form = document.getElementById('resetPasswordForm');

if (form) {
    const password = form.querySelector('#password');
    const passwordConfirm = form.querySelector('#passwordConfirm');
    const submitBtn = form.querySelector('#btnReset');

    initPasswordToggle(document.getElementById('togglePassword'), password);
    wireLiveValidation(password);
    wireLiveValidation(passwordConfirm);

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearFormErrors(form);

        let hasError = false;
        if (password.value.length < 6) {
            setFieldError(password, 'La contraseña debe tener al menos 6 caracteres.');
            hasError = true;
        }
        if (passwordConfirm.value !== password.value) {
            setFieldError(passwordConfirm, 'Las contraseñas no coinciden.');
            hasError = true;
        }
        if (hasError) return;

        setLoading(submitBtn, true);

        try {
            const { data } = await window.axios.post('/reset-password', {
                token: form.querySelector('#token').value,
                password: password.value,
                password_confirmation: passwordConfirm.value,
            });

            toast.success(data.message || 'Contraseña actualizada correctamente.');
            form.reset();
            setTimeout(() => {
                window.location.href = appUrl('/login');
            }, 1200);
        } catch (error) {
            const response = error.response;
            if (response?.status === 422 && response.data.errors) {
                applyServerErrors(form, response.data.errors);
            } else {
                toast.error(response?.data?.message || 'No se pudo restablecer la contraseña.');
            }
        } finally {
            setLoading(submitBtn, false);
        }
    });
}
