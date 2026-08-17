@extends(in_array(auth()->user()?->rolSlug, ['admin', 'rector']) ? 'layouts.rector' : (auth()->user()?->rolSlug === 'docente' ? 'layouts.docente' : 'layouts.panel'))

@section('title', 'Asistencia — '.$asignacion->materia->nombre_materia)

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
            <a href="{{ auth()->user()->tienePanelAdmin() ? route('gestion-academica.cursos.show', $asignacion->curso) : route('docente.dashboard') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="h4 fw-semibold font-serif mb-0">{{ $asignacion->materia->nombre_materia }}</h1>
                <div class="small text-secondary">
                    {{ $asignacion->curso->nombre_curso }} ·
                    {{ trim($asignacion->profesor->usuario->nombres.' '.$asignacion->profesor->usuario->apellidos) }}
                </div>
            </div>
            <a href="{{ route('asistencia.historial', $asignacion) }}" class="btn btn-sm btn-outline-secondary ms-auto">
                <i class="fas fa-clock-rotate-left me-1"></i> Ver historial
            </a>
        </div>

        <form method="GET" action="{{ route('asistencia.show', $asignacion) }}" class="row g-2 mb-3 align-items-center" data-autosubmit-form>
            <div class="col-6 col-md-3">
                <input type="date" class="form-control" name="fecha" value="{{ $fecha }}" data-autosubmit>
            </div>
        </form>

        @if ($estudiantes->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-user-graduate"></i></div>
                <p class="mb-0">Este curso no tiene estudiantes matriculados activos todavía.</p>
            </div>
        @else
            <form id="formAsistencia" data-url="{{ route('asistencia.guardar', $asignacion) }}" data-fecha="{{ $fecha }}">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Estudiante</th>
                                <th>Código</th>
                                <th style="width:160px;">Estado</th>
                                <th>Observación</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($estudiantes as $estudiante)
                                @php $registro = $registrosExistentes->get($estudiante->id_estudiante); @endphp
                                <tr>
                                    <td>{{ trim($estudiante->usuario->nombres.' '.$estudiante->usuario->apellidos) }}</td>
                                    <td><code>{{ $estudiante->codigo_estudiante }}</code></td>
                                    <td>
                                        <select class="form-select form-select-sm" data-id-estudiante="{{ $estudiante->id_estudiante }}" data-campo="estado">
                                            <option value="presente" @selected(($registro->estado_asistencia ?? 'presente') === 'presente')>Presente</option>
                                            <option value="ausente" @selected(($registro->estado_asistencia ?? '') === 'ausente')>Ausente</option>
                                            <option value="tarde" @selected(($registro->estado_asistencia ?? '') === 'tarde')>Tarde</option>
                                            <option value="excusa" @selected(($registro->estado_asistencia ?? '') === 'excusa')>Excusa</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" data-id-estudiante="{{ $estudiante->id_estudiante }}" data-campo="observacion"
                                               value="{{ $registro->observacion ?? '' }}" placeholder="Opcional">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-primary" id="btnGuardarAsistencia">
                        <i class="fas fa-save me-1"></i> Guardar asistencia
                    </button>
                </div>
            </form>
        @endif
    </div>
@endsection

@push('scripts')
    @vite('resources/js/pages/asistencia/asistencia.js')
@endpush
