<?php

namespace App\Modules\Docente\Requests;

use App\Modules\Observaciones\Models\Observacion;
use Illuminate\Foundation\Http\FormRequest;

class AnotacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_estudiante' => ['required', 'integer', 'exists:estudiantes,id_estudiante'],
            'tipo_observacion' => ['required', 'in:'.implode(',', Observacion::TIPOS)],
            'descripcion' => ['required', 'string'],
            'fecha' => ['required', 'date'],
            'nivel_gravedad' => ['required', 'in:'.implode(',', Observacion::NIVELES)],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Este campo es obligatorio.',
            'id_estudiante.exists' => 'El estudiante seleccionado no es válido.',
        ];
    }
}
