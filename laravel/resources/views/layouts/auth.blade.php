<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-url" content="{{ url('/') }}">
    <title>@yield('title', 'Iniciar sesión') · {{ $colegioConfiguracion->nombre_colegio }}</title>
    @include('layouts.partials._favicon')

    {{-- Fija el tema antes del primer paint para evitar el parpadeo --}}
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

    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    @stack('scripts')
</head>
<body>
    <div class="container-fluid vh-100 p-0">
        <div class="row g-0 h-100">
            <div class="col-lg-6 d-none d-lg-flex auth-visual text-white flex-column justify-content-between p-5">
                <a href="{{ url('/') }}" class="text-white-50 text-decoration-none small d-inline-flex align-items-center gap-2">
                    <i class="bi bi-arrow-left"></i> Volver al sitio
                </a>

                <div>
                    <div class="d-inline-flex align-items-center justify-content-center rounded-4 mb-3"
                         style="width:58px;height:58px;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.25);">
                        <i class="bi bi-mortarboard-fill fs-4"></i>
                    </div>
                    <h1 class="font-serif fw-semibold mb-2">{{ $colegioConfiguracion->nombre_colegio }}</h1>
                    <p class="text-white-50 mb-4">Portal académico institucional</p>

                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex align-items-start gap-3">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-3 flex-shrink-0"
                                 style="width:38px;height:38px;background:rgba(255,255,255,.12);">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div>
                                <h4 class="h6 mb-0">Acceso seguro</h4>
                                <p class="text-white-50 small mb-0">Tus datos y los de tus estudiantes, protegidos.</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-start gap-3">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-3 flex-shrink-0"
                                 style="width:38px;height:38px;background:rgba(255,255,255,.12);">
                                <i class="bi bi-graph-up"></i>
                            </div>
                            <div>
                                <h4 class="h6 mb-0">Seguimiento en tiempo real</h4>
                                <p class="text-white-50 small mb-0">Notas, asistencia y observaciones al día.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="text-white-50 small mb-0">&copy; {{ date('Y') }} {{ $colegioConfiguracion->nombre_colegio }}</p>
            </div>

            <div class="col-lg-6 d-flex align-items-center justify-content-center p-4 p-md-5 position-relative">
                <button type="button"
                        class="btn btn-outline-secondary rounded-circle position-absolute top-0 end-0 m-3 m-md-4"
                        style="width:40px;height:40px;"
                        data-theme-toggle
                        aria-label="Cambiar tema">
                    <i class="bi bi-moon-stars theme-icon-light"></i>
                    <i class="bi bi-sun theme-icon-dark"></i>
                </button>

                <div class="w-100" style="max-width: 420px;">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
</body>
</html>
