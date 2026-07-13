@extends('layouts.panel')

@section('title', 'Listados')

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <h1 class="h4 fw-semibold font-serif mb-3">Listados</h1>

        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'estudiantes' ? 'active' : '' }}" href="{{ route('listados', ['tab' => 'estudiantes']) }}">
                    <i class="fas fa-user-graduate me-1"></i> Estudiantes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'docentes' ? 'active' : '' }}" href="{{ route('listados', ['tab' => 'docentes']) }}">
                    <i class="fas fa-chalkboard-teacher me-1"></i> Docentes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'administrativos' ? 'active' : '' }}" href="{{ route('listados', ['tab' => 'administrativos']) }}">
                    <i class="fas fa-user-tie me-1"></i> Administrativos
                </a>
            </li>
        </ul>

        @if ($tab === 'estudiantes')
            @include('panel.listados.partials.estudiantes')
        @elseif ($tab === 'docentes')
            @include('panel.listados.partials.docentes')
        @else
            @include('panel.listados.partials.administrativos')
        @endif
    </div>
@endsection

@push('scripts')
    @vite('resources/js/pages/listados/listados.js')
@endpush
