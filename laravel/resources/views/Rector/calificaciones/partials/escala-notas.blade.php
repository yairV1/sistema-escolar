<p class="text-secondary small mb-3" style="max-width:70ch;">
    <i class="fas fa-circle-info me-1"></i>
    Define los rangos de desempeño (ej. Bajo, Básico, Alto, Superior) que se muestran junto a la nota definitiva en el boletín.
</p>

<form id="formEscalaNotas" data-url="{{ route('calificaciones.escala-notas.guardar') }}">
    <div class="table-responsive">
        <table class="table align-middle" id="tablaEscalaNotas">
            <thead>
                <tr>
                    <th>Etiqueta</th>
                    <th style="width:100px;">Sigla</th>
                    <th style="width:120px;">Nota mínima</th>
                    <th style="width:120px;">Nota máxima</th>
                    <th style="width:100px;">Orden</th>
                    <th style="width:50px;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($escalas as $escala)
                    <tr data-fila-escala>
                        <td>
                            <input type="hidden" name="id_escala" value="{{ $escala->id_escala }}">
                            <input type="text" class="form-control form-control-sm" name="etiqueta" maxlength="50" value="{{ $escala->etiqueta }}" required>
                        </td>
                        <td><input type="text" class="form-control form-control-sm" name="sigla" maxlength="5" value="{{ $escala->sigla }}" required></td>
                        <td><input type="number" class="form-control form-control-sm" name="valor_min" min="0" max="5" step="0.1" value="{{ $escala->valor_min }}" required></td>
                        <td><input type="number" class="form-control form-control-sm" name="valor_max" min="0" max="5" step="0.1" value="{{ $escala->valor_max }}" required></td>
                        <td><input type="number" class="form-control form-control-sm" name="orden" min="0" value="{{ $escala->orden }}" required></td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger" data-eliminar-fila title="Eliminar rango">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between mt-3">
        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnAgregarRangoEscala">
            <i class="fas fa-plus me-1"></i> Agregar rango
        </button>
        <button type="submit" class="btn btn-primary" id="btnGuardarEscalaNotas">
            <i class="fas fa-save me-1"></i> Guardar cambios
        </button>
    </div>
</form>

<template id="plantillaFilaEscala">
    <tr data-fila-escala>
        <td>
            <input type="hidden" name="id_escala" value="">
            <input type="text" class="form-control form-control-sm" name="etiqueta" maxlength="50" required>
        </td>
        <td><input type="text" class="form-control form-control-sm" name="sigla" maxlength="5" required></td>
        <td><input type="number" class="form-control form-control-sm" name="valor_min" min="0" max="5" step="0.1" required></td>
        <td><input type="number" class="form-control form-control-sm" name="valor_max" min="0" max="5" step="0.1" required></td>
        <td><input type="number" class="form-control form-control-sm" name="orden" min="0" required></td>
        <td class="text-end">
            <button type="button" class="btn btn-sm btn-outline-danger" data-eliminar-fila title="Eliminar rango">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>
</template>
