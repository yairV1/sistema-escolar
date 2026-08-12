@extends(auth()->user()?->esSuperAdmin() ? 'layouts.superadmin' : (in_array(auth()->user()?->rolSlug, ['admin', 'rector']) ? 'layouts.rector' : 'layouts.panel'))

@section('title', 'Mis solicitudes de soporte')

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <h1 class="h4 fw-semibold font-serif mb-0">Mis solicitudes de soporte</h1>
            <a href="{{ route('soporte.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Nueva solicitud
            </a>
        </div>

        @if ($soportes->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-life-ring"></i></div>
                <p class="mb-0">Todavía no has enviado ninguna solicitud de soporte.</p>
            </div>
        @else
            @php
                $estadoColores = ['nuevo' => 'warning', 'leido' => 'info', 'resuelto' => 'success'];
                $estadoLabels = ['nuevo' => 'Enviado, esperando respuesta', 'leido' => 'En revisión', 'resuelto' => 'Resuelto'];
            @endphp
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Asunto</th>
                            <th>Enviado</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($soportes as $soporte)
                            <tr>
                                <td>{{ $soporte->asunto }}</td>
                                <td>{{ $soporte->created_at->format('d/m/Y g:i A') }}</td>
                                <td><span class="badge text-bg-{{ $estadoColores[$soporte->estado] }}">{{ $estadoLabels[$soporte->estado] }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
