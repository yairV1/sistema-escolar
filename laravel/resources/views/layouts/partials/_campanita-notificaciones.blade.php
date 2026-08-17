<div class="campanita-notificaciones" id="campanitaNotificaciones"
     data-index-url="{{ route('notificaciones.index') }}"
     data-leer-todas-url="{{ route('notificaciones.leer-todas') }}"
     data-calendario-url="{{ route('calendario.index') }}"
     data-empty-text="{{ __('No tienes notificaciones.') }}">
    <button type="button"
            class="icon-toolbar-btn"
            id="campanitaToggle"
            aria-haspopup="true"
            aria-expanded="false"
            aria-label="{{ __('Notificaciones') }}">
        <i class="bi bi-bell"></i>
        <span class="campanita-badge d-none" id="campanitaBadge">0</span>
    </button>

    <div class="campanita-panel" id="campanitaPanel">
        <div class="campanita-header">
            <span>{{ __('Notificaciones') }}</span>
        </div>
        <div class="campanita-lista" id="campanitaLista">
            <p class="campanita-vacio">{{ __('No tienes notificaciones.') }}</p>
        </div>
    </div>
</div>
