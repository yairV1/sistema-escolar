<?php

namespace Database\Factories;

use App\Modules\Soporte\Models\Soporte;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Datos ficticios para pruebas. Las relaciones (institución, usuario, curso…)
 * las fija explícitamente cada prueba o Tests\Support\EscenarioDosColegios.
 *
 * @extends Factory<Soporte>
 */
class SoporteFactory extends Factory
{
    protected $model = Soporte::class;

    public function definition(): array
    {
        return [
            'asunto' => 'Soporte ficticio',
            'mensaje' => 'Mensaje ficticio',
            'estado' => 'nuevo',
        ];
    }
}
