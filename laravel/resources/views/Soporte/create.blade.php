@extends('layouts.panel')

@section('title', 'Soporte')

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <h1 class="h4 fw-semibold font-serif mb-1">Enviar soporte</h1>
        <p class="text-secondary mb-4">¿Encontraste un problema o necesitas ayuda con el sistema? Escríbele al equipo que lo administra.</p>

        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-body">
                        <form id="formSoporte" data-crud-form data-url="{{ route('soporte.store') }}" enctype="multipart/form-data" novalidate>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Asunto *</label>
                                    <input type="text" class="form-control" name="asunto" maxlength="150"
                                           placeholder="Ej: No puedo ver el horario de mi curso" data-feedback="err-sop-asunto" required>
                                    <div class="invalid-feedback" id="err-sop-asunto"></div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Mensaje *</label>
                                    <textarea class="form-control" name="mensaje" rows="6" maxlength="2000"
                                              placeholder="Contanos qué pasó, en qué pantalla estabas y qué esperabas que sucediera."
                                              data-feedback="err-sop-mensaje" required></textarea>
                                    <div class="invalid-feedback" id="err-sop-mensaje"></div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Captura de pantalla (opcional)</label>
                                    <input type="file" class="form-control" name="imagen" accept="image/png,image/jpeg,image/webp" data-feedback="err-sop-imagen">
                                    <div class="form-text">JPG, PNG o WEBP, máximo 2 MB.</div>
                                    <div class="invalid-feedback" id="err-sop-imagen"></div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i> Enviar soporte
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/pages/soporte/soporte.js')
@endpush
