<?php

namespace Database\Factories;

use App\Modules\Calificaciones\Models\Actividad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Datos ficticios para pruebas. Las relaciones (institución, usuario, curso…)
 * las fija explícitamente cada prueba o Tests\Support\EscenarioDosColegios.
 *
 * @extends Factory<Actividad>
 */
class ActividadFactory extends Factory
{
    protected $model = Actividad::class;

    public function definition(): array
    {
        return [
            'titulo' => 'Actividad ficticia',
            'porcentaje' => 50,
            'fecha_entrega' => '2026-11-20',
            'estado' => 'activa',
        ];
    }
}
