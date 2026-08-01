@extends('layouts.auth')

@section('title', 'Verificación en dos pasos')

@section('content')
    <h2 class="font-serif fw-semibold mb-1">Verificación en dos pasos</h2>
    <p class="text-secondary mb-4">Ingresa el código de tu app autenticadora, o un código de recuperación.</p>

    <form id="twoFactorChallengeForm" data-url="{{ route('2fa.challenge.store') }}" novalidate>
        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="codigo" name="codigo"
                   placeholder="Código" data-feedback="codigoError" autocomplete="one-time-code" required autofocus>
            <label for="codigo">Código de verificación</label>
            <div class="invalid-feedback" id="codigoError"></div>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2" id="btnVerificar">
            <i class="bi bi-shield-check me-1"></i> Verificar
        </button>
    </form>

    <div class="text-center mt-3">
        <a href="{{ route('login') }}" class="small">Volver al inicio de sesión</a>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/pages/auth/twoFactorChallenge.js')
@endpush
