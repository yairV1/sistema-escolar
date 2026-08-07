<?php

namespace App\Modules\Instituciones\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InstitucionStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // la autorización real corre en InstitucionPolicy, resuelta por el middleware `permission` de la ruta.
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:150', 'alpha_dash', 'unique:instituciones,slug'],
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
