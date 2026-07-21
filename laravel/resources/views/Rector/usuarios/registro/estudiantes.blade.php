@extends('layouts.panel')

@section('title', $estudiante ? 'Editar estudiante' : 'Registro de estudiantes')

@php
    $u = $estudiante?->usuario;
    $nombresPartes = $u ? explode(' ', $u->nombres, 2) : [];
    $apellidosPartes = $u ? explode(' ', $u->apellidos, 2) : [];
    $parentescoForm = $acudiente ? (array_search($acudiente->parentesco, \App\Modules\Usuarios\Models\Estudiante::PARENTESCO_MAP) ?: 'otro') : '';
@endphp

@section('content')
    <div class="container-fluid p-3 p-md-4" style="max-width: 900px;">
        <div class="d-flex align-items-center gap-2 mb-3">
            <a href="{{ route('listados', ['tab' => 'estudiantes']) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="h4 fw-semibold font-serif mb-0">{{ $estudiante ? 'Editar estudiante' : 'Registro de estudiantes' }}</h1>
        </div>

        <div class="wizard-pills">
            <div class="wizard-pill active" data-step-link="1"><span class="step-num"><span>1</span></span> Personales</div>
            <div class="wizard-pill" data-step-link="2"><span class="step-num"><span>2</span></span> Contacto</div>
            <div class="wizard-pill" data-step-link="3"><span class="step-num"><span>3</span></span> Académico</div>
            <div class="wizard-pill" data-step-link="4"><span class="step-num"><span>4</span></span> Acudiente</div>
        </div>

        <form id="wizardForm" novalidate>
            <input type="hidden" id="idEstudiante" value="{{ $estudiante?->id_estudiante }}">

            {{-- Paso 1 — Datos personales --}}
            <div class="wizard-step card mb-3" data-step="1">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Primer nombre *</label>
                            <input type="text" class="form-control" name="primer_nombre" id="primer_nombre"
                                   value="{{ $nombresPartes[0] ?? '' }}" data-feedback="err-primer_nombre" required>
                            <div class="invalid-feedback" id="err-primer_nombre"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Segundo nombre</label>
                            <input type="text" class="form-control" name="segundo_nombre" id="segundo_nombre" value="{{ $nombresPartes[1] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Primer apellido *</label>
                            <input type="text" class="form-control" name="primer_apellido" id="primer_apellido"
                                   value="{{ $apellidosPartes[0] ?? '' }}" data-feedback="err-primer_apellido" required>
                            <div class="invalid-feedback" id="err-primer_apellido"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Segundo apellido</label>
                            <input type="text" class="form-control" name="segundo_apellido" id="segundo_apellido" value="{{ $apellidosPartes[1] ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tipo de documento *</label>
                            <select class="form-select" name="tipo_documento" id="tipo_documento" data-feedback="err-tipo_documento" required>
                                @foreach (['TI' => 'Tarjeta de identidad', 'RC' => 'Registro civil', 'CC' => 'Cédula de ciudadanía', 'CE' => 'Cédula de extranjería'] as $val => $label)
                                    <option value="{{ $val }}" @selected($u?->tipo_documento === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="err-tipo_documento"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Número de documento *</label>
                            <input type="text" class="form-control" name="numero_documento" id="numero_documento"
                                   value="{{ $u?->numero_documento }}" data-feedback="err-numero_documento" required>
                            <div class="invalid-feedback" id="err-numero_documento"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fecha de nacimiento *</label>
                            <input type="date" class="form-control" name="fecha_nacimiento" id="fecha_nacimiento"
                                   value="{{ $estudiante?->fecha_nacimiento }}" data-feedback="err-fecha_nacimiento" required>
                            <div class="invalid-feedback" id="err-fecha_nacimiento"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Género *</label>
                            <select class="form-select" name="genero" id="genero" data-feedback="err-genero" required>
                                <option value="M" @selected($estudiante?->genero === 'M')>Masculino</option>
                                <option value="F" @selected($estudiante?->genero === 'F')>Femenino</option>
                                <option value="Otro" @selected($estudiante?->genero === 'Otro')>Otro</option>
                            </select>
                            <div class="invalid-feedback" id="err-genero"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Lugar de nacimiento</label>
                            <input type="text" class="form-control" name="lugar_nacimiento" id="lugar_nacimiento">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nacionalidad</label>
                            <input type="text" class="form-control" name="nacionalidad" id="nacionalidad" value="Colombiana">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Grupo sanguíneo</label>
                            <select class="form-select" name="grupo_sanguineo" id="grupo_sanguineo">
                                <option value="">Sin especificar</option>
                                @foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $gs)
                                    <option value="{{ $gs }}">{{ $gs }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">EPS</label>
                            <input type="text" class="form-control" name="eps" id="eps" value="{{ $estudiante?->eps_seguro }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Condición médica / alergias</label>
                            <textarea class="form-control" name="condicion_medica" id="condicion_medica" rows="1">{{ $estudiante?->observaciones_gral }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Paso 2 — Contacto --}}
            <div class="wizard-step card mb-3 d-none" data-step="2">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Dirección *</label>
                            <input type="text" class="form-control" name="direccion" id="direccion" data-feedback="err-direccion" required>
                            <div class="invalid-feedback" id="err-direccion"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Barrio</label>
                            <input type="text" class="form-control" name="barrio" id="barrio">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Localidad *</label>
                            <select class="form-select" name="localidad" id="localidad" data-feedback="err-localidad" required>
                                <option value="">Selecciona...</option>
                                @foreach (['Usaquén','Chapinero','Santa Fe','San Cristóbal','Usme','Tunjuelito','Bosa','Kennedy','Fontibón','Engativá','Suba','Barrios Unidos','Teusaquillo','Los Mártires','Antonio Nariño','Puente Aranda','La Candelaria','Rafael Uribe Uribe','Ciudad Bolívar','Sumapaz'] as $loc)
                                    <option value="{{ $loc }}">{{ $loc }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="err-localidad"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ciudad *</label>
                            <input type="text" class="form-control" name="ciudad" id="ciudad" value="Bogotá" data-feedback="err-ciudad" required>
                            <div class="invalid-feedback" id="err-ciudad"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Estrato</label>
                            <select class="form-select" name="estrato" id="estrato">
                                <option value="">Sin especificar</option>
                                @for ($i = 1; $i <= 6; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Teléfono del estudiante</label>
                            <input type="text" class="form-control" name="telefono_estudiante" id="telefono_estudiante" data-feedback="err-telefono_estudiante">
                            <div class="invalid-feedback" id="err-telefono_estudiante"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Correo del estudiante</label>
                            <input type="email" class="form-control" name="correo_estudiante" id="correo_estudiante"
                                   value="{{ $u?->correo }}" data-feedback="err-correo_estudiante" placeholder="Se autogenera si se deja vacío">
                            <div class="invalid-feedback" id="err-correo_estudiante"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Paso 3 — Académico --}}
            <div class="wizard-step card mb-3 d-none" data-step="3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Tipo de matrícula *</label>
                            <select class="form-select" name="tipo_matricula" id="tipo_matricula" data-feedback="err-tipo_matricula" required>
                                <option value="nueva" @selected(($matricula?->observacion) === 'nueva')>Nueva</option>
                                <option value="reingreso" @selected(($matricula?->observacion) === 'reingreso')>Reingreso</option>
                                <option value="traslado" @selected(($matricula?->observacion) === 'traslado')>Traslado</option>
                                <option value="interno" @selected(($matricula?->observacion) === 'interno')>Interno</option>
                            </select>
                            <div class="invalid-feedback" id="err-tipo_matricula"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Año lectivo *</label>
                            <select class="form-select" name="anio_lectivo" id="anio_lectivo" data-feedback="err-anio_lectivo" required>
                                @foreach ([now()->year, now()->year + 1] as $anio)
                                    <option value="{{ $anio }}" @selected($matricula?->anio_lectivo == $anio)>{{ $anio }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="err-anio_lectivo"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Jornada *</label>
                            <select class="form-select" name="jornada" id="jornada" data-feedback="err-jornada" required>
                                <option value="manana" @selected($matricula?->curso?->jornada === 'manana')>Mañana</option>
                                <option value="tarde" @selected($matricula?->curso?->jornada === 'tarde')>Tarde</option>
                                <option value="noche" @selected($matricula?->curso?->jornada === 'noche')>Noche</option>
                                <option value="unica" @selected($matricula?->curso?->jornada === 'unica')>Única</option>
                            </select>
                            <div class="invalid-feedback" id="err-jornada"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Grado *</label>
                            <select class="form-select" name="grado" id="grado" data-feedback="err-grado" required>
                                <optgroup label="Preescolar">
                                    <option value="PRE" @selected($grado === 'PRE')>Preescolar</option>
                                </optgroup>
                                <optgroup label="Primaria">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}" @selected(($grado ?? '') == $i)>{{ $i }}°</option>
                                    @endfor
                                </optgroup>
                                <optgroup label="Bachillerato">
                                    @for ($i = 6; $i <= 11; $i++)
                                        <option value="{{ $i }}" @selected(($grado ?? '') == $i)>{{ $i }}°</option>
                                    @endfor
                                </optgroup>
                            </select>
                            <div class="invalid-feedback" id="err-grado"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Grupo</label>
                            <select class="form-select" name="grupo" id="grupo">
                                <option value="">Asignar después</option>
                                @foreach (['A', 'B', 'C', 'D'] as $g)
                                    <option value="{{ $g }}" @selected(($grupo ?? '') === $g)>{{ $g }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Colegio anterior</label>
                            <input type="text" class="form-control" name="colegio_anterior" id="colegio_anterior">
                        </div>
                        <div class="col-12">
                            <label class="form-label d-block">¿Presenta alguna necesidad educativa especial (NEE)?</label>
                            <div class="btn-group" role="group">
                                <input type="radio" class="btn-check" name="nee" id="nee_no" value="no" checked>
                                <label class="btn btn-outline-secondary btn-sm" for="nee_no">No</label>
                                <input type="radio" class="btn-check" name="nee" id="nee_si" value="si">
                                <label class="btn btn-outline-secondary btn-sm" for="nee_si">Sí</label>
                            </div>
                        </div>
                        <div class="col-12 d-none" id="neeDescWrap">
                            <label class="form-label">Describe la necesidad *</label>
                            <textarea class="form-control" name="nee_descripcion" id="nee_descripcion" rows="2" data-feedback="err-nee_descripcion"></textarea>
                            <div class="invalid-feedback" id="err-nee_descripcion"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Paso 4 — Acudiente --}}
            <div class="wizard-step card mb-3 d-none" data-step="4">
                <div class="card-body">
                    @if ($estudiante)
                        <p class="text-secondary small">Deja este bloque vacío si no quieres cambiar el acudiente registrado.</p>
                    @endif
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre completo del acudiente @if(!$estudiante) *@endif</label>
                            <input type="text" class="form-control" name="acudiente_nombres" id="acudiente_nombres"
                                   value="{{ $acudiente->nombres ?? '' }} {{ $acudiente->apellidos ?? '' }}"
                                   data-feedback="err-acudiente_nombres" @if(!$estudiante) required @endif>
                            <div class="invalid-feedback" id="err-acudiente_nombres"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Parentesco @if(!$estudiante) *@endif</label>
                            <select class="form-select" name="acudiente_parentesco" id="acudiente_parentesco" data-feedback="err-acudiente_parentesco">
                                <option value="">Selecciona...</option>
                                @foreach (['madre' => 'Madre', 'padre' => 'Padre', 'abuelo' => 'Abuelo/a', 'tio' => 'Tío/a', 'hermano' => 'Hermano/a', 'acudiente' => 'Acudiente legal', 'otro' => 'Otro'] as $val => $label)
                                    <option value="{{ $val }}" @selected(($parentescoForm ?? '') === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="err-acudiente_parentesco"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tipo de documento</label>
                            <select class="form-select" name="acudiente_tipo_documento" id="acudiente_tipo_documento">
                                <option value="CC" @selected(($acudiente->tipo_documento ?? '') === 'CC')>Cédula de ciudadanía</option>
                                <option value="CE" @selected(($acudiente->tipo_documento ?? '') === 'CE')>Cédula de extranjería</option>
                                <option value="PAS" @selected(($acudiente->tipo_documento ?? '') === 'PAS')>Pasaporte</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Número de documento @if(!$estudiante) *@endif</label>
                            <input type="text" class="form-control" name="acudiente_numero_documento" id="acudiente_numero_documento"
                                   value="{{ $acudiente->numero_documento ?? '' }}" data-feedback="err-acudiente_numero_documento">
                            <div class="invalid-feedback" id="err-acudiente_numero_documento"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ocupación</label>
                            <input type="text" class="form-control" name="acudiente_ocupacion" id="acudiente_ocupacion" value="{{ $acudiente->ocupacion ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Teléfono principal @if(!$estudiante) *@endif</label>
                            <input type="text" class="form-control" name="acudiente_telefono" id="acudiente_telefono"
                                   value="{{ $acudiente->telefono ?? '' }}" data-feedback="err-acudiente_telefono">
                            <div class="invalid-feedback" id="err-acudiente_telefono"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Teléfono alterno</label>
                            <input type="text" class="form-control" name="acudiente_telefono2" id="acudiente_telefono2">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Correo @if(!$estudiante) *@endif</label>
                            <input type="email" class="form-control" name="acudiente_correo" id="acudiente_correo"
                                   value="{{ $acudiente->correo ?? '' }}" data-feedback="err-acudiente_correo">
                            <div class="invalid-feedback" id="err-acudiente_correo"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary d-none" data-wizard-prev>
                    <i class="fas fa-arrow-left me-1"></i> Anterior
                </button>
                <div class="ms-auto d-flex gap-2">
                    <button type="button" class="btn btn-primary" data-wizard-next data-wizard-hide-last>
                        Siguiente <i class="fas fa-arrow-right ms-1"></i>
                    </button>
                    <button type="submit" class="btn btn-primary d-none" id="btnGuardar" data-wizard-only-last>
                        <i class="fas fa-save me-1"></i> {{ $estudiante ? 'Guardar cambios' : 'Registrar estudiante' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>window.__RUTA_GUARDAR__ = @json($estudiante ? route('registro.estudiantes.update', $estudiante) : route('registro.estudiantes.store'));</script>
    @vite('resources/js/pages/registro/estudiantes.js')
@endpush
