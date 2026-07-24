import { Offcanvas } from 'bootstrap';
import { toast } from '../alerts/toast';
import { confirmAction } from '../alerts/sweetAlert';
import { abrirEdicion } from './calendar-modal';

const ETIQUETAS_PRIORIDAD = { baja: 'Baja', media: 'Media', alta: 'Alta', urgente: 'Urgente' };
const ETIQUETAS_ESTADO = { pendiente: 'Pendiente', completado: 'Completado', cancelado: 'Cancelado' };

let idEventoActual = null;

/**
 * Wiring de las acciones del panel (botones fijos en el HTML + los
 * formularios de comentario/adjunto). El contenido variable (detalle,
 * listas) lo pintan las funciones de render llamadas desde
 * crearHandlerAbrirPanel().
 */
export function initPanelEvento() {
    const panelEl = document.getElementById('panelEvento');
    if (!panelEl) return;

    panelEl.querySelector('[data-panel-editar]')?.addEventListener('click', () => {
        if (idEventoActual) abrirEdicion(idEventoActual);
    });

    panelEl.querySelector('[data-panel-duplicar]')?.addEventListener('click', duplicar);

    panelEl.querySelectorAll('[data-panel-estado]').forEach((item) => {
        item.addEventListener('click', (evento) => {
            evento.preventDefault();
            cambiarEstado(item.dataset.panelEstado);
        });
    });

    panelEl.querySelector('[data-panel-eliminar]')?.addEventListener('click', eliminar);

    panelEl.querySelector('#panelEventoFormComentario')?.addEventListener('submit', enviarComentario);
    panelEl.querySelector('#panelEventoFormAdjunto [name="adjunto"]')?.addEventListener('change', subirAdjunto);
}

/** Callback `eventClick` de FullCalendar: TODO clic (propio, ajeno visible, o derivado) abre el panel. */
export function crearHandlerAbrirPanel() {
    return function (info) {
        const panelEl = document.getElementById('panelEvento');
        if (!panelEl) return;

        const { tipo } = info.event.extendedProps;

        if (tipo === 'evento') {
            idEventoActual = info.event.extendedProps.id_evento;
            abrirDetalleEvento(idEventoActual);
        } else {
            idEventoActual = null;
            abrirDetalleDerivado(info.event);
        }

        Offcanvas.getOrCreateInstance(panelEl).show();
    };
}

async function abrirDetalleEvento(idEvento) {
    alternarModo('evento');
    alternarCargando(true);

    try {
        const { data } = await window.axios.get(`${storeUrl()}/${idEvento}`);
        renderizarEvento(data);
    } catch {
        toast.error('No se pudo cargar el detalle del evento.');
    } finally {
        alternarCargando(false);
    }
}

function abrirDetalleDerivado(evento) {
    alternarModo('derivado');
    alternarCargando(false);

    document.getElementById('panelEventoTitulo').textContent = evento.title;
    mostrarCategoria(evento.extendedProps.categoria_nombre);

    const filas = [
        ['Cuándo', formatearRangoEvento(evento.start, evento.end, evento.allDay)],
        ['Curso', evento.extendedProps.curso],
        ['Materia', evento.extendedProps.materia],
        ['Docente', evento.extendedProps.docente],
        ['Salón', evento.extendedProps.salon],
        ['Tipo de actividad', evento.extendedProps.tipo_actividad],
    ].filter(([, valor]) => valor);

    pintarDl('panelEventoDetalleSoloLectura', filas);
}

