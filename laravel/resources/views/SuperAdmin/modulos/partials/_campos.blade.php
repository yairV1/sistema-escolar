@php $modulo ??= null; @endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Nombre *</label>
        <input type="text" class="form-control" name="nombre" value="{{ old('nombre', $modulo->nombre ?? '') }}" data-feedback="err-nombre" required>
        <div class="invalid-feedback" id="err-nombre"></div>
    </div>
    <div class="col-md-6">
        <label class="form-label">Slug *</label>
        <input type="text" class="form-control" name="slug" value="{{ old('slug', $modulo->slug ?? '') }}" data-feedback="err-slug" required>
        <div class="invalid-feedback" id="err-slug"></div>
    </div>

    <div class="col-md-6">
        <label class="form-label">Categoría *</label>
        <select class="form-select" name="categoria" data-feedback="err-categoria" required>
            @foreach (['academico' => 'Académico', 'administrativo' => 'Administrativo', 'comunicacion' => 'Comunicación', 'finanzas' => 'Finanzas'] as $valor => $etiqueta)
                <option value="{{ $valor }}" @selected(old('categoria', $modulo->categoria ?? '') === $valor)>{{ $etiqueta }}</option>
            @endforeach
        </select>
        <div class="invalid-feedback" id="err-categoria"></div>
    </div>
    <div class="col-md-6">
        <label class="form-label">Ícono (clase Font Awesome)</label>
        <input type="text" class="form-control" name="icono" value="{{ old('icono', $modulo->icono ?? 'fas fa-puzzle-piece') }}" data-feedback="err-icono">
        <div class="invalid-feedback" id="err-icono"></div>
    </div>

    <div class="col-12">
        <label class="form-label">Descripción</label>
        <textarea class="form-control" name="descripcion" rows="3" data-feedback="err-descripcion">{{ old('descripcion', $modulo->descripcion ?? '') }}</textarea>
        <div class="invalid-feedback" id="err-descripcion"></div>
    </div>
</div>
