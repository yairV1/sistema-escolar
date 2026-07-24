<?php

namespace App\Modules\Calendario\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventoComentarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Comentar exige poder VER el evento, no editarlo — cualquiera que
        // lo tenga en su calendario puede participar de la conversación.
        return $this->user()->can('view', $this->route('evento'));
    }

    public function rules(): array
    {
        return [
            'comentario' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Escribe un comentario antes de enviar.',
            'comentario.max' => 'El comentario no puede superar los 1000 caracteres.',
        ];
    }
}
