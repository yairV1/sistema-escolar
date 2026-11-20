<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-url" content="{{ url('/') }}">
    <title>@yield('title', 'Iniciar sesión') · {{ $colegioConfiguracion->nombre_colegio }}</title>
    <link rel="icon" href="{{ $colegioConfiguracion->logoUrl ?? asset('favicon.ico') }}">

    {{-- Fija el tema antes del primer paint para evitar el parpadeo --}}
    <script>
        (function () {
            var stored = localStorage.getItem('cs-theme');
            var theme = stored || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>

    <link rel="stylesheet" href="{{ asset('vendor/fonts/fonts.css') }}">

    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    @stack('scripts')
</head>
@php
    // Foto del panel visual: la imagen de portada que el colegio configure.
    // Sin foto, el panel usa una escena en CSS con la paleta institucional.
    $authFoto = $colegioConfiguracion->imagen_hero
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($colegioConfiguracion->imagen_hero)
        : null;
@endphp
<body class="auth-shell">
    <div class="auth-ambiente" aria-hidden="true"></div>

    <main class="auth-stage">
        <div class="auth-card">
            <aside class="auth-visual {{ $authFoto ? 'con-foto' : '' }}"
                   @if ($authFoto) style="--auth-foto: url('{{ $authFoto }}')" @endif>
                <div class="auth-visual-escena" aria-hidden="true">
                    <span class="orbe orbe-1"></span>
                    <span class="orbe orbe-2"></span>
                    <span class="orbe orbe-3"></span>
                    <span class="colina colina-1"></span>
                    <span class="colina colina-2"></span>
                </div>

                <div class="auth-marca">
                    @if ($colegioConfiguracion->logoUrl)
                        <img src="{{ $colegioConfiguracion->logoUrl }}" alt="" class="auth-marca-logo">
                    @else
                        <span class="auth-marca-logo auth-marca-icono"><i class="bi bi-mortarboard-fill"></i></span>
                    @endif
                    <span class="auth-marca-nombre">{{ $colegioConfiguracion->nombre_colegio }}</span>
                </div>

                <div class="auth-visual-pie">
                    <p class="auth-visual-frase">Portal académico institucional</p>
                    <ul class="auth-visual-puntos">
                        <li><i class="bi bi-shield-check"></i> Acceso seguro</li>
                        <li><i class="bi bi-graph-up"></i> Notas y asistencia al día</li>
                    </ul>
                </div>
            </aside>

            <section class="auth-panel">
                <div class="auth-panel-barra">
                    <a href="{{ url('/') }}" class="auth-circulo" aria-label="Volver al sitio" title="Volver al sitio">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <button type="button" class="auth-circulo" data-theme-toggle aria-label="Cambiar tema">
                        <i class="bi bi-moon-stars theme-icon-light"></i>
                        <i class="bi bi-sun theme-icon-dark"></i>
                    </button>
                </div>

                <div class="auth-panel-cuerpo">
                    @yield('content')
                </div>

                <p class="auth-legal">&copy; {{ date('Y') }} {{ $colegioConfiguracion->nombre_colegio }}</p>
            </section>
        </div>
    </main>

    {{-- Los modales van fuera de .auth-stage: su z-index crea un contexto de
         apilamiento que dejaría el modal debajo del backdrop de Bootstrap. --}}
    @stack('modales')
</body>
</html>
