@extends('layouts.rector')

@section('title', 'Administrativo — '.trim($admin->nombres.' '.$admin->apellidos))

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
            <a href="{{ route('listados', ['tab' => 'administrativos']) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="h4 fw-semibold font-serif mb-0">{{ trim($admin->nombres.' '.$admin->apellidos) }}</h1>
                <div class="small text-secondary">
                    {{ strtoupper($admin->tipo_documento) }} {{ $admin->numero_documento }} · {{ $admin->rolLabel }}
                </div>
            </div>
            @php $estadoColores = ['activo' => 'success', 'inactivo' => 'secondary', 'bloqueado' => 'danger']; @endphp
            <span class="badge text-bg-{{ $estadoColores[$admin->estado_usuario] ?? 'secondary' }}">{{ ucfirst($admin->estado_usuario) }}</span>
            <a href="{{ route('registro.administrativos.edit', $admin) }}" class="btn btn-sm btn-outline-primary ms-auto">
                <i class="fas fa-pen me-1"></i> Editar
            </a>
        </div>

        <div class="table-responsive">
            <h2 class="h6 fw-semibold mb-2">Datos del usuario</h2>
            <table class="table table-hover align-middle">
                <tbody>
                    <tr><th class="text-secondary fw-normal" style="width: 260px">Nombres</th><td>{{ $admin->nombres }}</td></tr>
                    <tr><th class="text-secondary fw-normal">Apellidos</th><td>{{ $admin->apellidos }}</td></tr>
                    <tr><th class="text-secondary fw-normal">Tipo de documento</th><td>{{ strtoupper($admin->tipo_documento) }}</td></tr>
                    <tr><th class="text-secondary fw-normal">Número de documento</th><td>{{ $admin->numero_documento }}</td></tr>
                    <tr><th class="text-secondary fw-normal">Correo</th><td>{{ $admin->correo }}</td></tr>
                    <tr><th class="text-secondary fw-normal">Teléfono</th><td>{{ $admin->telefono ?? '—' }}</td></tr>
                    <tr><th class="text-secondary fw-normal">Rol</th><td>{{ $admin->rolLabel }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
