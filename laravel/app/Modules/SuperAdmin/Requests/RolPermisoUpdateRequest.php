<?php

namespace App\Modules\SuperAdmin\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * La matriz envía, por cada rol editado, la lista completa de permisos que
 * debe tener (checkboxes `permisos[{id_rol}][]`) — el controlador
 * reemplaza esas filas de `permission_role`, no las suma.
 */
class RolPermisoUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'permisos' => ['present', 'array'],
            'permisos.*' => ['array'],
            'permisos.*.*' => ['integer', 'exists:permissions,id_permiso'],
        ];
    }
}
