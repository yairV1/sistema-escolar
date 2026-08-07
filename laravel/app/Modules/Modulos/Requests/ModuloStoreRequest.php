<?php

namespace App\Modules\Modulos\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ModuloStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // la autorización real corre en ModuloPolicy, resuelta por el middleware `permission` de la ruta.
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:100', 'alpha_dash', 'unique:modulos,slug'],
            'categoria' => ['required', 'string', 'in:academico,administrativo,comunicacion,finanzas'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'icono' => ['nullable', 'string', 'max:60'],
            'planes' => ['nullable', 'array'],
            'planes.*' => ['integer', 'exists:planes,id_plan'],
        ];
    }
}
