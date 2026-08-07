import { toast } from '../../components/alerts/toast';
import { initAutosubmit, initEstadoToggle } from '../../components/listActions';
import { clearFormErrors, applyServerErrors } from '../../components/forms/validation';

/** Igual que instituciones.js: sigue `redirect` de la respuesta en vez de recargar la misma página. */
function initPlanForm() {
    const form = document.querySelector('[data-plan-form]');
    if (!form) return;

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearFormErrors(form);

        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn?.classList.add('btn-loading');
        submitBtn && (submitBtn.disabled = true);

        try {
            const formData = new FormData(form);
            const payload = {};
            for (const [key, value] of formData.entries()) {
                if (key === 'modulos[]') {
                    (payload.modulos ??= []).push(value);
                } else {
                    payload[key] = value;
                }
            }

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

/** Duplicar un plan: no requiere confirmación destructiva, solo aviso de éxito y redirección. */
function initDuplicar() {
    document.querySelectorAll('[data-duplicar]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            try {
                const { data } = await window.axios.post(btn.dataset.url);
                toast.success(data.message);
                setTimeout(() => {
                    window.location.href = data.redirect || window.location.href;
                }, 700);
            } catch (error) {
                toast.error('No se pudo duplicar el plan.');
            }
        });
    });
}

/** Toggle mensual/anual en las tarjetas de plan del índice — solo cambia qué precio se muestra, sin recargar. */
function initBillToggle() {
    const toggle = document.querySelector('[data-bill-toggle]');
    if (!toggle) return;

    toggle.querySelectorAll('button').forEach((btn) => {
        btn.addEventListener('click', () => {
            toggle.querySelectorAll('button').forEach((b) => b.classList.remove('active'));
            btn.classList.add('active');

            const anual = btn.dataset.ciclo === 'anual';
            document.querySelectorAll('.plan-precio-mensual').forEach((el) => el.classList.toggle('d-none', anual));
            document.querySelectorAll('.plan-precio-anual').forEach((el) => el.classList.toggle('d-none', !anual));
            document.querySelectorAll('.plan-precio-periodo').forEach((el) => { el.textContent = anual ? 'año' : 'mes'; });
        });
    });
}

initAutosubmit();
initEstadoToggle();
initPlanForm();
initDuplicar();
initBillToggle();
