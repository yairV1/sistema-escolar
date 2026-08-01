<?php

namespace App\Modules\Instituciones\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InstitucionUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $institucion = $this->route('institucion');

        return [
            'nombre' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:150', 'alpha_dash', Rule::unique('instituciones', 'slug')->ignore($institucion->id_institucion, 'id_institucion')],
            'nit' => ['nullable', 'string', 'max:50'],
            'email_contacto' => ['nullable', 'email', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string', 'max:200'],
            'ciudad' => ['nullable', 'string', 'max:100'],
            'pais' => ['nullable', 'string', 'max:100'],
            'plan' => ['required', 'string', 'in:basico,estandar,premium'],
            'limite_usuarios' => ['nullable', 'integer', 'min:1'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_vencimiento' => ['nullable', 'date', 'after:fecha_inicio'],
        ];
    }
}
