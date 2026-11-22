<?php

namespace Database\Factories;

use App\Modules\GestionAcademica\Models\Curso;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Datos ficticios para pruebas. Las relaciones (institución, usuario, curso…)
 * las fija explícitamente cada prueba o Tests\Support\EscenarioDosColegios.
 *
 * @extends Factory<Curso>
 */
class CursoFactory extends Factory
{
    protected $model = Curso::class;

    public function definition(): array
    {
        return [
            'nombre_curso' => $this->faker->unique()->bothify('#?#'),
            'nivel_academico' => 'secundaria',
            'jornada' => 'manana',
            'anio_lectivo' => 2026,
            'estado' => 'activo',
        ];
    }
}
