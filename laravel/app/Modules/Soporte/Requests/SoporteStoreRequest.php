<?php

namespace App\Modules\Soporte\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SoporteStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'asunto' => ['required', 'string', 'max:150'],
            'mensaje' => ['required', 'string', 'max:2000'],
            'imagen' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
