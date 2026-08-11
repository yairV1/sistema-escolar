@extends('layouts.panel')

@section('title', 'Mi horario')

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <h1 class="h4 fw-semibold font-serif mb-4">Mi horario</h1>

        @if ($horarios->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-calendar-week"></i></div>
                <p class="mb-0">Todavía no tenés bloques de horario asignados.</p>
            </div>
        @else
            @include('Rector.gestion-academica.partials.horario-grid', ['horarios' => $horarios, 'puedeAgregar' => false])
        @endif
    </div>
@endsection
