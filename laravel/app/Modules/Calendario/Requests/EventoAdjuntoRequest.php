<?php

namespace App\Modules\Calendario\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventoAdjuntoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('evento'));
    }

    public function rules(): array
    {
        return [
            'adjunto' => [
                'required',
                'file',
                'max:'.config('calendario.max_adjunto_kb'),
                'mimes:'.implode(',', config('calendario.mimes_permitidos')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Debes seleccionar un archivo.',
            'adjunto.max' => 'El archivo no puede superar los '.round(config('calendario.max_adjunto_kb') / 1024).' MB.',
            'adjunto.mimes' => 'Formato no permitido. Se aceptan: '.implode(', ', config('calendario.mimes_permitidos')).'.',
        ];
    }
}
