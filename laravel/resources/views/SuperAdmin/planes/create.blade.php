@extends('layouts.superadmin')

@section('title', 'Nuevo plan')

@section('content')
<div class="container-fluid p-3 p-md-4">
    <div class="d-flex align-items-center gap-2 mb-3">
        <a href="{{ route('superadmin.planes.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="h4 fw-semibold font-serif mb-0">Nuevo plan</h1>
    </div>

    <div class="card">
        <div class="card-body">
            <form data-plan-form data-url="{{ route('superadmin.planes.store') }}" novalidate>
                @include('SuperAdmin.planes.partials._campos')

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Crear plan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/pages/superadmin/planes.js')
@endpush
