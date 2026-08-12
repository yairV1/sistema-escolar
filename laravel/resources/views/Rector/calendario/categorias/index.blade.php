@extends('layouts.rector')

@section('title', 'Categorías del calendario')

@php
    $nombresColor = [
        'var(--cat-1)' => 'Azul', 'var(--cat-2)' => 'Naranja', 'var(--cat-3)' => 'Aqua',
        'var(--cat-4)' => 'Amarillo', 'var(--cat-5)' => 'Magenta', 'var(--cat-6)' => 'Verde',
        'var(--cat-7)' => 'Morado', 'var(--cat-8)' => 'Rojo', 'var(--cat-9)' => 'Celeste',
        'var(--bs-secondary)' => 'Gris',
    ];
    $nombresRol = [
        'admin' => 'Administrador', 'rector' => 'Directivo', 'coordinador' => 'Coordinador',
        'secretario' => 'Secretario', 'docente' => 'Docente', 'estudiante' => 'Estudiante', 'acudiente' => 'Acudiente',
    ];
@endphp

@section('content')
<div class="container-fluid p-3 p-md-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <a href="{{ route('calendario.index') }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Volver al calendario</a>
            <h1 class="h4 fw-semibold font-serif mb-0 mt-1">Categorías del calendario</h1>
        </div>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalNuevaCategoria">
            <i class="bi bi-plus-lg me-1"></i> Nueva categoría
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Categoría</th>
                    <th>Color</th>
                    <th>Puede crear</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categorias as $categoria)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi {{ $categoria->icono }}"></i>
                                <div>
                                    <div class="fw-semibold">{{ $categoria->nombre }}</div>
                                    @if ($categoria->es_sistema)
                                        <span class="badge text-bg-secondary">Sistema</span>
                                    @endif
                                    @if ($categoria->descripcion)
                                        <div class="small text-secondary">{{ $categoria->descripcion }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="calendario-categoria-dot" style="--categoria-color: {{ $categoria->color }};"></span>
                            {{ $nombresColor[$categoria->color] ?? $categoria->color }}
                        </td>
                        <td class="small text-secondary">
                            {{ $categoria->es_sistema ? 'Derivado automáticamente' : (collect($categoria->roles_crear)->map(fn ($r) => $nombresRol[$r] ?? $r)->join(', ') ?: 'Nadie') }}
                        </td>
                        <td>
                            <span class="badge text-bg-{{ $categoria->estado === 'activo' ? 'success' : 'secondary' }}">
                                {{ $categoria->estado === 'activo' ? 'Activa' : 'Inactiva' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-secondary" title="Editar"
                                    data-bs-toggle="modal" data-bs-target="#modalEditarCategoria{{ $categoria->id_categoria }}">
                                <i class="fas fa-pen"></i>
                            </button>
                            @if (! $categoria->es_sistema)
                                @if ($categoria->estado === 'activo')
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-desactivar
                                            data-url="{{ route('calendario.categorias.desactivar', $categoria) }}"
                                            data-nombre="{{ $categoria->nombre }}" title="Desactivar">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                @else
                                    <button type="button" class="btn btn-sm btn-outline-success" data-activar
                                            data-url="{{ route('calendario.categorias.activar', $categoria) }}"
                                            data-nombre="{{ $categoria->nombre }}" title="Reactivar">
                                        <i class="fas fa-rotate-left"></i>
                                    </button>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('modals')
    {{-- Modal: nueva categoría --}}
<div class="modal fade" id="modalNuevaCategoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form data-crud-form data-url="{{ route('calendario.categorias.store') }}" novalidate>
                @include('Rector.calendario.categorias.partials._campos-categoria', ['categoria' => null, 'sufijo' => 'nc', 'nombresColor' => $nombresColor, 'nombresRol' => $nombresRol, 'rolesDisponibles' => $rolesDisponibles])
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modales: editar categoría (uno por fila) --}}
@foreach ($categorias as $categoria)
    <div class="modal fade" id="modalEditarCategoria{{ $categoria->id_categoria }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form data-crud-form data-url="{{ route('calendario.categorias.update', $categoria) }}" novalidate>
                    @include('Rector.calendario.categorias.partials._campos-categoria', ['categoria' => $categoria, 'sufijo' => 'ec'.$categoria->id_categoria, 'nombresColor' => $nombresColor, 'nombresRol' => $nombresRol, 'rolesDisponibles' => $rolesDisponibles])
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endpush

@push('scripts')
    @vite('resources/js/pages/calendario/categorias.js')
@endpush
