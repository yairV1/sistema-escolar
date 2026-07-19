<?php

namespace App\Http\Requests\Landing;

use Illuminate\Foundation\Http\FormRequest;

class LandingNoticiaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:200'],
            'descripcion' => ['required', 'string', 'max:2000'],
            'etiqueta' => ['nullable', 'string', 'max:50'],
            'fecha' => ['nullable', 'date'],
            'orden' => ['nullable', 'integer', 'min:0'],
            'imagen' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
