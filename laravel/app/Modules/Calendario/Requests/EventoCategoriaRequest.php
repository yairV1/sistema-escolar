<?php

namespace App\Modules\Calendario\Requests;

use App\Modules\Auth\Models\Usuario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventoCategoriaRequest extends FormRequest
{
    /**
     * Slots de la paleta categórica ya existente (resources/css/_variables.scss)
     * — un <select>, no un <input type="color">, para que toda categoría nueva
     * siga siendo tema-adaptativa igual que las semilla.
     */
    public const COLORES_VALIDOS = [
        'var(--cat-1)', 'var(--cat-2)', 'var(--cat-3)', 'var(--cat-4)', 'var(--cat-5)',
        'var(--cat-6)', 'var(--cat-7)', 'var(--cat-8)', 'var(--cat-9)', 'var(--bs-secondary)',
    ];

    public function authorize(): bool
    {
        return true; // ya protegido por role:admin,rector en la ruta, igual que RolRequest.
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:60'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'color' => ['required', Rule::in(self::COLORES_VALIDOS)],
            'icono' => ['required', 'string', 'max:60'],
            'orden' => ['nullable', 'integer', 'min:0'],
            'roles_crear' => ['nullable', 'array'],
            'roles_crear.*' => [Rule::in(Usuario::allRoleSlugs())],
            'roles_editar' => ['nullable', 'array'],
            'roles_editar.*' => [Rule::in(Usuario::allRoleSlugs())],
            'roles_eliminar' => ['nullable', 'array'],
            'roles_eliminar.*' => [Rule::in(Usuario::allRoleSlugs())],
            'roles_ver' => ['nullable', 'array'],
            'roles_ver.*' => [Rule::in(Usuario::allRoleSlugs())],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Este campo es obligatorio.',
            'color.in' => 'Selecciona un color válido de la paleta.',
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $datos = parent::validated($key, $default);

        foreach (['roles_crear', 'roles_editar', 'roles_eliminar'] as $campo) {
            $datos[$campo] = $datos[$campo] ?? [];
        }
        $datos['roles_ver'] = empty($datos['roles_ver']) ? null : $datos['roles_ver'];

        return $datos;
    }
}
