<?php

namespace App\Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TwoFactorChallengeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.required' => 'Ingresa el código de tu app autenticadora o un código de recuperación.',
        ];
    }
}
