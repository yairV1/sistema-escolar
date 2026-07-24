{{--
    Filtro por curso — el backend ya soporta filtrar el feed por `curso`
    desde Fase 1; esto solo agrega la UI. Materia/docente quedan fuera de
    esta fase: el feed no tiene ese filtro implementado todavía.
--}}
<div class="calendario-sidebar__bloque">
    <h2 class="calendario-sidebar__titulo">Filtrar por curso</h2>
    <select class="form-select form-select-sm" id="calendarioFiltroCurso">
        <option value="">Todos los cursos</option>
        @foreach ($cursosParaFiltro as $curso)
            <option value="{{ $curso->id_curso }}">{{ $curso->nombre_curso }} ({{ $curso->anio_lectivo }})</option>
        @endforeach
    </select>
</div>
