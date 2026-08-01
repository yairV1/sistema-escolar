<?php

namespace Database\Seeders;

use App\Modules\Modulos\Models\Modulo;
use Illuminate\Database\Seeder;

/**
 * Catálogo inicial de módulos funcionales (docs/arquitectura/10-superadmin-plataforma.md,
 * sección "Catálogo de planes y módulos"). Idempotente por slug —
 * updateOrCreate no duplica filas si el seeder corre más de una vez.
 */
class ModulosSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->catalogo() as $orden => $modulo) {
            Modulo::updateOrCreate(
                ['slug' => $modulo['slug']],
                [...$modulo, 'orden' => $orden],
            );
        }
    }

    /** @return array<int, array<string, string>> */
    private function catalogo(): array
    {
        return [
            ['nombre' => 'Matrículas', 'slug' => 'matriculas', 'categoria' => 'administrativo', 'icono' => 'fas fa-user-graduate', 'descripcion' => 'Registro y gestión del proceso de admisión y matrícula anual.'],
            ['nombre' => 'Boletines', 'slug' => 'boletines', 'categoria' => 'academico', 'icono' => 'fas fa-file-lines', 'descripcion' => 'Generación y publicación de calificaciones por periodo.'],
            ['nombre' => 'Horarios', 'slug' => 'horarios', 'categoria' => 'academico', 'icono' => 'fas fa-clock', 'descripcion' => 'Construcción de horarios de clase por grado y docente.'],
            ['nombre' => 'Comunicados', 'slug' => 'comunicados', 'categoria' => 'comunicacion', 'icono' => 'fas fa-bullhorn', 'descripcion' => 'Circulares y avisos masivos a acudientes por curso o sede.'],
            ['nombre' => 'Pagos en línea', 'slug' => 'pagos-en-linea', 'categoria' => 'finanzas', 'icono' => 'fas fa-credit-card', 'descripcion' => 'Cobro de pensiones e impuestos con pasarela integrada.'],
            ['nombre' => 'Biblioteca', 'slug' => 'biblioteca', 'categoria' => 'academico', 'icono' => 'fas fa-book', 'descripcion' => 'Catálogo, préstamos y devoluciones de material bibliográfico.'],
            ['nombre' => 'Portal de padres', 'slug' => 'portal-de-padres', 'categoria' => 'comunicacion', 'icono' => 'fas fa-users', 'descripcion' => 'Acceso de acudientes a notas, asistencia y pagos.'],
            ['nombre' => 'Transporte escolar', 'slug' => 'transporte-escolar', 'categoria' => 'administrativo', 'icono' => 'fas fa-bus', 'descripcion' => 'Rutas, asignación de estudiantes y seguimiento del transporte.'],
            ['nombre' => 'Encuestas', 'slug' => 'encuestas', 'categoria' => 'comunicacion', 'icono' => 'fas fa-square-poll-vertical', 'descripcion' => 'Encuestas de satisfacción y consulta a la comunidad educativa.'],
            ['nombre' => 'Recursos humanos', 'slug' => 'recursos-humanos', 'categoria' => 'administrativo', 'icono' => 'fas fa-id-badge', 'descripcion' => 'Hojas de vida, contratos y novedades del personal.'],
        ];
    }
}
