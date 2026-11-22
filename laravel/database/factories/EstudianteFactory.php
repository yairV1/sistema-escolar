<?php

namespace Database\Factories;

use App\Modules\Usuarios\Models\Estudiante;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Datos ficticios para pruebas. Las relaciones (institución, usuario, curso…)
 * las fija explícitamente cada prueba o Tests\Support\EscenarioDosColegios.
 *
 * @extends Factory<Estudiante>
 */
class EstudianteFactory extends Factory
{
    protected $model = Estudiante::class;

    public function definition(): array
    {
        return [
            'codigo_estudiante' => 'F-EST-'.$this->faker->unique()->numerify('#####'),
            'fecha_nacimiento' => '2012-01-01',
            'genero' => 'F',
            'direccion' => 'Calle ficticia',
            'estado_academico' => 'activo',
            'fecha_ingreso' => '2026-01-15',
        ];
    }
}
