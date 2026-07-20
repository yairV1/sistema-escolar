@extends('layouts.panel')

@section('title', 'Mi perfil')

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <h1 class="h4 fw-semibold font-serif mb-4">Mi perfil</h1>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h6 fw-semibold mb-3"><i class="fas fa-user text-primary me-1"></i> Datos personales</h2>
                        <form id="formPerfil" data-url="{{ route('perfil.update') }}" novalidate>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nombres *</label>
                                    <input type="text" class="form-control" name="nombres" value="{{ $usuario->nombres }}" data-feedback="err-p-nombres" required>
                                    <div class="invalid-feedback" id="err-p-nombres"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Apellidos *</label>
                                    <input type="text" class="form-control" name="apellidos" value="{{ $usuario->apellidos }}" data-feedback="err-p-apellidos" required>
                                    <div class="invalid-feedback" id="err-p-apellidos"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Correo *</label>
                                    <input type="email" class="form-control" name="correo" value="{{ $usuario->correo }}" data-feedback="err-p-correo" required>
                                    <div class="invalid-feedback" id="err-p-correo"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Teléfono</label>
                                    <input type="text" class="form-control" name="telefono" value="{{ $usuario->telefono }}" data-feedback="err-p-telefono">
                                    <div class="invalid-feedback" id="err-p-telefono"></div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mt-3">
                                <button type="submit" class="btn btn-primary" id="btnGuardarPerfil">
                                    <i class="fas fa-save me-1"></i> Guardar cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h6 fw-semibold mb-3"><i class="fas fa-lock text-primary me-1"></i> Cambiar contraseña</h2>
                        <form id="formPassword" data-url="{{ route('perfil.password') }}" novalidate>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Contraseña actual *</label>
                                    <input type="password" class="form-control" name="password_actual" data-feedback="err-pw-actual" required>
                                    <div class="invalid-feedback" id="err-pw-actual"></div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Contraseña nueva *</label>
                                    <input type="password" class="form-control" name="password_nueva" minlength="8" data-feedback="err-pw-nueva" required>
                                    <div class="invalid-feedback" id="err-pw-nueva"></div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Confirmar contraseña nueva *</label>
                                    <input type="password" class="form-control" name="password_nueva_confirmation" minlength="8" required>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mt-3">
                                <button type="submit" class="btn btn-primary" id="btnCambiarPassword">
                                    <i class="fas fa-key me-1"></i> Cambiar contraseña
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/pages/perfil/perfil.js')
@endpush
