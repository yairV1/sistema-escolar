@extends('layouts.superadmin')

@section('title', 'Verificación en dos pasos')

@section('content')
<div class="container-fluid p-3 p-md-4">
    <h1 class="h4 fw-semibold font-serif mb-3">Verificación en dos pasos (2FA)</h1>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body" id="dosFactoresPanel" data-activo="{{ $activo ? '1' : '0' }}"
                     data-enable-url="{{ route('superadmin.2fa.enable') }}"
                     data-confirm-url="{{ route('superadmin.2fa.confirm') }}"
                     data-disable-url="{{ route('superadmin.2fa.disable') }}"
                     data-regenerar-url="{{ route('superadmin.2fa.regenerar-codigos') }}">

                    @if ($activo)
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge text-bg-success">Activo</span>
                            <span class="text-secondary small">Tu cuenta está protegida con un segundo factor.</span>
                        </div>

                        <button type="button" class="btn btn-outline-secondary btn-sm mb-2" id="btnRegenerarCodigos">
                            <i class="fas fa-rotate me-1"></i> Regenerar códigos de recuperación
                        </button>

                        <form id="formDeshabilitar2fa" class="mt-3 border-top pt-3">
                            <label class="form-label">Contraseña actual (para desactivar 2FA)</label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="password" id="passwordDeshabilitar"
                                       data-feedback="err-password-deshabilitar" required>
                                <button type="submit" class="btn btn-outline-danger">Desactivar 2FA</button>
                            </div>
                            <div class="invalid-feedback" id="err-password-deshabilitar"></div>
                        </form>
                    @else
                        <p class="text-secondary small">
                            Añade una capa extra de seguridad: además de tu contraseña, se pedirá un código de tu
                            app autenticadora (Google Authenticator, Authy, etc.) al iniciar sesión.
                        </p>
                        <button type="button" class="btn btn-primary" id="btnHabilitar2fa">
                            <i class="fas fa-lock me-1"></i> Activar 2FA
                        </button>

                        <div id="bloqueEnrolamiento" class="d-none mt-4">
                            <div class="text-center mb-3" id="qrContenedor"></div>
                            <p class="small text-secondary text-center">
                                Escanea el código QR o ingresa el secreto manualmente: <code id="secretoTexto"></code>
                            </p>
                            <form id="formConfirmar2fa">
                                <label class="form-label">Código de 6 dígitos</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="codigo" id="codigoConfirmar"
                                           maxlength="6" data-feedback="err-codigo-confirmar" required>
                                    <button type="submit" class="btn btn-primary">Confirmar</button>
                                </div>
                                <div class="invalid-feedback" id="err-codigo-confirmar"></div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card d-none" id="cardCodigosRecuperacion">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-2"><i class="fas fa-key text-warning me-1"></i> Códigos de recuperación</h2>
                    <p class="text-secondary small">
                        Guárdalos en un lugar seguro: cada uno sirve una sola vez si pierdes acceso a tu app
                        autenticadora. No se volverán a mostrar.
                    </p>
                    <ul class="list-group list-group-flush" id="listaCodigosRecuperacion"></ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/pages/superadmin/dosfactores.js')
@endpush
