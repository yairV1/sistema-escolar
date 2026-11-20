<?php

namespace Database\Factories;

use App\Modules\Observaciones\Models\Observacion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Datos ficticios para pruebas. Las relaciones (institución, usuario, curso…)
 * las fija explícitamente cada prueba o Tests\Support\EscenarioDosColegios.
 *
 * @extends Factory<Observacion>
 */
class ObservacionFactory extends Factory
{
    protected $model = Observacion::class;

    public function definition(): array
    {
        return [
            'tipo_observacion' => 'positiva',
            'descripcion' => 'Observación ficticia',
            'fecha' => '2026-11-16',
            'nivel_gravedad' => 'baja',
            'estado' => 'activa',
        ];
    }
}
