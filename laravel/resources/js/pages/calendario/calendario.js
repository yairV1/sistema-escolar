import { initCrudForms } from '../../components/listActions';
import { crearCalendarioPrincipal } from '../../components/calendar/calendar-ui';
import { crearFuenteEventos } from '../../components/calendar/calendar-feed';
import { initSidebarCalendario } from '../../components/calendar/calendar-sidebar';
import { initFiltrosCalendario } from '../../components/calendar/calendar-filters';
import { initModalEvento, crearHandlerSeleccionFecha } from '../../components/calendar/calendar-modal';
import { initPanelEvento, crearHandlerAbrirPanel } from '../../components/calendar/calendar-events';
import { crearHandlerMover } from '../../components/calendar/calendar-drag';
import { initTiempoReal } from '../../components/calendar/calendar-realtime';
import { initExportarCalendario } from '../../components/calendar/calendar-export';

document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('calendarioRoot');
    if (!root) return;

    const calendar = crearCalendarioPrincipal(root, crearFuenteEventos(root.dataset.feedUrl), {
        onSeleccionFecha: crearHandlerSeleccionFecha(),
        onClicEvento: crearHandlerAbrirPanel(),
        onEventoMovido: crearHandlerMover(),
    });
    calendar.render();

    initSidebarCalendario(calendar);
    initFiltrosCalendario(calendar);
    initModalEvento();
    initPanelEvento();
    initTiempoReal(calendar);
    initExportarCalendario();
    initCrudForms();

    document.querySelector('[data-calendario-reintentar]')?.addEventListener('click', () => {
        calendar.refetchEvents();
    });
});
