@extends(auth()->user()?->esSuperAdmin() ? 'layouts.superadmin' : 'layouts.panel')

@section('title', 'Bandeja de soportes')

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <h1 class="h4 fw-semibold font-serif mb-0">Bandeja de soportes</h1>

            <ul class="nav nav-pills">
                <li class="nav-item">
                    <a class="nav-link {{ ! $estadoSeleccionado ? 'active' : '' }}" href="{{ route('soportes.index') }}">Todos</a>
                </li>
                @foreach (['nuevo' => 'Nuevos', 'leido' => 'Leídos', 'resuelto' => 'Resueltos'] as $valor => $etiqueta)
                    <li class="nav-item">
                        <a class="nav-link {{ $estadoSeleccionado === $valor ? 'active' : '' }}"
                           href="{{ route('soportes.index', ['estado' => $valor]) }}">{{ $etiqueta }}</a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4">
                <div class="kpi-card bg-body-tertiary border h-100">
                    <div class="kpi-icon bg-warning-subtle text-warning mb-2"><i class="fas fa-envelope"></i></div>
                    <div class="kpi-value">{{ $kpis['nuevos'] }}</div>
                    <div class="kpi-label">Nuevos</div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="kpi-card bg-body-tertiary border h-100">
                    <div class="kpi-icon bg-info-subtle text-info mb-2"><i class="fas fa-envelope-open"></i></div>
                    <div class="kpi-value">{{ $kpis['leidos'] }}</div>
                    <div class="kpi-label">Leídos</div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="kpi-card bg-body-tertiary border h-100">
                    <div class="kpi-icon bg-success-subtle text-success mb-2"><i class="fas fa-circle-check"></i></div>
                    <div class="kpi-value">{{ $kpis['resueltos'] }}</div>
                    <div class="kpi-label">Resueltos</div>
                </div>
            </div>
        </div>

        @if ($soportes->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-life-ring"></i></div>
                <p class="mb-0">No hay soportes {{ $estadoSeleccionado ? 'en este estado' : 'todavía' }}.</p>
            </div>
        @else
            @php $estadoColores = ['nuevo' => 'warning', 'leido' => 'info', 'resuelto' => 'success']; @endphp
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Remitente</th>
                            <th>Asunto</th>
                            <th>Enviado</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($soportes as $soporte)
                            <tr role="button" onclick="window.location='{{ route('soportes.show', $soporte) }}'">
                                <td>
                                    <div class="fw-semibold">{{ trim($soporte->remitente->nombres.' '.$soporte->remitente->apellidos) }}</div>
                                    <div class="small text-secondary">{{ $soporte->remitente->rolLabel }}</div>
                                </td>
                                <td>{{ $soporte->asunto }}</td>
                                <td>{{ $soporte->created_at->format('d/m/Y g:i A') }}</td>
                                <td><span class="badge text-bg-{{ $estadoColores[$soporte->estado] }}">{{ ucfirst($soporte->estado) }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
