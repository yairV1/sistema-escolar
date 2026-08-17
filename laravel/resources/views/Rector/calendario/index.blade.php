@extends(auth()->user()?->esSuperAdmin() ? 'layouts.superadmin' : (in_array(auth()->user()?->rolSlug, ['admin', 'rector']) ? 'layouts.rector' : (auth()->user()?->rolSlug === 'docente' ? 'layouts.docente' : 'layouts.panel')))

@section('title', 'Calendario')

@section('content')
<div class="container-fluid p-3 p-md-4 calendario-page">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 fw-semibold font-serif mb-0">Calendario Institucional</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('calendario.exportar') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-download me-1"></i> Descargar .ics
            </a>
            <button type="button" class="btn btn-outline-secondary btn-sm" data-calendario-suscribirse
                    data-token-url="{{ route('calendario.exportar.token') }}">
                <i class="bi bi-rss me-1"></i> Suscribirme
            </button>
            @if ($puedeGestionarCategorias)
                <a href="{{ route('calendario.categorias.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-tags me-1"></i> Categorías
                </a>
            @endif
            @if ($categoriasCreables->isNotEmpty())
                <button type="button" class="btn btn-primary btn-sm" data-calendario-nuevo
                        data-bs-toggle="modal" data-bs-target="#modalEvento">
                    <i class="bi bi-plus-lg me-1"></i> Nuevo evento
                </button>
            @endif
        </div>
    </div>

    <div class="calendario-layout">
        <aside class="calendario-sidebar" id="calendarioSidebar">
            @include('Rector.calendario.partials._sidebar-categorias', ['categorias' => $categorias])
            @if ($cursosParaFiltro->count() > 1)
                @include('Rector.calendario.partials._filtros', ['cursosParaFiltro' => $cursosParaFiltro])
            @endif
        </aside>

        <div class="calendario-main">
            <div id="calendarioSkeleton">
                @include('Rector.calendario.partials._skeleton')
            </div>
            <div id="calendarioVacio" class="calendario-estado-vacio d-none">
                <i class="bi bi-calendar3"></i>
                <p>No hay nada programado en este rango de fechas.</p>
            </div>
            <div id="calendarioError" class="calendario-estado-error d-none">
                <i class="bi bi-exclamation-triangle"></i>
                <p>No se pudo cargar el calendario. Intenta de nuevo.</p>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-calendario-reintentar>Reintentar</button>
            </div>
            <div id="calendarioRoot" class="d-none"
                 data-feed-url="{{ route('calendario.feed') }}"
                 data-store-url="{{ route('calendario.store') }}"
                 data-usuario-id="{{ auth()->user()->id_usuario }}"
                 data-cursos-ids="{{ $cursosParaFiltro->pluck('id_curso')->implode(',') }}"></div>
        </div>

        @include('Rector.calendario.partials._proximos')
    </div>

</div>
@endsection

@push('modals')
    @if ($categoriasCreables->isNotEmpty())
        @include('Rector.calendario.partials._modal-evento', [
            'categoriasCreables' => $categoriasCreables,
            'personalParaCompartir' => $personalParaCompartir,
        ])
    @endif

    @include('Rector.calendario.partials._side-panel')
@endpush

@push('scripts')
    @vite('resources/js/pages/calendario/calendario.js')
@endpush
