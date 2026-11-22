<h2 class="h6 fw-semibold mb-3">Administrador inicial</h2>
<p class="small text-secondary mb-3">
    Se asignará el rol Administrador a este colegio. No se genera ni se muestra una contraseña temporal; el administrador deberá establecerla mediante recuperación de acceso.
</p>

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Nombres *</label>
        <input type="text" class="form-control" name="administrador_nombres" value="{{ old('administrador_nombres') }}"
               data-feedback="err-administrador-nombres" required>
        <div class="invalid-feedback" id="err-administrador-nombres"></div>
    </div>
    <div class="col-md-6">
        <label class="form-label">Apellidos *</label>
        <input type="text" class="form-control" name="administrador_apellidos" value="{{ old('administrador_apellidos') }}"
               data-feedback="err-administrador-apellidos" required>
        <div class="invalid-feedback" id="err-administrador-apellidos"></div>
    </div>

    <div class="col-md-4">
        <label class="form-label">Tipo de documento *</label>
        <select class="form-select" name="administrador_tipo_documento" data-feedback="err-administrador-tipo-documento" required>
            @foreach (['CC' => 'Cédula de ciudadanía', 'CE' => 'Cédula de extranjería', 'PAS' => 'Pasaporte'] as $valor => $etiqueta)
                <option value="{{ $valor }}" @selected(old('administrador_tipo_documento', 'CC') === $valor)>{{ $etiqueta }}</option>
            @endforeach
        </select>
        <div class="invalid-feedback" id="err-administrador-tipo-documento"></div>
    </div>
    <div class="col-md-4">
        <label class="form-label">Número de documento *</label>
        <input type="text" class="form-control" name="administrador_numero_documento" value="{{ old('administrador_numero_documento') }}"
               data-feedback="err-administrador-numero-documento" required>
        <div class="invalid-feedback" id="err-administrador-numero-documento"></div>
    </div>
    <div class="col-md-4">
        <label class="form-label">Teléfono</label>
        <input type="text" class="form-control" name="administrador_telefono" value="{{ old('administrador_telefono') }}"
               data-feedback="err-administrador-telefono">
        <div class="invalid-feedback" id="err-administrador-telefono"></div>
    </div>

    <div class="col-md-6">
        <label class="form-label">Correo *</label>
        <input type="email" class="form-control" name="administrador_correo" value="{{ old('administrador_correo') }}"
               data-feedback="err-administrador-correo" required>
        <div class="invalid-feedback" id="err-administrador-correo"></div>
    </div>
</div>
