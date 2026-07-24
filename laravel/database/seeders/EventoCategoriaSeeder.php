<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Las 9 categorías semilla del Calendario Institucional, una por cada slot
 * de la paleta categórica --cat-1..9 (resources/css/_variables.scss), ya
 * usada hoy para colorear materias en la grilla de horarios. "Clases" y
 * "Actividades" son `es_sistema` porque las alimenta la derivación de
 * horarios/actividades, no el CRUD de eventos. Idempotente por slug.
 */
class EventoCategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $ahora = now();

        $todosLosRoles = ['admin', 'rector', 'coordinador', 'secretario', 'docente', 'estudiante', 'acudiente'];
        $rolesAdministrativos = ['admin', 'rector', 'coordinador', 'secretario'];

        $categorias = [
            [
                'nombre' => 'Clases',
                'slug' => 'clases',
                'descripcion' => 'Bloques de clase derivados del horario académico.',
                'color' => 'var(--cat-1)',
                'icono' => 'bi-mortarboard',
                'roles_crear' => [],
                'roles_editar' => [],
                'roles_eliminar' => [],
                'roles_ver' => null,
                'es_sistema' => true,
                'orden' => 1,
            ],
            [
                'nombre' => 'Comunicados',
                'slug' => 'comunicados',
                'descripcion' => 'Avisos y circulares institucionales.',
                'color' => 'var(--cat-2)',
                'icono' => 'bi-megaphone',
                'roles_crear' => $rolesAdministrativos,
                'roles_editar' => $rolesAdministrativos,
                'roles_eliminar' => $rolesAdministrativos,
                'roles_ver' => null,
                'es_sistema' => false,
                'orden' => 2,
            ],
            [
                'nombre' => 'Actividades',
                'slug' => 'actividades',
                'descripcion' => 'Fechas de entrega derivadas de las actividades evaluativas.',
                'color' => 'var(--cat-4)',
                'icono' => 'bi-journal-check',
                'roles_crear' => [],
                'roles_editar' => [],
                'roles_eliminar' => [],
                'roles_ver' => null,
                'es_sistema' => true,
                'orden' => 3,
            ],
            [
                'nombre' => 'Personal',
                'slug' => 'personal',
                'descripcion' => 'Eventos privados de cada usuario.',
                'color' => 'var(--cat-5)',
                'icono' => 'bi-person',
                'roles_crear' => $todosLosRoles,
                // Vacío a propósito: cualquiera puede CREAR un evento personal
                // (roles_crear), pero solo su dueño puede editarlo/eliminarlo
                // (regla "dueño" de EventoPolicy) — si esto fuera $todosLosRoles,
                // cualquier usuario podría editar el evento personal de otro.
                'roles_editar' => [],
                'roles_eliminar' => [],
                'roles_ver' => null,
                'es_sistema' => false,
                'orden' => 4,
            ],
            [
                'nombre' => 'Reuniones',
                'slug' => 'reuniones',
                'descripcion' => 'Reuniones de docentes, directivas o padres de familia.',
                'color' => 'var(--cat-6)',
                'icono' => 'bi-people',
                'roles_crear' => $rolesAdministrativos,
                'roles_editar' => $rolesAdministrativos,
                'roles_eliminar' => $rolesAdministrativos,
                'roles_ver' => null,
                'es_sistema' => false,
                'orden' => 5,
            ],
            [
                'nombre' => 'Eventos Institucionales',
                'slug' => 'eventos-institucionales',
                'descripcion' => 'Actos, izadas de bandera y eventos generales del colegio.',
                'color' => 'var(--cat-7)',
                'icono' => 'bi-flag',
                'roles_crear' => $rolesAdministrativos,
                'roles_editar' => $rolesAdministrativos,
                'roles_eliminar' => $rolesAdministrativos,
                'roles_ver' => null,
                'es_sistema' => false,
                'orden' => 6,
            ],
            [
                'nombre' => 'Evaluaciones',
                'slug' => 'evaluaciones',
                'descripcion' => 'Exámenes y evaluaciones programadas.',
                'color' => 'var(--cat-8)',
                'icono' => 'bi-clipboard-check',
                'roles_crear' => array_merge($rolesAdministrativos, ['docente']),
                'roles_editar' => array_merge($rolesAdministrativos, ['docente']),
                'roles_eliminar' => array_merge($rolesAdministrativos, ['docente']),
                'roles_ver' => null,
                'es_sistema' => false,
                'orden' => 7,
            ],
            [
                'nombre' => 'Recordatorios',
                'slug' => 'recordatorios',
                'descripcion' => 'Recordatorios puntuales.',
                'color' => 'var(--cat-9)',
                'icono' => 'bi-bell',
                'roles_crear' => $todosLosRoles,
                // Mismo razonamiento que "Personal": solo el dueño edita/elimina su propio recordatorio.
                'roles_editar' => [],
                'roles_eliminar' => [],
                'roles_ver' => null,
                'es_sistema' => false,
                'orden' => 8,
            ],
            [
                'nombre' => 'Festivos',
                'slug' => 'festivos',
                'descripcion' => 'Días festivos y no hábiles.',
                'color' => 'var(--bs-secondary)',
                'icono' => 'bi-calendar-x',
                'roles_crear' => $rolesAdministrativos,
                'roles_editar' => $rolesAdministrativos,
                'roles_eliminar' => $rolesAdministrativos,
                'roles_ver' => null,
                'es_sistema' => false,
                'orden' => 9,
            ],
        ];

        foreach ($categorias as $categoria) {
            $categoria['roles_crear'] = json_encode($categoria['roles_crear']);
            $categoria['roles_editar'] = json_encode($categoria['roles_editar']);
            $categoria['roles_eliminar'] = json_encode($categoria['roles_eliminar']);
            $categoria['roles_ver'] = $categoria['roles_ver'] === null ? null : json_encode($categoria['roles_ver']);
            $categoria['estado'] = 'activo';
            $categoria['created_at'] = $ahora;
            $categoria['updated_at'] = $ahora;

            DB::table('evento_categorias')->insertOrIgnore($categoria);
        }
    }
}
