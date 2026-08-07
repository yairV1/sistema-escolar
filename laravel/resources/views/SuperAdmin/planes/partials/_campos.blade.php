@php $plan ??= null; @endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Nombre *</label>
        <input type="text" class="form-control" name="nombre" value="{{ old('nombre', $plan->nombre ?? '') }}" data-feedback="err-nombre" required>
        <div class="invalid-feedback" id="err-nombre"></div>
    </div>
    <div class="col-md-6">
        <label class="form-label">Slug *</label>
        <input type="text" class="form-control" name="slug" value="{{ old('slug', $plan->slug ?? '') }}" data-feedback="err-slug" required>
        <div class="invalid-feedback" id="err-slug"></div>
    </div>

    <div class="col-12">
        <label class="form-label">Descripción</label>
        <input type="text" class="form-control" name="descripcion" value="{{ old('descripcion', $plan->descripcion ?? '') }}" data-feedback="err-descripcion">
        <div class="invalid-feedback" id="err-descripcion"></div>
    </div>

    <div class="col-md-4">
        <label class="form-label">Precio mensual (COP) *</label>
        <input type="number" min="0" step="1000" class="form-control" name="precio_mensual" value="{{ old('precio_mensual', $plan->precio_mensual ?? 0) }}" data-feedback="err-precio-mensual" required>
        <div class="invalid-feedback" id="err-precio-mensual"></div>
    </div>
    <div class="col-md-4">
        <label class="form-label">Precio anual (COP) *</label>
        <input type="number" min="0" step="1000" class="form-control" name="precio_anual" value="{{ old('precio_anual', $plan->precio_anual ?? 0) }}" data-feedback="err-precio-anual" required>
        <div class="invalid-feedback" id="err-precio-anual"></div>
    </div>
    <div class="col-md-4">
        <label class="form-label">Límite de usuarios</label>
        <input type="number" min="1" class="form-control" name="limite_usuarios" value="{{ old('limite_usuarios', $plan->limite_usuarios ?? '') }}" placeholder="Ilimitado" data-feedback="err-limite">
        <div class="invalid-feedback" id="err-limite"></div>
    </div>

    <div class="col-12">
        <label class="form-label">Beneficios (uno por línea)</label>
        <textarea class="form-control" name="beneficios" rows="4" data-feedback="err-beneficios">{{ old('beneficios', isset($plan) ? implode("\n", $plan->beneficios ?? []) : '') }}</textarea>
        <div class="invalid-feedback" id="err-beneficios"></div>
    </div>

    <div class="col-12">
        <label class="form-label">Módulos incluidos</label>
        <div class="row g-2">
            @foreach ($modulos as $modulo)
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="modulos[]" value="{{ $modulo->id_modulo }}"
                               id="modulo-{{ $modulo->id_modulo }}"
                               @checked(in_array($modulo->id_modulo, old('modulos', $modulosSeleccionados ?? [])))>
                        <label class="form-check-label small" for="modulo-{{ $modulo->id_modulo }}">
                            <i class="{{ $modulo->icono }} me-1"></i>{{ $modulo->nombre }}
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
