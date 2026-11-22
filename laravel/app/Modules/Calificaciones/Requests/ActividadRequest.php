<?php

namespace App\Modules\Calificaciones\Requests;

use App\Modules\Instituciones\Support\TenantGuard;
use App\Modules\Calificaciones\Models\Periodo;
use App\Modules\Calificaciones\Models\TipoActividad;
use Illuminate\Foundation\Http\FormRequest;

class ActividadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_periodo' => ['required', 'integer', TenantGuard::existe(Periodo::class)],
            'id_tipo_actividad' => ['required', 'integer', TenantGuard::existe(TipoActividad::class)],
            'titulo' => ['required', 'string', 'max:200'],
            'descripcion' => ['nullable', 'string'],
            'porcentaje' => ['required', 'numeric', 'min:0', 'max:100'],
            'fecha_entrega' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Este campo es obligatorio.',
            'id_periodo.exists' => 'El periodo seleccionado no es válido.',
            'id_tipo_actividad.exists' => 'El tipo de actividad seleccionado no es válido.',
        ];
    }
}
