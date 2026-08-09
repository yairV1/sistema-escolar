@extends('layouts.superadmin')

@section('title', 'Editar institución')

@section('content')
<div class="container-fluid p-3 p-md-4">
    <div class="d-flex align-items-center gap-2 mb-3">
        <a href="{{ route('superadmin.instituciones.show', $institucion) }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="h4 fw-semibold font-serif mb-0">Editar {{ $institucion->nombre }}</h1>
    </div>

    <form data-institucion-form data-url="{{ route('superadmin.instituciones.update', $institucion) }}" novalidate>
        <div class="card">
            <div class="card-body">
                @include('SuperAdmin.instituciones.partials._campos')
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                @include('SuperAdmin.instituciones.partials._campos-rector')
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
    @vite('resources/js/pages/superadmin/instituciones.js')
@endpush
