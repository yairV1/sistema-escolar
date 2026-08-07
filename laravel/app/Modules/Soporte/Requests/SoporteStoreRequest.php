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
            // Tope alineado a upload_max_filesize de php.ini (2M en este entorno);
            // subirlo acá sin subir también el de php.ini no tendría efecto.
            'imagen' => ['nullable', 'image', 'max:2048', 'mimes:jpg,jpeg,png,webp'],
        ];
    }

    public function messages(): array
    {
        return [
            'imagen.image' => 'El archivo debe ser una imagen.',
            'imagen.max' => 'La imagen no puede superar los 2 MB.',
            'imagen.mimes' => 'Formato no permitido. Se aceptan: jpg, jpeg, png, webp.',
        ];
    }
}