function renderizarEvento(data) {
    const { evento, participantes, adjuntos, comentarios, historial, puede_editar: puedeEditar, puede_eliminar: puedeEliminar } = data;

    document.getElementById('panelEventoTitulo').textContent = evento.titulo;
    mostrarCategoria(evento.categoria_nombre);

    document.querySelector('[data-panel-editar]')?.classList.toggle('d-none', !puedeEditar);
    document.querySelector('[data-panel-duplicar]')?.classList.toggle('d-none', !puedeEditar);
    document.querySelector('[data-panel-estado-wrapper]')?.classList.toggle('d-none', !puedeEditar);
    document.querySelector('[data-panel-eliminar]')?.classList.toggle('d-none', !puedeEliminar);

    const filas = [
        ['Cuándo', formatearRangoTexto(evento)],
        ['Prioridad', ETIQUETAS_PRIORIDAD[evento.prioridad] ?? evento.prioridad],
        ['Estado', ETIQUETAS_ESTADO[evento.estado] ?? evento.estado],
        ['Descripción', evento.descripcion],
        ['Salón', evento.salon],
        ['Ubicación', evento.ubicacion],
        ['Creado por', evento.creador_nombre],
    ].filter(([, valor]) => valor);

    pintarDl('panelEventoDetalle', filas);

    pintarLista('panelEventoParticipantes', participantes, (p) => `${escaparHtml(p.nombre)} <span class="text-secondary">(${p.rol_participacion})</span>`, 'Sin participantes.');

    pintarLista('panelEventoAdjuntos', adjuntos, (a) => `
        <a href="${a.url}" target="_blank" rel="noopener">${escaparHtml(a.nombre_original)}</a>
        <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-2" data-eliminar-adjunto="${a.id_adjunto}">
            <i class="bi bi-trash"></i>
        </button>
    `, 'Sin adjuntos.');

    pintarLista('panelEventoComentarios', comentarios, (c) => `
        <strong>${escaparHtml(c.autor)}</strong> <span class="text-secondary">${c.fecha}</span><br>
        ${escaparHtml(c.comentario)}
        ${c.es_propio || puedeEditar ? `<button type="button" class="btn btn-sm btn-link text-danger p-0 ms-2" data-eliminar-comentario="${c.id_comentario}"><i class="bi bi-trash"></i></button>` : ''}
    `, 'Sin comentarios todavía.');

    pintarLista('panelEventoHistorial', historial, (h) => `${ETIQUETAS_ACCION[h.accion] ?? h.accion} — ${h.fecha}`, 'Sin actividad registrada.');

    document.getElementById('panelEventoFormAdjunto')?.classList.toggle('d-none', !puedeEditar);
    document.getElementById('panelEventoFormComentario')?.classList.remove('d-none');

    document.querySelectorAll('[data-eliminar-adjunto]').forEach((btn) => btn.addEventListener('click', () => eliminarAdjunto(btn.dataset.eliminarAdjunto)));
    document.querySelectorAll('[data-eliminar-comentario]').forEach((btn) => btn.addEventListener('click', () => eliminarComentario(btn.dataset.eliminarComentario)));
}

const ETIQUETAS_ACCION = {
    creado: 'Creado',
    actualizado: 'Actualizado',
    estado_cambiado: 'Estado cambiado',
    desactivado: 'Desactivado',
    activado: 'Reactivado',
    movido: 'Movido',
};

async function duplicar() {
    if (!idEventoActual) return;

    try {
        const { data } = await window.axios.post(`${storeUrl()}/${idEventoActual}/duplicar`);
        toast.success(data.message);
        setTimeout(() => window.location.reload(), 900);
    } catch {
        toast.error('No se pudo duplicar el evento.');
    }
}

async function cambiarEstado(nuevoEstado) {
    if (!idEventoActual) return;

    try {
        const { data } = await window.axios.post(`${storeUrl()}/${idEventoActual}/estado`, { estado: nuevoEstado });
        toast.success(data.message);
        setTimeout(() => window.location.reload(), 900);
    } catch {
        toast.error('No se pudo cambiar el estado.');
    }
}

async function eliminar() {
    if (!idEventoActual) return;

    const resultado = await confirmAction({
        title: '¿Eliminar este evento?',
        text: 'Podrás reactivarlo más adelante desde tu calendario.',
        confirmText: 'Eliminar',
    });
    if (!resultado.isConfirmed) return;

    try {
        const { data } = await window.axios.post(`${storeUrl()}/${idEventoActual}/desactivar`);
        toast.success(data.message);
        setTimeout(() => window.location.reload(), 900);
    } catch {
        toast.error('No se pudo eliminar el evento.');
    }
}

async function enviarComentario(evento) {
    evento.preventDefault();
    if (!idEventoActual) return;

    const input = evento.target.querySelector('[name="comentario"]');
    const texto = input.value.trim();
    if (!texto) return;

    try {
        await window.axios.post(`${storeUrl()}/${idEventoActual}/comentarios`, { comentario: texto });
        input.value = '';
        await abrirDetalleEvento(idEventoActual);
    } catch {
        toast.error('No se pudo enviar el comentario.');
    }
}

