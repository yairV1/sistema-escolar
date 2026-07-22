@extends('layouts.panel')

@section('title', 'Matrículas')

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h1 class="h4 fw-semibold font-serif mb-0">Matrículas</h1>
            <a href="{{ route('registro.estudiantes.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> Nueva matrícula
            </a>
        </div>

        <div class="row g-3 mb-3">
            @foreach ([
                ['label' => 'Total', 'value' => $resumen['total'], 'color' => 'primary'],
                ['label' => 'Activas', 'value' => $resumen['activas'], 'color' => 'success'],
                ['label' => 'Pendientes', 'value' => $resumen['pendientes'], 'color' => 'warning'],
                ['label' => 'Retiradas / canceladas', 'value' => $resumen['retiradasCanceladas'], 'color' => 'secondary'],
            ] as $stat)
                <div class="col-6 col-md-3">
                    <div class="border rounded-3 p-3 bg-body-tertiary">
                        <div class="fs-4 fw-semibold text-{{ $stat['color'] }}">{{ $stat['value'] }}</div>
                        <div class="small text-secondary">{{ $stat['label'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($solicitudes->isNotEmpty())
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-semibold"><i class="fas fa-inbox me-1"></i> Solicitudes de admisión desde la web</span>
                    <span class="badge text-bg-warning">{{ $solicitudes->count() }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Acudiente</th>
                                <th>Estudiante</th>
                                <th>Grado de interés</th>
                                <th>Contacto</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($solicitudes as $solicitud)
                                <tr>
                                    <td>{{ $solicitud->nombre_acudiente }} {{ $solicitud->apellido_acudiente }}</td>
                                    <td>{{ $solicitud->nombre_estudiante }}</td>
                                    <td>{{ $solicitud->grado_interes }}</td>
                                    <td>
                                        <div class="small">{{ $solicitud->correo }}</div>
                                        <div class="small text-secondary">{{ $solicitud->telefono }}</div>
                                    </td>
                                    <td>{{ $solicitud->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge text-bg-{{ $solicitud->estado === 'pendiente' ? 'warning' : 'info' }}">
                                            {{ ucfirst($solicitud->estado) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('registro.estudiantes.create', ['solicitud' => $solicitud->id_solicitud]) }}"
                                           class="btn btn-sm btn-outline-success">Convertir en matrícula</a>
                                        @if ($solicitud->estado === 'pendiente')
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-cambiar-estado-solicitud
                                                    data-url="{{ route('matriculas.solicitudes.estado', $solicitud) }}"
                                                    data-estado="contactada" data-nombre="{{ $solicitud->nombre_estudiante }}" data-accion="Marcar como contactada">
                                                Contactada
                                            </button>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-cambiar-estado-solicitud
                                                data-url="{{ route('matriculas.solicitudes.estado', $solicitud) }}"
                                                data-estado="descartada" data-nombre="{{ $solicitud->nombre_estudiante }}" data-accion="Descartar">
                                            Descartar
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <form method="GET" action="{{ route('matriculas') }}" class="row g-2 mb-3 align-items-center" data-autosubmit-form>
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-body"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" name="q" value="{{ $filtros['q'] ?? '' }}"
                           placeholder="Buscar por estudiante o código..." data-autosubmit-debounce>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <select class="form-select" name="curso" data-autosubmit>
                    <option value="">Todos los cursos</option>
                    @foreach ($cursos as $curso)
                        <option value="{{ $curso->id_curso }}" @selected(($filtros['curso'] ?? '') == $curso->id_curso)>{{ $curso->nombre_curso }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select class="form-select" name="anio" data-autosubmit>
                    <option value="">Todos los años</option>
                    @foreach ($anios as $anio)
                        <option value="{{ $anio }}" @selected(($filtros['anio'] ?? '') == $anio)>{{ $anio }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select class="form-select" name="estado" data-autosubmit>
                    <option value="">Todos los estados</option>
                    <option value="activa" @selected(($filtros['estado'] ?? '') === 'activa')>Activa</option>
                    <option value="pendiente" @selected(($filtros['estado'] ?? '') === 'pendiente')>Pendiente</option>
                    <option value="retirada" @selected(($filtros['estado'] ?? '') === 'retirada')>Retirada</option>
                    <option value="cancelada" @selected(($filtros['estado'] ?? '') === 'cancelada')>Cancelada</option>
                </select>
            </div>
            <div class="col-6 col-md-2 d-grid">
                <a href="{{ route('matriculas') }}" class="btn btn-outline-secondary btn-sm">Limpiar filtros</a>
            </div>
        </form>

        @if ($matriculas->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-file-signature"></i></div>
                @if (($filtros['q'] ?? '') || ($filtros['curso'] ?? '') || ($filtros['anio'] ?? '') || ($filtros['estado'] ?? ''))
                    <p class="mb-0">No hay matrículas que coincidan con los filtros.</p>
                @else
                    <p class="mb-2">Aún no hay matrículas registradas.</p>
                    <a href="{{ route('registro.estudiantes.create') }}" class="btn btn-sm btn-primary">Registrar la primera matrícula</a>
                @endif
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Estudiante</th>
                            <th>Curso</th>
                            <th>Año</th>
                            <th>Fecha matrícula</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $estadoColores = ['activa' => 'success', 'pendiente' => 'warning', 'retirada' => 'secondary', 'cancelada' => 'danger'];
                            $transiciones = [
                                'pendiente' => [['a' => 'activa', 'label' => 'Activar', 'color' => 'success'], ['a' => 'cancelada', 'label' => 'Cancelar', 'color' => 'danger']],
                                'activa' => [['a' => 'retirada', 'label' => 'Retirar', 'color' => 'secondary'], ['a' => 'cancelada', 'label' => 'Cancelar', 'color' => 'danger']],
                                'retirada' => [['a' => 'activa', 'label' => 'Reactivar', 'color' => 'success']],
                                'cancelada' => [['a' => 'activa', 'label' => 'Reactivar', 'color' => 'success']],
                            ];
                        @endphp
                        @foreach ($matriculas as $matricula)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ trim($matricula->estudiante->usuario->nombres.' '.$matricula->estudiante->usuario->apellidos) }}</div>
                                    <div class="small text-secondary"><code>{{ $matricula->estudiante->codigo_estudiante }}</code></div>
                                </td>
                                <td>{{ $matricula->curso->nombre_curso ?? '—' }}</td>
                                <td>{{ $matricula->anio_lectivo }}</td>
                                <td>{{ \Illuminate\Support\Carbon::parse($matricula->fecha_matricula)->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge text-bg-{{ $estadoColores[$matricula->estado_matricula] ?? 'secondary' }}">
                                        {{ ucfirst($matricula->estado_matricula) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    @foreach ($transiciones[$matricula->estado_matricula] ?? [] as $t)
                                        <button type="button" class="btn btn-sm btn-outline-{{ $t['color'] }}" data-cambiar-estado
                                                data-url="{{ route('matriculas.estado', $matricula) }}"
                                                data-estado="{{ $t['a'] }}"
                                                data-nombre="{{ trim($matricula->estudiante->usuario->nombres.' '.$matricula->estudiante->usuario->apellidos) }}"
                                                data-accion="{{ $t['label'] }}">
                                            {{ $t['label'] }}
                                        </button>
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $matriculas->links('pagination::bootstrap-5') }}
        @endif
    </div>
@endsection

@push('scripts')
    @vite('resources/js/pages/matriculas/matriculas.js')
@endpush
