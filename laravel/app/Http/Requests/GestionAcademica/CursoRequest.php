<?php

namespace App\Http\Requests\GestionAcademica;

use Illuminate\Foundation\Http\FormRequest;

class CursoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->id_director_grupo === '') {
            $this->merge(['id_director_grupo' => null]);
        }
    }

    public function rules(): array
    {
        return [
            'nombre_curso' => ['required', 'string', 'max:20'],
            'nivel_academico' => ['required', 'in:preescolar,primaria,secundaria,media'],
            'jornada' => ['required', 'in:manana,tarde,noche,unica'],
            'anio_lectivo' => ['required', 'integer', 'digits:4', 'min:2000', 'max:2100'],
            'id_director_grupo' => ['nullable', 'integer', 'exists:profesores,id_profesor'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Este campo es obligatorio.',
            'id_director_grupo.exists' => 'El profesor seleccionado no es válido.',
        ];
    }
}
