import { toast } from '../../components/alerts/toast';
import { confirmAction } from '../../components/alerts/sweetAlert';
import { clearFormErrors, applyServerErrors } from '../../components/forms/validation';

function initAutosubmit() {
    document.querySelectorAll('[data-autosubmit-form]').forEach((form) => {
        form.querySelectorAll('[data-autosubmit]').forEach((el) => {
            el.addEventListener('change', () => form.submit());
        });

        let timer;
        form.querySelectorAll('[data-autosubmit-debounce]').forEach((input) => {
            input.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(() => form.submit(), 400);
            });
        });
    });
}

function initDesactivar() {
    document.querySelectorAll('[data-desactivar]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const nombre = btn.dataset.nombre;

            const result = await confirmAction({
                title: `¿Desactivar ${nombre}?`,
                text: 'Podrás gestionar esto más adelante desde el mismo módulo.',
                icon: 'warning',
                confirmText: 'Desactivar',
            });
            if (!result.isConfirmed) return;

            try {
                await window.axios.post(btn.dataset.url);
                toast.success('Se desactivó correctamente.');
                setTimeout(() => window.location.reload(), 900);
            } catch (error) {
                toast.error('No se pudo desactivar. Intenta de nuevo.');
            }
        });
    });
}

function initCrudForms() {
    document.querySelectorAll('[data-crud-form]').forEach((form) => {
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
                setTimeout(() => window.location.reload(), 900);
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
                submitBtn?.classList.remove('btn-loading');
                submitBtn && (submitBtn.disabled = false);
            }
        });
    });
}

initAutosubmit();
initDesactivar();
initCrudForms();
