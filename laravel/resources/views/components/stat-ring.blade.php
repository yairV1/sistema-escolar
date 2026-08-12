@props([
    'value' => 0,
    'label' => '',
    'color' => null,
])

{{--
    Anillo de progreso circular reutilizable — SVG "donut" (técnica estándar
    stroke-dasharray sobre un círculo de circunferencia 100), sin dependencias
    JS. Estilos en css/layouts/_rector.scss (.stat-ring*).

    Uso:
    <x-stat-ring :value="83" label="Object program." />
    <x-stat-ring :value="97" label="Web develop." color="var(--sb-secondary)" />
--}}

@php
    $pct = max(0, min(100, (float) $value));
@endphp

<div {{ $attributes->class(['stat-ring']) }} style="@if($color)--ring-color: {{ $color }};@endif">
    <svg viewBox="0 0 36 36" class="stat-ring-svg" role="img" aria-label="{{ $label }}: {{ $pct }}%">
        <path class="stat-ring-bg"
              d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
        <path class="stat-ring-fg"
              stroke-dasharray="{{ $pct }}, 100"
              d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
    </svg>
    <div class="stat-ring-center">
        <span class="stat-ring-value">{{ (int) round($pct) }}%</span>
    </div>
    @if ($label)
        <span class="stat-ring-label">{{ $label }}</span>
    @endif
</div>
