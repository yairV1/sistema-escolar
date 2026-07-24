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
 *
 * El título/texto/ícono/etiqueta de confirmación y el mensaje de éxito se
 * pueden personalizar con data-confirm-title / data-confirm-text /
 * data-confirm-icon / data-confirm-btn / data-mensaje-exito (todos
 * opcionales); si no se indican, se arma el texto genérico de siempre a
 * partir de data-nombre. Así el botón "Eliminar clase" del modal de horario
 * puede pedir una confirmación específica sin tocar el resto de los usos.
 */
export function initEstadoToggle() {
    document.querySelectorAll('[data-desactivar], [data-activar]').forEach((btn) => {
        const reactivando = btn.hasAttribute('data-activar');

        btn.addEventListener('click', async () => {
            const nombre = btn.dataset.nombre;

            const result = await confirmAction({
                title: btn.dataset.confirmTitle || (reactivando ? `¿Reactivar ${nombre}?` : `¿Desactivar ${nombre}?`),
                text: btn.dataset.confirmText || (reactivando
                    ? 'Volverá a operar con normalidad en el sistema.'
                    : 'Podrás reactivarlo más adelante desde el mismo módulo.'),
                icon: btn.dataset.confirmIcon || (reactivando ? 'question' : 'warning'),
                confirmText: btn.dataset.confirmBtn || (reactivando ? 'Reactivar' : 'Desactivar'),
            });
            if (!result.isConfirmed) return;

            try {
                await window.axios.post(btn.dataset.url);
                toast.success(btn.dataset.mensajeExito || (reactivando ? 'Se reactivó correctamente.' : 'Se desactivó correctamente.'));
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
 *
 * Caso especial (horarios): si el backend responde 409 con
 * `conflicto_id` + `conflicto_desactivar_url`, significa que ese curso ya
 * tiene otra materia en ese mismo horario. En vez de solo mostrar el error,
 * se ofrece reemplazarla: si el usuario confirma, se desactiva el horario
 * que chocaba y se reintenta el guardado original.
 */
export function initCrudForms() {
    document.querySelectorAll('[data-crud-form]').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            clearFormErrors(form);
            await enviarCrudForm(form);
        });
    });
}

async function enviarCrudForm(form) {
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
        } else if (response?.status === 409 && response.data.conflicto_desactivar_url) {
            submitBtn?.classList.remove('btn-loading');
            submitBtn && (submitBtn.disabled = false);

            const result = await confirmAction({
                title: 'Ya hay una clase a esa hora',
                text: response.data.message,
                icon: 'warning',
                confirmText: 'Reemplazar',
            });

            if (result.isConfirmed) {
                try {
                    await window.axios.post(response.data.conflicto_desactivar_url);
                    await enviarCrudForm(form);
                } catch {
                    toast.error('No se pudo reemplazar el horario existente.');
                }
            }
            return;
        } else if (response?.status === 409) {
            toast.error(response.data.message);
        } else {
            toast.error('No se pudo guardar. Intenta de nuevo.');
        }
    } finally {
        submitBtn?.classList.remove('btn-loading');
        submitBtn && (submitBtn.disabled = false);
    }
}

/**
 * Click en una celda vacía de la grilla de horarios: abre #modalNuevoHorario
 * con el día y la hora de esa celda ya precargados (Bootstrap expone el
 * elemento que disparó el modal como `event.relatedTarget`). Si el modal se
 * abrió desde el botón "Agregar horario" (sin esos data-attributes), el
 * formulario queda con los valores por defecto de siempre.
 */
export function initModalNuevoHorario() {
    const modal = document.getElementById('modalNuevoHorario');
    if (!modal) return;

    modal.addEventListener('show.bs.modal', (event) => {
        const trigger = event.relatedTarget;
        const form = modal.querySelector('form');
        if (!trigger || !form) return;

        const diaSelect = form.querySelector('[name="dia_semana"]');
        const horaInicio = form.querySelector('[name="hora_inicio"]');
        const horaFin = form.querySelector('[name="hora_fin"]');

        if (trigger.dataset.dia && diaSelect) {
            diaSelect.value = trigger.dataset.dia;
        }
        if (trigger.dataset.horaInicio && horaInicio) {
            horaInicio.value = trigger.dataset.horaInicio;
            if (horaFin) {
                const [h, m] = trigger.dataset.horaInicio.split(':').map(Number);
                horaFin.value = `${String((h + 1) % 24).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
            }
        }
    });

    modal.addEventListener('hidden.bs.modal', () => {
        const form = modal.querySelector('form');
        if (!form) return;
        form.reset();
        clearFormErrors(form);
    });
}
