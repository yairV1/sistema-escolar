@extends('layouts.panel')

@section('title', 'Mi horario')

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <h1 class="h4 fw-semibold font-serif mb-4">Mi horario</h1>

        @include('Rector.gestion-academica.partials.horario-grid', ['horarios' => $horarios, 'puedeAgregar' => false])
    </div>
@endsection
