<div class="d-flex justify-content-end mb-3">
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalNuevaFoto">
        <i class="fas fa-plus me-1"></i> Agregar foto
    </button>
</div>

@if ($galeria->isEmpty())
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-images"></i></div>
        <p class="mb-0">Aún no hay fotos en la galería.</p>
    </div>
@else
    <div class="row g-3">
        @foreach ($galeria as $foto)
            <div class="col-6 col-md-3">
                <div class="card h-100">
                    @if ($foto->imagen)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($foto->imagen) }}" class="card-img-top" style="height:140px;object-fit:cover;" alt="">
                    @else
                        <div class="d-flex align-items-center justify-content-center bg-body-tertiary text-secondary" style="height:140px;">
                            <i class="fas fa-image fa-2x"></i>
                        </div>
                    @endif
                    <div class="card-body p-2">
                        <p class="small mb-2">{{ $foto->descripcion ?: '—' }}</p>
                        <span class="badge text-bg-{{ $foto->estado === 'activo' ? 'success' : 'secondary' }} mb-2">{{ ucfirst($foto->estado) }}</span>
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-secondary flex-fill" title="Editar"
                                    data-bs-toggle="modal" data-bs-target="#modalEditarFoto{{ $foto->id_foto }}">
                                <i class="fas fa-pen"></i>
                            </button>
                            @if ($foto->estado === 'activo')
                                <button type="button" class="btn btn-sm btn-outline-danger flex-fill" data-desactivar
                                        data-url="{{ route('editar-landing.galeria.desactivar', $foto) }}"
                                        data-nombre="esta foto" title="Desactivar">
                                    <i class="fas fa-ban"></i>
                                </button>
                            @else
                                <button type="button" class="btn btn-sm btn-outline-success flex-fill" data-activar
                                        data-url="{{ route('editar-landing.galeria.activar', $foto) }}"
                                        data-nombre="esta foto" title="Reactivar">
                                    <i class="fas fa-rotate-left"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

@push('modals')
{{-- Modal: nueva foto --}}
<div class="modal fade" id="modalNuevaFoto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form data-landing-form data-url="{{ route('editar-landing.galeria.store') }}" enctype="multipart/form-data" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title font-serif">Agregar foto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Imagen *</label>
                            <input type="file" class="form-control" name="imagen" accept="image/*" data-feedback="err-nf-imagen" required>
                            <div class="invalid-feedback" id="err-nf-imagen"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <input type="text" class="form-control" name="descripcion" placeholder="Ej: Acto cívico">
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

{{-- Modales: editar foto (una por card) --}}
@foreach ($galeria as $foto)
    <div class="modal fade" id="modalEditarFoto{{ $foto->id_foto }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form data-landing-form data-url="{{ route('editar-landing.galeria.update', $foto) }}" enctype="multipart/form-data" novalidate>
                    <div class="modal-header">
                        <h5 class="modal-title font-serif">Editar foto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Imagen (dejar vacío para mantener la actual)</label>
                                <input type="file" class="form-control" name="imagen" accept="image/*">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Descripción</label>
                                <input type="text" class="form-control" name="descripcion" value="{{ $foto->descripcion }}">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endpush
