@extends('layouts.panel')

@section('title', 'Notas')

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <h1 class="h4 fw-semibold font-serif mb-3">Notas</h1>

        @include('Acudiente.partials.selector-estudiante')

        <div class="d-flex flex-wrap justify-content-end mb-3">
            <ul class="nav nav-pills">
                @foreach ($periodos as $periodo)
                    <li class="nav-item">
                        <a class="nav-link {{ $periodo === $periodoSeleccionado ? 'active' : '' }}"
                           href="{{ route('acudiente.notas', ['estudiante' => $hijoActivo->id, 'periodo' => $periodo]) }}">{{ $periodo }}</a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="kpi-card bg-body-tertiary border h-100">
                    <div class="kpi-icon bg-primary-subtle text-primary mb-2"><i class="fas fa-star"></i></div>
                    <div class="kpi-value">{{ number_format($boletin->promedio_general, 1) }}</div>
                    <div class="kpi-label">Promedio del periodo</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card bg-body-tertiary border h-100">
                    <div class="kpi-icon bg-info-subtle text-info mb-2"><i class="fas fa-ranking-star"></i></div>
                    <div class="kpi-value">{{ $boletin->puesto_curso }}<span class="fs-6 text-secondary">/{{ $boletin->total_curso }}</span></div>
                    <div class="kpi-label">Puesto en el curso</div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Asignatura</th>
                        <th>Profesor</th>
                        <th>Nota definitiva</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($boletin->detalle as $detalle)
                        <tr>
                            <td class="fw-semibold">{{ $detalle->materia }}</td>
                            <td>{{ $detalle->profesor }}</td>
                            <td>
                                <span class="badge {{ $detalle->nota_definitiva < 3.5 ? 'text-bg-danger' : 'text-bg-success' }}">
                                    {{ number_format($detalle->nota_definitiva, 1) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-secondary py-4">Sin asignaturas registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
