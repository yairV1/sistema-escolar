<?php

namespace App\Modules\Planes\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlanUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // la autorización real corre en PlanPolicy, resuelta por el middleware `permission` de la ruta.
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique('planes', 'slug')->ignore($this->route('plan')?->id_plan, 'id_plan')],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'precio_mensual' => ['required', 'numeric', 'min:0'],
            'precio_anual' => ['required', 'numeric', 'min:0'],
            'limite_usuarios' => ['nullable', 'integer', 'min:1'],
            'beneficios' => ['nullable', 'string'],
            'modulos' => ['nullable', 'array'],
            'modulos.*' => ['integer', 'exists:modulos,id_modulo'],
        ];
    }
}
