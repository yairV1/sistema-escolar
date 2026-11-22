<?php

namespace Database\Factories;

use App\Modules\Auth\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * Datos ficticios para pruebas. Las relaciones (institución, usuario, curso…)
 * las fija explícitamente cada prueba o Tests\Support\EscenarioDosColegios.
 *
 * @extends Factory<Usuario>
 */
class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    public function definition(): array
    {
        return [
            'nombres' => $this->faker->firstName(),
            'apellidos' => $this->faker->lastName(),
            'tipo_documento' => 'CC',
            'numero_documento' => 'F-'.$this->faker->unique()->numerify('########'),
            'correo' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('Prueba123!'),
            'id_rol' => 2,
            'estado_usuario' => 'activo',
        ];
    }
}
