<?php

namespace Database\Factories;

use App\Modules\Calificaciones\Models\Nota;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Datos ficticios para pruebas. Las relaciones (institución, usuario, curso…)
 * las fija explícitamente cada prueba o Tests\Support\EscenarioDosColegios.
 *
 * @extends Factory<Nota>
 */
class NotaFactory extends Factory
{
    protected $model = Nota::class;

    public function definition(): array
    {
        return [
            'nota' => 4.0,
            'estado' => 'registrada',
        ];
    }
}
