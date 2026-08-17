<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-url" content="{{ url('/') }}">
    <title>@yield('title', __('Panel')) · {{ (auth()->user()?->institucion?->nombre) ?? $colegioConfiguracion->nombre_colegio }}</title>

    <script>
        (function () {
            var serverTheme = @json(auth()->user()?->tema);
            var stored = serverTheme || localStorage.getItem('cs-theme');
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
<body class="has-sidebar" data-panel="docente" style="{{ \App\Shared\AccentColor::inlineStyle(auth()->user()?->color_acento) }}">
    @php
        $sidebarBuilder = new \App\Shared\SidebarBuilder(
            config('panel_menu'),
            $currentPage ?? '',
            [auth()->user()?->rolSlug],
        );
        $usuario = auth()->user();
        // Institución del usuario logueado, no la fila global de colegio_configuracion:
        // en una plataforma multi-tenant cada admin ve el nombre/logo de SU institución,
        // no la de la #1 codificada. $colegioConfiguracion queda como respaldo defensivo.
        $institucionUsuario = $usuario?->institucion;
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
            <div class="sidebar-logo">
                @if ($institucionUsuario?->logoUrl)
                    <img src="{{ $institucionUsuario->logoUrl }}" alt="{{ $institucionUsuario->nombre }}">
                @else
                    <i class="bi bi-mortarboard"></i>
                @endif
            </div>
            <div class="sidebar-brand">
                <span class="sb-name">{{ $institucionUsuario->nombre ?? $colegioConfiguracion->nombre_colegio }}</span>
                <span class="sb-sub">{{ __('Panel :rol', ['rol' => __($usuario?->rolLabel ?? '')]) }}</span>
            </div>
            <button class="sidebar-collapse" id="sidebarCollapse" title="Contraer" aria-label="Contraer menú">
                <i class="bi bi-chevron-left"></i>
            </button>
        </div>

        <nav class="sidebar-nav" aria-label="Navegación principal">
            {!! $sidebarBuilder->render() !!}
        </nav>

        <div class="sidebar-footer">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="button" class="sidebar-logout" data-logout data-label="{{ __('Cerrar sesión') }}">
                    <i class="bi bi-box-arrow-right"></i> <span>{{ __('Cerrar sesión') }}</span>
                </button>
            </form>
        </div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <main class="panel-content">
        <header class="dc-topbar">
            <div class="rc-search" data-buscar-url="{{ route('buscar') }}">
                <i class="bi bi-search"></i>
                <input type="search" placeholder="{{ __('Buscar en el panel…') }}" aria-label="{{ __('Buscar') }}" autocomplete="off">
            </div>

            <div class="dc-topbar-actions">
                <div class="icon-toolbar">
                    @include('layouts.partials._campanita-notificaciones')

                    <button type="button"
                            class="icon-toolbar-btn"
                            data-theme-toggle
                            aria-label="{{ __('Cambiar tema') }}">
                        <i class="bi bi-moon-stars theme-icon-light"></i>
                        <i class="bi bi-sun theme-icon-dark"></i>
                    </button>
                </div>

                <button type="button" class="dc-topbar-user" id="userMenuToggle" aria-haspopup="true" aria-expanded="false">
                    <div class="td-avatar">
                        @if ($usuario?->fotoPerfilUrl)
                            <img src="{{ $usuario->fotoPerfilUrl }}" alt="{{ $nombreCompleto }}">
                        @else
                            {{ $iniciales }}
                        @endif
                    </div>
                    <div class="td-info d-none d-sm-block">
                        <p class="td-name">{{ $nombreCompleto }}</p>
                        <p class="td-email">{{ __($usuario?->rolLabel ?? '') }}</p>
                    </div>
                    <i class="bi bi-chevron-down dc-topbar-user-chevron"></i>
                </button>

                <div class="td-dropup" id="userMenuDropup">
                    <a href="{{ route('perfil.show') }}" class="td-item"><i class="bi bi-person-circle"></i> {{ __('Mi perfil') }}</a>
                    <div class="td-divider"></div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="button" class="td-item td-logout" data-logout>
                            <i class="bi bi-box-arrow-right"></i> {{ __('Cerrar sesión') }}
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <div class="dc-page">
            @yield('content')
        </div>
    </main>

    {{-- Mismo motivo que en layouts/rector.blade.php: algunas páginas (ej.
         calificaciones/asignacion.blade.php) extienden uno u otro layout
         según el rol, así que este stack tiene que existir acá también. --}}
    @stack('modals')
</body>
</html>
