<?php

namespace Database\Factories;

use App\Modules\Calendario\Models\Evento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Datos ficticios para pruebas. Las relaciones (institución, usuario, curso…)
 * las fija explícitamente cada prueba o Tests\Support\EscenarioDosColegios.
 *
 * @extends Factory<Evento>
 */
class EventoFactory extends Factory
{
    protected $model = Evento::class;

    public function definition(): array
    {
        return [
            'titulo' => 'Evento ficticio',
            'fecha_inicio' => '2026-11-20',
            'fecha_fin' => '2026-11-20',
            'todo_el_dia' => true,
            'prioridad' => 'media',
            'estado' => 'pendiente',
            'visibilidad' => 'publico',
            'tipo_recurrencia' => 'ninguna',
            'estado_activo' => 'activo',
        ];
    }
}
