<?php

namespace App\Http\Requests\Landing;

use Illuminate\Foundation\Http\FormRequest;

class LandingGaleriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'descripcion' => ['nullable', 'string', 'max:150'],
            'orden' => ['nullable', 'integer', 'min:0'],
            'imagen' => [$this->isMethod('post') && $this->routeIs('*.store') ? 'required' : 'nullable', 'image', 'max:2048'],
        ];
    }
}
