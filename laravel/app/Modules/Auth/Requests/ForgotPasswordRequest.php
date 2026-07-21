<?php

namespace App\Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'correo' => ['required', 'email'],
        ];
    }

    public function messages(): array
    {
        return [
            'correo.required' => 'Ingresa tu correo institucional.',
            'correo.email' => 'Ingresa un correo válido.',
        ];
    }
}
