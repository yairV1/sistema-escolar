<div class="row g-3 mb-3">
    @foreach ([
        ['label' => 'Total', 'value' => $resumenAdministrativos['total'], 'color' => 'primary'],
        ['label' => 'Activos', 'value' => $resumenAdministrativos['activos'], 'color' => 'success'],
        ['label' => 'Inactivos', 'value' => $resumenAdministrativos['inactivos'], 'color' => 'secondary'],
    ] as $stat)
        <div class="col-6 col-md-4">
            <div class="border rounded-3 p-3 bg-body-tertiary">
                <div class="fs-4 fw-semibold text-{{ $stat['color'] }}">{{ $stat['value'] }}</div>
                <div class="small text-secondary">{{ $stat['label'] }}</div>
            </div>
        </div>
    @endforeach
</div>

<form method="GET" action="{{ route('listados') }}" class="row g-2 mb-3 align-items-center" data-autosubmit-form>
    <input type="hidden" name="tab" value="administrativos">
    <div class="col-12 col-md-4">
        <div class="input-group">
            <span class="input-group-text bg-body"><i class="fas fa-search"></i></span>
            <input type="text" class="form-control" name="q" value="{{ $filtros['q'] ?? '' }}"
                   placeholder="Buscar por nombre, correo o documento..." data-autosubmit-debounce>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <select class="form-select" name="rol" data-autosubmit>
            <option value="">Todos los roles</option>
            <option value="1" @selected(($filtros['rol'] ?? '') == '1')>Administrador</option>
            <option value="2" @selected(($filtros['rol'] ?? '') == '2')>Directivo</option>
            <option value="3" @selected(($filtros['rol'] ?? '') == '3')>Coordinador</option>
            <option value="4" @selected(($filtros['rol'] ?? '') == '4')>Secretario</option>
        </select>
    </div>
    <div class="col-6 col-md-3">
        <select class="form-select" name="estado" data-autosubmit>
            <option value="">Todos los estados</option>
            <option value="activo" @selected(($filtros['estado'] ?? '') === 'activo')>Activo</option>
            <option value="inactivo" @selected(($filtros['estado'] ?? '') === 'inactivo')>Inactivo</option>
            <option value="bloqueado" @selected(($filtros['estado'] ?? '') === 'bloqueado')>Bloqueado</option>
        </select>
    </div>
    <div class="col-md-2 d-grid">
        <a href="{{ route('listados', ['tab' => 'administrativos']) }}" class="btn btn-outline-secondary btn-sm">Limpiar filtros</a>
    </div>
</form>

@if ($administrativos->isEmpty())
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-user-tie"></i></div>
        @if (($filtros['q'] ?? '') || ($filtros['rol'] ?? '') || ($filtros['estado'] ?? ''))
            <p class="mb-0">No hay personal administrativo que coincida con los filtros.</p>
        @else
            <p class="mb-2">Aún no hay personal administrativo registrado.</p>
            <a href="{{ route('registro.administrativos.create') }}" class="btn btn-sm btn-primary">Registrar el primero</a>
        @endif
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Administrativo</th>
                    <th>Documento</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($administrativos as $admin)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ trim($admin->nombres.' '.$admin->apellidos) }}</div>
                            <div class="small text-secondary">{{ $admin->correo }}</div>
                        </td>
                        <td><code>{{ $admin->numero_documento }}</code></td>
                        <td>{{ $admin->rolLabel }}</td>
                        <td>
                            @php
                                $estadoColores = ['activo' => 'success', 'inactivo' => 'secondary', 'bloqueado' => 'danger'];
                            @endphp
                            <span class="badge text-bg-{{ $estadoColores[$admin->estado_usuario] ?? 'secondary' }}">{{ ucfirst($admin->estado_usuario) }}</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('registro.administrativos.show', $admin) }}" class="btn btn-sm btn-outline-secondary" title="Ver detalle">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('registro.administrativos.edit', $admin) }}" class="btn btn-sm btn-outline-secondary" title="Editar">
                                <i class="fas fa-pen"></i>
                            </a>
                            @if ($admin->estado_usuario === 'activo')
                                <button type="button" class="btn btn-sm btn-outline-danger" data-desactivar
                                        data-url="{{ route('listados.administrativos.desactivar', $admin) }}"
                                        data-nombre="{{ trim($admin->nombres.' '.$admin->apellidos) }}"
                                        title="Desactivar">
                                    <i class="fas fa-ban"></i>
                                </button>
                            @else
                                <button type="button" class="btn btn-sm btn-outline-success" data-activar
                                        data-url="{{ route('listados.administrativos.activar', $admin) }}"
                                        data-nombre="{{ trim($admin->nombres.' '.$admin->apellidos) }}"
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

    {{ $administrativos->links('pagination::bootstrap-5') }}
@endif
