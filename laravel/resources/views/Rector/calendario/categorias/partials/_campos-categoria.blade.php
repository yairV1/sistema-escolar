{{--
    Campos compartidos entre el modal de crear y los N modales de editar
    (uno por categoría, mismo patrón que Materias). $categoria es null al crear.
    $sufijo evita colisión de ids entre los distintos modales de la página.
--}}
<div class="modal-header">
    <h5 class="modal-title font-serif">{{ $categoria ? 'Editar categoría' : 'Nueva categoría' }}</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
</div>
<div class="modal-body">
    <div class="mb-3">
        <label class="form-label">Nombre *</label>
        <input type="text" class="form-control" name="nombre" value="{{ $categoria->nombre ?? '' }}"
               data-feedback="err-{{ $sufijo }}-nombre" required>
        <div class="invalid-feedback" id="err-{{ $sufijo }}-nombre"></div>
    </div>
    <div class="mb-3">
        <label class="form-label">Descripción</label>
        <textarea class="form-control" name="descripcion" rows="2"
                  data-feedback="err-{{ $sufijo }}-descripcion">{{ $categoria->descripcion ?? '' }}</textarea>
        <div class="invalid-feedback" id="err-{{ $sufijo }}-descripcion"></div>
    </div>
    <div class="row g-3 mb-1">
        <div class="col-6">
            <label class="form-label">Color *</label>
            <select class="form-select" name="color" data-feedback="err-{{ $sufijo }}-color" required>
                @foreach ($nombresColor as $valor => $etiqueta)
                    <option value="{{ $valor }}" @selected(($categoria->color ?? 'var(--cat-1)') === $valor)>{{ $etiqueta }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback" id="err-{{ $sufijo }}-color"></div>
        </div>
        <div class="col-6">
            <label class="form-label">Ícono (bootstrap-icons) *</label>
            <input type="text" class="form-control" name="icono" value="{{ $categoria->icono ?? 'bi-calendar-event' }}"
                   placeholder="bi-calendar-event" data-feedback="err-{{ $sufijo }}-icono" required>
            <div class="invalid-feedback" id="err-{{ $sufijo }}-icono"></div>
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label">Orden</label>
        <input type="number" class="form-control" name="orden" min="0" value="{{ $categoria->orden ?? 0 }}"
               data-feedback="err-{{ $sufijo }}-orden">
        <div class="invalid-feedback" id="err-{{ $sufijo }}-orden"></div>
    </div>

    @unless ($categoria?->es_sistema)
        @foreach (['roles_crear' => 'Puede crear', 'roles_editar' => 'Puede editar', 'roles_eliminar' => 'Puede eliminar'] as $campo => $etiqueta
        )
            <div class="mb-2">
                <label class="form-label d-block">{{ $etiqueta }}</label>
                @foreach ($rolesDisponibles as $rol)
                    <div class="form-check form-check-inline">
                        <input type="checkbox" class="form-check-input" name="{{ $campo }}[]" value="{{ $rol }}"
                               id="{{ $sufijo }}-{{ $campo }}-{{ $rol }}"
                               @checked(in_array($rol, $categoria->{$campo} ?? [], true))>
                        <label class="form-check-label small" for="{{ $sufijo }}-{{ $campo }}-{{ $rol }}">{{ $nombresRol[$rol] ?? $rol }}</label>
                    </div>
                @endforeach
            </div>
        @endforeach
    @else
        <p class="small text-secondary mb-0">
            <i class="bi bi-info-circle"></i> Categoría del sistema: se alimenta automáticamente (horarios/actividades), no tiene permisos de creación configurables.
        </p>
    @endunless
</div>
