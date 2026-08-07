@extends('layouts.superadmin')

@section('title', 'Panel')

@section('content')
<div class="container-fluid p-3 p-md-4">

    <div class="dash-welcome mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="small text-white-50 mb-1">
                <i class="fas fa-shield-halved me-1"></i> Nivel de acceso: plataforma
            </div>
            <h1 class="h3 fw-semibold mb-1 font-serif">Hola, {{ auth()->user()->nombres }}</h1>
            <p class="mb-0 text-white-50">Panel SuperAdmin · vista global de todas las instituciones</p>
        </div>
    </div>

    @php
        $kpiCards = [
            ['icon' => 'fa-school', 'color' => 'primary', 'label' => 'Instituciones activas', 'value' => $resumen['instituciones_activas']],
            ['icon' => 'fa-ban', 'color' => 'danger', 'label' => 'Instituciones suspendidas', 'value' => $resumen['instituciones_suspendidas']],
            ['icon' => 'fa-users', 'color' => 'info', 'label' => 'Usuarios totales', 'value' => $resumen['usuarios_totales']],
            ['icon' => 'fa-bullhorn', 'color' => 'secondary', 'label' => 'Comunicados enviados', 'value' => $resumen['comunicados_enviados']],
            ['icon' => 'fa-calendar-days', 'color' => 'success', 'label' => 'Eventos creados', 'value' => $resumen['eventos_creados']],
            ['icon' => 'fa-headset', 'color' => 'warning', 'label' => 'Soporte sin atender', 'value' => $resumen['soporte_nuevos']],
            ['icon' => 'fa-layer-group', 'color' => 'primary', 'label' => 'Planes activos', 'value' => $resumen['planes_activos']],
            ['icon' => 'fa-puzzle-piece', 'color' => 'info', 'label' => 'Módulos activos', 'value' => $resumen['modulos_activos']],
        ];
    @endphp

    <div class="row g-3 mb-4">
        @foreach ($kpiCards as $card)
            <div class="col-6 col-lg-4 col-xl-2">
                <div class="kpi-card bg-body-tertiary border h-100">
                    <div class="kpi-icon bg-{{ $card['color'] }}-subtle text-{{ $card['color'] }} mb-2">
                        <i class="fas {{ $card['icon'] }}"></i>
                    </div>
                    <div class="kpi-value">{{ $card['value'] }}</div>
                    <div class="kpi-label">{{ $card['label'] }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3"><i class="fas fa-chart-line text-primary me-1"></i> Instituciones nuevas (últimos 12 meses)</h2>
                    <canvas id="chartCrecimiento" height="220"
                            data-labels="{{ $crecimientoInstituciones->keys()->toJson() }}"
                            data-valores="{{ $crecimientoInstituciones->values()->toJson() }}"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3"><i class="fas fa-triangle-exclamation text-warning me-1"></i> Instituciones próximas a vencer</h2>

                    @if (empty($institucionesProximasAVencer))
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-circle-check"></i></div>
                            <p class="mb-0">Ninguna institución vence en los próximos 30 días.</p>
                        </div>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($institucionesProximasAVencer as $institucion)
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <a href="{{ route('superadmin.instituciones.show', $institucion) }}" class="fw-semibold text-decoration-none">
                                        {{ $institucion->nombre }}
                                    </a>
                                    <span class="badge text-bg-warning">
                                        Vence {{ $institucion->fecha_vencimiento->format('d/m/Y') }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3"><i class="fas fa-bullhorn text-primary me-1"></i> Comunicados por mes</h2>
                    <canvas id="chartComunicados" height="200"
                            data-labels="{{ $comunicadosPorMes->keys()->toJson() }}"
                            data-valores="{{ $comunicadosPorMes->values()->toJson() }}"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3"><i class="fas fa-users text-primary me-1"></i> Usuarios por rol</h2>

                    @if ($resumen['usuarios_por_rol']->isEmpty())
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-users"></i></div>
                            <p class="mb-0">Aún no hay usuarios registrados.</p>
                        </div>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($resumen['usuarios_por_rol'] as $fila)
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span>{{ $fila['rol'] }}</span>
                                    <span class="badge text-bg-secondary">{{ $fila['total'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3"><i class="fas fa-layer-group text-primary me-1"></i> Mezcla de planes</h2>

                    @if ($planesDistribucion->isEmpty())
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-layer-group"></i></div>
                            <p class="mb-0">Ninguna institución tiene un plan del catálogo asignado todavía.</p>
                        </div>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($planesDistribucion as $fila)
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span>{{ $fila['plan'] }}</span>
                                    <span class="badge text-bg-secondary">{{ $fila['total'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
    @vite('resources/js/pages/superadmin/dashboard.js')
@endpush
