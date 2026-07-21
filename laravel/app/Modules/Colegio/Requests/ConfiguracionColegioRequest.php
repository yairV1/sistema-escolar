<?php

namespace App\Modules\Colegio\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfiguracionColegioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_colegio' => ['required', 'string', 'max:150'],
            'direccion' => ['required', 'string', 'max:200'],
            'ciudad' => ['required', 'string', 'max:100'],
            'departamento' => ['required', 'string', 'max:100'],
            'pais' => ['required', 'string', 'max:100'],
            'telefono' => ['required', 'string', 'max:30'],
            'email_institucional' => ['required', 'email', 'max:150'],
            'sitio_web' => ['nullable', 'url', 'max:200'],
            'descripcion' => ['nullable', 'string', 'max:300'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'logo_removido' => ['nullable', 'boolean'],
        ];
    }
}
