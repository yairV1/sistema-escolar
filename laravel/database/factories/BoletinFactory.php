<?php

namespace Database\Factories;

use App\Modules\Reportes\Models\Boletin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Datos ficticios para pruebas. Las relaciones (institución, usuario, curso…)
 * las fija explícitamente cada prueba o Tests\Support\EscenarioDosColegios.
 *
 * @extends Factory<Boletin>
 */
class BoletinFactory extends Factory
{
    protected $model = Boletin::class;

    public function definition(): array
    {
        return [
            'promedio_general' => 4.0,
            'estado' => 'borrador',
        ];
    }
}
