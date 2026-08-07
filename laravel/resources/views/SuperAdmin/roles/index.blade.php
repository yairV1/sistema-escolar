@extends('layouts.superadmin')

@section('title', 'Roles y permisos')

@section('content')
<div class="container-fluid p-3 p-md-4">
    <h1 class="h4 fw-semibold font-serif mb-1">Roles y permisos</h1>
    <p class="text-secondary small mb-3">
        Matriz rol → permiso (docs/arquitectura/03-rbac.md). El rol SuperAdmin no aparece aquí: sus permisos
        <code>plataforma.*</code> son fijos por diseño y no se editan desde este panel.
    </p>

    <form id="formMatrizRoles" data-url="{{ route('superadmin.roles.update') }}">
        @csrf
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle">
                <thead>
                    <tr>
                        <th style="min-width:260px;">Permiso</th>
                        @foreach ($roles as $rol)
                            <th class="text-center">
                                <span class="rol-nombre-texto" data-rol-nombre="{{ $rol->id_rol }}">{{ $rol->nombre_rol }}</span>
                                <button type="button" class="btn btn-sm btn-link p-0 ms-1 rol-renombrar-btn"
                                        data-rol-renombrar="{{ $rol->id_rol }}"
                                        data-url="{{ route('superadmin.roles.renombrar', $rol) }}"
                                        title="Renombrar rol">
                                    <i class="fas fa-pen small"></i>
                                </button>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($permisosPorModulo as $modulo => $permisos)
                        <tr class="table-secondary">
                            <td colspan="{{ $roles->count() + 1 }}" class="fw-semibold text-uppercase small">{{ $modulo }}</td>
                        </tr>
                        @foreach ($permisos as $permiso)
                            <tr>
                                <td>
                                    <div class="small fw-semibold">{{ $permiso->slug }}</div>
                                    <div class="small text-secondary">{{ $permiso->descripcion }}</div>
                                </td>
                                @foreach ($roles as $rol)
                                    <td class="text-center">
                                        <input type="checkbox"
                                               class="form-check-input"
                                               name="permisos[{{ $rol->id_rol }}][]"
                                               value="{{ $permiso->id_permiso }}"
                                               @checked(in_array($permiso->id_permiso, $asignaciones[$rol->id_rol] ?? []))>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>

        <button type="submit" class="btn btn-primary" id="btnGuardarMatriz">
            <i class="fas fa-floppy-disk me-1"></i> Guardar matriz
        </button>
    </form>
</div>
@endsection

@push('scripts')
    @vite('resources/js/pages/superadmin/roles.js')
@endpush
