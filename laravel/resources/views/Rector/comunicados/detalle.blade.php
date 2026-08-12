@extends('layouts.rector')

@section('title', $comunicado->titulo.' — Comunicado')

@php
    $totalDestinatarios = $notificaciones->count();
    $totalLeidos = $notificaciones->where('leida', true)->count();
@endphp

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
            <a href="{{ route('comunicados.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="h4 fw-semibold font-serif mb-0">{{ $comunicado->titulo }}</h1>
                <div class="small text-secondary">
                    {{ \App\Modules\Comunicados\Models\Notificacion::TIPOS_LABELS[$comunicado->tipo_notificacion] ?? ucfirst($comunicado->tipo_notificacion) }}
                    · <span class="text-capitalize">{{ $comunicado->canal }}</span>
                    · Enviado el {{ \Illuminate\Support\Carbon::parse($comunicado->fecha_envio)->format('d/m/Y g:i A') }}
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h2 class="h6 fw-semibold mb-2">Mensaje</h2>
                <p class="mb-0">{{ $comunicado->mensaje }}</p>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-4">
                <div class="kpi-card bg-body-tertiary border h-100">
                    <div class="kpi-icon bg-primary-subtle text-primary mb-2"><i class="fas fa-users"></i></div>
                    <div class="kpi-value">{{ $totalDestinatarios }}</div>
                    <div class="kpi-label">Destinatarios</div>
                </div>
            </div>
            <div class="col-4">
                <div class="kpi-card bg-body-tertiary border h-100">
                    <div class="kpi-icon bg-success-subtle text-success mb-2"><i class="fas fa-envelope-open"></i></div>
                    <div class="kpi-value">{{ $totalLeidos }}</div>
                    <div class="kpi-label">Leídos</div>
                </div>
            </div>
            <div class="col-4">
                <div class="kpi-card bg-body-tertiary border h-100">
                    <div class="kpi-icon bg-secondary-subtle text-secondary mb-2"><i class="fas fa-envelope"></i></div>
                    <div class="kpi-value">{{ $totalDestinatarios - $totalLeidos }}</div>
                    <div class="kpi-label">Sin leer</div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Destinatario</th>
                        <th>Leído</th>
                        <th>Fecha de lectura</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($notificaciones as $notificacion)
                        <tr>
                            <td>
                                @if ($notificacion->destino)
                                    <div class="fw-semibold">{{ trim($notificacion->destino->nombres.' '.$notificacion->destino->apellidos) }}</div>
                                    <div class="small text-secondary">{{ $notificacion->destino->rolLabel }}</div>
                                @else
                                    <span class="text-secondary">Usuario eliminado</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge text-bg-{{ $notificacion->leida ? 'success' : 'secondary' }}">
                                    {{ $notificacion->leida ? 'Leído' : 'Sin leer' }}
                                </span>
                            </td>
                            <td>{{ $notificacion->fecha_lectura ? \Illuminate\Support\Carbon::parse($notificacion->fecha_lectura)->format('d/m/Y g:i A') : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
