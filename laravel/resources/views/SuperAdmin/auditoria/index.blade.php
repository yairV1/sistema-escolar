@extends('layouts.superadmin')

@section('title', 'Auditoría')

@section('content')
<div class="container-fluid p-3 p-md-4">
    <h1 class="h4 fw-semibold font-serif mb-3">Auditoría de la plataforma</h1>

    <form method="GET" action="{{ route('superadmin.auditoria.index') }}" class="row g-2 mb-3 align-items-center" data-autosubmit-form>
        <div class="col-12 col-md-4">
            <div class="input-group">
                <span class="input-group-text bg-body"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" name="accion" value="{{ $filtros['accion'] ?? '' }}"
                       placeholder="Buscar por acción (ej. institucion.crear)..." data-autosubmit-debounce>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <input type="date" class="form-control" name="desde" value="{{ $filtros['desde'] ?? '' }}" data-autosubmit>
        </div>
        <div class="col-6 col-md-3">
            <input type="date" class="form-control" name="hasta" value="{{ $filtros['hasta'] ?? '' }}" data-autosubmit>
        </div>
        <div class="col-6 col-md-2 d-grid">
            <a href="{{ route('superadmin.auditoria.index') }}" class="btn btn-outline-secondary btn-sm">Limpiar filtros</a>
        </div>
    </form>

    @if ($logs->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-clipboard-list"></i></div>
            <p class="mb-0">No hay registros de auditoría que coincidan con los filtros.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle audit-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>Institución</th>
                        <th>Acción</th>
                        <th>Entidad</th>
                        <th>IP</th>
                        <th class="text-end">Cambios</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($logs as $log)
                        @php
                            $accionLower = strtolower($log->accion);
                            $colorAccion = match (true) {
                                str_contains($accionLower, 'crear') => 'success',
                                str_contains($accionLower, 'desactivar') => 'danger',
                                str_contains($accionLower, 'suspender') => 'danger',
                                str_contains($accionLower, 'eliminar') => 'danger',
                                str_contains($accionLower, 'invalido') => 'danger',
                                str_contains($accionLower, 'activar') => 'success',
                                str_contains($accionLower, 'verificado') => 'success',
                                str_contains($accionLower, 'editar') => 'primary',
                                str_contains($accionLower, 'actualizar') => 'primary',
                                str_contains($accionLower, 'cambiar') => 'primary',
                                default => 'secondary',
                            };

                            // Diff campo a campo: si hay antes+después solo se listan los campos
                            // que cambiaron; si es una creación (solo después) se listan todos.
                            $cambios = [];
                            $camposIgnorados = ['created_at', 'updated_at'];
                            if (!empty($log->datos_despues)) {
                                foreach ($log->datos_despues as $campo => $valorNuevo) {
                                    if (in_array($campo, $camposIgnorados, true)) {
                                        continue;
                                    }
                                    $valorAnterior = $log->datos_antes[$campo] ?? null;
                                    if (empty($log->datos_antes) || $valorAnterior !== $valorNuevo) {
                                        $cambios[$campo] = ['antes' => $valorAnterior, 'despues' => $valorNuevo];
                                    }
                                }
                            }
                            $formatearValor = fn ($v) => match (true) {
                                is_null($v) => '—',
                                is_bool($v) => $v ? 'Sí' : 'No',
                                is_array($v) => json_encode($v, JSON_UNESCAPED_UNICODE),
                                default => (string) $v,
                            };
                        @endphp
                        <tr>
                            <td class="small">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $log->usuario ? trim($log->usuario->nombres.' '.$log->usuario->apellidos) : '—' }}</td>
                            <td>{{ $log->institucion?->nombre ?? '—' }}</td>
                            <td><span class="badge text-bg-{{ $colorAccion }} audit-accion">{{ $log->accion }}</span></td>
                            <td class="small text-secondary">
                                {{ $log->entidad_tipo ? class_basename($log->entidad_tipo).' #'.$log->entidad_id : '—' }}
                            </td>
                            <td class="small text-secondary">{{ $log->ip ?? '—' }}</td>
                            <td class="text-end">
                                @if (!empty($cambios))
                                    <button type="button" class="btn btn-sm btn-outline-secondary audit-toggle"
                                            data-bs-toggle="collapse" data-bs-target="#audit-detalle-{{ $log->id_log }}"
                                            aria-expanded="false" aria-label="Ver cambios">
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @if (!empty($cambios))
                            <tr class="collapse" id="audit-detalle-{{ $log->id_log }}">
                                <td colspan="7" class="p-0">
                                    <div class="audit-detalle">
                                        <table class="table table-sm mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Campo</th>
                                                    <th>Antes</th>
                                                    <th>Después</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($cambios as $campo => $valores)
                                                    <tr>
                                                        <td class="small fw-semibold">{{ $campo }}</td>
                                                        <td class="small text-secondary">{{ $formatearValor($valores['antes']) }}</td>
                                                        <td class="small">{{ $formatearValor($valores['despues']) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $logs->links('pagination::bootstrap-5') }}
    @endif
</div>
@endsection

@push('scripts')
    @vite('resources/js/pages/superadmin/auditoria.js')
@endpush
