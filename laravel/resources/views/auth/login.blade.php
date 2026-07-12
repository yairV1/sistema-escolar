@extends('layouts.auth')

@section('title', 'Iniciar sesión')

@section('content')
    <h2 class="font-serif fw-semibold mb-1">Bienvenido de nuevo</h2>
    <p class="text-secondary mb-4">Ingresa tus credenciales para acceder al portal.</p>

    <form id="loginForm" novalidate>
        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="usuario" name="usuario"
                   placeholder="Correo o número de documento" data-feedback="usuarioError" required>
            <label for="usuario">Correo o número de documento</label>
            <div class="invalid-feedback" id="usuarioError"></div>
        </div>

        <div class="mb-2">
            <div class="input-group">
                <div class="form-floating">
                    <input type="password" class="form-control" id="password" name="password"
                           placeholder="Contraseña" data-feedback="passwordError" required>
                    <label for="password">Contraseña</label>
                </div>
                <button class="btn btn-outline-secondary password-toggle" type="button" id="togglePassword"
                        aria-label="Mostrar contraseña">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
            <div class="invalid-feedback d-block" id="passwordError"></div>
        </div>

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                <label class="form-check-label small" for="remember">Recordarme</label>
            </div>
            <a href="#" class="small" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">
                ¿Olvidaste tu contraseña?
            </a>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2" id="btnLogin">
            <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar sesión
        </button>
    </form>

    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-2">
                <div class="modal-header border-0">
                    <h5 class="modal-title font-serif">Recuperar contraseña</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="text-secondary small">
                        Ingresa tu correo institucional y te enviaremos un enlace para restablecer tu contraseña.
                    </p>

                    <form id="forgotPasswordForm" novalidate>
                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" id="recoveryEmail" name="correo"
                                   placeholder="Correo institucional" data-feedback="recoveryEmailError" required>
                            <label for="recoveryEmail">Correo institucional</label>
                            <div class="invalid-feedback" id="recoveryEmailError"></div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" id="btnRecovery">Enviar enlace</button>
                    </form>

                    <div class="alert alert-success d-none mt-3" id="forgotSuccess" role="alert"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/pages/auth/login.js')
@endpush
