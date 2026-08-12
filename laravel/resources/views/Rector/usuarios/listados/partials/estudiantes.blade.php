<div class="row g-3 mb-3">
    @foreach ([
        ['label' => 'Total', 'value' => $resumenEstudiantes['total'], 'color' => 'primary'],
        ['label' => 'Activos', 'value' => $resumenEstudiantes['activos'], 'color' => 'success'],
        ['label' => 'Inactivos', 'value' => $resumenEstudiantes['inactivos'], 'color' => 'secondary'],
        ['label' => 'En riesgo', 'value' => $resumenEstudiantes['enRiesgo'], 'color' => 'danger'],
    ] as $stat)
        <div class="col-6 col-md-3">
            <div class="border rounded-3 p-3 bg-body-tertiary">
                <div class="fs-4 fw-semibold text-{{ $stat['color'] }}">{{ $stat['value'] }}</div>
                <div class="small text-secondary">{{ $stat['label'] }}</div>
            </div>
        </div>
    @endforeach
</div>

<form method="GET" action="{{ route('listados.estudiantes.index') }}" class="row g-2 mb-3 align-items-center" data-autosubmit-form>
    <div class="col-12 col-md-4">
        <div class="input-group">
            <span class="input-group-text bg-body"><i class="fas fa-search"></i></span>
            <input type="text" class="form-control" name="q" value="{{ $filtros['q'] ?? '' }}"
                   placeholder="Buscar por nombre, correo o código..." data-autosubmit-debounce>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <select class="form-select" name="estado" data-autosubmit>
            <option value="">Todos los estados</option>
            <option value="activo" @selected(($filtros['estado'] ?? '') === 'activo')>Activo</option>
            <option value="riesgo" @selected(($filtros['estado'] ?? '') === 'riesgo')>En riesgo</option>
            <option value="inactivo" @selected(($filtros['estado'] ?? '') === 'inactivo')>Inactivo</option>
        </select>
    </div>
    <div class="col-6 col-md-3">
        <select class="form-select" name="curso" data-autosubmit>
            <option value="">Todos los cursos</option>
            @foreach ($cursos as $curso)
                <option value="{{ $curso->id_curso }}" @selected(($filtros['curso'] ?? '') == $curso->id_curso)>{{ $curso->nombre_curso }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2 d-grid">
        <a href="{{ route('listados.estudiantes.index') }}" class="btn btn-outline-secondary btn-sm">Limpiar filtros</a>
    </div>
</form>

@if ($estudiantes->isEmpty())
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-user-graduate"></i></div>
        @if (($filtros['q'] ?? '') || ($filtros['estado'] ?? '') || ($filtros['curso'] ?? ''))
            <p class="mb-0">No hay estudiantes que coincidan con los filtros.</p>
        @else
            <p class="mb-2">Aún no hay estudiantes registrados.</p>
            <a href="{{ route('registro.estudiantes.create') }}" class="btn btn-sm btn-primary">Registrar el primer estudiante</a>
        @endif
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Estudiante</th>
                    <th>Código</th>
                    <th>Curso</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($estudiantes as $estudiante)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ trim($estudiante->usuario->nombres.' '.$estudiante->usuario->apellidos) }}</div>
                            <div class="small text-secondary">{{ $estudiante->usuario->correo }}</div>
                        </td>
                        <td><code>{{ $estudiante->codigo_estudiante }}</code></td>
                        <td>{{ $estudiante->matriculas->first()->curso->nombre_curso ?? '—' }}</td>
                        <td>
                            @if ($estudiante->estado_academico !== 'activo')
                                <span class="badge text-bg-secondary">Inactivo</span>
                            @elseif ($idsEnRiesgo->contains($estudiante->id_estudiante))
                                <span class="badge text-bg-danger">En riesgo</span>
                            @else
                                <span class="badge text-bg-success">Activo</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('registro.estudiantes.show', $estudiante) }}"
                               class="btn btn-sm btn-outline-secondary" title="Ver detalle">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('registro.estudiantes.edit', $estudiante) }}"
                               class="btn btn-sm btn-outline-secondary" title="Editar">
                                <i class="fas fa-pen"></i>
                            </a>
                            @if ($estudiante->estado_academico === 'activo')
                                <button type="button" class="btn btn-sm btn-outline-danger" data-desactivar
                                        data-url="{{ route('listados.estudiantes.desactivar', $estudiante) }}"
                                        data-nombre="{{ trim($estudiante->usuario->nombres.' '.$estudiante->usuario->apellidos) }}"
                                        title="Desactivar">
                                    <i class="fas fa-ban"></i>
                                </button>
                            @else
                                <button type="button" class="btn btn-sm btn-outline-success" data-activar
                                        data-url="{{ route('listados.estudiantes.activar', $estudiante) }}"
                                        data-nombre="{{ trim($estudiante->usuario->nombres.' '.$estudiante->usuario->apellidos) }}"
                                        title="Reactivar">
                                    <i class="fas fa-rotate-left"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $estudiantes->links('pagination::bootstrap-5') }}
@endif
