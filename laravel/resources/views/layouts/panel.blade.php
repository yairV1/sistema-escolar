<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-url" content="{{ url('/') }}">
    <title>@yield('title', 'Panel') · Colegio San Cristóbal</title>

    <script>
        (function () {
            var stored = localStorage.getItem('cs-theme');
            var theme = stored || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.scss', 'resources/js/app.js', 'resources/js/pages/panel/panel.js'])
    @stack('styles')
    @stack('scripts')
</head>
<body class="has-sidebar">
    @php
        $sidebarBuilder = new \App\Support\SidebarBuilder(
            config('panel_menu'),
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
        <i class="fas fa-bars"></i>
    </button>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo"><i class="fas fa-graduation-cap"></i></div>
            <div class="sidebar-brand">
                <span class="sb-name">San <strong>Cristóbal</strong></span>
                <span class="sb-sub">Panel {{ $usuario?->rolLabel }}</span>
            </div>
            <button class="sidebar-collapse" id="sidebarCollapse" title="Contraer" aria-label="Contraer menú">
                <i class="fas fa-chevron-left"></i>
            </button>
        </div>

        <nav class="sidebar-nav" aria-label="Navegación principal">
            {!! $sidebarBuilder->render() !!}
        </nav>

        <div class="sidebar-footer">
            <div class="td-header">
                <div class="td-avatar">{{ $iniciales }}</div>
                <div>
                    <p class="td-name">{{ $nombreCompleto }}</p>
                    <p class="td-email">{{ $usuario?->correo }}</p>
                    <span class="td-badge">{{ $usuario?->rolLabel }}</span>
                </div>
            </div>
            <div class="td-divider"></div>
            <ul class="td-menu">
                <li><a href="{{ route('perfil.show') }}" class="td-item"><i class="fas fa-user-circle"></i> Mi perfil</a></li>
            </ul>
            <div class="td-divider"></div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="button" class="td-logout" data-logout title="Cerrar sesión">
                    <i class="fas fa-sign-out-alt"></i> <span>Cerrar sesión</span>
                </button>
            </form>
        </div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <main class="panel-content">
        <button type="button"
                class="btn btn-outline-secondary rounded-circle position-fixed"
                style="width:40px;height:40px;top:16px;right:16px;z-index:1020;"
                data-theme-toggle
                aria-label="Cambiar tema">
            <i class="bi bi-moon-stars theme-icon-light"></i>
            <i class="bi bi-sun theme-icon-dark"></i>
        </button>

        @yield('content')
    </main>
</body>
</html>
