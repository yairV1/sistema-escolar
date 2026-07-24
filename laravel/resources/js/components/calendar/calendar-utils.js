/** Helpers genéricos y sin estado, compartidos por el resto de módulos del calendario. */

/** "2026-08-03T07:00:00" -> "2026-08-03" (FullCalendar entrega fechas con hora en fetchInfo). */
export function soloFecha(isoString) {
    return isoString.slice(0, 10);
}

/**
 * Formatea un objeto Date con sus componentes LOCALES (no toISOString(),
 * que convierte a UTC y puede correr la fecha según el huso horario) —
 * usado siempre que FullCalendar entrega un `Date` (select, drag&drop) en
 * vez del string ISO que ya trae fetchInfo.
 */
export function formatearFechaLocal(date) {
    const anio = date.getFullYear();
    const mes = String(date.getMonth() + 1).padStart(2, '0');
    const dia = String(date.getDate()).padStart(2, '0');

    return `${anio}-${mes}-${dia}`;
}

export function formatearHoraLocal(date) {
    const horas = String(date.getHours()).padStart(2, '0');
    const minutos = String(date.getMinutes()).padStart(2, '0');

    return `${horas}:${minutos}`;
}

/** Un día antes de `date`, en componentes locales — para convertir el `end` exclusivo de FullCalendar en fecha_fin inclusiva. */
export function diaAnteriorLocal(date) {
    const anterior = new Date(date.getTime());
    anterior.setDate(anterior.getDate() - 1);

    return formatearFechaLocal(anterior);
}

/** Agrupa llamadas seguidas en una sola, `wait` ms después de la última — usado por calendar-realtime.js para colapsar ráfagas de eventos en un solo refetchEvents(). */
export function debounce(fn, wait) {
    let temporizador;

    return (...args) => {
        clearTimeout(temporizador);
        temporizador = setTimeout(() => fn(...args), wait);
    };
}
