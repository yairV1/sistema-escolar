<?php

namespace App\Modules\Calendario\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventoMoverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('evento'));
    }

    public function rules(): array
    {
        return [
            'fecha_ocurrencia' => ['nullable', 'date_format:Y-m-d'],
            'fecha_inicio' => ['required', 'date_format:Y-m-d'],
            'hora_inicio' => ['nullable', 'date_format:H:i'],
            'fecha_fin' => ['required', 'date_format:Y-m-d', 'after_or_equal:fecha_inicio'],
            'hora_fin' => ['nullable', 'date_format:H:i', 'after:hora_inicio'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Este campo es obligatorio.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la de inicio.',
            'hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
        ];
    }

    public function nuevaPosicion(): array
    {
        return [
            'fecha_inicio' => $this->input('fecha_inicio'),
            'hora_inicio' => $this->input('hora_inicio'),
            'fecha_fin' => $this->input('fecha_fin'),
            'hora_fin' => $this->input('hora_fin'),
        ];
    }
}
