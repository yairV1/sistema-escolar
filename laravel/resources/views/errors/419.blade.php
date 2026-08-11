@extends('layouts.auth')

@section('title', 'Sesión expirada')

@section('content')
    @include('errors.partials._codigo', [
        'codigo' => '419',
        'icono' => 'bi-hourglass-split',
        'titulo' => 'Tu sesión expiró',
        'mensaje' => 'Pasó mucho tiempo desde que cargaste esta página. Volvé a intentarlo.',
    ])
@endsection
