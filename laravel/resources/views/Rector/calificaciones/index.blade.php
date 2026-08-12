@extends('layouts.rector')

@section('title', 'Calificaciones')

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <h1 class="h4 fw-semibold font-serif mb-2">Calificaciones</h1>
        <p class="text-secondary small mb-3">
            <i class="fas fa-circle-info me-1"></i>
            Para crear actividades y cargar notas, entrá a <strong>Gestión Académica → un curso → Asignaturas y profesores asignados → Calificar</strong>.
        </p>

        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'periodos' ? 'active' : '' }}" href="{{ route('calificaciones.index', ['tab' => 'periodos']) }}">
                    <i class="fas fa-calendar-days me-1"></i> Periodos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'tipos-actividad' ? 'active' : '' }}" href="{{ route('calificaciones.index', ['tab' => 'tipos-actividad']) }}">
                    <i class="fas fa-list-check me-1"></i> Tipos de actividad
                </a>
            </li>
        </ul>

        @if ($tab === 'periodos')
            @include('Rector.calificaciones.partials.periodos')
        @else
            @include('Rector.calificaciones.partials.tipos-actividad')
        @endif
    </div>
@endsection

@push('scripts')
    @vite('resources/js/pages/calificaciones/calificaciones.js')
@endpush
