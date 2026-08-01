@extends('layouts.panel')

@section('title', 'Asistencia')

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <h1 class="h4 fw-semibold font-serif mb-3">Asistencia</h1>

        @include('Acudiente.partials.selector-estudiante')

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4">
                <div class="kpi-card bg-body-tertiary border h-100">
                    <div class="kpi-icon bg-success-subtle text-success mb-2"><i class="fas fa-calendar-check"></i></div>
                    <div class="kpi-value">{{ $resumen->porcentaje }}%</div>
                    <div class="kpi-label">Asistencia del periodo</div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="kpi-card bg-body-tertiary border h-100">
                    <div class="kpi-icon bg-danger-subtle text-danger mb-2"><i class="fas fa-calendar-xmark"></i></div>
                    <div class="kpi-value">{{ $resumen->faltas }}</div>
                    <div class="kpi-label">Faltas</div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="kpi-card bg-body-tertiary border h-100">
                    <div class="kpi-icon bg-warning-subtle text-warning mb-2"><i class="fas fa-clock"></i></div>
                    <div class="kpi-value">{{ $resumen->tardanzas }}</div>
                    <div class="kpi-label">Tardanzas</div>
                </div>
            </div>
        </div>

        @if ($resumen->filas->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-clock-rotate-left"></i></div>
                <p class="mb-0">Todavía no hay asistencia registrada.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($resumen->filas as $fila)
                            @php
                                $etiquetas = ['presente' => 'Presente', 'ausente' => 'Ausente', 'tarde' => 'Tarde', 'excusa' => 'Excusa'];
                            @endphp
                            <tr>
                                <td>{{ \Illuminate\Support\Carbon::parse($fila->fecha)->translatedFormat('d \d\e F, Y') }}</td>
                                <td><span class="attendance-pill attendance-pill--{{ $fila->estado }}">{{ $etiquetas[$fila->estado] }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
