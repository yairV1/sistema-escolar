<?php

namespace App\Modules\Calificaciones\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TipoActividadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_tipo' => ['required', 'string', 'max:50'],
            'descripcion' => ['nullable', 'string', 'max:200'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Este campo es obligatorio.',
        ];
    }
}
