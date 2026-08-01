@props([
    'icon' => 'fas fa-inbox',
    'message' => 'No hay datos para mostrar todavía.',
    'actionLabel' => null,
    'actionUrl' => null,
])

{{--
    Componente reutilizable de estado vacío (docs/arquitectura/08-estandares.md
    §4.5, "componentes de interfaz reutilizables"). Reemplaza los bloques
    `.empty-state` que cada vista repetía a mano, sin cambiar el markup/CSS
    ya existente (`components/_alerts.scss` u homólogo define `.empty-state`,
    `.empty-icon`) — mismo look, un solo lugar para mantenerlo.

    Uso:
    <x-empty-state icon="fas fa-chart-column" message="Aún no hay calificaciones registradas para este periodo." />
    <x-empty-state icon="fas fa-bullhorn" message="Aún no hay comunicados enviados."
                   action-label="Ver todos los comunicados" :action-url="route('comunicados.index')" />
--}}

<div {{ $attributes->class(['empty-state']) }}>
    <div class="empty-icon"><i class="{{ $icon }}"></i></div>
    <p class="mb-{{ $actionLabel ? '2' : '0' }}">{{ $message }}</p>
    @if ($actionLabel && $actionUrl)
        <a href="{{ $actionUrl }}" class="btn btn-sm btn-primary">{{ $actionLabel }}</a>
    @endif
</div>