async function eliminarComentario(idComentario) {
    const resultado = await confirmAction({ title: '¿Eliminar este comentario?', confirmText: 'Eliminar' });
    if (!resultado.isConfirmed) return;

    try {
        await window.axios.post(`${document.getElementById('calendarioRoot')?.dataset.storeUrl}/comentarios/${idComentario}/eliminar`);
        await abrirDetalleEvento(idEventoActual);
    } catch {
        toast.error('No se pudo eliminar el comentario.');
    }
}

async function subirAdjunto(evento) {
    if (!idEventoActual) return;

    const archivo = evento.target.files[0];
    if (!archivo) return;

    const formData = new FormData();
    formData.append('adjunto', archivo);

    try {
        await window.axios.post(`${storeUrl()}/${idEventoActual}/adjuntos`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        toast.success('Archivo adjuntado.');
        evento.target.value = '';
        await abrirDetalleEvento(idEventoActual);
    } catch (error) {
        toast.error(error.response?.data?.errors?.adjunto?.[0] ?? 'No se pudo adjuntar el archivo.');
    }
}

async function eliminarAdjunto(idAdjunto) {
    const resultado = await confirmAction({ title: '¿Eliminar este adjunto?', confirmText: 'Eliminar' });
    if (!resultado.isConfirmed) return;

    try {
        await window.axios.post(`${document.getElementById('calendarioRoot')?.dataset.storeUrl}/adjuntos/${idAdjunto}/eliminar`);
        await abrirDetalleEvento(idEventoActual);
    } catch {
        toast.error('No se pudo eliminar el adjunto.');
    }
}

function alternarModo(modo) {
    document.getElementById('panelEventoContenido')?.classList.toggle('d-none', modo !== 'evento');
    document.getElementById('panelEventoSoloLectura')?.classList.toggle('d-none', modo !== 'derivado');
}

function alternarCargando(cargando) {
    document.getElementById('panelEventoCargando')?.classList.toggle('d-none', !cargando);
    if (cargando) {
        document.getElementById('panelEventoContenido')?.classList.add('d-none');
    }
}

function mostrarCategoria(nombre) {
    const badge = document.getElementById('panelEventoCategoria');
    if (!badge) return;
    badge.textContent = nombre ?? '';
    badge.classList.toggle('d-none', !nombre);
}

function pintarDl(idContenedor, filas) {
    const contenedor = document.getElementById(idContenedor);
    if (!contenedor) return;
    contenedor.innerHTML = filas.map(([etiqueta, valor]) => `<dt>${escaparHtml(etiqueta)}</dt><dd>${escaparHtml(String(valor))}</dd>`).join('');
}

function pintarLista(idContenedor, items, plantilla, mensajeVacio) {
    const contenedor = document.getElementById(idContenedor);
    if (!contenedor) return;

    if (!items || items.length === 0) {
        contenedor.innerHTML = `<li class="text-secondary">${mensajeVacio}</li>`;
        return;
    }

    contenedor.innerHTML = items.map((item) => `<li class="mb-2">${plantilla(item)}</li>`).join('');
}

function formatearRangoTexto(evento) {
    if (evento.todo_el_dia) {
        return evento.fecha_inicio === evento.fecha_fin
            ? `${evento.fecha_inicio} (todo el día)`
            : `${evento.fecha_inicio} — ${evento.fecha_fin} (todo el día)`;
    }

    const inicio = `${evento.fecha_inicio} ${(evento.hora_inicio ?? '').slice(0, 5)}`;
    const fin = evento.fecha_fin === evento.fecha_inicio
        ? (evento.hora_fin ?? '').slice(0, 5)
        : `${evento.fecha_fin} ${(evento.hora_fin ?? '').slice(0, 5)}`;

    return `${inicio} — ${fin}`;
}

function formatearRangoEvento(inicio, fin, todoElDia) {
    const opciones = todoElDia
        ? { day: 'numeric', month: 'short' }
        : { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' };

    const textoInicio = inicio.toLocaleString('es-CO', opciones);
    const textoFin = fin ? fin.toLocaleString('es-CO', opciones) : null;

    return textoFin && textoFin !== textoInicio ? `${textoInicio} — ${textoFin}` : textoInicio;
}

function storeUrl() {
    return document.getElementById('calendarioRoot')?.dataset.storeUrl;
}

function escaparHtml(texto) {
    const div = document.createElement('div');
    div.textContent = texto ?? '';
    return div.innerHTML;
}
