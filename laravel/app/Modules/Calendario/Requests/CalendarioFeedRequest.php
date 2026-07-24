<?php

namespace App\Modules\Calendario\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CalendarioFeedRequest extends FormRequest
{
    /** Rango máximo permitido en días — nunca se carga el calendario completo de una vez. */
    public const RANGO_MAXIMO_DIAS = 120;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'desde' => ['required', 'date_format:Y-m-d'],
            'hasta' => ['required', 'date_format:Y-m-d', 'after_or_equal:desde'],
            'curso' => ['nullable', 'integer', 'exists:cursos,id_curso'],
            'categorias' => ['nullable', 'array'],
            'categorias.*' => ['integer', 'exists:evento_categorias,id_categoria'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Este campo es obligatorio.',
            'desde.date_format' => 'La fecha "desde" no es válida.',
            'hasta.date_format' => 'La fecha "hasta" no es válida.',
            'hasta.after_or_equal' => 'La fecha "hasta" debe ser posterior o igual a "desde".',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->filled('desde') || ! $this->filled('hasta')) {
                return;
            }

            $dias = CarbonImmutable::parse($this->input('desde'))->diffInDays(CarbonImmutable::parse($this->input('hasta')));
            if ($dias > self::RANGO_MAXIMO_DIAS) {
                $validator->errors()->add('hasta', 'El rango de fechas no puede superar los '.self::RANGO_MAXIMO_DIAS.' días.');
            }
        });
    }

    public function filtros(): array
    {
        return [
            'curso' => $this->input('curso'),
            'categorias' => $this->input('categorias'),
        ];
    }
}
