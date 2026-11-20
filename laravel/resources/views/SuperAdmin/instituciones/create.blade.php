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

    @if ($requisitosPendientes !== [])
        <div class="alert alert-danger" role="alert">
            <h2 class="h6">Aprovisionamiento bloqueado</h2>
            <p>Aplica y verifica B2–B8 en una ventana de mantenimiento. Esta pantalla no ejecuta migraciones.</p>
            <ul class="mb-0">
                @foreach ($requisitosPendientes as $requisito)
                    <li>{{ $requisito }}</li>
                @endforeach
            </ul>
        </div>
    @else
        <form data-institucion-form data-url="{{ route('superadmin.instituciones.store') }}" novalidate>
            <div class="card">
                <div class="card-body">
                    @include('SuperAdmin.instituciones.partials._campos')
                    <div class="mt-3 col-md-4">
                        <label class="form-label" for="estado">Estado inicial</label>
                        <select class="form-select" name="estado" id="estado" data-feedback="err-estado">
                            <option value="activa" @selected(old('estado', 'activa') === 'activa')>Activa</option>
                            <option value="suspendida" @selected(old('estado') === 'suspendida')>Suspendida</option>
                        </select>
                        <div class="invalid-feedback" id="err-estado"></div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    @include('SuperAdmin.instituciones.partials._campos-administrador')
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Crear institución</button>
            </div>
        </form>
    @endif
</div>
@endsection

@push('scripts')
    @vite('resources/js/pages/superadmin/instituciones.js')
@endpush
