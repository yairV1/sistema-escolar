<?php

namespace Database\Factories;

use App\Modules\Usuarios\Models\Profesor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Datos ficticios para pruebas. Las relaciones (institución, usuario, curso…)
 * las fija explícitamente cada prueba o Tests\Support\EscenarioDosColegios.
 *
 * @extends Factory<Profesor>
 */
class ProfesorFactory extends Factory
{
    protected $model = Profesor::class;

    public function definition(): array
    {
        return [
            'codigo_profesor' => 'F-DOC-'.$this->faker->unique()->numerify('#####'),
            'estado_laboral' => 'activo',
        ];
    }
}
