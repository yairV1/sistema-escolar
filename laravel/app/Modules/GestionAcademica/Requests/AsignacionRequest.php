<?php

namespace App\Modules\GestionAcademica\Requests;

use App\Modules\GestionAcademica\Models\Materia;
use Illuminate\Foundation\Http\FormRequest;
use App\Modules\GestionAcademica\Models\Curso;
use App\Modules\Instituciones\Support\TenantGuard;
use App\Modules\Usuarios\Models\Profesor;

class AsignacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_profesor' => ['required', 'integer', TenantGuard::existe(Profesor::class)],
            'id_materia' => ['required', 'integer', TenantGuard::existe(Materia::class)],
            'id_curso' => ['required', 'integer', TenantGuard::existe(Curso::class)],
            'anio_lectivo' => ['required', 'integer', 'digits:4', 'min:2000', 'max:2100'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Este campo es obligatorio.',
            'id_profesor.exists' => 'El profesor seleccionado no es válido.',
            'id_materia.exists' => 'La materia seleccionada no es válida.',
            'id_curso.exists' => 'El curso seleccionado no es válido.',
        ];
    }
}
