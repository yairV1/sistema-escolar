import { Modal } from 'bootstrap';
import { clearFormErrors } from '../forms/validation';
import { diaAnteriorLocal } from './calendar-utils';

/**
 * Modal único de crear/editar evento. La *submission* en sí sigue usando
 * initCrudForms() de listActions.js sin cambios (no hay pipeline AJAX
 * nuevo) — este módulo solo decide título/data-url/campos dinámicos y
 * pre-rellena.
 */
export function initModalEvento() {
    const modalEl = document.getElementById('modalEvento');
    if (!modalEl) return; // usuario sin ninguna categoría creable: el modal ni se renderiza

    const form = modalEl.querySelector('form');

    modalEl.addEventListener('show.bs.modal', (event) => {
        if (event.relatedTarget?.hasAttribute('data-calendario-nuevo')) {
            prepararCreacion(form);
        }
    });

    modalEl.addEventListener('hidden.bs.modal', () => {
        form.reset();
        clearFormErrors(form);
        alternarCamposHora(form);
        alternarCamposRecurrencia(form);
        alternarCampoParticipantes(form);
    });

    form.querySelector('[data-evento-todo-dia]')?.addEventListener('change', () => alternarCamposHora(form));
    form.querySelector('[data-evento-tipo-recurrencia]')?.addEventListener('change', () => alternarCamposRecurrencia(form));
    form.querySelector('[data-evento-visibilidad]')?.addEventListener('change', () => alternarCampoParticipantes(form));
}

/** Callback `select` de FullCalendar (un clic simple ya cuenta como selección mínima): crea prellenando el rango elegido. */
export function crearHandlerSeleccionFecha() {
    return function (info) {
        const modalEl = document.getElementById('modalEvento');
        if (!modalEl) return;

        const form = modalEl.querySelector('form');
        prepararCreacion(form);

        form.querySelector('[name="fecha_inicio"]').value = soloFecha(info.startStr);
        form.querySelector('[name="fecha_fin"]').value = info.allDay ? diaAnteriorLocal(info.end) : soloFecha(info.endStr);

        if (!info.allDay) {
            form.querySelector('[name="todo_el_dia"]').checked = false;
            form.querySelector('[name="hora_inicio"]').value = soloHora(info.startStr);
            form.querySelector('[name="hora_fin"]').value = soloHora(info.endStr);
        }
        alternarCamposHora(form);

        Modal.getOrCreateInstance(modalEl).show();
    };
}

/** Exportado: lo usa calendar-events.js desde el botón "Editar" del panel lateral (ver crearHandlerAbrirPanel). */
export async function abrirEdicion(idEvento) {
    const modalEl = document.getElementById('modalEvento');
    if (!modalEl) return;

    const storeUrl = document.getElementById('calendarioRoot')?.dataset.storeUrl;
    const form = modalEl.querySelector('form');

    try {
        const { data } = await window.axios.get(`${storeUrl}/${idEvento}`);
        if (!data.puede_editar) return;

        prepararEdicion(form, idEvento, data.evento, data.participantes ?? []);
        Modal.getOrCreateInstance(modalEl).show();
    } catch {
        // Silencioso: si el show() falla (ej. 403 por cambio de permisos), simplemente no abre.
    }
}

function prepararCreacion(form) {
    form.reset();
    clearFormErrors(form);
    form.dataset.url = document.getElementById('calendarioRoot')?.dataset.storeUrl ?? form.dataset.url;
    document.getElementById('modalEventoTitulo').textContent = 'Nuevo evento';
    alternarCamposHora(form);
    alternarCamposRecurrencia(form);
    alternarCampoParticipantes(form);
}

