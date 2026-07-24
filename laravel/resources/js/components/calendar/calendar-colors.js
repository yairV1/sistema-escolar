/**
 * Resuelve la identidad visual (color/ícono) de cada evento renderizado.
 * El color ya viene resuelto desde el backend (EventoCategoria.color); acá
 * solo se traduce a la variable CSS --categoria-color que usa
 * _calendario.scss (mismo lenguaje visual que _horarios.scss: borde
 * izquierdo + punto + degradado sutil).
 */
export function aplicarColorEvento(info) {
    const color = info.event.backgroundColor || info.event.extendedProps.color;
    if (color) {
        info.el.style.setProperty('--categoria-color', color);
    }
    info.el.classList.add('calendario-evento');

    const icono = info.event.extendedProps.icono;
    const contenedorTitulo = info.el.querySelector('.fc-event-title, .fc-list-event-title');
    if (icono && contenedorTitulo && !contenedorTitulo.querySelector('.calendario-evento__icono')) {
        const iconoEl = document.createElement('i');
        iconoEl.className = `bi ${icono} calendario-evento__icono`;
        contenedorTitulo.prepend(iconoEl);
    }
}
