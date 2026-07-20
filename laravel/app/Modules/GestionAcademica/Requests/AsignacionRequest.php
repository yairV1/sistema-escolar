<?php

namespace App\Modules\GestionAcademica\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AsignacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_profesor' => ['required', 'integer', 'exists:profesores,id_profesor'],
            'id_materia' => ['required', 'integer', 'exists:materias,id_materia'],
            'id_curso' => ['required', 'integer', 'exists:cursos,id_curso'],
            'anio_lectivo' => ['required', 'integer', 'digits:4', 'min:2000', 'max:2100'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Este campo es obligatorio.',
            'id_profesor.exists' => 'El profesor seleccionado no es válido.',
            'id_materia.exists' => 'La materia seleccionada no es válida.',
            'id_curso.exists' => 'El curso seleccionado no es válido.',
        ];
    }
}
