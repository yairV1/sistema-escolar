@extends('layouts.panel')

@section('title', 'Director de grupo')

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <h1 class="h4 fw-semibold font-serif mb-2">Director de grupo</h1>
        <p class="text-secondary small mb-3" style="max-width:60ch;">
            <i class="fas fa-circle-info me-1"></i>
            Digita la nota definitiva de cada materia directamente, sin pasar actividad por actividad.
            Una nota digitada aquí reemplaza el promedio calculado y se conserva aunque se vuelvan a generar los boletines.
        </p>

        @if ($cursos->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-user-tie"></i></div>
                <p class="mb-0">Todavía no estás asignado como director de grupo de ningún curso.</p>
            </div>
        @else
            @unless ($esDirectorDeAlgunCurso)
                <div class="alert alert-info small">
                    <i class="fas fa-circle-info me-1"></i>
                    No eres director de grupo de ningún curso — estás viendo esta pantalla como administrador/rector.
                </div>
            @endunless

            <form method="GET" action="{{ route('director-grupo.index') }}" class="row g-2 mb-3 align-items-center" data-autosubmit-form>
                <div class="col-6 col-md-4">
                    <select class="form-select" name="curso" data-autosubmit>
                        <option value="">Selecciona un curso...</option>
                        @foreach ($cursos as $curso)
                            <option value="{{ $curso->id_curso }}" @selected($idCurso == $curso->id_curso)>{{ $curso->nombre_curso }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-4">
                    <select class="form-select" name="periodo" data-autosubmit>
                        <option value="">Selecciona un periodo...</option>
                        @foreach ($periodos as $periodo)
                            <option value="{{ $periodo->id_periodo }}" @selected($idPeriodo == $periodo->id_periodo)>{{ $periodo->nombre_periodo }} ({{ $periodo->anio_lectivo }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    @if ($idCurso && $idPeriodo)
                        <a href="{{ route('director-grupo.notas', ['curso' => $idCurso, 'periodo' => $idPeriodo]) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-marker me-1"></i> Ver / digitar notas definitivas
                        </a>
                    @endif
                </div>
            </form>

            @if (! $idCurso || ! $idPeriodo)
                <div class="empty-state">
                    <div class="empty-icon"><i class="fas fa-marker"></i></div>
                    <p class="mb-0">Selecciona un curso y un periodo para digitar las notas definitivas.</p>
                </div>
            @endif
        @endif
    </div>
@endsection

@push('scripts')
    @vite('resources/js/pages/director-grupo/index.js')
@endpush
