@extends('layouts.rector')

@section('title', $titulo)

@section('content')
<div class="container-fluid p-3 p-md-4">
    <h1 class="h4 fw-semibold font-serif mb-3">{{ $titulo }}</h1>

    @if ($tab === 'materias')
        @include('Rector.gestion-academica.partials.materias')
    @elseif ($tab === 'cursos')
        @include('Rector.gestion-academica.partials.cursos')
    @elseif ($tab === 'asignaciones')
        @include('Rector.gestion-academica.partials.asignaciones')
    @else
        @include('Rector.gestion-academica.partials.horarios')
    @endif

</div>
@endsection

@push('scripts')
    @vite('resources/js/pages/gestion-academica/gestion-academica.js')
@endpush