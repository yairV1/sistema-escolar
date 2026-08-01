@extends('layouts.superadmin')

@section('title', 'Editar módulo')

@section('content')
<div class="container-fluid p-3 p-md-4">
    <div class="d-flex align-items-center gap-2 mb-3">
        <a href="{{ route('superadmin.modulos.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="h4 fw-semibold font-serif mb-0">Editar {{ $modulo->nombre }}</h1>
    </div>

    <div class="card">
        <div class="card-body">
            <form data-modulo-form data-url="{{ route('superadmin.modulos.update', $modulo) }}" novalidate>
                @include('SuperAdmin.modulos.partials._campos')

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/pages/superadmin/modulos.js')
@endpush
