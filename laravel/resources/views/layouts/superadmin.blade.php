<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-url" content="{{ url('/') }}">
    <title>@yield('title', 'Panel') · SuperAdmin</title>

    <script>
        (function () {
            var stored = localStorage.getItem('cs-theme');
            var theme = stored || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>

    {{-- Tipografía propia del panel SuperAdmin (Archivo + IBM Plex), autoalojada vía
         @font-face en _superadmin.scss — sin dependencia de Google Fonts, a diferencia
         del panel institucional. Ver docs/arquitectura/10-superadmin-plataforma.md §7. --}}

    @vite(['resources/css/app.scss', 'resources/js/app.js', 'resources/js/pages/panel/panel.js'])
    @stack('styles')
    @stack('scripts')
</head>
<body class="has-sidebar" data-panel="superadmin">
    @php
        $sidebarBuilder = new \App\Shared\SidebarBuilder(
            config('superadmin_menu'),
            $currentPage ?? '',
            [auth()->user()?->rolSlug],
        );
        $usuario = auth()->user();
        $nombreCompleto = $usuario ? trim($usuario->nombres.' '.$usuario->apellidos) : 'Invitado';
        $iniciales = collect(explode(' ', $nombreCompleto))
            ->filter()
            ->map(fn ($parte) => mb_strtoupper(mb_substr($parte, 0, 1)))
            ->join('');
        $iniciales = mb_substr($iniciales, 0, 2) ?: '?';
    @endphp

    <button class="sidebar-mobile-trigger" id="topbarMenu" aria-label="Abrir menú">
        <i class="bi bi-list"></i>
    </button>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo"><i class="bi bi-shield-lock"></i></div>
            <div class="sidebar-brand">
                <span class="sb-name">Plataforma</span>
                <span class="sb-sub">Panel SuperAdmin</span>
            </div>
            <button class="sidebar-collapse" id="sidebarCollapse" title="Contraer" aria-label="Contraer menú">
                <i class="bi bi-chevron-left"></i>
            </button>
        </div>

        <nav class="sidebar-nav" aria-label="Navegación principal">
            {!! $sidebarBuilder->render() !!}
        </nav>

        <div class="sidebar-footer">
            <button type="button" class="td-header" id="userMenuToggle" aria-haspopup="true" aria-expanded="false">
                <div class="td-avatar">{{ $iniciales }}</div>
                <div class="td-info">
                    <p class="td-name">{{ $nombreCompleto }}</p>
                    <p class="td-email">{{ $usuario?->correo }}</p>
                </div>
                <i class="bi bi-three-dots-vertical td-kebab"></i>
            </button>

            <div class="td-dropup" id="userMenuDropup">
                <a href="{{ route('perfil.show') }}" class="td-item"><i class="bi bi-person-circle"></i> Mi perfil</a>
                <div class="td-divider"></div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="button" class="td-item td-logout" data-logout>
                        <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <main class="panel-content">
        <div class="panel-topbar-actions position-fixed d-flex gap-2" style="top:16px;right:16px;z-index:1020;">
            @include('layouts.partials._campanita-notificaciones')

            <button type="button"
                    class="btn btn-outline-secondary rounded-circle"
                    style="width:40px;height:40px;"
                    data-theme-toggle
                    aria-label="Cambiar tema">
                <i class="bi bi-moon-stars theme-icon-light"></i>
                <i class="bi bi-sun theme-icon-dark"></i>
            </button>
        </div>

        @yield('content')
    </main>
</body>
</html>
