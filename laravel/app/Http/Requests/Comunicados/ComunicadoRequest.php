<?php

namespace App\Http\Requests\Comunicados;

use App\Models\Notificacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ComunicadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:150'],
            'mensaje' => ['required', 'string', 'max:2000'],
            'tipo_notificacion' => ['required', Rule::in(Notificacion::TIPOS)],
            'canal' => ['required', Rule::in(Notificacion::CANALES)],
            'audiencias' => ['required', 'array', 'min:1'],
            'audiencias.*' => [Rule::in(['estudiantes', 'docentes', 'administrativos', 'acudientes'])],
        ];
    }

    public function messages(): array
    {
        return [
            'audiencias.required' => 'Selecciona al menos un destinatario.',
        ];
    }
}
