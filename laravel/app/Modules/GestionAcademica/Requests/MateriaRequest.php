<?php

namespace App\Modules\GestionAcademica\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MateriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->id_area === '') {
            $this->merge(['id_area' => null]);
        }
    }

    public function rules(): array
    {
        return [
            'id_area' => ['nullable', 'integer', 'exists:areas,id_area'],
            'nombre_materia' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'intensidad_horaria' => ['required', 'integer', 'min:1', 'max:40'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Este campo es obligatorio.',
        ];
    }
}
