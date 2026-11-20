<?php

namespace Database\Factories;

use App\Modules\Matriculas\Models\SolicitudAdmision;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Datos ficticios para pruebas. Las relaciones (institución, usuario, curso…)
 * las fija explícitamente cada prueba o Tests\Support\EscenarioDosColegios.
 *
 * @extends Factory<SolicitudAdmision>
 */
class SolicitudAdmisionFactory extends Factory
{
    protected $model = SolicitudAdmision::class;

    public function definition(): array
    {
        return [
            'nombre_acudiente' => 'Solicitante',
            'apellido_acudiente' => 'Ficticio',
            'correo' => $this->faker->unique()->safeEmail(),
            'telefono' => '3000000000',
            'nombre_estudiante' => 'Niño ficticio',
            'grado_interes' => '6',
            'estado' => 'pendiente',
        ];
    }
}
