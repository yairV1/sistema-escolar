<?php

namespace Database\Factories;

use App\Modules\GestionAcademica\Models\Horario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Datos ficticios para pruebas. Las relaciones (institución, usuario, curso…)
 * las fija explícitamente cada prueba o Tests\Support\EscenarioDosColegios.
 *
 * @extends Factory<Horario>
 */
class HorarioFactory extends Factory
{
    protected $model = Horario::class;

    public function definition(): array
    {
        return [
            'dia_semana' => 'lunes',
            'hora_inicio' => '07:00',
            'hora_fin' => '08:00',
            'salon' => '101',
            'estado' => 'activo',
        ];
    }
}
