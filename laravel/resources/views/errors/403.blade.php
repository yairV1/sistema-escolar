@extends('layouts.auth')

@section('title', 'Acceso no permitido')

@section('content')
    @include('errors.partials._codigo', [
        'codigo' => '403',
        'icono' => 'bi-shield-lock',
        'titulo' => 'No tenés acceso a esto',
        'mensaje' => $exception->getMessage() ?: 'Tu cuenta no tiene permiso para ver esta página. Si creés que es un error, contactá al administrador del colegio.',
    ])
@endsection
