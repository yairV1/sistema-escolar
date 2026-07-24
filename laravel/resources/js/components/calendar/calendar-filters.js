/**
 * Panel de filtros del calendario. Fase 2 solo trae filtro por curso (el
 * único que el backend soporta hoy — materia/docente quedan para cuando
 * el feed tenga esos filtros implementados).
 */
export function getCursoSeleccionado() {
    return document.getElementById('calendarioFiltroCurso')?.value || null;
}

export function initFiltrosCalendario(calendarPrincipal) {
    document.getElementById('calendarioFiltroCurso')?.addEventListener('change', () => {
        calendarPrincipal.refetchEvents();
    });
}