function prepararEdicion(form, idEvento, evento, participantes) {
    form.reset();
    clearFormErrors(form);

    const storeUrl = document.getElementById('calendarioRoot')?.dataset.storeUrl;
    form.dataset.url = `${storeUrl}/${idEvento}`;
    document.getElementById('modalEventoTitulo').textContent = 'Editar evento';

    form.querySelector('[name="titulo"]').value = evento.titulo ?? '';
    asegurarOpcionCategoria(form.querySelector('[name="id_categoria"]'), evento.id_categoria, evento.categoria_nombre);
    form.querySelector('[name="prioridad"]').value = evento.prioridad ?? 'media';
    form.querySelector('[name="todo_el_dia"]').checked = !!evento.todo_el_dia;
    form.querySelector('[name="fecha_inicio"]').value = evento.fecha_inicio ?? '';
    form.querySelector('[name="hora_inicio"]').value = (evento.hora_inicio ?? '').slice(0, 5);
    form.querySelector('[name="fecha_fin"]').value = evento.fecha_fin ?? '';
    form.querySelector('[name="hora_fin"]').value = (evento.hora_fin ?? '').slice(0, 5);
    form.querySelector('[name="descripcion"]').value = evento.descripcion ?? '';
    form.querySelector('[name="salon"]').value = evento.salon ?? '';
    form.querySelector('[name="ubicacion"]').value = evento.ubicacion ?? '';
    form.querySelector('[name="visibilidad"]').value = evento.visibilidad ?? 'privado';
    form.querySelector('[name="tipo_recurrencia"]').value = evento.tipo_recurrencia ?? 'ninguna';
    form.querySelector('[name="intervalo_recurrencia"]').value = evento.intervalo_recurrencia ?? 1;
    form.querySelector('[name="fecha_fin_recurrencia"]').value = evento.fecha_fin_recurrencia ?? '';
    form.querySelectorAll('[name="dias_semana_recurrencia[]"]').forEach((casilla) => {
        casilla.checked = Array.isArray(evento.dias_semana_recurrencia) && evento.dias_semana_recurrencia.includes(casilla.value);
    });
    form.querySelectorAll('[name="recordatorio_minutos_antes[]"]').forEach((casilla) => {
        casilla.checked = Array.isArray(evento.recordatorio_minutos_antes) && evento.recordatorio_minutos_antes.map(String).includes(casilla.value);
    });

    const idsParticipantes = participantes.map((p) => String(p.id_usuario));
    form.querySelectorAll('[name="participantes[]"] option').forEach((opcion) => {
        opcion.selected = idsParticipantes.includes(opcion.value);
    });

    alternarCamposHora(form);
    alternarCamposRecurrencia(form);
    alternarCampoParticipantes(form);
}

function asegurarOpcionCategoria(select, idCategoria, nombreCategoria) {
    if (!select.querySelector(`option[value="${idCategoria}"]`)) {
        const opcion = document.createElement('option');
        opcion.value = idCategoria;
        opcion.textContent = nombreCategoria ?? `Categoría #${idCategoria}`;
        select.appendChild(opcion);
    }
    select.value = idCategoria;
}

function alternarCamposHora(form) {
    const todoElDia = form.querySelector('[name="todo_el_dia"]').checked;
    form.querySelectorAll('[data-evento-campo-hora]').forEach((campo) => campo.classList.toggle('d-none', todoElDia));
}

function alternarCamposRecurrencia(form) {
    const tipo = form.querySelector('[name="tipo_recurrencia"]').value;
    const contenedor = form.querySelector('[data-evento-campos-recurrencia]');
    const esRecurrente = tipo !== 'ninguna';
    contenedor.hidden = !esRecurrente;

    const diasSemana = form.querySelector('[data-evento-dias-semana]');
    diasSemana.hidden = tipo !== 'semanal';
}

function alternarCampoParticipantes(form) {
    const visibilidad = form.querySelector('[data-evento-visibilidad]')?.value;
    const contenedor = form.querySelector('[data-evento-campo-participantes]');
    if (!contenedor) return;

    contenedor.hidden = visibilidad !== 'compartido';
}

function soloFecha(valor) {
    return valor.slice(0, 10);
}

function soloHora(valor) {
    return valor.length >= 16 ? valor.slice(11, 16) : '';
}
