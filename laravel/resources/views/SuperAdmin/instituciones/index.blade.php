@extends('layouts.superadmin')

@section('title', 'Instituciones')

@section('content')
<div class="container-fluid p-3 p-md-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 fw-semibold font-serif mb-0">Instituciones</h1>
        <a href="{{ route('superadmin.instituciones.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Nueva institución
        </a>
    </div>

    <form method="GET" action="{{ route('superadmin.instituciones.index') }}" class="row g-2 mb-3 align-items-center" data-autosubmit-form>
        <div class="col-12 col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-body"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" name="buscar" value="{{ $filtros['buscar'] ?? '' }}"
                       placeholder="Buscar por nombre..." data-autosubmit-debounce>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <select class="form-select" name="estado" data-autosubmit>
                <option value="">Todos los estados</option>
                <option value="activa" @selected(($filtros['estado'] ?? '') === 'activa')>Activa</option>
                <option value="suspendida" @selected(($filtros['estado'] ?? '') === 'suspendida')>Suspendida</option>
                <option value="inactiva" @selected(($filtros['estado'] ?? '') === 'inactiva')>Inactiva</option>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <select class="form-select" name="id_plan" data-autosubmit>
                <option value="">Todos los planes</option>
                @foreach ($planes as $planCatalogo)
                    <option value="{{ $planCatalogo->id_plan }}" @selected((string) ($filtros['id_plan'] ?? '') === (string) $planCatalogo->id_plan)>{{ $planCatalogo->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2 d-grid">
            <a href="{{ route('superadmin.instituciones.index') }}" class="btn btn-outline-secondary btn-sm">Limpiar filtros</a>
        </div>
    </form>

    @if ($instituciones->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-school"></i></div>
            <p class="mb-0">No hay instituciones que coincidan con los filtros.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Institución</th>
                        <th>Plan</th>
                        <th>Usuarios</th>
                        <th>Vence</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($instituciones as $institucion)
                        <tr>
                            <td>
                                <a href="{{ route('superadmin.instituciones.show', $institucion) }}" class="fw-semibold text-decoration-none">
                                    {{ $institucion->nombre }}
                                </a>
                                <div class="small text-secondary">{{ $institucion->slug }}</div>
                            </td>
                            <td><span class="badge text-bg-secondary">{{ $institucion->planCatalogo->nombre ?? ucfirst($institucion->plan) }}</span></td>
                            <td>{{ $institucion->usuarios_count }}</td>
                            <td>{{ $institucion->fecha_vencimiento?->format('d/m/Y') ?? '—' }}</td>
                            <td>
                                @php
                                    $colorEstado = ['activa' => 'success', 'suspendida' => 'warning', 'inactiva' => 'secondary'];
                                @endphp
                                <span class="badge text-bg-{{ $colorEstado[$institucion->estado] }}">{{ ucfirst($institucion->estado) }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('superadmin.instituciones.show', $institucion) }}" class="btn btn-sm btn-outline-secondary" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('superadmin.instituciones.edit', $institucion) }}" class="btn btn-sm btn-outline-secondary" title="Editar">
                                    <i class="fas fa-pen"></i>
                                </a>
                                @if ($institucion->estado === 'activa')
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-desactivar
                                            data-url="{{ route('superadmin.instituciones.desactivar', $institucion) }}"
                                            data-nombre="{{ $institucion->nombre }}" title="Suspender">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                @else
                                    <button type="button" class="btn btn-sm btn-outline-success" data-activar
                                            data-url="{{ route('superadmin.instituciones.activar', $institucion) }}"
                                            data-nombre="{{ $institucion->nombre }}" title="Activar">
                                        <i class="fas fa-rotate-left"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $instituciones->links('pagination::bootstrap-5') }}
    @endif
</div>
@endsection

@push('scripts')
    @vite('resources/js/pages/superadmin/instituciones.js')
@endpush
