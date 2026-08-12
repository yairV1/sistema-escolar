@extends('layouts.rector')

@section('title', $admin ? 'Editar administrativo' : 'Registro de administrativos')

@section('content')
    <div class="container-fluid p-3 p-md-4" style="max-width: 640px;">
        <div class="d-flex align-items-center gap-2 mb-3">
            <a href="{{ route('listados', ['tab' => 'administrativos']) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="h4 fw-semibold font-serif mb-0">{{ $admin ? 'Editar administrativo' : 'Registro de personal administrativo' }}</h1>
        </div>

        <form id="formAdministrativo" novalidate>
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombres *</label>
                            <input type="text" class="form-control" name="nombres" id="nombres" value="{{ $admin?->nombres }}" data-feedback="err-nombres" required>
                            <div class="invalid-feedback" id="err-nombres"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apellidos *</label>
                            <input type="text" class="form-control" name="apellidos" id="apellidos" value="{{ $admin?->apellidos }}" data-feedback="err-apellidos" required>
                            <div class="invalid-feedback" id="err-apellidos"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tipo de documento *</label>
                            <select class="form-select" name="tipo_documento" id="tipo_documento" data-feedback="err-tipo_documento" required>
                                <option value="CC" @selected(($admin?->tipo_documento ?? 'CC') === 'CC')>Cédula de ciudadanía</option>
                                <option value="CE" @selected($admin?->tipo_documento === 'CE')>Cédula de extranjería</option>
                                <option value="PAS" @selected($admin?->tipo_documento === 'PAS')>Pasaporte</option>
                            </select>
                            <div class="invalid-feedback" id="err-tipo_documento"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Número de documento *</label>
                            <input type="text" class="form-control" name="numero_documento" id="numero_documento" value="{{ $admin?->numero_documento }}" data-feedback="err-numero_documento" required>
                            <div class="invalid-feedback" id="err-numero_documento"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Rol *</label>
                            <select class="form-select" name="id_rol" id="id_rol" data-feedback="err-id_rol" required>
                                <option value="1" @selected(($admin?->id_rol ?? 1) == 1)>Administrador</option>
                                <option value="2" @selected($admin?->id_rol == 2)>Directivo</option>
                                <option value="3" @selected($admin?->id_rol == 3)>Coordinador</option>
                                <option value="4" @selected($admin?->id_rol == 4)>Secretario</option>
                            </select>
                            <div class="invalid-feedback" id="err-id_rol"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Correo *</label>
                            <input type="email" class="form-control" name="correo" id="correo" value="{{ $admin?->correo }}" data-feedback="err-correo" required>
                            <div class="invalid-feedback" id="err-correo"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Teléfono</label>
                            <input type="text" class="form-control" name="telefono" id="telefono" value="{{ $admin?->telefono }}" data-feedback="err-telefono">
                            <div class="invalid-feedback" id="err-telefono"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-3">
                <button type="submit" class="btn btn-primary" id="btnGuardar">
                    <i class="fas fa-save me-1"></i> {{ $admin ? 'Guardar cambios' : 'Registrar' }}
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>window.__RUTA_GUARDAR__ = @json($admin ? route('registro.administrativos.update', $admin) : route('registro.administrativos.store'));</script>
    @vite('resources/js/pages/registro/administrativos.js')
@endpush
