@extends('layouts.rector')

@section('title', 'Roles')

@section('content')
<div class="container-fluid p-3 p-md-4">
    <h1 class="h4 fw-semibold font-serif mb-1">Roles</h1>
    <p class="text-secondary small mb-3">
        Los roles son comunes a toda la plataforma. Desactivar un rol impide iniciar sesión a todos los usuarios
        que lo tengan, por eso su nombre, descripción y estado solo los administra el SuperAdmin.
    </p>
    @if ($soloLectura)
        <div class="alert alert-info small py-2" role="status">
            <i class="fas fa-lock me-1" aria-hidden="true"></i>
            Consulta en modo lectura. La columna «Usuarios asignados» cuenta solo los usuarios de tu institución.
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Rol</th>
                    <th>Descripción</th>
                    <th>Usuarios asignados</th>
                    <th>Estado</th>
                    @unless ($soloLectura)
                        <th class="text-end">Acciones</th>
                    @endunless
                </tr>
            </thead>
            <tbody>
                @foreach ($roles as $rol)
                    <tr>
                        <td class="fw-semibold">{{ $rol->nombre_rol }}</td>
                        <td>{{ $rol->descripcion ?: '—' }}</td>
                        <td>{{ $usuariosPorRol[$rol->id_rol] ?? 0 }}</td>
                        <td>
                            <span class="badge text-bg-{{ $rol->estado === 'activo' ? 'success' : 'secondary' }}">
                                {{ $rol->estado === 'activo' ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        @unless ($soloLectura)
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-secondary" title="Editar"
                                    data-bs-toggle="modal" data-bs-target="#modalEditarRol{{ $rol->id_rol }}">
                                <i class="fas fa-pen"></i>
                            </button>
                            @if ($rol->estado === 'activo')
                                <button type="button" class="btn btn-sm btn-outline-danger" data-desactivar
                                        data-url="{{ route('roles.desactivar', $rol) }}"
                                        data-nombre="{{ $rol->nombre_rol }}"
                                        title="Desactivar">
                                    <i class="fas fa-ban"></i>
                                </button>
                            @else
                                <button type="button" class="btn btn-sm btn-outline-success" data-activar
                                        data-url="{{ route('roles.activar', $rol) }}"
                                        data-nombre="{{ $rol->nombre_rol }}"
                                        title="Reactivar">
                                    <i class="fas fa-rotate-left"></i>
                                </button>
                            @endif
                        </td>
                        @endunless
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('modals')
    {{-- Modales: editar rol (uno por fila) — solo si se puede administrar el catálogo --}}
    @foreach ($soloLectura ? [] : $roles as $rol)
    <div class="modal fade" id="modalEditarRol{{ $rol->id_rol }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form data-crud-form data-url="{{ route('roles.update', $rol) }}" novalidate>
                    <div class="modal-header">
                        <h5 class="modal-title font-serif">Editar rol</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre *</label>
                            <input type="text" class="form-control" name="nombre_rol" value="{{ $rol->nombre_rol }}"
                                   data-feedback="err-er-nombre-{{ $rol->id_rol }}" required>
                            <div class="invalid-feedback" id="err-er-nombre-{{ $rol->id_rol }}"></div>
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion" rows="2"
                                      data-feedback="err-er-descripcion-{{ $rol->id_rol }}">{{ $rol->descripcion }}</textarea>
                            <div class="invalid-feedback" id="err-er-descripcion-{{ $rol->id_rol }}"></div>
                        </div>
                    </div>
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
    @vite('resources/js/pages/roles/roles.js')
@endpush
