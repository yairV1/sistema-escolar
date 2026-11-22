<?php

namespace App\Modules\Instituciones\Requests;

use App\Modules\Instituciones\Support\SlugInstitucion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'slug' => ['required', 'string', 'max:120', SlugInstitucion::regla()],
            'nit' => ['nullable', 'string', 'max:50'],
            'email_contacto' => ['nullable', 'email', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string', 'max:200'],
            'ciudad' => ['nullable', 'string', 'max:100'],
            'pais' => ['nullable', 'string', 'max:100'],
            'id_plan' => ['required', 'integer', Rule::exists('planes', 'id_plan')->where('estado', 'activo')],
            'limite_usuarios' => ['nullable', 'integer', 'min:1'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_vencimiento' => ['nullable', 'date', 'after:fecha_inicio'],
            'estado' => ['nullable', Rule::in(['activa', 'suspendida'])],

            'administrador_nombres' => ['required', 'string', 'max:150'],
            'administrador_apellidos' => ['required', 'string', 'max:150'],
            'administrador_tipo_documento' => ['required', 'in:CC,CE,PAS'],
            'administrador_numero_documento' => ['required', 'string', 'max:50', 'unique:usuarios,numero_documento'],
            'administrador_correo' => ['required', 'email', 'max:150', 'unique:usuarios,correo'],
            'administrador_telefono' => ['nullable', 'regex:/^[0-9+\s-]{7,20}$/'],
        ];
    }
}
