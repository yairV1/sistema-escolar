<?php

namespace Database\Factories;

use App\Modules\GestionAcademica\Models\Materia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Datos ficticios para pruebas. Las relaciones (institución, usuario, curso…)
 * las fija explícitamente cada prueba o Tests\Support\EscenarioDosColegios.
 *
 * @extends Factory<Materia>
 */
class MateriaFactory extends Factory
{
    protected $model = Materia::class;

    public function definition(): array
    {
        return [
            'nombre_materia' => 'Materia '.$this->faker->unique()->numerify('####'),
            'intensidad_horaria' => 4,
            'estado' => 'activo',
        ];
    }
}
