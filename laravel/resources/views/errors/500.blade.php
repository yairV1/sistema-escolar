@extends('layouts.auth')

@section('title', 'Algo salió mal')

@section('content')
    @include('errors.partials._codigo', [
        'codigo' => '500',
        'icono' => 'bi-cone-striped',
        'titulo' => 'Algo salió mal de nuestro lado',
        'mensaje' => 'Ya quedó registrado. Intentá de nuevo en un momento, o avisá al equipo de soporte si el problema sigue.',
    ])
@endsection
