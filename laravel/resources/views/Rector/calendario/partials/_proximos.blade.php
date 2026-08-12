{{--
    Panel derecho: lista de próximos eventos. Se pinta por completo en JS
    (calendar-upcoming.js) con su propia consulta al feed — mismo patrón que
    #calendarioMini, que también es un contenedor vacío poblado por JS —
    porque necesita un rango de fechas independiente del mes que esté
    navegando el calendario principal.
--}}
<aside class="calendario-sidebar__bloque calendario-proximos" id="calendarioProximos">
    <h2 class="calendario-sidebar__titulo">Próximos eventos</h2>
    <div class="calendario-proximos-lista" id="calendarioProximosLista"></div>
</aside>
