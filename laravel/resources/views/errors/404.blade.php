@extends('layouts.auth')

@section('title', 'Página no encontrada')

@section('content')
    @include('errors.partials._codigo', [
        'codigo' => '404',
        'icono' => 'bi-signpost-2',
        'titulo' => 'No encontramos esta página',
        'mensaje' => 'El enlace puede estar mal escrito o la página ya no existe. Revisá la dirección o volvé al inicio.',
    ])
@endsection
