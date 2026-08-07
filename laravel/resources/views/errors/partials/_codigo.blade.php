@php
    $usuario = auth()->user();
    $urlVolver = match (true) {
        ! $usuario => route('login'),
        $usuario->esSuperAdmin() => route('superadmin.dashboard'),
        $usuario->tienePanelAdmin() => route('inicio'),
        $usuario->rolSlug === 'docente' => route('docente.dashboard'),
        $usuario->rolSlug === 'estudiante' => route('estudiante.inicio'),
        $usuario->rolSlug === 'acudiente' => route('acudiente.inicio'),
        default => url('/'),
    };
@endphp

<div class="text-center text-lg-start">
    <div class="d-inline-flex align-items-center justify-content-center rounded-4 mb-4"
         style="width:64px;height:64px;background:var(--cs-verde-claro);">
        <i class="bi {{ $icono }} fs-3" style="color: var(--cs-verde-oscuro);"></i>
    </div>

    <p class="font-serif fw-semibold mb-1" style="font-size:3rem; line-height:1; color: var(--bs-secondary-color);">{{ $codigo }}</p>
    <h1 class="h3 font-serif fw-semibold mb-2">{{ $titulo }}</h1>
    <p class="text-secondary mb-4">{{ $mensaje }}</p>

    <a href="{{ $urlVolver }}" class="btn btn-primary px-4">
        <i class="bi bi-arrow-left me-1"></i> {{ $usuario ? 'Volver a mi panel' : 'Ir al inicio de sesión' }}
    </a>
</div>
