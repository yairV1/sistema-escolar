import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';
import esLocale from '@fullcalendar/core/locales/es';
import { aplicarColorEvento } from './calendar-colors';

let primeraCargaCompleta = false;

/**
 * Instancia principal de FullCalendar (mes/semana/día/lista). `editable:true`
 * a nivel de calendario habilita drag&drop/resize, pero cada evento sigue
 * respetando su propio flag `editable` calculado en el backend (horarios/
 * actividades derivados, o eventos sin permiso, simplemente no se pueden
 * arrastrar — FullCalendar ya lo resuelve por evento). El skeleton solo se
 * muestra en la primera carga; las navegaciones siguientes no vuelven a
 * tapar el calendario para que la experiencia se sienta fluida.
 */
export function crearCalendarioPrincipal(root, eventSource, handlers = {}) {
    return new Calendar(root, {
        plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
        locale: esLocale,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
        },
        buttonText: { today: 'Hoy', month: 'Mes', week: 'Semana', day: 'Día', list: 'Lista' },
        height: 'auto',
        nowIndicator: true,
        firstDay: 1,
        editable: true,
        selectable: true,
        selectMirror: true,
        dayMaxEvents: true,
        events: eventSource,
        eventDidMount: aplicarColorEvento,
        loading: alternarCarga,
        // Con selectable:true un solo clic ya dispara `select` (con una
        // selección de duración mínima) igual que un arrastre — no hace
        // falta además `dateClick`, evita manejar el mismo gesto dos veces.
        select: handlers.onSeleccionFecha,
        eventClick: handlers.onClicEvento,
        eventDrop: handlers.onEventoMovido,
        eventResize: handlers.onEventoMovido,
    });
}

function alternarCarga(cargando) {
    const skeleton = document.getElementById('calendarioSkeleton');
    const root = document.getElementById('calendarioRoot');
    if (!skeleton || !root) return;

    if (!primeraCargaCompleta) {
        if (cargando) {
            skeleton.classList.remove('d-none');
            root.classList.add('d-none');
        } else {
            skeleton.classList.add('d-none');
            root.classList.remove('d-none');
            primeraCargaCompleta = true;
        }
        return;
    }

    root.classList.toggle('calendario-cargando', cargando);
}

export function mostrarEstadoVacio(mostrar) {
    document.getElementById('calendarioVacio')?.classList.toggle('d-none', !mostrar);
}

export function mostrarEstadoError(mostrar) {
    document.getElementById('calendarioError')?.classList.toggle('d-none', !mostrar);
}
