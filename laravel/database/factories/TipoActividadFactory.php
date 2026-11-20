<?php

namespace Database\Factories;

use App\Modules\Calificaciones\Models\TipoActividad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Datos ficticios para pruebas. Las relaciones (institución, usuario, curso…)
 * las fija explícitamente cada prueba o Tests\Support\EscenarioDosColegios.
 *
 * @extends Factory<TipoActividad>
 */
class TipoActividadFactory extends Factory
{
    protected $model = TipoActividad::class;

    public function definition(): array
    {
        return [
            'nombre_tipo' => 'Tipo '.$this->faker->unique()->numerify('###'),
            'estado' => 'activo',
        ];
    }
}
