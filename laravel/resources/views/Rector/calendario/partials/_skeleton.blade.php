{{-- Estado de carga mientras FullCalendar hace el primer fetch. --}}
<div class="calendario-skeleton" aria-hidden="true">
    <div class="calendario-skeleton__toolbar">
        <span class="calendario-skeleton__bloque" style="width: 7rem;"></span>
        <span class="calendario-skeleton__bloque" style="width: 10rem;"></span>
        <span class="calendario-skeleton__bloque" style="width: 5rem;"></span>
    </div>
    <div class="calendario-skeleton__grid">
        @for ($i = 0; $i < 35; $i++)
            <div class="calendario-skeleton__cell"></div>
        @endfor
    </div>
</div>
