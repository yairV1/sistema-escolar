import { fetchFeed } from './calendar-api';
import { mostrarEstadoVacio, mostrarEstadoError } from './calendar-ui';
import { soloFecha } from './calendar-utils';
import { getCategoriasSeleccionadas } from './calendar-sidebar';
import { getCursoSeleccionado } from './calendar-filters';

/**
 * Cachea por rango+filtros en memoria (Map, vive mientras dure la sesión de
 * la página) para no repetir un fetch cuando el usuario vuelve a un rango
 * ya visto con los mismos filtros — cambiar de categoría ya cambia la
 * clave, así que no hace falta invalidar nada explícitamente.
 */
const cacheRangos = new Map();

/** Fuente de eventos "function" de FullCalendar: se le pasa directo a `events`. */
export function crearFuenteEventos(feedUrl) {
    return function (fetchInfo, successCallback, failureCallback) {
        const desde = soloFecha(fetchInfo.startStr);
        const hasta = soloFecha(fetchInfo.endStr);
        const categorias = getCategoriasSeleccionadas();
        const curso = getCursoSeleccionado();
        const clave = `${desde}|${hasta}|${categorias.join(',')}|${curso || ''}`;

        mostrarEstadoError(false);

        if (cacheRangos.has(clave)) {
            const eventos = cacheRangos.get(clave);
            mostrarEstadoVacio(eventos.length === 0);
            successCallback(eventos);
            return;
        }

        fetchFeed(feedUrl, { desde, hasta, categorias, curso })
            .then((eventos) => {
                cacheRangos.set(clave, eventos);
                mostrarEstadoVacio(eventos.length === 0);
                successCallback(eventos);
            })
            .catch((error) => {
                mostrarEstadoVacio(false);
                mostrarEstadoError(true);
                failureCallback(error);
            });
    };
}
