<div class="row g-3 mb-3">
    @foreach ([
        ['label' => 'Total', 'value' => $resumenDocentes['total'], 'color' => 'primary'],
        ['label' => 'Activos', 'value' => $resumenDocentes['activos'], 'color' => 'success'],
        ['label' => 'En licencia', 'value' => $resumenDocentes['licencia'], 'color' => 'warning'],
        ['label' => 'Inactivos', 'value' => $resumenDocentes['inactivos'], 'color' => 'secondary'],
    ] as $stat)
        <div class="col-6 col-md-3">
            <div class="border rounded-3 p-3 bg-body-tertiary">
                <div class="fs-4 fw-semibold text-{{ $stat['color'] }}">{{ $stat['value'] }}</div>
                <div class="small text-secondary">{{ $stat['label'] }}</div>
            </div>
        </div>
    @endforeach
</div>

<form method="GET" action="{{ route('listados') }}" class="row g-2 mb-3 align-items-center" data-autosubmit-form>
    <input type="hidden" name="tab" value="docentes">
    <div class="col-12 col-md-5">
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
            <option value="licencia" @selected(($filtros['estado'] ?? '') === 'licencia')>Licencia</option>
            <option value="vacaciones" @selected(($filtros['estado'] ?? '') === 'vacaciones')>Vacaciones</option>
            <option value="retirado" @selected(($filtros['estado'] ?? '') === 'retirado')>Retirado</option>
        </select>
    </div>
    <div class="col-md-2 d-grid">
        <a href="{{ route('listados', ['tab' => 'docentes']) }}" class="btn btn-outline-secondary btn-sm">Limpiar filtros</a>
    </div>
</form>

@if ($docentes->isEmpty())
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-chalkboard-teacher"></i></div>
        @if (($filtros['q'] ?? '') || ($filtros['estado'] ?? ''))
            <p class="mb-0">No hay docentes que coincidan con los filtros.</p>
        @else
            <p class="mb-2">Aún no hay docentes registrados.</p>
            <a href="{{ route('registro.docentes.create') }}" class="btn btn-sm btn-primary">Registrar el primer docente</a>
        @endif
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Docente</th>
                    <th>Código</th>
                    <th>Asignaturas</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($docentes as $docente)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ trim($docente->usuario->nombres.' '.$docente->usuario->apellidos) }}</div>
                            <div class="small text-secondary">{{ $docente->usuario->correo }}</div>
                        </td>
                        <td><code>{{ $docente->codigo_profesor }}</code></td>
                        <td class="small">{{ $docente->asignaciones->pluck('materia.nombre_materia')->filter()->unique()->join(', ') ?: '—' }}</td>
                        <td>
                            @php
                                $estadoColores = ['activo' => 'success', 'licencia' => 'warning', 'vacaciones' => 'info', 'retirado' => 'secondary'];
                            @endphp
                            <span class="badge text-bg-{{ $estadoColores[$docente->estado_laboral] ?? 'secondary' }}">{{ ucfirst($docente->estado_laboral) }}</span>
                        </td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Disponible próximamente">
                                <i class="fas fa-pen"></i>
                            </button>
                            @if ($docente->estado_laboral === 'activo')
                                <button type="button" class="btn btn-sm btn-outline-danger" data-desactivar
                                        data-url="{{ route('listados.docentes.desactivar', $docente) }}"
                                        data-nombre="{{ trim($docente->usuario->nombres.' '.$docente->usuario->apellidos) }}"
                                        title="Desactivar">
                                    <i class="fas fa-ban"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $docentes->links('pagination::bootstrap-5') }}
@endif
