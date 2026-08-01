<?php

namespace App\Modules\SuperAdmin\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UsuarioGlobalCambiarRolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_rol' => ['required', 'integer', Rule::in(range(1, 8))],
        ];
    }
}
