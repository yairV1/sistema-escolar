<?php

namespace App\Modules\Usuarios\Requests\Registro;

/**
 * Igual que el registro nuevo, salvo que el bloque de acudiente es
 * opcional como grupo (igual que el legacy: si no se toca, no se exige).
 */
class EstudianteUpdateRequest extends EstudianteStoreRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        foreach (['acudiente_nombres', 'acudiente_parentesco', 'acudiente_tipo_documento', 'acudiente_numero_documento', 'acudiente_telefono', 'acudiente_correo'] as $campo) {
            $rules[$campo] = ['nullable', ...array_slice($rules[$campo], 1)];
        }

        return $rules;
    }
}
