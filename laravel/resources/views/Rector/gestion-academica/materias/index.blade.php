@extends('layouts.panel')

@section('title', 'Materias')

@section('content')
<div class="container-fluid p-3 p-md-4">
    <h1 class="h4 fw-semibold font-serif mb-3">Materias</h1>

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'materias' ? 'active' : '' }}" href="{{ route('gestion-academica.materias.index', ['tab' => 'materias']) }}">
                <i class="fas fa-book me-1"></i> Materias
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'areas' ? 'active' : '' }}" href="{{ route('gestion-academica.materias.index', ['tab' => 'areas']) }}">
                <i class="fas fa-diagram-project me-1"></i> Áreas
            </a>
        </li>
    </ul>

    @if ($tab === 'materias')
        @include('Rector.gestion-academica.materias.partials.materias')
    @else
        @include('Rector.gestion-academica.materias.partials.areas')
    @endif
</div>
@endsection

@push('scripts')
    @vite('resources/js/pages/gestion-academica/gestion-academica.js')
@endpush
