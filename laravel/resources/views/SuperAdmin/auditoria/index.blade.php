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
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>Institución</th>
                        <th>Acción</th>
                        <th>Entidad</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($logs as $log)
                        <tr>
                            <td class="small">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $log->usuario ? trim($log->usuario->nombres.' '.$log->usuario->apellidos) : '—' }}</td>
                            <td>{{ $log->institucion?->nombre ?? '—' }}</td>
                            <td><code class="small">{{ $log->accion }}</code></td>
                            <td class="small text-secondary">
                                {{ $log->entidad_tipo ? class_basename($log->entidad_tipo).' #'.$log->entidad_id : '—' }}
                            </td>
                            <td class="small text-secondary">{{ $log->ip ?? '—' }}</td>
                        </tr>
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
