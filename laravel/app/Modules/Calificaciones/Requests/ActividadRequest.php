<?php

namespace App\Modules\Calificaciones\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActividadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_periodo' => ['required', 'integer', 'exists:periodos_academicos,id_periodo'],
            'id_tipo_actividad' => ['required', 'integer', 'exists:tipos_actividad,id_tipo_actividad'],
            'titulo' => ['required', 'string', 'max:200'],
            'descripcion' => ['nullable', 'string'],
            'porcentaje' => ['required', 'numeric', 'min:0', 'max:100'],
            'fecha_entrega' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Este campo es obligatorio.',
            'id_periodo.exists' => 'El periodo seleccionado no es válido.',
            'id_tipo_actividad.exists' => 'El tipo de actividad seleccionado no es válido.',
        ];
    }
}
