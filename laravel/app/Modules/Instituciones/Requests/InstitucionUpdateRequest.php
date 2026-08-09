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
        // Institución creada antes de que el rector fuera obligatorio (o sin
        // rector por cualquier otro motivo): el formulario permite crearlo
        // desde aquí, así que puede no haber id_usuario que excluir todavía.
        $rector = $institucion->usuarios()->where('id_rol', 2)->first();

        return [
            'nombre' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:150', 'alpha_dash', Rule::unique('instituciones', 'slug')->ignore($institucion->id_institucion, 'id_institucion')],
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

            'rector_nombres' => ['required', 'string', 'max:150'],
            'rector_apellidos' => ['required', 'string', 'max:150'],
            'rector_tipo_documento' => ['required', 'in:CC,CE,PAS'],
            'rector_numero_documento' => ['required', 'string', 'max:50', Rule::unique('usuarios', 'numero_documento')->ignore($rector?->id_usuario, 'id_usuario')],
            'rector_correo' => ['required', 'email', 'max:150', Rule::unique('usuarios', 'correo')->ignore($rector?->id_usuario, 'id_usuario')],
            'rector_telefono' => ['nullable', 'regex:/^[0-9+\s-]{7,20}$/'],
        ];
    }
}
