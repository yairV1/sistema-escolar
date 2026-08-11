<?php

namespace App\Modules\Modulos\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ModuloUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // la autorización real corre en ModuloPolicy, resuelta por el middleware `permission` de la ruta.
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique('modulos', 'slug')->ignore($this->route('modulo')?->id_modulo, 'id_modulo')],
            'categoria' => ['required', 'string', 'in:academico,administrativo,comunicacion,finanzas'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'icono' => ['nullable', 'string', 'max:60'],
            'planes' => ['nullable', 'array'],
            'planes.*' => ['integer', 'exists:planes,id_plan'],
        ];
    }
}
