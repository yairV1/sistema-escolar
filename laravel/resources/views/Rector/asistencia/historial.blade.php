@extends(in_array(auth()->user()?->rolSlug, ['admin', 'rector']) ? 'layouts.rector' : (auth()->user()?->rolSlug === 'docente' ? 'layouts.docente' : 'layouts.panel'))

@section('title', 'Historial de asistencia — '.$asignacion->materia->nombre_materia)

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
            <a href="{{ route('asistencia.show', $asignacion) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="h4 fw-semibold font-serif mb-0">{{ $asignacion->materia->nombre_materia }}</h1>
                <div class="small text-secondary">{{ $asignacion->curso->nombre_curso }} · Historial de asistencia</div>
            </div>
        </div>

        @if ($resumen->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-clock-rotate-left"></i></div>
                <p class="mb-0">Todavía no hay asistencia registrada para esta asignación.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Presentes</th>
                            <th>Ausentes</th>
                            <th>Tarde</th>
                            <th>Excusa</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($resumen as $fila)
                            <tr>
                                <td>{{ \Illuminate\Support\Carbon::parse($fila->fecha)->format('d/m/Y') }}</td>
                                <td>{{ $fila->total }}</td>
                                <td>{{ $fila->presentes }}</td>
                                <td>{{ $fila->ausentes }}</td>
                                <td>{{ $fila->tardes }}</td>
                                <td>{{ $fila->excusas }}</td>
                                <td class="text-end">
                                    <a href="{{ route('asistencia.show', ['asignacion' => $asignacion, 'fecha' => $fila->fecha]) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
