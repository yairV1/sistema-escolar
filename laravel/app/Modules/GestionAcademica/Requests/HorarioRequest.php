<?php

namespace App\Modules\GestionAcademica\Requests;

use App\Modules\GestionAcademica\Models\Horario;
use Illuminate\Foundation\Http\FormRequest;
use App\Modules\GestionAcademica\Models\AsignacionAcademica;
use App\Modules\Instituciones\Support\TenantGuard;

class HorarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_asignacion' => ['required', 'integer', TenantGuard::existe(AsignacionAcademica::class)],
            'dia_semana' => ['required', 'in:'.implode(',', Horario::DIAS_SEMANA)],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'salon' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Este campo es obligatorio.',
            'id_asignacion.exists' => 'La asignación seleccionada no es válida.',
            'hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
        ];
    }
}
