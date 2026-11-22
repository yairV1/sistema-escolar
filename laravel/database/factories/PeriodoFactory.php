<?php

namespace Database\Factories;

use App\Modules\Calificaciones\Models\Periodo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Datos ficticios para pruebas. Las relaciones (institución, usuario, curso…)
 * las fija explícitamente cada prueba o Tests\Support\EscenarioDosColegios.
 *
 * @extends Factory<Periodo>
 */
class PeriodoFactory extends Factory
{
    protected $model = Periodo::class;

    public function definition(): array
    {
        return [
            'nombre_periodo' => 'Periodo '.$this->faker->unique()->numerify('###'),
            'fecha_inicio' => '2026-11-01',
            'fecha_fin' => '2026-12-15',
            'anio_lectivo' => 2026,
            'estado' => 'activo',
        ];
    }
}
