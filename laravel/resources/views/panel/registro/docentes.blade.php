@extends('layouts.panel')

@section('title', $profesor ? 'Editar docente' : 'Registro de docentes')

@php
    $u = $profesor?->usuario;
    $nombresPartes = $u ? explode(' ', $u->nombres, 2) : [];
    $apellidosPartes = $u ? explode(' ', $u->apellidos, 2) : [];
@endphp

@section('content')
    <div class="container-fluid p-3 p-md-4" style="max-width: 700px;">
        <div class="d-flex align-items-center gap-2 mb-3">
            <a href="{{ route('listados', ['tab' => 'docentes']) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="h4 fw-semibold font-serif mb-0">{{ $profesor ? 'Editar docente' : 'Registro de docentes' }}</h1>
        </div>

        <div class="wizard-pills">
            <div class="wizard-pill active" data-step-link="1"><span class="step-num"><span>1</span></span> Datos personales</div>
            <div class="wizard-pill" data-step-link="2"><span class="step-num"><span>2</span></span> Datos profesionales</div>
        </div>

        <form id="wizardForm" novalidate>
            <div class="wizard-step card mb-3" data-step="1">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Primer nombre *</label>
                            <input type="text" class="form-control" name="primer_nombre" id="primer_nombre"
                                   value="{{ $nombresPartes[0] ?? '' }}" data-feedback="err-primer_nombre" required>
                            <div class="invalid-feedback" id="err-primer_nombre"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Segundo nombre</label>
                            <input type="text" class="form-control" name="segundo_nombre" id="segundo_nombre" value="{{ $nombresPartes[1] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Primer apellido *</label>
                            <input type="text" class="form-control" name="primer_apellido" id="primer_apellido"
                                   value="{{ $apellidosPartes[0] ?? '' }}" data-feedback="err-primer_apellido" required>
                            <div class="invalid-feedback" id="err-primer_apellido"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Segundo apellido</label>
                            <input type="text" class="form-control" name="segundo_apellido" id="segundo_apellido" value="{{ $apellidosPartes[1] ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tipo de documento *</label>
                            <select class="form-select" name="tipo_documento" id="tipo_documento" data-feedback="err-tipo_documento" required>
                                <option value="CC" @selected(($u?->tipo_documento ?? 'CC') === 'CC')>Cédula de ciudadanía</option>
                                <option value="CE" @selected($u?->tipo_documento === 'CE')>Cédula de extranjería</option>
                                <option value="PAS" @selected($u?->tipo_documento === 'PAS')>Pasaporte</option>
                            </select>
                            <div class="invalid-feedback" id="err-tipo_documento"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Número de documento *</label>
                            <input type="text" class="form-control" name="numero_documento" id="numero_documento"
                                   value="{{ $u?->numero_documento }}" data-feedback="err-numero_documento" required>
                            <div class="invalid-feedback" id="err-numero_documento"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Teléfono</label>
                            <input type="text" class="form-control" name="telefono" id="telefono" value="{{ $u?->telefono }}" data-feedback="err-telefono">
                            <div class="invalid-feedback" id="err-telefono"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Correo *</label>
                            <input type="email" class="form-control" name="correo" id="correo" value="{{ $u?->correo }}" data-feedback="err-correo" required>
                            <div class="invalid-feedback" id="err-correo"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="wizard-step card mb-3 d-none" data-step="2">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Profesión</label>
                            <input type="text" class="form-control" name="profesion" id="profesion" placeholder="Ej. Licenciado en Matemáticas" value="{{ $profesor?->profesion }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Especialidad</label>
                            <input type="text" class="form-control" name="especialidad" id="especialidad" placeholder="Ej. Educación básica" value="{{ $profesor?->especialidad }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha de ingreso</label>
                            <input type="date" class="form-control" name="fecha_ingreso" id="fecha_ingreso" value="{{ $profesor?->fecha_ingreso ?? now()->toDateString() }}">
                        </div>
                    </div>
                    <div class="alert alert-secondary mt-3 mb-0 small">
                        <i class="fas fa-circle-info me-1"></i>
                        La asignación de materias y cursos se hace desde <strong>Gestión Académica</strong> una vez el docente esté registrado.
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary d-none" data-wizard-prev>
                    <i class="fas fa-arrow-left me-1"></i> Anterior
                </button>
                <div class="ms-auto d-flex gap-2">
                    <button type="button" class="btn btn-primary" data-wizard-next data-wizard-hide-last>
                        Siguiente <i class="fas fa-arrow-right ms-1"></i>
                    </button>
                    <button type="submit" class="btn btn-primary d-none" id="btnGuardar" data-wizard-only-last>
                        <i class="fas fa-save me-1"></i> {{ $profesor ? 'Guardar cambios' : 'Registrar docente' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>window.__RUTA_GUARDAR__ = @json($profesor ? route('registro.docentes.update', $profesor) : route('registro.docentes.store'));</script>
    @vite('resources/js/pages/registro/docentes.js')
@endpush
