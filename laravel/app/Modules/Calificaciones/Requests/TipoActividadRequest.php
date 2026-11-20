<?php

namespace App\Modules\Calificaciones\Requests;

use App\Modules\Instituciones\Support\UnicoEnInstitucion;
use App\Modules\Calificaciones\Models\TipoActividad;
use Illuminate\Foundation\Http\FormRequest;

class TipoActividadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_tipo' => ['required', 'string', 'max:50', UnicoEnInstitucion::regla(TipoActividad::class, ['nombre_tipo'], $this->route('tipoActividad'), 'Ya existe un tipo de actividad con ese nombre.')],
            'descripcion' => ['nullable', 'string', 'max:200'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Este campo es obligatorio.',
        ];
    }
}
