@extends('layouts.superadmin')

@section('title', 'Planes')

@section('content')
<div class="container-fluid p-3 p-md-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h4 fw-semibold font-serif mb-1">Planes</h1>
            <p class="text-secondary mb-0 small">Catálogo comercial de la plataforma. No hay cobro automático — es la referencia que usa cada institución.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <div class="btn-group" role="group" data-bill-toggle>
                <button type="button" class="btn btn-sm btn-outline-secondary active" data-ciclo="mensual">Mensual</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-ciclo="anual">Anual</button>
            </div>
            <a href="{{ route('superadmin.planes.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> Nuevo plan
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('superadmin.planes.index') }}" class="row g-2 mb-3" data-autosubmit-form>
        <div class="col-6 col-md-3">
            <select class="form-select" name="estado" data-autosubmit>
                <option value="">Todos los estados</option>
                <option value="activo" @selected(($filtros['estado'] ?? '') === 'activo')>Activo</option>
                <option value="inactivo" @selected(($filtros['estado'] ?? '') === 'inactivo')>Inactivo</option>
            </select>
        </div>
    </form>

    @if ($planes->isEmpty())
        <x-empty-state icon="fas fa-layer-group" message="Aún no hay planes en el catálogo."
                       action-label="Crear el primer plan" :action-url="route('superadmin.planes.create')" />
    @else
        <div class="row g-3">
            @foreach ($planes as $plan)
                <div class="col-md-6 col-xl-4">
                    <div class="card h-100 plan-card @if($plan->estado === 'inactivo') plan-card-inactivo @endif">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h2 class="h5 fw-semibold font-serif mb-0">{{ $plan->nombre }}</h2>
                                <span class="badge text-bg-{{ $plan->estado === 'activo' ? 'success' : 'secondary' }}">{{ ucfirst($plan->estado) }}</span>
                            </div>
                            <p class="text-secondary small mb-3">{{ $plan->descripcion }}</p>

                            <div class="plan-precio mb-3">
                                <span class="plan-precio-mensual">${{ number_format($plan->precio_mensual, 0, ',', '.') }}</span>
                                <span class="plan-precio-anual d-none">${{ number_format($plan->precio_anual, 0, ',', '.') }}</span>
                                <span class="text-secondary small">/ <span class="plan-precio-periodo">mes</span></span>
                            </div>

                            @if (!empty($plan->beneficios))
                                <ul class="list-unstyled small mb-3">
                                    @foreach ($plan->beneficios as $beneficio)
                                        <li class="mb-1"><i class="fas fa-check text-success me-2"></i>{{ $beneficio }}</li>
                                    @endforeach
                                </ul>
                            @endif

                            <div class="mb-3">
                                <span class="badge text-bg-secondary"><i class="fas fa-puzzle-piece me-1"></i>{{ $plan->modulos->count() }} módulos</span>
                                <span class="badge text-bg-secondary"><i class="fas fa-school me-1"></i>{{ $plan->instituciones_count }} instituciones</span>
                            </div>

                            <div class="mt-auto d-flex gap-2">
                                <a href="{{ route('superadmin.planes.edit', $plan) }}" class="btn btn-sm btn-outline-secondary flex-fill">
                                    <i class="fas fa-pen me-1"></i> Editar
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-secondary" title="Duplicar"
                                        data-duplicar data-url="{{ route('superadmin.planes.duplicar', $plan) }}">
                                    <i class="fas fa-clone"></i>
                                </button>
                                @if ($plan->estado === 'activo')
                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Desactivar"
                                            data-desactivar data-url="{{ route('superadmin.planes.desactivar', $plan) }}" data-nombre="el plan {{ $plan->nombre }}">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                @else
                                    <button type="button" class="btn btn-sm btn-outline-success" title="Activar"
                                            data-activar data-url="{{ route('superadmin.planes.activar', $plan) }}" data-nombre="el plan {{ $plan->nombre }}">
                                        <i class="fas fa-rotate-left"></i>
                                    </button>
                                @endif
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
    @vite('resources/js/pages/superadmin/planes.js')
@endpush
