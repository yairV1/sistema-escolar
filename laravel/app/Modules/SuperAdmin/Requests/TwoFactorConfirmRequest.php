<?php

namespace App\Modules\SuperAdmin\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TwoFactorConfirmRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'regex:/^\d{6}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.regex' => 'Ingresa el código de 6 dígitos de tu app autenticadora.',
        ];
    }
}
