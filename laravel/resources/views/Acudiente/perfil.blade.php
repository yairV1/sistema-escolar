@extends('layouts.panel')

@section('title', 'Mi perfil')

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <h1 class="h4 fw-semibold font-serif mb-4">Mi perfil</h1>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h6 fw-semibold mb-3"><i class="fas fa-user text-primary me-1"></i> Datos personales</h2>
                        <dl class="row mb-0">
                            <dt class="col-sm-4 text-secondary fw-normal">Nombre completo</dt>
                            <dd class="col-sm-8">{{ trim($usuario->nombres.' '.$usuario->apellidos) }}</dd>

                            <dt class="col-sm-4 text-secondary fw-normal">Correo</dt>
                            <dd class="col-sm-8">{{ $usuario->correo }}</dd>

                            <dt class="col-sm-4 text-secondary fw-normal">Teléfono</dt>
                            <dd class="col-sm-8">{{ $usuario->telefono ?? '—' }}</dd>

                            <dt class="col-sm-4 text-secondary fw-normal">Documento</dt>
                            <dd class="col-sm-8 mb-0">{{ strtoupper($usuario->tipo_documento) }} {{ $usuario->numero_documento }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="h6 fw-semibold mb-0"><i class="fas fa-user-graduate text-primary me-1"></i> Estudiantes vinculados</h2>
                            <a href="{{ route('acudiente.estudiantes') }}" class="small">Ver todos</a>
                        </div>

                        @if ($hijos->isEmpty())
                            <div class="empty-state">
                                <div class="empty-icon"><i class="fas fa-user-graduate"></i></div>
                                <p class="mb-0">No tienes estudiantes vinculados todavía.</p>
                            </div>
                        @else
                            <ul class="list-group list-group-flush">
                                @foreach ($hijos as $hijo)
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="child-option__avatar">{{ mb_substr($hijo->nombres, 0, 1).mb_substr($hijo->apellidos, 0, 1) }}</span>
                                            <div>
                                                <div class="fw-semibold">{{ $hijo->nombres }} {{ $hijo->apellidos }}</div>
                                                <div class="small text-secondary">{{ $hijo->curso }} · {{ ucfirst($hijo->parentesco) }}</div>
                                            </div>
                                        </div>
                                        @if ($hijo->es_principal)
                                            <span class="badge text-bg-success">Principal</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
