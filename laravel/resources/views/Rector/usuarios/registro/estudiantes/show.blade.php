@extends('layouts.rector')

@section('title', 'Estudiante — '.trim($estudiante->usuario->nombres.' '.$estudiante->usuario->apellidos))

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
            <a href="{{ route('listados', ['tab' => 'estudiantes']) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="h4 fw-semibold font-serif mb-0">{{ trim($estudiante->usuario->nombres.' '.$estudiante->usuario->apellidos) }}</h1>
                <div class="small text-secondary">
                    <code>{{ $estudiante->codigo_estudiante }}</code>
                    @if ($matricula)
                        · {{ $grado }}{{ $grupo }}
                    @endif
                </div>
            </div>
            @if ($estudiante->estado_academico !== 'activo')
                <span class="badge text-bg-secondary">Inactivo</span>
            @elseif ($enRiesgo)
                <span class="badge text-bg-danger">En riesgo</span>
            @else
                <span class="badge text-bg-success">Activo</span>
            @endif
            <a href="{{ route('registro.estudiantes.edit', $estudiante) }}" class="btn btn-sm btn-outline-primary ms-auto">
                <i class="fas fa-pen me-1"></i> Editar
            </a>
        </div>

        <div class="table-responsive mb-4">
            <h2 class="h6 fw-semibold mb-2">Datos personales</h2>
            <table class="table table-hover align-middle">
                <tbody>
                    <tr><th class="text-secondary fw-normal" style="width: 260px">Tipo de documento</th><td>{{ strtoupper($estudiante->usuario->tipo_documento) }}</td></tr>
                    <tr><th class="text-secondary fw-normal">Número de documento</th><td>{{ $estudiante->usuario->numero_documento }}</td></tr>
                    <tr><th class="text-secondary fw-normal">Fecha de nacimiento</th><td>{{ $estudiante->fecha_nacimiento ?? '—' }}</td></tr>
                    <tr><th class="text-secondary fw-normal">Género</th><td>{{ $estudiante->genero ?? '—' }}</td></tr>
                    <tr><th class="text-secondary fw-normal">Dirección</th><td>{{ $estudiante->direccion ?? '—' }}</td></tr>
                    <tr><th class="text-secondary fw-normal">EPS / Seguro</th><td>{{ $estudiante->eps_seguro ?? '—' }}</td></tr>
                    <tr><th class="text-secondary fw-normal">Correo</th><td>{{ $estudiante->usuario->correo }}</td></tr>
                    <tr><th class="text-secondary fw-normal">Teléfono</th><td>{{ $estudiante->usuario->telefono ?? '—' }}</td></tr>
                    <tr><th class="text-secondary fw-normal">Observaciones</th><td>{{ $estudiante->observaciones_gral ?? '—' }}</td></tr>
                </tbody>
            </table>
        </div>

        <div class="table-responsive mb-4">
            <h2 class="h6 fw-semibold mb-2">Matrícula actual</h2>
            @if ($matricula)
                @php $estadoMatriculaColores = ['activa' => 'success', 'pendiente' => 'warning', 'retirada' => 'secondary', 'cancelada' => 'danger']; @endphp
                <table class="table table-hover align-middle">
                    <tbody>
                        <tr><th class="text-secondary fw-normal" style="width: 260px">Año lectivo</th><td>{{ $matricula->anio_lectivo }}</td></tr>
                        <tr><th class="text-secondary fw-normal">Curso</th><td>{{ $matricula->curso->nombre_curso ?? '—' }}</td></tr>
                        <tr><th class="text-secondary fw-normal">Jornada</th><td>{{ $matricula->curso->jornada ?? '—' }}</td></tr>
                        <tr><th class="text-secondary fw-normal">Fecha de matrícula</th><td>{{ $matricula->fecha_matricula ?? '—' }}</td></tr>
                        <tr><th class="text-secondary fw-normal">Estado</th><td><span class="badge text-bg-{{ $estadoMatriculaColores[$matricula->estado_matricula] ?? 'secondary' }}">{{ ucfirst($matricula->estado_matricula) }}</span></td></tr>
                        <tr><th class="text-secondary fw-normal">Observación</th><td>{{ $matricula->observacion ?? '—' }}</td></tr>
                    </tbody>
                </table>
            @else
                <p class="text-secondary small">Sin matrícula registrada.</p>
            @endif
        </div>

        <div class="card">
            <div class="card-body">
                <h2 class="h6 fw-semibold mb-3">Acudiente principal</h2>
                @if ($acudiente)
                    <div class="row g-3 small">
                        <div class="col-md-6"><span class="text-secondary">Nombre:</span> {{ trim($acudiente->nombres.' '.$acudiente->apellidos) }}</div>
                        <div class="col-md-6"><span class="text-secondary">Parentesco:</span> {{ ucfirst($acudiente->parentesco) }}</div>
                        <div class="col-md-6"><span class="text-secondary">Documento:</span> {{ strtoupper($acudiente->tipo_documento) }} {{ $acudiente->numero_documento }}</div>
                        <div class="col-md-6"><span class="text-secondary">Ocupación:</span> {{ $acudiente->ocupacion ?? '—' }}</div>
                        <div class="col-md-6"><span class="text-secondary">Teléfono:</span> {{ $acudiente->telefono ?? '—' }}</div>
                        <div class="col-md-6"><span class="text-secondary">Correo:</span> {{ $acudiente->correo ?? '—' }}</div>
                    </div>
                @else
                    <p class="text-secondary small mb-0">No hay acudiente principal registrado.</p>
                @endif
            </div>
        </div>
    </div>
@endsection
