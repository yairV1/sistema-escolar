<?php

namespace App\Modules\Colegio\Requests;

use App\Modules\Colegio\Models\ColegioImagen;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ColegioImagenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'imagen' => ['required', 'image', 'max:2048'],
            'tipo' => ['required', Rule::in(array_keys(ColegioImagen::TIPOS_LABELS))],
            'descripcion' => ['nullable', 'string', 'max:150'],
        ];
    }
}
