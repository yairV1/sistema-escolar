import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import esLocale from '@fullcalendar/core/locales/es';

/** IDs (string) de las categorías con el checkbox activo — leído por calendar-feed.js en cada fetch. */
export function getCategoriasSeleccionadas() {
    return Array.from(document.querySelectorAll('[data-categoria-toggle]:checked')).map((el) => el.value);
}

/** Botón "Hoy", toggles de categoría y mini-calendario de navegación. */
export function initSidebarCalendario(calendarPrincipal) {
    document.querySelector('[data-calendario-hoy]')?.addEventListener('click', () => {
        calendarPrincipal.today();
    });

    document.querySelectorAll('[data-categoria-toggle]').forEach((checkbox) => {
        checkbox.addEventListener('change', () => calendarPrincipal.refetchEvents());
    });

    crearMiniCalendario(calendarPrincipal);
}

function crearMiniCalendario(calendarPrincipal) {
    const el = document.getElementById('calendarioMini');
    if (!el) return;

    const mini = new Calendar(el, {
        plugins: [dayGridPlugin, interactionPlugin],
        locale: esLocale,
        initialView: 'dayGridMonth',
        headerToolbar: { left: 'prev', center: 'title', right: 'next' },
        height: 'auto',
        firstDay: 1,
        fixedWeekCount: false,
        dateClick(info) {
            calendarPrincipal.gotoDate(info.dateStr);
        },
    });

    mini.render();
}
