<div class="d-flex justify-content-end mb-3">
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalNuevaNoticia">
        <i class="fas fa-plus me-1"></i> Nueva noticia
    </button>
</div>

@if ($noticias->isEmpty())
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-newspaper"></i></div>
        <p class="mb-0">Aún no hay noticias registradas.</p>
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Imagen</th>
                    <th>Título</th>
                    <th>Etiqueta</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($noticias as $noticia)
                    <tr>
                        <td>
                            @if ($noticia->imagen)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($noticia->imagen) }}" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:6px;">
                            @else
                                <div class="text-secondary"><i class="fas fa-image"></i></div>
                            @endif
                        </td>
                        <td class="fw-semibold">{{ $noticia->titulo }}</td>
                        <td>{{ $noticia->etiqueta ?: '—' }}</td>
                        <td>{{ $noticia->fecha ? \Illuminate\Support\Carbon::parse($noticia->fecha)->format('d/m/Y') : '—' }}</td>
                        <td>
                            <span class="badge text-bg-{{ $noticia->estado === 'activo' ? 'success' : 'secondary' }}">{{ ucfirst($noticia->estado) }}</span>
                        </td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-secondary" title="Editar"
                                    data-bs-toggle="modal" data-bs-target="#modalEditarNoticia{{ $noticia->id_noticia }}">
                                <i class="fas fa-pen"></i>
                            </button>
                            @if ($noticia->estado === 'activo')
                                <button type="button" class="btn btn-sm btn-outline-danger" data-desactivar
                                        data-url="{{ route('editar-landing.noticias.desactivar', $noticia) }}"
                                        data-nombre="esta noticia" title="Desactivar">
                                    <i class="fas fa-ban"></i>
                                </button>
                            @else
                                <button type="button" class="btn btn-sm btn-outline-success" data-activar
                                        data-url="{{ route('editar-landing.noticias.activar', $noticia) }}"
                                        data-nombre="esta noticia" title="Reactivar">
                                    <i class="fas fa-rotate-left"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@push('modals')
{{-- Modal: nueva noticia --}}
<div class="modal fade" id="modalNuevaNoticia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form data-landing-form data-url="{{ route('editar-landing.noticias.store') }}" enctype="multipart/form-data" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title font-serif">Nueva noticia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Título *</label>
                            <input type="text" class="form-control" name="titulo" data-feedback="err-nn-titulo" required>
                            <div class="invalid-feedback" id="err-nn-titulo"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción *</label>
                            <textarea class="form-control" name="descripcion" rows="3" data-feedback="err-nn-descripcion" required></textarea>
                            <div class="invalid-feedback" id="err-nn-descripcion"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Etiqueta</label>
                            <input type="text" class="form-control" name="etiqueta" placeholder="Ej: Logros" data-feedback="err-nn-etiqueta">
                            <div class="invalid-feedback" id="err-nn-etiqueta"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha</label>
                            <input type="date" class="form-control" name="fecha" data-feedback="err-nn-fecha">
                            <div class="invalid-feedback" id="err-nn-fecha"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Imagen</label>
                            <input type="file" class="form-control" name="imagen" accept="image/*" data-feedback="err-nn-imagen">
                            <div class="invalid-feedback" id="err-nn-imagen"></div>
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

{{-- Modales: editar noticia (uno por fila) --}}
@foreach ($noticias as $noticia)
    <div class="modal fade" id="modalEditarNoticia{{ $noticia->id_noticia }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form data-landing-form data-url="{{ route('editar-landing.noticias.update', $noticia) }}" enctype="multipart/form-data" novalidate>
                    <div class="modal-header">
                        <h5 class="modal-title font-serif">Editar noticia</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Título *</label>
                                <input type="text" class="form-control" name="titulo" value="{{ $noticia->titulo }}"
                                       data-feedback="err-en-titulo-{{ $noticia->id_noticia }}" required>
                                <div class="invalid-feedback" id="err-en-titulo-{{ $noticia->id_noticia }}"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Descripción *</label>
                                <textarea class="form-control" name="descripcion" rows="3"
                                          data-feedback="err-en-descripcion-{{ $noticia->id_noticia }}" required>{{ $noticia->descripcion }}</textarea>
                                <div class="invalid-feedback" id="err-en-descripcion-{{ $noticia->id_noticia }}"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Etiqueta</label>
                                <input type="text" class="form-control" name="etiqueta" value="{{ $noticia->etiqueta }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha</label>
                                <input type="date" class="form-control" name="fecha" value="{{ $noticia->fecha }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Imagen (dejar vacío para mantener la actual)</label>
                                <input type="file" class="form-control" name="imagen" accept="image/*">
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
