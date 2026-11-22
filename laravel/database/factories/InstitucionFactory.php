<?php

namespace Database\Factories;

use App\Modules\Instituciones\Models\Institucion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Datos ficticios para pruebas. Las relaciones (institución, usuario, curso…)
 * las fija explícitamente cada prueba o Tests\Support\EscenarioDosColegios.
 *
 * @extends Factory<Institucion>
 */
class InstitucionFactory extends Factory
{
    protected $model = Institucion::class;

    public function definition(): array
    {
        return [
            'nombre' => 'Colegio '.$this->faker->unique()->lastName(),
            'slug' => $this->faker->unique()->slug(2),
            'plan' => 'basico',
            'estado' => 'activa',
            'fecha_inicio' => '2026-01-01',
        ];
    }
}
