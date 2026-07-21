@extends('layouts.panel')

@section('title', 'Gestión Académica')

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <h1 class="h4 fw-semibold font-serif mb-3">Gestión Académica</h1>

        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'materias' ? 'active' : '' }}" href="{{ route('gestion-academica.index', ['tab' => 'materias']) }}">
                    <i class="fas fa-book me-1"></i> Materias
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'cursos' ? 'active' : '' }}" href="{{ route('gestion-academica.index', ['tab' => 'cursos']) }}">
                    <i class="fas fa-chalkboard me-1"></i> Cursos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'asignaciones' ? 'active' : '' }}" href="{{ route('gestion-academica.index', ['tab' => 'asignaciones']) }}">
                    <i class="fas fa-diagram-project me-1"></i> Asignaciones
                </a>
            </li>
        </ul>

        @if ($tab === 'materias')
            @include('Rector.gestion-academica.partials.materias')
        @elseif ($tab === 'cursos')
            @include('Rector.gestion-academica.partials.cursos')
        @else
            @include('Rector.gestion-academica.partials.asignaciones')
        @endif
    </div>
@endsection

@push('scripts')
    @vite('resources/js/pages/gestion-academica/gestion-academica.js')
@endpush
