@extends('layouts.auth')

@section('title', 'Restablecer contraseña')

@section('content')
    @if (empty($token))
        <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>Este enlace no incluye un token válido. Solicita uno nuevo desde la pantalla de inicio de sesión.</span>
        </div>
        <a href="{{ route('login') }}" class="btn btn-outline-primary w-100 mt-2">Volver al inicio de sesión</a>
    @else
        <h2 class="font-serif fw-semibold mb-1">Crea tu nueva contraseña</h2>
        <p class="text-secondary mb-4">Debe tener al menos 6 caracteres.</p>

        <form id="resetPasswordForm" novalidate>
            <input type="hidden" id="token" value="{{ $token }}">

            <div class="mb-3">
                <div class="input-group">
                    <div class="form-floating">
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="Nueva contraseña" data-feedback="passwordError" required>
                        <label for="password">Nueva contraseña</label>
                    </div>
                    <button class="btn btn-outline-secondary password-toggle" type="button" id="togglePassword"
                            aria-label="Mostrar contraseña">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <div class="invalid-feedback d-block" id="passwordError"></div>
            </div>

            <div class="form-floating mb-4">
                <input type="password" class="form-control" id="passwordConfirm" name="password_confirmation"
                       placeholder="Confirmar contraseña" data-feedback="passwordConfirmError" required>
                <label for="passwordConfirm">Confirmar contraseña</label>
                <div class="invalid-feedback" id="passwordConfirmError"></div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2" id="btnReset">
                <i class="bi bi-check2-circle me-1"></i> Restablecer contraseña
            </button>
        </form>
    @endif
@endsection

@push('scripts')
    @vite('resources/js/pages/auth/resetPassword.js')
@endpush
