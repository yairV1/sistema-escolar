<?php
$colegioConfiguracion = \App\Modules\Colegio\Models\ColegioConfiguracion::singleton();
$usuario = auth()->user();
$rutaSolicitada = '/'.request()->path();

// Ruta real del panel según el rol — solo si existe de verdad (nada de
// inventar destinos). Coordinador/Secretario no tienen home propio hoy
// (ROLES_PANEL_ADMIN solo cubre admin/rector), así que se quedan sin
// este botón en vez de linkear a algo que no existe.
$panelRoute = match (true) {
    ! $usuario => null,
    $usuario->esSuperAdmin() => 'superadmin.dashboard',
    $usuario->tienePanelAdmin() => 'inicio',
    $usuario->rolSlug === 'docente' => 'docente.dashboard',
    $usuario->rolSlug === 'estudiante' => 'estudiante.inicio',
    $usuario->rolSlug === 'acudiente' => 'acudiente.inicio',
    default => null,
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Página no encontrada · {{ $colegioConfiguracion->nombre_colegio }}</title>
    <link rel="icon" href="{{ $colegioConfiguracion->logoUrl ?? asset('favicon.ico') }}">

    {{-- Fija el tema antes del primer paint, igual que layouts.auth --}}
    <script>
        (function () {
            var stored = localStorage.getItem('cs-theme');
            var theme = stored || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.scss'])
</head>
<body>
    <main class="notfound-page">
        <div class="notfound-grid" aria-hidden="true"></div>
        <div class="notfound-cloud" style="width:260px; height:260px; top:-80px; left:-60px;" aria-hidden="true"></div>
        <div class="notfound-cloud" style="width:200px; height:200px; bottom:-60px; right:-40px;" aria-hidden="true"></div>

        <div class="notfound-content">
            <div class="notfound-hero" aria-hidden="true">
                <span class="notfound-hero__ghost">404</span>
                <span class="notfound-hero__main">404</span>
            </div>

            <div class="notfound-radar" aria-hidden="true">
                <div class="notfound-radar__ring notfound-radar__ring--outer"></div>
                <div class="notfound-radar__ring notfound-radar__ring--inner"></div>
                <div class="notfound-radar__sweep"></div>

                <div class="notfound-radar__chip notfound-radar__chip--doc">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 2.5h8l4 4V21a.5.5 0 0 1-.5.5h-11A.5.5 0 0 1 6 21V3a.5.5 0 0 1 .5-.5Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>
                        <path d="M14 2.5V7h4" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>
                        <path d="M9 12h6M9 15.5h6M9 8.5h2.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                    </svg>
                </div>

                <div class="notfound-radar__chip notfound-radar__chip--cap">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 3 2 8l10 5 8-4v6" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round" stroke-linecap="round"/>
                        <path d="M6 10.5V15c0 1.4 2.7 3 6 3s6-1.6 6-3v-4.5" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round" stroke-linecap="round"/>
                    </svg>
                </div>

                <div class="notfound-mascot">
                    <div class="notfound-mascot__body"></div>
                    <div class="notfound-mascot__blush notfound-mascot__blush--left"></div>
                    <div class="notfound-mascot__blush notfound-mascot__blush--right"></div>
                    <div class="notfound-mascot__eyes">
                        <div class="notfound-mascot__eye notfound-mascot__eye--left"><span></span></div>
                        <div class="notfound-mascot__eye notfound-mascot__eye--right"><span></span></div>
                    </div>
                    <div class="notfound-mascot__mouth"></div>
                </div>
            </div>

            <div class="notfound-status" aria-hidden="true">
                <span class="notfound-status__dot"></span> Buscando en el sistema&hellip;
            </div>

            <h1 class="h4 font-serif fw-semibold mb-2">¡Ups! Esta página se perdió en el recreo</h1>
            <p class="text-secondary mb-4">
                La dirección que buscás no existe, se movió o ya no está disponible dentro del sistema escolar.
            </p>

            <div class="notfound-card mb-4">
                <div class="notfound-card__row">
                    <span class="notfound-card__label"><i class="fas fa-triangle-exclamation" aria-hidden="true"></i> Error</span>
                    <span class="notfound-card__value">404 · Página no encontrada</span>
                </div>
                <div class="notfound-card__row">
                    <span class="notfound-card__label"><i class="fas fa-route" aria-hidden="true"></i> Ruta</span>
                    <code class="notfound-card__value notfound-card__value--code" title="{{ $rutaSolicitada }}">{{ $rutaSolicitada }}</code>
                </div>
            </div>

            <div class="notfound-actions">
                <a href="{{ url('/') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left" aria-hidden="true"></i> Volver al inicio
                </a>
                @if ($panelRoute)
                    <a href="{{ route($panelRoute) }}" class="btn btn-outline-primary">
                        <i class="fas fa-gauge-high" aria-hidden="true"></i> Ir a mi panel
                    </a>
                @endif
                <button type="button" class="btn btn-outline-secondary" onclick="history.back()" aria-label="Regresar a la página anterior">
                    <i class="fas fa-rotate-left" aria-hidden="true"></i> Regresar
                </button>
            </div>
        </div>
    </main>
</body>
</html>
