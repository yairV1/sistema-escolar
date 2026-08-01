@extends('layouts.superadmin')

@section('title', 'Módulos')

@section('content')
<div class="container-fluid p-3 p-md-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h4 fw-semibold font-serif mb-1">Módulos</h1>
            <p class="text-secondary mb-0 small">Catálogo de funcionalidades que un plan puede incluir.</p>
        </div>
        <a href="{{ route('superadmin.modulos.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Nuevo módulo
        </a>
    </div>

    <form method="GET" action="{{ route('superadmin.modulos.index') }}" class="row g-2 mb-3" data-autosubmit-form>
        <div class="col-6 col-md-3">
            <select class="form-select" name="categoria" data-autosubmit>
                <option value="">Todas las categorías</option>
                @foreach (['academico' => 'Académico', 'administrativo' => 'Administrativo', 'comunicacion' => 'Comunicación', 'finanzas' => 'Finanzas'] as $valor => $etiqueta)
                    <option value="{{ $valor }}" @selected(($filtros['categoria'] ?? '') === $valor)>{{ $etiqueta }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-3">
            <select class="form-select" name="estado" data-autosubmit>
                <option value="">Todos los estados</option>
                <option value="activo" @selected(($filtros['estado'] ?? '') === 'activo')>Activo</option>
                <option value="inactivo" @selected(($filtros['estado'] ?? '') === 'inactivo')>Inactivo</option>
            </select>
        </div>
    </form>

    @if ($modulos->isEmpty())
        <x-empty-state icon="fas fa-puzzle-piece" message="No hay módulos que coincidan con los filtros."
                       action-label="Crear el primer módulo" :action-url="route('superadmin.modulos.create')" />
    @else
        <div class="row g-3">
            @foreach ($modulos as $modulo)
                <div class="col-md-6 col-xl-4">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="modulo-icono"><i class="{{ $modulo->icono }}"></i></div>
                                <span class="badge text-bg-{{ $modulo->estado === 'activo' ? 'success' : 'secondary' }}">{{ ucfirst($modulo->estado) }}</span>
                            </div>
                            <h2 class="h6 fw-semibold mb-1">{{ $modulo->nombre }}</h2>
                            <span class="badge text-bg-secondary align-self-start mb-2">{{ ucfirst($modulo->categoria) }}</span>
                            <p class="text-secondary small mb-3">{{ $modulo->descripcion }}</p>

                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <span class="text-secondary small"><i class="fas fa-layer-group me-1"></i>{{ $modulo->planes_count }} planes</span>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('superadmin.modulos.edit', $modulo) }}" class="btn btn-sm btn-outline-secondary" title="Editar">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    @if ($modulo->estado === 'activo')
                                        <button type="button" class="btn btn-sm btn-outline-danger" title="Desactivar"
                                                data-desactivar data-url="{{ route('superadmin.modulos.desactivar', $modulo) }}" data-nombre="el módulo {{ $modulo->nombre }}">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-success" title="Activar"
                                                data-activar data-url="{{ route('superadmin.modulos.activar', $modulo) }}" data-nombre="el módulo {{ $modulo->nombre }}">
                                            <i class="fas fa-rotate-left"></i>
                                        </button>
                                    @endif
                                </div>
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
    @vite('resources/js/pages/superadmin/modulos.js')
@endpush
