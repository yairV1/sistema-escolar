@extends('layouts.rector')

@section('title', 'Configuración del Colegio')

@section('content')
    <div class="container-fluid p-3 p-md-4 pb-5 configuracion-colegio">

        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
            <div>
                <h1 class="h4 fw-semibold font-serif mb-2">Configuración del Colegio</h1>
                <p class="text-secondary small mb-0" style="max-width:52ch;">
                    <i class="fas fa-circle-info me-1"></i>
                    Información institucional usada en comunicados, boletines y la página pública.
                </p>
            </div>
            <button type="submit" form="formConfiguracion" class="btn btn-primary" id="btnGuardarConfiguracion">
                <i class="fas fa-save me-1"></i> Guardar cambios
            </button>
        </div>

        <form id="formConfiguracion" data-url="{{ route('configuracion-colegio.update') }}" enctype="multipart/form-data" novalidate>

            {{-- ============ IDENTIDAD ============ --}}
            <div class="card mb-3">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3"><i class="fas fa-building-columns text-primary me-1"></i> Identidad</h2>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nombre del colegio <span class="text-danger">*</span></label>
                            <input type="text" name="nombre_colegio" class="form-control" maxlength="150"
                                   value="{{ $configuracion->nombre_colegio }}" data-feedback="err-nombre_colegio" required>
                            <div class="invalid-feedback" id="err-nombre_colegio"></div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Logo institucional <span class="text-muted fw-normal">(PNG o JPG, fondo transparente recomendado — máx. 2 MB)</span></label>
                            <div class="logo-row">
                                <div class="logo-dropzone{{ $configuracion->logo_url ? ' has-preview' : '' }}" id="logoDropzone" tabindex="0" role="button" aria-label="Subir logo">
                                    <img src="{{ $configuracion->logo_url }}" alt="Logo actual" id="logoPreview" class="{{ $configuracion->logo_url ? '' : 'd-none' }}">
                                    <div class="dz-placeholder {{ $configuracion->logo_url ? 'd-none' : '' }}" id="logoPlaceholder">
                                        <div class="dz-icon"><i class="fas fa-cloud-arrow-up"></i></div>
                                        <span><b>Haz clic</b> o arrastra una imagen</span>
                                    </div>
                                    <button type="button" class="dz-remove {{ $configuracion->logo_url ? '' : 'd-none' }}" id="logoRemove" title="Quitar logo">
                                        <i class="fas fa-xmark"></i>
                                    </button>
                                    <input type="file" name="logo" id="logoInput" accept="image/*" class="d-none" data-feedback="err-logo">
                                </div>
                                <input type="hidden" name="logo_removido" id="logoRemovido" value="0">
                                <div class="logo-meta">
                                    <p class="hint text-secondary small mb-0">
                                        Se muestra en el sidebar del panel, en el encabezado de comunicados por correo y en boletines.
                                    </p>
                                    <div class="invalid-feedback d-block" id="err-logo"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Descripción / misión <span class="text-muted fw-normal">(opcional, máx. 300 caracteres)</span></label>
                            <textarea name="descripcion" class="form-control" rows="3" maxlength="300" id="descripcionInput"
                                      data-feedback="err-descripcion">{{ $configuracion->descripcion }}</textarea>
                            <div class="invalid-feedback" id="err-descripcion"></div>
                            <p class="hint text-secondary small mb-0 mt-1"><span id="descripcionCount">{{ mb_strlen($configuracion->descripcion ?? '') }}</span> / 300 caracteres</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============ UBICACIÓN Y CONTACTO ============ --}}
            <div class="card mb-3">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3"><i class="fas fa-location-dot text-primary me-1"></i> Ubicación y contacto</h2>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Dirección <span class="text-danger">*</span></label>
                            <input type="text" name="direccion" class="form-control" maxlength="200"
                                   value="{{ $configuracion->direccion }}" data-feedback="err-direccion" required>
                            <div class="invalid-feedback" id="err-direccion"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Ciudad <span class="text-danger">*</span></label>
                            <input type="text" name="ciudad" class="form-control" maxlength="100"
                                   value="{{ $configuracion->ciudad }}" data-feedback="err-ciudad" required>
                            <div class="invalid-feedback" id="err-ciudad"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Departamento <span class="text-danger">*</span></label>
                            <input type="text" name="departamento" class="form-control" maxlength="100"
                                   value="{{ $configuracion->departamento }}" data-feedback="err-departamento" required>
                            <div class="invalid-feedback" id="err-departamento"></div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">País <span class="text-danger">*</span></label>
                            <input type="text" name="pais" class="form-control" maxlength="100"
                                   value="{{ $configuracion->pais }}" data-feedback="err-pais" required>
                            <div class="invalid-feedback" id="err-pais"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Teléfono <span class="text-danger">*</span></label>
                            <input type="tel" name="telefono" class="form-control" maxlength="30"
                                   value="{{ $configuracion->telefono }}" data-feedback="err-telefono" required>
                            <div class="invalid-feedback" id="err-telefono"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Correo institucional <span class="text-danger">*</span></label>
                            <input type="email" name="email_institucional" class="form-control" maxlength="150"
                                   value="{{ $configuracion->email_institucional }}" data-feedback="err-email_institucional" required>
                            <div class="invalid-feedback" id="err-email_institucional"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Sitio web <span class="text-muted fw-normal">(opcional)</span></label>
                            <input type="url" name="sitio_web" class="form-control" maxlength="200" placeholder="https://..."
                                   value="{{ $configuracion->sitio_web }}" data-feedback="err-sitio_web">
                            <div class="invalid-feedback" id="err-sitio_web"></div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        {{-- ============ GALERÍA ============ --}}
        <div class="card mb-3">
            <div class="card-body">
                <h2 class="h6 fw-semibold mb-1"><i class="fas fa-images text-primary me-1"></i> Galería de imágenes institucionales</h2>
                <p class="text-secondary small mb-3">Fachada, aulas y espacios que se muestran en la página pública. Arrastra las tarjetas para reordenar.</p>

                <div id="galeriaGrid" class="gallery-grid" data-url-reordenar="{{ route('configuracion-colegio.imagenes.reordenar') }}">
                    @foreach ($imagenes as $imagen)
                        <div class="g-item" draggable="true" data-id="{{ $imagen->id_imagen }}">
                            <div class="g-thumb" style="background-image:url('{{ $imagen->imagen_url }}');">
                                <span class="g-drag" title="Arrastrar para reordenar"><i class="fas fa-up-down-left-right"></i></span>
                                <button type="button" class="g-del" title="Eliminar"
                                        data-url="{{ route('configuracion-colegio.imagenes.eliminar', $imagen) }}">
                                    <i class="fas fa-xmark"></i>
                                </button>
                            </div>
                            <div class="g-meta">
                                <span class="g-tag">{{ $tipos[$imagen->tipo] ?? 'Otro' }}</span>
                                <div class="g-cap text-truncate">{{ $imagen->descripcion ?: '—' }}</div>
                            </div>
                        </div>
                    @endforeach

                    <button type="button" class="g-item add-tile" id="btnAgregarImagen" {{ $imagenes->count() >= 12 ? 'disabled' : '' }}
                            data-bs-toggle="modal" data-bs-target="#modalNuevaImagen">
                        <i class="fas fa-plus"></i>
                        <span>Agregar imagen</span>
                    </button>
                </div>

                @if ($imagenes->isEmpty())
                    <div class="empty-state mt-2">
                        <div class="empty-icon"><i class="fas fa-images"></i></div>
                        <p class="mb-0">Aún no hay fotos en la galería.</p>
                    </div>
                @endif

                <p class="text-secondary small mb-0 mt-3" id="galeriaContador">{{ $imagenes->count() }} imágenes · máx. 12</p>
            </div>
        </div>
    </div>
@endsection

@push('modals')
    {{-- Modal: nueva imagen de galería --}}
    <div class="modal fade" id="modalNuevaImagen" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form data-galeria-form data-url="{{ route('configuracion-colegio.imagenes.store') }}" enctype="multipart/form-data" novalidate>
                    <div class="modal-header">
                        <h5 class="modal-title font-serif">Agregar imagen</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Imagen <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" name="imagen" accept="image/*" data-feedback="err-nf-imagen" required>
                                <div class="invalid-feedback" id="err-nf-imagen"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Tipo <span class="text-danger">*</span></label>
                                <select class="form-select" name="tipo" data-feedback="err-nf-tipo" required>
                                    @foreach ($tipos as $valor => $etiqueta)
                                        <option value="{{ $valor }}">{{ $etiqueta }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="err-nf-tipo"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Descripción <span class="text-muted fw-normal">(opcional)</span></label>
                                <input type="text" class="form-control" name="descripcion" maxlength="150" placeholder="Ej: Entrada principal">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    @vite('resources/js/pages/configuracion-colegio/configuracion-colegio.js')
@endpush
