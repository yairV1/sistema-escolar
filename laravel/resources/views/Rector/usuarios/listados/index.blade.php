@extends('layouts.rector')

@section('title', $titulo)

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <h1 class="h4 fw-semibold font-serif mb-3">{{ $titulo }}</h1>

        @if ($tab === 'estudiantes')
            @include('Rector.usuarios.listados.partials.estudiantes')
        @elseif ($tab === 'docentes')
            @include('Rector.usuarios.listados.partials.docentes')
        @else
            @include('Rector.usuarios.listados.partials.administrativos')
        @endif
    </div>
@endsection

@push('scripts')
    @vite('resources/js/pages/listados/listados.js')
@endpush
