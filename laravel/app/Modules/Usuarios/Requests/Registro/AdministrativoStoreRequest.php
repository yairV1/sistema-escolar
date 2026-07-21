<?php

namespace App\Modules\Usuarios\Requests\Registro;

use Illuminate\Foundation\Http\FormRequest;

class AdministrativoStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'tipo_documento' => ['required', 'in:CC,CE,PAS'],
            'numero_documento' => ['required', 'string', 'max:20'],
            'correo' => ['required', 'email'],
            'telefono' => ['nullable', 'regex:/^[0-9+\s-]{7,20}$/'],
            'id_rol' => ['required', 'in:1,2,3,4'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Este campo es obligatorio.',
            'id_rol.required' => 'Selecciona un rol.',
        ];
    }
}
