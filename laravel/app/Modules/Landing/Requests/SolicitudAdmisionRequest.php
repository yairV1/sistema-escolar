<?php

namespace App\Modules\Landing\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SolicitudAdmisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_acudiente' => ['required', 'string', 'max:100'],
            'apellido_acudiente' => ['required', 'string', 'max:100'],
            'correo' => ['required', 'email', 'max:150'],
            'telefono' => ['required', 'string', 'max:20'],
            'nombre_estudiante' => ['required', 'string', 'max:150'],
            'grado_interes' => ['required', 'string', 'max:50'],
            'mensaje' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Este campo es obligatorio.',
            'correo.email' => 'Ingresa un correo válido.',
        ];
    }
}
