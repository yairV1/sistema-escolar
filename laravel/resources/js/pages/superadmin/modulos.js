import { toast } from '../../components/alerts/toast';
import { initAutosubmit, initEstadoToggle } from '../../components/listActions';
import { clearFormErrors, applyServerErrors } from '../../components/forms/validation';

function initModuloForm() {
    const form = document.querySelector('[data-modulo-form]');
    if (!form) return;

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearFormErrors(form);

        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn?.classList.add('btn-loading');
        submitBtn && (submitBtn.disabled = true);

        try {
            const payload = Object.fromEntries(new FormData(form).entries());
            const { data } = await window.axios.post(form.dataset.url, payload);

            toast.success(data.message);
            setTimeout(() => {
                window.location.href = data.redirect || window.location.href;
            }, 700);
        } catch (error) {
            const response = error.response;
            if (response?.status === 422 && response.data.errors) {
                applyServerErrors(form, response.data.errors);
                toast.error('Revisa los campos marcados.');
            } else {
                toast.error('No se pudo guardar. Intenta de nuevo.');
            }
        } finally {
            submitBtn?.classList.remove('btn-loading');
            submitBtn && (submitBtn.disabled = false);
        }
    });
}

initAutosubmit();
initEstadoToggle();
initModuloForm();
