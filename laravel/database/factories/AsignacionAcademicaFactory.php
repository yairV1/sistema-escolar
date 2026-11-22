<?php

namespace Database\Factories;

use App\Modules\GestionAcademica\Models\AsignacionAcademica;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Datos ficticios para pruebas. Las relaciones (institución, usuario, curso…)
 * las fija explícitamente cada prueba o Tests\Support\EscenarioDosColegios.
 *
 * @extends Factory<AsignacionAcademica>
 */
class AsignacionAcademicaFactory extends Factory
{
    protected $model = AsignacionAcademica::class;

    public function definition(): array
    {
        return [
            'anio_lectivo' => 2026,
            'estado' => 'activo',
        ];
    }
}
