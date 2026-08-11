@extends('layouts.panel')

@section('title', 'Mis estudiantes')

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <h1 class="h4 fw-semibold font-serif mb-4">Mis estudiantes</h1>

        @if ($grupos->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-user-graduate"></i></div>
                <p class="mb-0">Todavía no tenés asignaciones activas.</p>
            </div>
        @else
            @foreach ($grupos as $grupo)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="h6 fw-semibold mb-0">
                                {{ $grupo->asignacion->materia->nombre_materia }}
                                <span class="text-secondary fw-normal">· {{ $grupo->asignacion->curso->nombre_curso }}</span>
                            </h2>
                            <span class="badge text-bg-secondary">{{ $grupo->estudiantes->count() }} estudiantes</span>
                        </div>

                        @if ($grupo->estudiantes->isEmpty())
                            <div class="empty-state py-3">
                                <div class="empty-icon"><i class="fas fa-user-graduate"></i></div>
                                <p class="mb-0">Sin estudiantes matriculados en este curso todavía.</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Estudiante</th>
                                            <th>Código</th>
                                            <th class="text-center">Promedio</th>
                                            <th class="text-center">Asistencia</th>
                                            <th class="text-center">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($grupo->estudiantes as $fila)
                                            <tr>
                                                <td class="fw-semibold">{{ trim($fila->estudiante->usuario->nombres.' '.$fila->estudiante->usuario->apellidos) }}</td>
                                                <td class="text-secondary small">{{ $fila->estudiante->codigo_estudiante }}</td>
                                                <td class="text-center">{{ $fila->promedio ?? '—' }}</td>
                                                <td class="text-center">{{ $fila->asistencia !== null ? $fila->asistencia.'%' : '—' }}</td>
                                                <td class="text-center">
                                                    @if ($fila->en_riesgo)
                                                        <span class="badge text-bg-danger"><i class="fas fa-triangle-exclamation me-1"></i>En riesgo</span>
                                                    @else
                                                        <span class="badge text-bg-success">Al día</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif
    </div>
@endsection
