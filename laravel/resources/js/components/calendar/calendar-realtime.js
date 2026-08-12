// Efecto secundario: crea window.Echo. Solo se importa desde acá (esta
// página) — el resto del panel nunca abre un WebSocket.
import '../../core/echo';
import { debounce } from './calendar-utils';

const EVENTOS_CALENDARIO = ['.evento.creado', '.evento.actualizado', '.evento.movido', '.evento.eliminado'];

/**
 * Se suscribe al canal propio, al institucional (eventos públicos sin
 * curso), y a un canal por cada curso en el alcance del usuario
 * (data-cursos-ids, ya calculado por VisibilidadCalendarioService para el
 * filtro — se reutiliza, no se duplica la consulta). El payload que llega
 * no tiene detalle (ver EventoBroadcastEvent) — cada mensaje solo dispara
 * un refetchEvents() debounced, que ya resuelve visibilidad/recurrencia
 * correctamente en el servidor. `onCambio` es opcional — hoy lo usa el
 * panel de próximos eventos para recargarse con el mismo debounce, sin
 * suscribirse otra vez a los mismos canales.
 */
export function initTiempoReal(calendar, onCambio) {
    if (!window.Echo) return;

    const root = document.getElementById('calendarioRoot');
    if (!root) return;

    const idUsuario = root.dataset.usuarioId;
    const cursosIds = (root.dataset.cursosIds || '').split(',').filter(Boolean);

    const refrescar = debounce(() => {
        calendar.refetchEvents();
        onCambio?.();
    }, 400);

    if (idUsuario) {
        suscribirCanal(`App.Modules.Auth.Models.Usuario.${idUsuario}`, refrescar);
    }
    suscribirCanal('calendario.institucional', refrescar);
    cursosIds.forEach((idCurso) => suscribirCanal(`calendario.curso.${idCurso}`, refrescar));
}

function suscribirCanal(nombreCanal, callback) {
    const canal = window.Echo.private(nombreCanal);
    EVENTOS_CALENDARIO.forEach((nombreEvento) => canal.listen(nombreEvento, callback));
}
