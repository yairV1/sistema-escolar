import { toast } from '../../components/alerts/toast';
import { setFieldError, clearFormErrors, wireLiveValidation } from '../../components/forms/validation';
import { appUrl } from '../../core/csrf';

function setLoading(button, loading) {
    button.classList.toggle('btn-loading', loading);
    button.disabled = loading;
}

function initChallengeForm() {
    const form = document.getElementById('twoFactorChallengeForm');
    if (!form) return;

    const codigo = form.querySelector('#codigo');
    const submitBtn = form.querySelector('#btnVerificar');

    wireLiveValidation(codigo);

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearFormErrors(form);

        if (!codigo.value.trim()) {
            setFieldError(codigo, 'Ingresa tu código de verificación.');
            return;
        }

        setLoading(submitBtn, true);

        try {
            const { data } = await window.axios.post(form.dataset.url, { codigo: codigo.value.trim() });
            toast.success(data.message || '¡Bienvenido! Redirigiendo...');
            setTimeout(() => {
                window.location.href = data.redirect || appUrl('/');
            }, 700);
        } catch (error) {
            const response = error.response;
            if (response?.status === 419) {
                toast.error(response.data.message);
                setTimeout(() => { window.location.href = appUrl('/login'); }, 1200);
            } else {
                setFieldError(codigo, response?.data?.message || 'No se pudo verificar el código.');
                codigo.value = '';
                codigo.focus();
            }
        } finally {
            setLoading(submitBtn, false);
        }
    });
}

initChallengeForm();
