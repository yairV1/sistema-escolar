<?php

namespace Database\Factories;

use App\Modules\Asistencia\Models\Asistencia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Datos ficticios para pruebas. Las relaciones (institución, usuario, curso…)
 * las fija explícitamente cada prueba o Tests\Support\EscenarioDosColegios.
 *
 * @extends Factory<Asistencia>
 */
class AsistenciaFactory extends Factory
{
    protected $model = Asistencia::class;

    public function definition(): array
    {
        return [
            'fecha' => '2026-11-16',
            'estado_asistencia' => 'presente',
        ];
    }
}
