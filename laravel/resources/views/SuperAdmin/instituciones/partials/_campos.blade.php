@php $institucion ??= null; @endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Nombre *</label>
        <input type="text" class="form-control" name="nombre" value="{{ old('nombre', $institucion->nombre ?? '') }}"
               data-feedback="err-nombre" required>
        <div class="invalid-feedback" id="err-nombre"></div>
    </div>
    <div class="col-md-6">
        <label class="form-label">Slug *</label>
        <input type="text" class="form-control" name="slug" value="{{ old('slug', $institucion->slug ?? '') }}"
               data-feedback="err-slug" required>
        <div class="invalid-feedback" id="err-slug"></div>
    </div>

    <div class="col-md-4">
        <label class="form-label">NIT</label>
        <input type="text" class="form-control" name="nit" value="{{ old('nit', $institucion->nit ?? '') }}" data-feedback="err-nit">
        <div class="invalid-feedback" id="err-nit"></div>
    </div>
    <div class="col-md-4">
        <label class="form-label">Email de contacto</label>
        <input type="email" class="form-control" name="email_contacto" value="{{ old('email_contacto', $institucion->email_contacto ?? '') }}" data-feedback="err-email">
        <div class="invalid-feedback" id="err-email"></div>
    </div>
    <div class="col-md-4">
        <label class="form-label">Teléfono</label>
        <input type="text" class="form-control" name="telefono" value="{{ old('telefono', $institucion->telefono ?? '') }}" data-feedback="err-telefono">
        <div class="invalid-feedback" id="err-telefono"></div>
    </div>

    <div class="col-md-5">
        <label class="form-label">Dirección</label>
        <input type="text" class="form-control" name="direccion" value="{{ old('direccion', $institucion->direccion ?? '') }}" data-feedback="err-direccion">
        <div class="invalid-feedback" id="err-direccion"></div>
    </div>
    <div class="col-md-4">
        <label class="form-label">Ciudad</label>
        <input type="text" class="form-control" name="ciudad" value="{{ old('ciudad', $institucion->ciudad ?? '') }}" data-feedback="err-ciudad">
        <div class="invalid-feedback" id="err-ciudad"></div>
    </div>
    <div class="col-md-3">
        <label class="form-label">País</label>
        <input type="text" class="form-control" name="pais" value="{{ old('pais', $institucion->pais ?? '') }}" data-feedback="err-pais">
        <div class="invalid-feedback" id="err-pais"></div>
    </div>

    <div class="col-md-4">
        <label class="form-label">Plan *</label>
        <select class="form-select" name="id_plan" data-feedback="err-id_plan" required>
            <option value="" disabled @selected(old('id_plan', $institucion->id_plan ?? null) === null)>Selecciona un plan</option>
            @foreach ($planes as $planCatalogo)
                <option value="{{ $planCatalogo->id_plan }}" @selected((int) old('id_plan', $institucion->id_plan ?? 0) === $planCatalogo->id_plan)>{{ $planCatalogo->nombre }}</option>
            @endforeach
        </select>
        <div class="invalid-feedback" id="err-id_plan"></div>
    </div>
    <div class="col-md-4">
        <label class="form-label">Límite de usuarios</label>
        <input type="number" min="1" class="form-control" name="limite_usuarios" value="{{ old('limite_usuarios', $institucion->limite_usuarios ?? '') }}" data-feedback="err-limite">
        <div class="invalid-feedback" id="err-limite"></div>
    </div>

    <div class="col-md-4">
        <label class="form-label">Fecha de inicio</label>
        <input type="date" class="form-control" name="fecha_inicio"
               value="{{ old('fecha_inicio', optional($institucion->fecha_inicio ?? null)->format('Y-m-d')) }}" data-feedback="err-fecha-inicio">
        <div class="invalid-feedback" id="err-fecha-inicio"></div>
    </div>
    <div class="col-md-4">
        <label class="form-label">Fecha de vencimiento</label>
        <input type="date" class="form-control" name="fecha_vencimiento"
               value="{{ old('fecha_vencimiento', optional($institucion->fecha_vencimiento ?? null)->format('Y-m-d')) }}" data-feedback="err-fecha-vencimiento">
        <div class="invalid-feedback" id="err-fecha-vencimiento"></div>
    </div>
</div>
