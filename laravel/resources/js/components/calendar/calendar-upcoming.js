import { fetchFeed } from './calendar-api';
import { formatearFechaLocal } from './calendar-utils';
import { abrirPanelDesdeItem } from './calendar-events';

const MAX_ITEMS = 8;
const DIAS_ADELANTE = 60;

/**
 * Panel de "próximos eventos": pide su propio rango [hoy, hoy+60 días] al
 * mismo feed que usa FullCalendar, en vez de leer del eventsSet del
 * calendario principal — ese solo cubre el rango visible (el mes que se
 * esté navegando), no necesariamente lo que sigue desde hoy.
 * Devuelve la función de recarga para conectarla al realtime.
 */
export function initPanelProximos() {
    const contenedor = document.getElementById('calendarioProximosLista');
    const root = document.getElementById('calendarioRoot');
    const feedUrl = root?.dataset.feedUrl;

    if (!contenedor || !feedUrl) {
        return () => {};
    }

    async function cargar() {
        const hoy = new Date();
        const limite = new Date();
        limite.setDate(limite.getDate() + DIAS_ADELANTE);

        try {
            const items = await fetchFeed(feedUrl, {
                desde: formatearFechaLocal(hoy),
                hasta: formatearFechaLocal(limite),
            });

            const ahora = new Date();
            const proximos = items.filter((item) => new Date(item.start) >= ahora).slice(0, MAX_ITEMS);
            pintar(proximos);
        } catch {
            contenedor.innerHTML = '<p class="calendario-proximos-vacio">No se pudo cargar.</p>';
        }
    }

    function pintar(items) {
        if (items.length === 0) {
            contenedor.innerHTML = '<p class="calendario-proximos-vacio">Nada programado próximamente.</p>';
            return;
        }

        contenedor.innerHTML = items.map((item, indice) => `
            <button type="button" class="calendario-proximo-item" style="--categoria-color: ${item.color};" data-indice="${indice}">
                <span class="calendario-categoria-dot"></span>
                <span class="calendario-proximo-info">
                    <span class="calendario-proximo-fecha">${formatearFechaItem(item)}</span>
                    <span class="calendario-proximo-titulo">${escaparHtml(item.title)}</span>
                </span>
            </button>
        `).join('');

        contenedor.querySelectorAll('[data-indice]').forEach((boton) => {
            boton.addEventListener('click', () => abrirPanelDesdeItem(items[Number(boton.dataset.indice)]));
        });
    }

    cargar();

    return cargar;
}

function formatearFechaItem(item) {
    const fecha = new Date(item.start);
    const opciones = item.allDay
        ? { day: 'numeric', month: 'short' }
        : { day: 'numeric', month: 'short', hour: 'numeric', minute: '2-digit' };

    return fecha.toLocaleString('es-CO', opciones).toUpperCase();
}

function escaparHtml(texto) {
    const div = document.createElement('div');
    div.textContent = texto ?? '';
    return div.innerHTML;
}
