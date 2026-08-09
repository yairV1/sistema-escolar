@php $rector ??= null; @endphp

<h2 class="h6 fw-semibold mb-3">Datos del rector</h2>
<p class="small text-secondary mb-3">
    @if ($rector)
        Se actualiza el usuario con rol Rector de esta institución. Su contraseña no cambia aquí.
    @else
        Se crea junto con la institución, con el rol de Rector. Su contraseña inicial es su número de documento.
    @endif
</p>

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Nombres *</label>
        <input type="text" class="form-control" name="rector_nombres" value="{{ old('rector_nombres', $rector->nombres ?? '') }}"
               data-feedback="err-rector-nombres" required>
        <div class="invalid-feedback" id="err-rector-nombres"></div>
    </div>
    <div class="col-md-6">
        <label class="form-label">Apellidos *</label>
        <input type="text" class="form-control" name="rector_apellidos" value="{{ old('rector_apellidos', $rector->apellidos ?? '') }}"
               data-feedback="err-rector-apellidos" required>
        <div class="invalid-feedback" id="err-rector-apellidos"></div>
    </div>

    <div class="col-md-4">
        <label class="form-label">Tipo de documento *</label>
        <select class="form-select" name="rector_tipo_documento" data-feedback="err-rector-tipo-documento" required>
            @foreach (['CC' => 'Cédula de ciudadanía', 'CE' => 'Cédula de extranjería', 'PAS' => 'Pasaporte'] as $valor => $etiqueta)
                <option value="{{ $valor }}" @selected(old('rector_tipo_documento', $rector->tipo_documento ?? 'CC') === $valor)>{{ $etiqueta }}</option>
            @endforeach
        </select>
        <div class="invalid-feedback" id="err-rector-tipo-documento"></div>
    </div>
    <div class="col-md-4">
        <label class="form-label">Número de documento *</label>
        <input type="text" class="form-control" name="rector_numero_documento" value="{{ old('rector_numero_documento', $rector->numero_documento ?? '') }}"
               data-feedback="err-rector-numero-documento" required>
        <div class="invalid-feedback" id="err-rector-numero-documento"></div>
    </div>
    <div class="col-md-4">
        <label class="form-label">Teléfono</label>
        <input type="text" class="form-control" name="rector_telefono" value="{{ old('rector_telefono', $rector->telefono ?? '') }}"
               data-feedback="err-rector-telefono">
        <div class="invalid-feedback" id="err-rector-telefono"></div>
    </div>

    <div class="col-md-6">
        <label class="form-label">Correo *</label>
        <input type="email" class="form-control" name="rector_correo" value="{{ old('rector_correo', $rector->correo ?? '') }}"
               data-feedback="err-rector-correo" required>
        <div class="invalid-feedback" id="err-rector-correo"></div>
    </div>
</div>
