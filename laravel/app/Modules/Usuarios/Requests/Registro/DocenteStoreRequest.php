<?php

namespace App\Modules\Usuarios\Requests\Registro;

use Illuminate\Foundation\Http\FormRequest;

class DocenteStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'primer_nombre' => ['required', 'string', 'max:100'],
            'segundo_nombre' => ['nullable', 'string', 'max:100'],
            'primer_apellido' => ['required', 'string', 'max:100'],
            'segundo_apellido' => ['nullable', 'string', 'max:100'],
            'tipo_documento' => ['required', 'in:CC,CE,PAS'],
            'numero_documento' => ['required', 'string', 'max:20'],
            'correo' => ['required', 'email'],
            'telefono' => ['nullable', 'regex:/^[0-9+\s-]{7,20}$/'],
            'profesion' => ['nullable', 'string', 'max:100'],
            'especialidad' => ['nullable', 'string', 'max:100'],
            'fecha_ingreso' => ['nullable', 'date'],
            'materias' => ['nullable', 'array'],
            'materias.*' => ['integer', 'exists:materias,id_materia'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Este campo es obligatorio.',
        ];
    }
}
