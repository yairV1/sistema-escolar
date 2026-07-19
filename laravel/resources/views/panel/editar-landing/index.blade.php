@extends('layouts.panel')

@section('title', 'Editar Landing Page')

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <h1 class="h4 fw-semibold font-serif mb-2">Editar Landing Page</h1>
        <p class="text-secondary small mb-3">
            <i class="fas fa-circle-info me-1"></i>
            Los cambios se reflejan en la página pública del colegio de inmediato.
        </p>

        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'contenido' ? 'active' : '' }}" href="{{ route('editar-landing.index', ['tab' => 'contenido']) }}">
                    <i class="fas fa-align-left me-1"></i> Contenido
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'noticias' ? 'active' : '' }}" href="{{ route('editar-landing.index', ['tab' => 'noticias']) }}">
                    <i class="fas fa-newspaper me-1"></i> Noticias
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'galeria' ? 'active' : '' }}" href="{{ route('editar-landing.index', ['tab' => 'galeria']) }}">
                    <i class="fas fa-images me-1"></i> Galería
                </a>
            </li>
        </ul>

        @if ($tab === 'contenido')
            @include('panel.editar-landing.partials.contenido')
        @elseif ($tab === 'noticias')
            @include('panel.editar-landing.partials.noticias')
        @else
            @include('panel.editar-landing.partials.galeria')
        @endif
    </div>
@endsection

@push('scripts')
    @vite('resources/js/pages/editar-landing/editar-landing.js')
@endpush
