import { toast } from './alerts/toast';
import { confirmAction } from './alerts/sweetAlert';
import { clearFormErrors, applyServerErrors } from './forms/validation';

/**
 * Auto-submit de formularios de filtros: cambios en <select> mandan el
 * form de inmediato, texto libre lo hace con debounce. Usado por
 * cualquier página con una barra de filtros (Listados, Matrículas,
 * Gestión Académica...).
 */
export function initAutosubmit() {
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

/**
 * Desactivar/reactivar un registro (estudiante, docente, administrativo,
 * materia, curso, asignación...). Un mismo botón hace una de las dos
 * cosas según lleve data-desactivar o data-activar.
 */
export function initEstadoToggle() {
    document.querySelectorAll('[data-desactivar], [data-activar]').forEach((btn) => {
        const reactivando = btn.hasAttribute('data-activar');

        btn.addEventListener('click', async () => {
            const nombre = btn.dataset.nombre;

            const result = await confirmAction({
                title: reactivando ? `¿Reactivar ${nombre}?` : `¿Desactivar ${nombre}?`,
                text: reactivando
                    ? 'Volverá a operar con normalidad en el sistema.'
                    : 'Podrás reactivarlo más adelante desde el mismo módulo.',
                icon: reactivando ? 'question' : 'warning',
                confirmText: reactivando ? 'Reactivar' : 'Desactivar',
            });
            if (!result.isConfirmed) return;

            try {
                await window.axios.post(btn.dataset.url);
                toast.success(reactivando ? 'Se reactivó correctamente.' : 'Se desactivó correctamente.');
                setTimeout(() => window.location.reload(), 900);
            } catch (error) {
                toast.error(reactivando ? 'No se pudo reactivar. Intenta de nuevo.' : 'No se pudo desactivar. Intenta de nuevo.');
            }
        });
    });
}

/**
 * Formularios de crear/editar dentro de un modal (materias, cursos,
 * asignaciones, horarios...). Espera <form data-crud-form data-url="...">.
 */
export function initCrudForms() {
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
