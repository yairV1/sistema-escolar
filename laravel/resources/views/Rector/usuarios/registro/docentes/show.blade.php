@extends('layouts.rector')

@section('title', 'Docente — '.trim($profesor->usuario->nombres.' '.$profesor->usuario->apellidos))

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
            <a href="{{ route('listados.docentes.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="h4 fw-semibold font-serif mb-0">{{ trim($profesor->usuario->nombres.' '.$profesor->usuario->apellidos) }}</h1>
                <div class="small text-secondary">
                    <code>{{ $profesor->codigo_profesor }}</code> · {{ strtoupper($profesor->usuario->tipo_documento) }} {{ $profesor->usuario->numero_documento }}
                </div>
            </div>
            @php $estadoColores = ['activo' => 'success', 'licencia' => 'warning', 'vacaciones' => 'info', 'retirado' => 'secondary']; @endphp
            <span class="badge text-bg-{{ $estadoColores[$profesor->estado_laboral] ?? 'secondary' }}">{{ ucfirst($profesor->estado_laboral) }}</span>
            <a href="{{ route('registro.docentes.edit', $profesor) }}" class="btn btn-sm btn-outline-primary ms-auto">
                <i class="fas fa-pen me-1"></i> Editar
            </a>
        </div>

        <div class="table-responsive mb-4">
            <h2 class="h6 fw-semibold mb-2">Datos personales</h2>
            <table class="table table-hover align-middle">
                <tbody>
                    <tr><th class="text-secondary fw-normal" style="width: 260px">Correo</th><td>{{ $profesor->usuario->correo }}</td></tr>
                    <tr><th class="text-secondary fw-normal">Teléfono</th><td>{{ $profesor->usuario->telefono ?? '—' }}</td></tr>
                </tbody>
            </table>
        </div>

        <div class="table-responsive mb-4">
            <h2 class="h6 fw-semibold mb-2">Datos profesionales</h2>
            <table class="table table-hover align-middle">
                <tbody>
                    <tr><th class="text-secondary fw-normal" style="width: 260px">Profesión</th><td>{{ $profesor->profesion ?? '—' }}</td></tr>
                    <tr><th class="text-secondary fw-normal">Especialidad</th><td>{{ $profesor->especialidad ?? '—' }}</td></tr>
                    <tr><th class="text-secondary fw-normal">Fecha de ingreso</th><td>{{ $profesor->fecha_ingreso ?? '—' }}</td></tr>
                </tbody>
            </table>
        </div>

        <div class="mb-4">
            <h2 class="h6 fw-semibold mb-2">Asignaturas que dicta</h2>
            @forelse ($profesor->materias as $materia)
                <span class="badge text-bg-light border me-1 mb-1">{{ $materia->nombre_materia }}</span>
            @empty
                <p class="text-secondary small mb-0">Sin asignaturas asignadas. Puedes agregarlas editando el docente.</p>
            @endforelse
        </div>

        <div class="table-responsive">
            <h2 class="h6 fw-semibold mb-2">Asignaciones académicas</h2>
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Asignatura</th>
                        <th>Curso</th>
                        <th>Año lectivo</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($profesor->asignaciones as $asignacion)
                        <tr>
                            <td>{{ $asignacion->materia->nombre_materia ?? '—' }}</td>
                            <td>{{ $asignacion->curso->nombre_curso ?? '—' }}</td>
                            <td>{{ $asignacion->anio_lectivo }}</td>
                            <td><span class="badge text-bg-{{ $asignacion->estado === 'activo' ? 'success' : 'secondary' }}">{{ ucfirst($asignacion->estado) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-secondary py-4">Sin asignaciones registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <p class="text-secondary small mb-0">La asignación de asignaturas y cursos se gestiona desde Gestión Académica.</p>
        </div>
    </div>
@endsection
