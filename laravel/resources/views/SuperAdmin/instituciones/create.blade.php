@extends('layouts.superadmin')

@section('title', 'Nueva institución')

@section('content')
<div class="container-fluid p-3 p-md-4">
    <div class="d-flex align-items-center gap-2 mb-3">
        <a href="{{ route('superadmin.instituciones.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="h4 fw-semibold font-serif mb-0">Nueva institución</h1>
    </div>

    <div class="card">
        <div class="card-body">
            <form data-institucion-form data-url="{{ route('superadmin.instituciones.store') }}" novalidate>
                @include('SuperAdmin.instituciones.partials._campos')

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Crear institución</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/pages/superadmin/instituciones.js')
@endpush
