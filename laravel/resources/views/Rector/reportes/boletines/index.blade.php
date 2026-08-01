@extends('layouts.panel')

@section('title', 'Boletines')

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <h1 class="h4 fw-semibold font-serif mb-3">Boletines</h1>

        <form method="GET" action="{{ route('boletines.index') }}" class="row g-2 mb-3 align-items-center" data-autosubmit-form>
            <div class="col-6 col-md-4">
                <select class="form-select" name="curso" data-autosubmit>
                    <option value="">Selecciona un curso...</option>
                    @foreach ($cursos as $curso)
                        <option value="{{ $curso->id_curso }}" @selected($idCurso == $curso->id_curso)>{{ $curso->nombre_curso }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-4">
                <select class="form-select" name="periodo" data-autosubmit>
                    <option value="">Selecciona un periodo...</option>
                    @foreach ($periodos as $periodo)
                        <option value="{{ $periodo->id_periodo }}" @selected($idPeriodo == $periodo->id_periodo)>{{ $periodo->nombre_periodo }} ({{ $periodo->anio_lectivo }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-4 d-flex gap-2">
                @if ($idCurso && $idPeriodo)
                    <button type="button" class="btn btn-primary btn-sm" id="btnGenerarBoletines"
                            data-url="{{ route('boletines.generar') }}" data-curso="{{ $idCurso }}" data-periodo="{{ $idPeriodo }}">
                        <i class="fas fa-rotate me-1"></i> Generar/actualizar boletines
                    </button>
                    @if ($boletines->isNotEmpty())
                        <a href="{{ route('boletines.pdf-masivo', ['id_curso' => $idCurso, 'id_periodo' => $idPeriodo]) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-file-pdf me-1"></i> Generar PDF por estudiante ({{ $boletines->count() }})
                        </a>
                    @endif
                @endif
            </div>
        </form>

        @if (! $idCurso || ! $idPeriodo)
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-file-lines"></i></div>
                <p class="mb-0">Selecciona un curso y un periodo para ver o generar sus boletines.</p>
            </div>
        @elseif ($boletines->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-file-lines"></i></div>
                <p class="mb-2">Todavía no hay boletines generados para este curso y periodo.</p>
                <button type="button" class="btn btn-sm btn-primary" id="btnGenerarBoletinesVacio"
                        data-url="{{ route('boletines.generar') }}" data-curso="{{ $idCurso }}" data-periodo="{{ $idPeriodo }}">
                    Generar ahora
                </button>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Puesto</th>
                            <th>Estudiante</th>
                            <th>Promedio</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $estadoColores = ['borrador' => 'secondary', 'publicado' => 'success', 'anulado' => 'danger']; @endphp
                        @foreach ($boletines as $boletin)
                            <tr>
                                <td>{{ $boletin->puesto_curso ?? '—' }}</td>
                                <td>{{ trim($boletin->estudiante->usuario->nombres.' '.$boletin->estudiante->usuario->apellidos) }}</td>
                                <td>{{ $boletin->promedio_general ?? '—' }}</td>
                                <td><span class="badge text-bg-{{ $estadoColores[$boletin->estado] ?? 'secondary' }}">{{ ucfirst($boletin->estado) }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('boletines.show', $boletin) }}" class="btn btn-sm btn-outline-primary" title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('boletines.pdf', $boletin) }}" class="btn btn-sm btn-outline-secondary" title="Descargar PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                    @if ($boletin->estado === 'borrador')
                                        <button type="button" class="btn btn-sm btn-outline-success" data-boletin-accion
                                                data-url="{{ route('boletines.publicar', $boletin) }}" title="Publicar">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    @elseif ($boletin->estado === 'publicado')
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-boletin-accion
                                                data-url="{{ route('boletines.anular', $boletin) }}" title="Anular">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-boletin-accion
                                                data-url="{{ route('boletines.borrador', $boletin) }}" title="Volver a borrador">
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
    </div>
@endsection

@push('scripts')
    @vite('resources/js/pages/boletines/boletines.js')
@endpush
