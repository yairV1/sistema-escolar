@extends('layouts.superadmin')

@section('title', 'Configuración global')

@section('content')
<div class="container-fluid p-3 p-md-4">
    <h1 class="h4 fw-semibold font-serif mb-3">Configuración global de la plataforma</h1>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3">Parámetros por defecto</h2>
                    <form data-crud-form data-url="{{ route('superadmin.configuracion.update') }}" novalidate>
                        <div class="mb-3">
                            <label class="form-label">Límite de usuarios por defecto (plan sin límite explícito)</label>
                            <input type="number" min="1" class="form-control" name="limite_usuarios_default"
                                   value="{{ old('limite_usuarios_default', $configuracion->limite_usuarios_default) }}"
                                   data-feedback="err-limite-usuarios">
                            <div class="invalid-feedback" id="err-limite-usuarios"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Límite de instituciones de la plataforma</label>
                            <input type="number" min="1" class="form-control" name="limite_instituciones"
                                   value="{{ old('limite_instituciones', $configuracion->limite_instituciones) }}"
                                   data-feedback="err-limite-instituciones">
                            <div class="invalid-feedback" id="err-limite-instituciones"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Plantilla de comunicado global por defecto</label>
                            <textarea class="form-control" name="plantilla_comunicado_default" rows="4"
                                      data-feedback="err-plantilla">{{ old('plantilla_comunicado_default', $configuracion->plantilla_comunicado_default) }}</textarea>
                            <div class="invalid-feedback" id="err-plantilla"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email de contacto de soporte</label>
                            <input type="email" class="form-control" name="soporte_email_contacto"
                                   value="{{ old('soporte_email_contacto', $configuracion->soporte_email_contacto) }}"
                                   data-feedback="err-soporte-email">
                            <div class="invalid-feedback" id="err-soporte-email"></div>
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3">Integración WhatsApp Cloud API</h2>
                    <p class="text-secondary small">
                        Las credenciales de esta integración se gestionan por variables de entorno
                        (<code>.env</code>, <code>config('services.whatsapp_cloud')</code>), no desde este panel —
                        un secreto de integración no debe vivir en una tabla editable.
                    </p>
                    <dl class="row mb-0 small">
                        <dt class="col-6">Estado</dt>
                        <dd class="col-6">
                            @if ($whatsapp['token'])
                                <span class="badge text-bg-success">Configurado</span>
                            @else
                                <span class="badge text-bg-secondary">Sin configurar</span>
                            @endif
                        </dd>
                        <dt class="col-6">Versión de API</dt>
                        <dd class="col-6">{{ $whatsapp['api_version'] }}</dd>
                        <dt class="col-6">Plantilla</dt>
                        <dd class="col-6">{{ $whatsapp['template_name'] ?: '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/pages/superadmin/configuracion.js')
@endpush
