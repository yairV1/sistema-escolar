<?php

namespace Database\Factories;

use App\Modules\Matriculas\Models\Matricula;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Datos ficticios para pruebas. Las relaciones (institución, usuario, curso…)
 * las fija explícitamente cada prueba o Tests\Support\EscenarioDosColegios.
 *
 * @extends Factory<Matricula>
 */
class MatriculaFactory extends Factory
{
    protected $model = Matricula::class;

    public function definition(): array
    {
        return [
            'anio_lectivo' => 2026,
            'fecha_matricula' => '2026-01-15',
            'estado_matricula' => 'activa',
        ];
    }
}
