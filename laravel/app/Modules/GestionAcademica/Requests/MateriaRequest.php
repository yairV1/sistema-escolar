<?php

namespace App\Modules\GestionAcademica\Requests;

use App\Modules\GestionAcademica\Models\Materia;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class MateriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_materia' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'intensidad_horaria' => ['required', 'integer', 'min:1', 'max:40'],
            'id_materia_padre' => ['nullable', 'integer', 'exists:materias,id_materia'],
        ];
    }

    /** Un solo nivel de anidación: ni auto-referencia ni encadenar sub-asignaturas como padre de otras. */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $idPadre = $this->input('id_materia_padre');
            if (! $idPadre) {
                return;
            }

            $materiaActual = $this->route('materia');
            if ($materiaActual && (int) $idPadre === $materiaActual->id_materia) {
                $validator->errors()->add('id_materia_padre', 'Una asignatura no puede ser su propia asignatura padre.');

                return;
            }

            $padre = Materia::find($idPadre);
            if ($padre && $padre->id_materia_padre) {
                $validator->errors()->add('id_materia_padre', 'Esa asignatura ya es una sub-asignatura; solo se permite un nivel.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'required' => 'Este campo es obligatorio.',
        ];
    }
}
