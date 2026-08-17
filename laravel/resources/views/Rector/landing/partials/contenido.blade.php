@php
    $c = fn ($clave) => $contenido[$clave] ?? '';
@endphp

<div class="card mb-3">
    <div class="card-body">
        <h2 class="h6 fw-semibold mb-3"><i class="fas fa-image text-primary me-1"></i> Imagen principal (banner)</h2>
        <p class="text-secondary small mb-3">Se muestra como imagen destacada del Hero en la página pública.</p>
        <div class="d-flex align-items-center gap-3 flex-wrap">
            @if ($c('hero_imagen'))
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($c('hero_imagen')) }}" alt="Imagen principal"
                     style="width:140px;height:90px;object-fit:cover;border-radius:.5rem;">
            @endif
            <form data-landing-form data-url="{{ route('editar-landing.contenido.imagen.update') }}" class="d-flex align-items-center gap-2">
                <input type="file" class="form-control" name="hero_imagen" accept="image/*" required style="max-width:280px;">
                <button type="submit" class="btn btn-outline-primary btn-sm text-nowrap">
                    <i class="fas fa-upload me-1"></i> Subir
                </button>
            </form>
            @if ($c('hero_imagen'))
                <form data-landing-form data-url="{{ route('editar-landing.contenido.imagen.eliminar') }}">
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-trash me-1"></i> Quitar imagen
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

<form id="formContenidoLanding" data-url="{{ route('editar-landing.contenido.update') }}">
    <div class="card mb-3">
        <div class="card-body">
            <h2 class="h6 fw-semibold mb-3"><i class="fas fa-house text-primary me-1"></i> Hero (Inicio)</h2>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Insignia</label>
                    <input type="text" class="form-control" data-clave="hero_badge" value="{{ $c('hero_badge') }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Título</label>
                    <input type="text" class="form-control" data-clave="hero_titulo" value="{{ $c('hero_titulo') }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Descripción</label>
                    <textarea class="form-control" rows="2" data-clave="hero_descripcion">{{ $c('hero_descripcion') }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Estadística 1</label>
                    <div class="input-group mb-1">
                        <input type="text" class="form-control" data-clave="hero_stat1_valor" value="{{ $c('hero_stat1_valor') }}" placeholder="Valor">
                    </div>
                    <input type="text" class="form-control" data-clave="hero_stat1_label" value="{{ $c('hero_stat1_label') }}" placeholder="Etiqueta">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Estadística 2</label>
                    <div class="input-group mb-1">
                        <input type="text" class="form-control" data-clave="hero_stat2_valor" value="{{ $c('hero_stat2_valor') }}" placeholder="Valor">
                    </div>
                    <input type="text" class="form-control" data-clave="hero_stat2_label" value="{{ $c('hero_stat2_label') }}" placeholder="Etiqueta">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Estadística 3</label>
                    <div class="input-group mb-1">
                        <input type="text" class="form-control" data-clave="hero_stat3_valor" value="{{ $c('hero_stat3_valor') }}" placeholder="Valor">
                    </div>
                    <input type="text" class="form-control" data-clave="hero_stat3_label" value="{{ $c('hero_stat3_label') }}" placeholder="Etiqueta">
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h2 class="h6 fw-semibold mb-3"><i class="fas fa-users text-primary me-1"></i> Nosotros</h2>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Párrafo 1</label>
                    <textarea class="form-control" rows="2" data-clave="nosotros_texto1">{{ $c('nosotros_texto1') }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Párrafo 2</label>
                    <textarea class="form-control" rows="2" data-clave="nosotros_texto2">{{ $c('nosotros_texto2') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Misión</label>
                    <textarea class="form-control" rows="2" data-clave="nosotros_mision">{{ $c('nosotros_mision') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Visión</label>
                    <textarea class="form-control" rows="2" data-clave="nosotros_vision">{{ $c('nosotros_vision') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h2 class="h6 fw-semibold mb-3"><i class="fas fa-address-book text-primary me-1"></i> Contacto</h2>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Dirección</label>
                    <input type="text" class="form-control" data-clave="contacto_direccion" value="{{ $c('contacto_direccion') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Teléfono fijo</label>
                    <input type="text" class="form-control" data-clave="contacto_telefono" value="{{ $c('contacto_telefono') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Celular</label>
                    <input type="text" class="form-control" data-clave="contacto_celular" value="{{ $c('contacto_celular') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Correo</label>
                    <input type="email" class="form-control" data-clave="contacto_correo" value="{{ $c('contacto_correo') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Correo admisiones</label>
                    <input type="email" class="form-control" data-clave="contacto_correo_admisiones" value="{{ $c('contacto_correo_admisiones') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Horario</label>
                    <input type="text" class="form-control" data-clave="contacto_horario" value="{{ $c('contacto_horario') }}">
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h2 class="h6 fw-semibold mb-3"><i class="fas fa-share-nodes text-primary me-1"></i> Redes sociales</h2>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Facebook</label>
                    <input type="url" class="form-control" data-clave="social_facebook" value="{{ $c('social_facebook') }}" placeholder="https://facebook.com/...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Instagram</label>
                    <input type="url" class="form-control" data-clave="social_instagram" value="{{ $c('social_instagram') }}" placeholder="https://instagram.com/...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">YouTube</label>
                    <input type="url" class="form-control" data-clave="social_youtube" value="{{ $c('social_youtube') }}" placeholder="https://youtube.com/...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">WhatsApp</label>
                    <input type="text" class="form-control" data-clave="social_whatsapp" value="{{ $c('social_whatsapp') }}" placeholder="573100000000">
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" id="btnGuardarContenido">
            <i class="fas fa-save me-1"></i> Guardar cambios
        </button>
    </div>
</form>
