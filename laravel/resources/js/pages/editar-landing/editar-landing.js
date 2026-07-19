import { initEstadoToggle } from '../../components/listActions';
import { toast } from '../../components/alerts/toast';
import { clearFormErrors, applyServerErrors } from '../../components/forms/validation';

initEstadoToggle();

// ---------- Formulario de contenido (texto plano, sin archivos) ----------
const formContenido = document.getElementById('formContenidoLanding');

if (formContenido) {
    formContenido.addEventListener('submit', async (event) => {
        event.preventDefault();

        const btn = document.getElementById('btnGuardarContenido');
        btn.disabled = true;
        btn.classList.add('btn-loading');

        try {
            const valores = {};
            formContenido.querySelectorAll('[data-clave]').forEach((el) => {
                valores[el.dataset.clave] = el.value;
            });

            const { data } = await window.axios.post(formContenido.dataset.url, { valores });
            toast.success(data.message);
        } catch (error) {
            toast.error('No se pudo guardar el contenido. Intenta de nuevo.');
        } finally {
            btn.disabled = false;
            btn.classList.remove('btn-loading');
        }
    });
}

// ---------- Formularios de noticias/galería (con posible archivo) ----------
document.querySelectorAll('[data-landing-form]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearFormErrors(form);

        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.classList.add('btn-loading');

        try {
            const formData = new FormData(form);
            const { data } = await window.axios.post(form.dataset.url, formData);

            toast.success(data.message);
            setTimeout(() => window.location.reload(), 900);
        } catch (error) {
            const response = error.response;
            if (response?.status === 422 && response.data.errors) {
                applyServerErrors(form, response.data.errors);
                toast.error('Revisa los campos marcados.');
            } else {
                toast.error('No se pudo guardar. Intenta de nuevo.');
            }
        } finally {
            submitBtn.disabled = false;
            submitBtn.classList.remove('btn-loading');
        }
    });
});
