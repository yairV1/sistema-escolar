@extends('layouts.auth')

@section('title', 'Iniciar sesión')

@section('content')
    {{-- Avatar animado basado en "Sign In" de ilkhoeri (Uiverse.io, licencia MIT).
         Estados (los pone login.js en data-estado): reposo | mira | tapa | espia --}}
    <div class="auth-mono" id="authMono" data-estado="reposo" aria-hidden="true">
        <svg class="mono-cara" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">
            <ellipse cx="53.7" cy="33" rx="8.3" ry="8.2" fill="#89664c"/>
            <ellipse cx="53.7" cy="33" rx="5.4" ry="5.4" fill="#ffc5d3"/>
            <ellipse cx="10.2" cy="33" rx="8.2" ry="8.2" fill="#89664c"/>
            <ellipse cx="10.2" cy="33" rx="5.4" ry="5.4" fill="#ffc5d3"/>
            <g fill="#89664c">
                <path d="m43.4 10.8c1.1-.6 1.9-.9 1.9-.9-3.2-1.1-6-1.8-8.5-2.1 1.3-1 2.1-1.3 2.1-1.3-20.4-2.9-30.1 9-30.1 19.5h46.4c-.7-7.4-4.8-12.4-11.8-15.2"/>
                <path d="m55.3 27.6c0-9.7-10.4-17.6-23.3-17.6s-23.3 7.9-23.3 17.6c0 2.3.6 4.4 1.6 6.4-1 2-1.6 4.2-1.6 6.4 0 9.7 10.4 17.6 23.3 17.6s23.3-7.9 23.3-17.6c0-2.3-.6-4.4-1.6-6.4 1-2 1.6-4.2 1.6-6.4"/>
            </g>
            <path d="m52 28.2c0-16.9-20-6.1-20-6.1s-20-10.8-20 6.1c0 4.7 2.9 9 7.5 11.7-1.3 1.7-2.1 3.6-2.1 5.7 0 6.1 6.6 11 14.7 11s14.7-4.9 14.7-11c0-2.1-.8-4-2.1-5.7 4.4-2.7 7.3-7 7.3-11.7" fill="#e0ac7e"/>
            <g fill="#3b302a" class="mono-rasgos">
                <path d="m35.1 38.7c0 1.1-.4 2.1-1 2.1-.6 0-1-.9-1-2.1 0-1.1.4-2.1 1-2.1.6.1 1 1 1 2.1"/>
                <path d="m30.9 38.7c0 1.1-.4 2.1-1 2.1-.6 0-1-.9-1-2.1 0-1.1.4-2.1 1-2.1.5.1 1 1 1 2.1"/>
                <ellipse cx="40.7" cy="31.7" rx="3.5" ry="4.5" class="mono-ojo"/>
                <ellipse cx="23.3" cy="31.7" rx="3.5" ry="4.5" class="mono-ojo"/>
            </g>
        </svg>
        <svg class="mono-manos" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">
            <path fill="#89664C" d="M9.4,32.5L2.1,61.9H14c-1.6-7.7,4-21,4-21L9.4,32.5z"/>
            <path fill="#FFD6BB" d="M15.8,24.8c0,0,4.9-4.5,9.5-3.9c2.3,0.3-7.1,7.6-7.1,7.6s9.7-8.2,11.7-5.6c1.8,2.3-8.9,9.8-8.9,9.8s10-8.1,9.6-4.6c-0.3,3.8-7.9,12.8-12.5,13.8C11.5,43.2,6.3,39,9.8,24.4C11.6,17,13.3,25.2,15.8,24.8"/>
            <path fill="#89664C" d="M54.8,32.5l7.3,29.4H50.2c1.6-7.7-4-21-4-21L54.8,32.5z"/>
            <path fill="#FFD6BB" d="M48.4,24.8c0,0-4.9-4.5-9.5-3.9c-2.3,0.3,7.1,7.6,7.1,7.6s-9.7-8.2-11.7-5.6c-1.8,2.3,8.9,9.8,8.9,9.8s-10-8.1-9.7-4.6c0.4,3.8,8,12.8,12.6,13.8c6.6,1.3,11.8-2.9,8.3-17.5C52.6,17,50.9,25.2,48.4,24.8"/>
        </svg>
    </div>

    <h1 class="auth-titulo">Bienvenido de nuevo</h1>
    <p class="auth-subtitulo">Ingresa tus credenciales para acceder al portal.</p>

    <form id="loginForm" class="auth-form" novalidate>
        <div class="auth-campo">
            <label class="auth-label" for="usuario">Correo o número de documento</label>
            <input type="text" class="form-control auth-input" id="usuario" name="usuario"
                   autocomplete="username" spellcheck="false" data-feedback="usuarioError" required>
            <div class="invalid-feedback" id="usuarioError"></div>
        </div>

        <div class="auth-campo">
            <div class="auth-label-fila">
                <label class="auth-label" for="password">Contraseña</label>
                <a href="#" class="auth-enlace" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">¿Olvidaste tu contraseña?</a>
            </div>
            <div class="auth-input-clave">
                <input type="password" class="form-control auth-input" id="password" name="password"
                       autocomplete="current-password" data-feedback="passwordError" required>
                <button type="button" class="auth-ver-clave" id="togglePassword"
                        aria-label="Mostrar contraseña" aria-pressed="false">Mostrar</button>
            </div>
            <div class="invalid-feedback d-block" id="passwordError"></div>
        </div>

        <div class="form-check auth-recordar">
            <input class="form-check-input" type="checkbox" id="remember" name="remember">
            <label class="form-check-label" for="remember">Recordarme en este equipo</label>
        </div>

        <button type="submit" class="btn auth-btn" id="btnLogin">
            <span>Iniciar sesión</span>
            <span class="auth-btn-flecha" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
        </button>
    </form>
@endsection

@push('modales')
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
@endpush

@push('scripts')
    @vite('resources/js/pages/auth/login.js')
@endpush
