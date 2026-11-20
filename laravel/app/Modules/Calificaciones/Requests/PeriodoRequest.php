<?php

namespace App\Modules\Calificaciones\Requests;

use App\Modules\Calificaciones\Models\Periodo;
use App\Modules\Instituciones\Support\UnicoEnInstitucion;
use Illuminate\Foundation\Http\FormRequest;

class PeriodoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_periodo' => ['required', 'string', 'max:50', UnicoEnInstitucion::regla(Periodo::class, ['nombre_periodo', 'anio_lectivo'], $this->route('periodo'), 'Ya existe un periodo con ese nombre en ese año lectivo.')],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after:fecha_inicio'],
            'anio_lectivo' => ['required', 'integer', 'digits:4', 'min:2000', 'max:2100'],
            'estado' => ['nullable', 'in:'.implode(',', Periodo::ESTADOS)],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Este campo es obligatorio.',
            'fecha_fin.after' => 'La fecha de fin debe ser posterior a la de inicio.',
        ];
    }
}
