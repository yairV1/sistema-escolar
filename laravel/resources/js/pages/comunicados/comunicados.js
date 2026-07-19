import { toast } from '../../components/alerts/toast';
import { clearFormErrors, applyServerErrors } from '../../components/forms/validation';

const form = document.querySelector('#modalNuevoComunicado form[data-crud-form]');

if (form) {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearFormErrors(form);

        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.classList.add('btn-loading');

        try {
            const payload = {
                titulo: form.querySelector('[name="titulo"]').value,
                mensaje: form.querySelector('[name="mensaje"]').value,
                tipo_notificacion: form.querySelector('[name="tipo_notificacion"]').value,
                canal: form.querySelector('[name="canal"]').value,
                audiencias: Array.from(form.querySelectorAll('[name="audiencias[]"]:checked')).map((el) => el.value),
            };

            const { data } = await window.axios.post(form.dataset.url, payload);

            toast.success(data.message);
            setTimeout(() => window.location.reload(), 900);
        } catch (error) {
            const response = error.response;
            if (response?.status === 422 && response.data.errors) {
                applyServerErrors(form, response.data.errors);
                toast.error('Revisa los campos marcados.');
            } else if (response?.data?.message) {
                toast.error(response.data.message);
            } else {
                toast.error('No se pudo enviar el comunicado. Intenta de nuevo.');
            }
        } finally {
            submitBtn.disabled = false;
            submitBtn.classList.remove('btn-loading');
        }
    });
}
