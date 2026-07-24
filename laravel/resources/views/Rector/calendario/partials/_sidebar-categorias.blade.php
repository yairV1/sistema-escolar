{{--
    Panel lateral del calendario: botón "Hoy", mini-calendario (JS lo
    renderiza dentro de #calendarioMini) y toggles de categoría — cada
    checkbox dispara refetchEvents() vía calendar-sidebar.js. Las
    categorías vienen de EventoCategoria::activas() (server-side), no se
    duplican en JS.
--}}
<div class="calendario-sidebar__bloque">
    <button type="button" class="btn btn-primary w-100" data-calendario-hoy>
        <i class="bi bi-calendar-event"></i> Hoy
    </button>
</div>

<div class="calendario-sidebar__bloque">
    <div id="calendarioMini"></div>
</div>

<div class="calendario-sidebar__bloque">
    <h2 class="calendario-sidebar__titulo">Categorías</h2>
    <ul class="calendario-categorias" id="calendarioCategorias">
        @foreach ($categorias as $categoria)
            <li class="calendario-categoria-item" style="--categoria-color: {{ $categoria->color }};">
                <label class="calendario-categoria-toggle">
                    <input type="checkbox" checked value="{{ $categoria->id_categoria }}" data-categoria-toggle>
                    <span class="calendario-categoria-dot"></span>
                    <i class="bi {{ $categoria->icono }}"></i>
                    <span>{{ $categoria->nombre }}</span>
                </label>
            </li>
        @endforeach
    </ul>
</div>
