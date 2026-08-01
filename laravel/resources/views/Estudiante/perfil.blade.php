@extends('layouts.panel')

@section('title', 'Mi perfil')

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <h1 class="h4 fw-semibold font-serif mb-4">Mi perfil</h1>

        <div class="row g-3">
            <div class="col-lg-7">
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

            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h6 fw-semibold mb-3"><i class="fas fa-people-roof text-primary me-1"></i> Acudiente asociado</h2>
                        <div class="d-flex align-items-center gap-3">
                            <div class="child-selector__avatar" style="width:44px;height:44px;font-size:.85rem;">
                                {{ collect(explode(' ', $acudiente->nombre))->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->take(2)->join('') }}
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $acudiente->nombre }}</div>
                                <div class="small text-secondary">{{ $acudiente->parentesco }}</div>
                            </div>
                        </div>
                        <dl class="row mt-3 mb-0">
                            <dt class="col-sm-5 text-secondary fw-normal">Teléfono</dt>
                            <dd class="col-sm-7">{{ $acudiente->telefono }}</dd>
                            <dt class="col-sm-5 text-secondary fw-normal">Correo</dt>
                            <dd class="col-sm-7 mb-0">{{ $acudiente->correo }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
