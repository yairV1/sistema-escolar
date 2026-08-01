@extends('layouts.panel')

@section('title', 'Horario')

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <h1 class="h4 fw-semibold font-serif mb-3">Horario</h1>

        @include('Acudiente.partials.selector-estudiante')

        @include('Rector.gestion-academica.partials.horario-grid', ['horarios' => $horarios, 'puedeAgregar' => false])
    </div>
@endsection
