@extends('layouts.superadmin')

@section('title', 'Usuarios globales')

@section('content')
<div class="container-fluid p-3 p-md-4">
    <h1 class="h4 fw-semibold font-serif mb-3">Usuarios globales</h1>

    <form method="GET" action="{{ route('superadmin.usuarios.index') }}" class="row g-2 mb-3 align-items-center" data-autosubmit-form>
        <div class="col-12 col-md-4">
            <div class="input-group">
                <span class="input-group-text bg-body"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" name="buscar" value="{{ $filtros['buscar'] ?? '' }}"
                       placeholder="Buscar por nombre o correo..." data-autosubmit-debounce>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <select class="form-select" name="id_institucion" data-autosubmit>
                <option value="">Todas las instituciones</option>
                @foreach ($instituciones as $institucion)
                    <option value="{{ $institucion->id_institucion }}" @selected(($filtros['id_institucion'] ?? '') == $institucion->id_institucion)>{{ $institucion->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <select class="form-select" name="id_rol" data-autosubmit>
                <option value="">Todos los roles</option>
                @foreach ($roles as $rol)
                    <option value="{{ $rol->id_rol }}" @selected(($filtros['id_rol'] ?? '') == $rol->id_rol)>{{ $rol->nombre_rol }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-1">
            <select class="form-select" name="estado" data-autosubmit>
                <option value="">Estado</option>
                <option value="activo" @selected(($filtros['estado'] ?? '') === 'activo')>Activo</option>
                <option value="inactivo" @selected(($filtros['estado'] ?? '') === 'inactivo')>Inactivo</option>
            </select>
        </div>
        <div class="col-6 col-md-2 d-grid">
            <a href="{{ route('superadmin.usuarios.index') }}" class="btn btn-outline-secondary btn-sm">Limpiar filtros</a>
        </div>
    </form>

    @if ($usuarios->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-users"></i></div>
            <p class="mb-0">No hay usuarios que coincidan con los filtros.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Institución</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($usuarios as $usuario)
                        <tr>
                            <td class="fw-semibold">{{ trim($usuario->nombres.' '.$usuario->apellidos) }}</td>
                            <td>{{ $usuario->correo }}</td>
                            <td>{{ $usuario->institucion?->nombre ?? '—' }}</td>
                            <td>{{ $usuario->rol?->nombre_rol }}</td>
                            <td>
                                <span class="badge text-bg-{{ $usuario->estado_usuario === 'activo' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($usuario->estado_usuario) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-secondary" title="Cambiar rol"
                                        data-bs-toggle="modal" data-bs-target="#modalCambiarRol{{ $usuario->id_usuario }}">
                                    <i class="fas fa-user-gear"></i>
                                </button>
                                @if ($usuario->estado_usuario === 'activo')
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-desactivar
                                            data-url="{{ route('superadmin.usuarios.estado', $usuario) }}"
                                            data-nombre="{{ trim($usuario->nombres.' '.$usuario->apellidos) }}"
                                            data-confirm-text="Su sesión activa se invalidará."
                                            title="Inactivar">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                @else
                                    <button type="button" class="btn btn-sm btn-outline-success" data-activar
                                            data-url="{{ route('superadmin.usuarios.estado', $usuario) }}"
                                            data-nombre="{{ trim($usuario->nombres.' '.$usuario->apellidos) }}"
                                            title="Reactivar">
                                        <i class="fas fa-rotate-left"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $usuarios->links('pagination::bootstrap-5') }}
    @endif
</div>

{{-- Modales: cambiar rol (uno por fila) --}}
@foreach ($usuarios as $usuario)
    <div class="modal fade" id="modalCambiarRol{{ $usuario->id_usuario }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form data-crud-form data-url="{{ route('superadmin.usuarios.rol', $usuario) }}" novalidate>
                    <div class="modal-header">
                        <h5 class="modal-title font-serif">Cambiar rol</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-secondary small">
                            {{ trim($usuario->nombres.' '.$usuario->apellidos) }} perderá su sesión activa al cambiar de rol.
                        </p>
                        <label class="form-label">Nuevo rol *</label>
                        <select class="form-select" name="id_rol" data-feedback="err-rol-{{ $usuario->id_usuario }}" required>
                            @foreach ($roles as $rol)
                                <option value="{{ $rol->id_rol }}" @selected($usuario->id_rol === $rol->id_rol)>{{ $rol->nombre_rol }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="err-rol-{{ $usuario->id_usuario }}"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar cambio</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endsection

@push('scripts')
    @vite('resources/js/pages/superadmin/usuarios.js')
@endpush
