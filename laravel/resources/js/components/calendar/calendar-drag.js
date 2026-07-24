import { toast } from '../alerts/toast';
import { formatearFechaLocal, formatearHoraLocal, diaAnteriorLocal } from './calendar-utils';

/**
 * eventDrop/eventResize de FullCalendar. Única ruta del módulo sin reload
 * (POST /calendario/{evento}/mover): se confía en el propio render
 * optimista de FullCalendar y solo se revierte si el servidor rechaza. Un
 * evento recurrente arrastrado nunca toca la plantilla — mover() decide
 * eso del lado del servidor según `fecha_original` (ver EventoService).
 */
export function crearHandlerMover() {
    return function (info) {
        const { tipo, id_evento: idEvento, fecha_original: fechaOriginal } = info.event.extendedProps;

        if (tipo !== 'evento') {
            info.revert();
            return;
        }

        const posicionAnterior = extraerPosicion(info.oldEvent);
        const nuevaPosicion = extraerPosicion(info.event);

        enviarMover(idEvento, fechaOriginal, nuevaPosicion)
            .then(() => {
                toast.accion('Evento movido.', {
                    etiqueta: 'Deshacer',
                    onClick: () => deshacer(idEvento, fechaOriginal, posicionAnterior, info),
                });
            })
            .catch(() => {
                toast.error('No se pudo mover el evento.');
                info.revert();
            });
    };
}

function deshacer(idEvento, fechaOriginal, posicionAnterior, info) {
    enviarMover(idEvento, fechaOriginal, posicionAnterior)
        .then(() => {
            toast.info('Movimiento deshecho.');
            info.view.calendar.refetchEvents();
        })
        .catch(() => toast.error('No se pudo deshacer el movimiento.'));
}

function extraerPosicion(evento) {
    const allDay = evento.allDay;
    const inicio = evento.start;
    const fin = evento.end ?? evento.start;

    return {
        fecha_inicio: formatearFechaLocal(inicio),
        hora_inicio: allDay ? null : formatearHoraLocal(inicio),
        fecha_fin: allDay ? diaAnteriorLocal(fin) : formatearFechaLocal(fin),
        hora_fin: allDay ? null : formatearHoraLocal(fin),
    };
}

function enviarMover(idEvento, fechaOriginal, posicion) {
    const storeUrl = document.getElementById('calendarioRoot')?.dataset.storeUrl;

    return window.axios.post(`${storeUrl}/${idEvento}/mover`, {
        fecha_ocurrencia: fechaOriginal,
        ...posicion,
    });
}
