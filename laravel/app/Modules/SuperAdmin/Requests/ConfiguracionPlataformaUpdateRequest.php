<?php

namespace App\Modules\SuperAdmin\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfiguracionPlataformaUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'limite_usuarios_default' => ['nullable', 'integer', 'min:1'],
            'limite_instituciones' => ['nullable', 'integer', 'min:1'],
            'plantilla_comunicado_default' => ['nullable', 'string', 'max:2000'],
            'soporte_email_contacto' => ['nullable', 'email', 'max:150'],
        ];
    }
}
