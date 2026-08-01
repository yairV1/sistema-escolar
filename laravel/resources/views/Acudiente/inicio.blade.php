@extends('layouts.panel')

@section('title', 'Inicio')

@section('content')
    <div class="container-fluid p-3 p-md-4">

        <div class="dash-welcome mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <span class="role-badge role-badge--acudiente mb-2"><i class="fas fa-people-roof"></i> Acudiente</span>
                <h1 class="h3 fw-semibold mb-1 font-serif">Hola, {{ auth()->user()->nombres }} 👋</h1>
                <p class="mb-0 text-white-50">Resumen de tus {{ $hijos->count() === 1 ? 'estudiante a cargo' : 'estudiantes a cargo' }}</p>
            </div>
        </div>

        @if ($hijos->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-user-graduate"></i></div>
                <p class="mb-0">Todavía no tienes estudiantes vinculados a tu cuenta.</p>
            </div>
        @else
            <div class="row g-3" id="hijosContainer">
                @foreach ($hijos as $hijo)
                    <div class="col-md-6 col-xl-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="skeleton-block" style="width:48px;height:48px;min-height:0;border-radius:50%;flex-shrink:0;"></div>
                                    <div class="flex-grow-1">
                                        <div class="skeleton-line" style="width:60%;"></div>
                                        <div class="skeleton-line" style="width:40%;"></div>
                                    </div>
                                </div>
                                <div class="skeleton-line"></div>
                                <div class="skeleton-line"></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="row g-3 d-none" id="hijosReal">
                @foreach ($hijos as $hijo)
                    <div class="col-md-6 col-xl-4">
                        <div class="card child-card {{ $hijo->alertas ? 'child-card--alert' : '' }}">
                            <div class="card-body">
                                <div class="child-card__head">
                                    <span class="child-card__avatar">{{ mb_substr($hijo->nombres, 0, 1).mb_substr($hijo->apellidos, 0, 1) }}</span>
                                    <div>
                                        <h3 class="h6 fw-semibold mb-0">{{ $hijo->nombres }} {{ $hijo->apellidos }}</h3>
                                        <div class="small text-secondary">{{ $hijo->curso }} · <code>{{ $hijo->codigo }}</code></div>
                                    </div>
                                </div>

                                <div class="child-card__stats">
                                    <div>
                                        <div class="child-card__stat-value">{{ number_format($hijo->promedio, 1) }}</div>
                                        <div class="child-card__stat-label">Promedio</div>
                                    </div>
                                    <div>
                                        <div class="child-card__stat-value">{{ $hijo->asistencia }}%</div>
                                        <div class="child-card__stat-label">Asistencia</div>
                                    </div>
                                </div>

                                @if ($hijo->alertas)
                                    <div class="child-card__alerts">
                                        @foreach ($hijo->alertas as $alerta)
                                            <div class="child-alert"><i class="fas fa-triangle-exclamation"></i> {{ $alerta }}</div>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="d-flex gap-2 flex-wrap mt-3">
                                    <a href="{{ route('acudiente.notas', ['estudiante' => $hijo->id]) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-star me-1"></i> Notas
                                    </a>
                                    <a href="{{ route('acudiente.asistencia', ['estudiante' => $hijo->id]) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-clipboard-check me-1"></i> Asistencia
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var skeleton = document.getElementById('hijosContainer');
            var real = document.getElementById('hijosReal');
            if (!skeleton || !real) return;
            setTimeout(function () {
                skeleton.classList.add('d-none');
                real.classList.remove('d-none');
            }, 350);
        });
    </script>
@endpush
