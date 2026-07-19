@extends('layouts.panel')

@section('title', 'Comunicados')

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h1 class="h4 fw-semibold font-serif mb-0">Comunicados</h1>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoComunicado">
                <i class="fas fa-paper-plane me-1"></i> Nuevo comunicado
            </button>
        </div>

        @if ($comunicados->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-bullhorn"></i></div>
                <p class="mb-0">Aún no se ha enviado ningún comunicado.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Tipo</th>
                            <th>Canal</th>
                            <th>Enviado</th>
                            <th>Destinatarios</th>
                            <th>Leídos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($comunicados as $comunicado)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $comunicado->titulo }}</div>
                                    <div class="small text-secondary">{{ \Illuminate\Support\Str::limit($comunicado->mensaje, 70) }}</div>
                                </td>
                                <td class="text-capitalize">{{ $comunicado->tipo_notificacion }}</td>
                                <td class="text-capitalize">{{ $comunicado->canal }}</td>
                                <td>{{ \Illuminate\Support\Carbon::parse($comunicado->fecha_envio)->format('d/m/Y g:i A') }}</td>
                                <td>{{ $comunicado->total_destinatarios }}</td>
                                <td>{{ $comunicado->total_leidos }} / {{ $comunicado->total_destinatarios }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $comunicados->links('pagination::bootstrap-5') }}
        @endif
    </div>

    @php
        $tiposOpciones = ['informativa' => 'Informativa', 'academica' => 'Académica', 'disciplinaria' => 'Disciplinaria', 'pago' => 'Pago', 'sistema' => 'Sistema'];
        $canalesOpciones = ['interno' => 'Interno (panel)', 'correo' => 'Correo', 'whatsapp' => 'WhatsApp', 'todos' => 'Todos los canales'];
        $audienciasOpciones = ['estudiantes' => 'Estudiantes', 'docentes' => 'Docentes', 'administrativos' => 'Administrativos', 'acudientes' => 'Acudientes'];
    @endphp

    {{-- Modal: nuevo comunicado --}}
    <div class="modal fade" id="modalNuevoComunicado" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form data-crud-form data-url="{{ route('comunicados.store') }}" novalidate>
                    <div class="modal-header">
                        <h5 class="modal-title font-serif">Nuevo comunicado</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Título *</label>
                                <input type="text" class="form-control" name="titulo" placeholder="Ej: Suspensión de clases" data-feedback="err-nc-titulo" required>
                                <div class="invalid-feedback" id="err-nc-titulo"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Mensaje *</label>
                                <textarea class="form-control" name="mensaje" rows="3" data-feedback="err-nc-mensaje" required></textarea>
                                <div class="invalid-feedback" id="err-nc-mensaje"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tipo *</label>
                                <select class="form-select" name="tipo_notificacion" data-feedback="err-nc-tipo" required>
                                    @foreach ($tiposOpciones as $valor => $etiqueta)
                                        <option value="{{ $valor }}">{{ $etiqueta }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="err-nc-tipo"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Canal *</label>
                                <select class="form-select" name="canal" data-feedback="err-nc-canal" required>
                                    @foreach ($canalesOpciones as $valor => $etiqueta)
                                        <option value="{{ $valor }}">{{ $etiqueta }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="err-nc-canal"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Destinatarios *</label>
                                <div class="d-flex flex-wrap gap-3">
                                    @foreach ($audienciasOpciones as $valor => $etiqueta)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="audiencias[]" value="{{ $valor }}" id="aud-{{ $valor }}">
                                            <label class="form-check-label" for="aud-{{ $valor }}">{{ $etiqueta }}</label>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="invalid-feedback" id="err-nc-audiencias"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Enviar comunicado</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/pages/comunicados/comunicados.js')
@endpush
