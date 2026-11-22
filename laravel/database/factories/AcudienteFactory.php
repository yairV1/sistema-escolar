<?php

namespace Database\Factories;

use App\Modules\Usuarios\Models\Acudiente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Datos ficticios para pruebas. Las relaciones (institución, usuario, curso…)
 * las fija explícitamente cada prueba o Tests\Support\EscenarioDosColegios.
 *
 * @extends Factory<Acudiente>
 */
class AcudienteFactory extends Factory
{
    protected $model = Acudiente::class;

    public function definition(): array
    {
        return [
            'estado' => 'activo',
        ];
    }
}
