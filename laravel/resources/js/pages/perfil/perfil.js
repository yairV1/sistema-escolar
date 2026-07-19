import { toast } from '../../components/alerts/toast';
import { clearFormErrors, applyServerErrors, setFieldError } from '../../components/forms/validation';

function wireForm(formId, buttonId, buildPayload, { resetOnSuccess = false } = {}) {
    const form = document.getElementById(formId);
    if (!form) return;

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearFormErrors(form);

        const btn = document.getElementById(buttonId);
        btn.disabled = true;
        btn.classList.add('btn-loading');

        try {
            const { data } = await window.axios.post(form.dataset.url, buildPayload(form));
            toast.success(data.message);
            if (resetOnSuccess) form.reset();
        } catch (error) {
            const response = error.response;
            if (response?.status === 422 && response.data.errors) {
                applyServerErrors(form, response.data.errors);
                toast.error('Revisa los campos marcados.');
            } else if (response?.status === 422 && response.data.message) {
                const actual = form.querySelector('[name="password_actual"]');
                if (actual) setFieldError(actual, response.data.message);
                toast.error(response.data.message);
            } else {
                toast.error('No se pudo guardar. Intenta de nuevo.');
            }
        } finally {
            btn.disabled = false;
            btn.classList.remove('btn-loading');
        }
    });
}

wireForm('formPerfil', 'btnGuardarPerfil', (form) => ({
    nombres: form.querySelector('[name="nombres"]').value,
    apellidos: form.querySelector('[name="apellidos"]').value,
    correo: form.querySelector('[name="correo"]').value,
    telefono: form.querySelector('[name="telefono"]').value,
}));

wireForm('formPassword', 'btnCambiarPassword', (form) => ({
    password_actual: form.querySelector('[name="password_actual"]').value,
    password_nueva: form.querySelector('[name="password_nueva"]').value,
    password_nueva_confirmation: form.querySelector('[name="password_nueva_confirmation"]').value,
}), { resetOnSuccess: true });
