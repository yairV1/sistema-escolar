import { toast } from '../../components/alerts/toast';
import { clearFormErrors, applyServerErrors } from '../../components/forms/validation';
import { appUrl } from '../../core/csrf';

const form = document.getElementById('formAdministrativo');
if (form) {
    form.querySelectorAll('input, select').forEach((el) => {
        el.addEventListener('input', () => el.classList.remove('is-invalid'));
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearFormErrors(form);

        const btn = document.getElementById('btnGuardar');
        btn.disabled = true;
        btn.classList.add('btn-loading');

        try {
            const payload = Object.fromEntries(new FormData(form).entries());
            const { data } = await window.axios.post(window.__RUTA_GUARDAR__, payload);

            toast.success(data.message);
            setTimeout(() => {
                window.location.href = appUrl('/listados?tab=administrativos');
            }, 1200);
        } catch (error) {
            const response = error.response;
            if (response?.status === 422 && response.data.errors) {
                applyServerErrors(form, response.data.errors);
                toast.error('Revisa los campos marcados.');
            } else if (response?.status === 409) {
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
