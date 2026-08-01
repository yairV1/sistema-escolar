@extends('layouts.superadmin')

@section('title', $institucion->nombre)

@section('content')
<div class="container-fluid p-3 p-md-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('superadmin.instituciones.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="h4 fw-semibold font-serif mb-0">{{ $institucion->nombre }}</h1>
            @php
                $colorEstado = ['activa' => 'success', 'suspendida' => 'warning', 'inactiva' => 'secondary'];
            @endphp
            <span class="badge text-bg-{{ $colorEstado[$institucion->estado] }}">{{ ucfirst($institucion->estado) }}</span>
        </div>
        <a href="{{ route('superadmin.instituciones.edit', $institucion) }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-pen me-1"></i> Editar
        </a>
    </div>

    <ul class="nav nav-pills mb-3">
        @foreach (['info' => 'Información general', 'usuarios' => 'Usuarios', 'metricas' => 'Uso y métricas'] as $clave => $etiqueta)
            <li class="nav-item">
                <a class="nav-link {{ $tab === $clave ? 'active' : '' }}"
                   href="{{ route('superadmin.instituciones.show', [$institucion, 'tab' => $clave]) }}">{{ $etiqueta }}</a>
            </li>
        @endforeach
    </ul>

    @if ($tab === 'usuarios')
        @if ($usuarios->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-users"></i></div>
                <p class="mb-0">Esta institución aún no tiene usuarios registrados.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Rol</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($usuarios as $usuario)
                            <tr>
                                <td>{{ trim($usuario->nombres.' '.$usuario->apellidos) }}</td>
                                <td>{{ $usuario->correo }}</td>
                                <td>{{ $usuario->rol?->nombre_rol }}</td>
                                <td>
                                    <span class="badge text-bg-{{ $usuario->estado_usuario === 'activo' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($usuario->estado_usuario) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $usuarios->links('pagination::bootstrap-5') }}
        @endif
    @elseif ($tab === 'metricas')
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="kpi-card bg-body-tertiary border h-100">
                    <div class="kpi-icon bg-primary-subtle text-primary mb-2"><i class="fas fa-users"></i></div>
                    <div class="kpi-value">{{ $institucion->usuarios_count }}</div>
                    <div class="kpi-label">Usuarios totales</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card bg-body-tertiary border h-100">
                    <div class="kpi-icon bg-secondary-subtle text-secondary mb-2"><i class="fas fa-gauge"></i></div>
                    <div class="kpi-value">{{ $institucion->limite_usuarios ?? '—' }}</div>
                    <div class="kpi-label">Límite del plan</div>
                </div>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Slug</dt>
                    <dd class="col-sm-9">{{ $institucion->slug }}</dd>

                    <dt class="col-sm-3">NIT</dt>
                    <dd class="col-sm-9">{{ $institucion->nit ?: '—' }}</dd>

                    <dt class="col-sm-3">Contacto</dt>
                    <dd class="col-sm-9">{{ $institucion->email_contacto ?: '—' }} · {{ $institucion->telefono ?: '—' }}</dd>

                    <dt class="col-sm-3">Ubicación</dt>
                    <dd class="col-sm-9">{{ $institucion->direccion ?: '—' }}, {{ $institucion->ciudad ?: '—' }}, {{ $institucion->pais ?: '—' }}</dd>

                    <dt class="col-sm-3">Plan</dt>
                    <dd class="col-sm-9"><span class="badge text-bg-secondary">{{ ucfirst($institucion->plan) }}</span></dd>

                    <dt class="col-sm-3">Vigencia</dt>
                    <dd class="col-sm-9">
                        {{ $institucion->fecha_inicio?->format('d/m/Y') ?? '—' }}
                        &mdash;
                        {{ $institucion->fecha_vencimiento?->format('d/m/Y') ?? 'sin vencimiento' }}
                    </dd>
                </dl>
            </div>
        </div>
    @endif
</div>
@endsection
