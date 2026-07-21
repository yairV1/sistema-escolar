<?php

namespace Database\Seeders;

use App\Modules\Auth\Models\Permission;
use App\Modules\Auth\Models\Rol;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Puebla `permission_role` segun la matriz Rol -> Permisos aprobada en
 * docs/arquitectura/03-rbac.md, seccion 4. Esa matriz es la unica fuente
 * de verdad: este seeder no agrega, agrupa, simplifica ni elimina ningun
 * permiso respecto a lo alli definido.
 *
 * Resuelve permisos por `slug` (columna unica de `permissions`).
 *
 * Resuelve roles por `roles.nombre_rol` (columna unica, uq_nombre_rol) en
 * lugar de id_rol escrito a mano. Importante: `nombre_rol` en la base de
 * datos real es el nombre legacy (Administrador, Directivo, Coordinador,
 * Secretario, Profesor, Estudiante, Acudiente) y NO coincide con las
 * etiquetas de negocio usadas en la seccion 4 del documento (Administrador
 * tecnico, Rector, Secretaria, Docente...) ni con lo que `RolesSeeder`
 * intenta sembrar (ese seeder usa `insertOrIgnore` y nunca sobreescribe
 * las 7 filas legacy ya existentes). Por eso cada entrada de la matriz
 * lleva su `nombre_rol` real de BD y su etiqueta de negocio del documento
 * por separado.
 *
 * Idempotente: `insertOrIgnore` sobre la clave compuesta (id_rol,
 * id_permiso) no duplica relaciones si se ejecuta mas de una vez, y no
 * elimina ninguna fila existente.
 */
class PermissionRoleSeeder extends Seeder
{
    public function run(): void
    {
        $matriz = $this->matriz();

        $totalPermisos = Permission::count();
        $slugsCatalogo = Permission::pluck('id_permiso', 'slug');

        $this->validarSlugsExisten($matriz, $slugsCatalogo);

        $roles = Rol::pluck('id_rol', 'nombre_rol');
        $this->validarRolesExisten($matriz, $roles);

        $this->reportarCobertura($matriz, $slugsCatalogo, $totalPermisos);

        $filas = [];
        foreach ($matriz as $nombreRol => $datos) {
            $idRol = $roles[$nombreRol];
            foreach ($datos['permisos'] as $slug) {
                $filas[] = [
                    'id_rol' => $idRol,
                    'id_permiso' => $slugsCatalogo[$slug],
                ];
            }
        }

        DB::table('permission_role')->insertOrIgnore($filas);

        $this->validarResultado($matriz, $roles);
    }

    /**
     * Matriz Rol -> Permisos, sin agrupar ni resumir: cada slug es
     * exactamente uno de la seccion 3, tal como los enumera la seccion 4
     * de docs/arquitectura/03-rbac.md por rol.
     *
     * Clave = `roles.nombre_rol` real en base de datos (legacy).
     * `etiqueta` = nombre de negocio usado en el titulo de cada subseccion
     * de la seccion 4 (solo para los informes, no para resolver el rol).
     *
     * @return array<string, array{etiqueta: string, permisos: array<int, string>}>
     */
    private function matriz(): array
    {
        return [
            // 4.1 — Administrador tecnico: cuentas.* / usuarios.* / landing.* (todos)
            // + reportes.estadisticas.ver + reportes.exportar + dashboard.ver
            'Administrador' => [
                'etiqueta' => 'Administrador técnico',
                'permisos' => [
                    'cuentas.ver',
                    'cuentas.crear',
                    'cuentas.editar',
                    'cuentas.activar',
                    'cuentas.desactivar',
                    'cuentas.asignar_rol',
                    'cuentas.resetear_password',
                    'usuarios.listados.ver',
                    'usuarios.estudiantes.ver',
                    'usuarios.estudiantes.crear',
                    'usuarios.estudiantes.editar',
                    'usuarios.estudiantes.activar',
                    'usuarios.estudiantes.desactivar',
                    'usuarios.estudiantes.exportar',
                    'usuarios.docentes.ver',
                    'usuarios.docentes.crear',
                    'usuarios.docentes.editar',
                    'usuarios.docentes.activar',
                    'usuarios.docentes.desactivar',
                    'usuarios.administrativos.ver',
                    'usuarios.administrativos.crear',
                    'usuarios.administrativos.editar',
                    'usuarios.administrativos.activar',
                    'usuarios.administrativos.desactivar',
                    'usuarios.acudientes.ver',
                    'usuarios.acudientes.vincular',
                    'usuarios.acudientes.editar',
                    'landing.contenido.editar',
                    'landing.noticias.crear',
                    'landing.noticias.editar',
                    'landing.noticias.activar',
                    'landing.noticias.desactivar',
                    'landing.galeria.crear',
                    'landing.galeria.editar',
                    'landing.galeria.activar',
                    'landing.galeria.desactivar',
                    'reportes.estadisticas.ver',
                    'reportes.exportar',
                    'dashboard.ver',
                ],
            ],

            // 4.2 — Rector
            'Directivo' => [
                'etiqueta' => 'Rector',
                'permisos' => [
                    'reportes.institucionales.ver',
                    'reportes.estadisticas.ver',
                    'reportes.exportar',
                    'matriculas.ver',
                    'matriculas.aprobar',
                    'calificaciones.periodos.ver',
                    'calificaciones.periodos.cerrar_periodo',
                    'calificaciones.notas.ver',
                    'boletines.ver',
                    'boletines.publicar',
                    'asistencia.ver_historial',
                    'observaciones.ver',
                    'comunicados.ver',
                    'comunicados.crear',
                    'comunicados.publicar',
                    'comunicados.ver_recibidos',
                    'gestion_academica.materias.ver',
                    'gestion_academica.cursos.ver',
                    'gestion_academica.asignaciones.ver',
                    'gestion_academica.horarios.ver',
                    'dashboard.ver',
                    'landing.contenido.editar',
                ],
            ],

            // 4.3 — Coordinador: gestion_academica.* (todos, confirmado con el
            // usuario) + permisos de solo consulta/seguimiento del resto de dominios.
            'Coordinador' => [
                'etiqueta' => 'Coordinador',
                'permisos' => [
                    'gestion_academica.materias.ver',
                    'gestion_academica.materias.crear',
                    'gestion_academica.materias.editar',
                    'gestion_academica.materias.activar',
                    'gestion_academica.materias.desactivar',
                    'gestion_academica.cursos.ver',
                    'gestion_academica.cursos.crear',
                    'gestion_academica.cursos.editar',
                    'gestion_academica.cursos.activar',
                    'gestion_academica.cursos.desactivar',
                    'gestion_academica.asignaciones.ver',
                    'gestion_academica.asignaciones.crear',
                    'gestion_academica.asignaciones.activar',
                    'gestion_academica.asignaciones.desactivar',
                    'gestion_academica.horarios.ver',
                    'gestion_academica.horarios.crear',
                    'gestion_academica.horarios.editar',
                    'gestion_academica.horarios.activar',
                    'gestion_academica.horarios.desactivar',
                    'calificaciones.periodos.ver',
                    'calificaciones.actividades.ver',
                    'calificaciones.notas.ver',
                    'asistencia.ver',
                    'asistencia.ver_historial',
                    'observaciones.ver',
                    'observaciones.crear',
                    'observaciones.editar',
                    'reportes.institucionales.ver',
                    'reportes.exportar',
                    'comunicados.ver',
                    'comunicados.crear',
                    'dashboard.ver',
                ],
            ],

            // 4.4 — Secretaria: usuarios.estudiantes.* / usuarios.acudientes.* (todos)
            'Secretario' => [
                'etiqueta' => 'Secretaría',
                'permisos' => [
                    'usuarios.estudiantes.ver',
                    'usuarios.estudiantes.crear',
                    'usuarios.estudiantes.editar',
                    'usuarios.estudiantes.activar',
                    'usuarios.estudiantes.desactivar',
                    'usuarios.estudiantes.exportar',
                    'usuarios.acudientes.ver',
                    'usuarios.acudientes.vincular',
                    'usuarios.acudientes.editar',
                    'usuarios.listados.ver',
                    'matriculas.ver',
                    'matriculas.crear',
                    'matriculas.editar',
                    'matriculas.cambiar_estado',
                    'matriculas.exportar',
                    'reportes.estadisticas.ver',
                    'comunicados.ver',
                    'comunicados.crear',
                    'dashboard.ver',
                ],
            ],

            // 4.5 — Docente (perfil.* se excluye: seccion 3.13, no es permiso de rol)
            'Profesor' => [
                'etiqueta' => 'Docente',
                'permisos' => [
                    'gestion_academica.asignaciones.ver',
                    'calificaciones.actividades.ver',
                    'calificaciones.actividades.crear',
                    'calificaciones.actividades.editar',
                    'calificaciones.notas.ver',
                    'calificaciones.notas.registrar',
                    'calificaciones.notas.editar',
                    'asistencia.registrar',
                    'asistencia.ver',
                    'asistencia.editar',
                    'observaciones.crear',
                    'observaciones.ver',
                    'observaciones.editar',
                    'comunicados.ver_recibidos',
                    'comunicados.crear',
                    'dashboard.ver',
                ],
            ],

            // 4.6 — Estudiante (perfil.* excluido, seccion 3.13)
            'Estudiante' => [
                'etiqueta' => 'Estudiante',
                'permisos' => [
                    'calificaciones.notas.ver',
                    'boletines.ver',
                    'asistencia.ver',
                    'observaciones.ver',
                    'comunicados.ver_recibidos',
                ],
            ],

            // 4.7 — Acudiente (perfil.* excluido, seccion 3.13)
            'Acudiente' => [
                'etiqueta' => 'Acudiente',
                'permisos' => [
                    'calificaciones.notas.ver',
                    'boletines.ver',
                    'asistencia.ver',
                    'observaciones.ver',
                    'matriculas.ver',
                    'comunicados.ver_recibidos',
                ],
            ],
        ];
    }

    /**
     * @param  array<string, array{etiqueta: string, permisos: array<int, string>}>  $matriz
     */
    private function validarSlugsExisten(array $matriz, Collection $slugsCatalogo): void
    {
        $faltantes = [];

        foreach ($matriz as $nombreRol => $datos) {
            foreach ($datos['permisos'] as $slug) {
                if (! $slugsCatalogo->has($slug)) {
                    $faltantes[] = "{$datos['etiqueta']} ({$nombreRol}): {$slug}";
                }
            }
        }

        if ($faltantes !== []) {
            throw new RuntimeException(
                "PermissionRoleSeeder: la matriz de 03-rbac.md seccion 4 referencia permisos "
                ."que no existen en el catalogo sembrado (permissions.slug):\n"
                .implode("\n", $faltantes)
            );
        }
    }

    /**
     * @param  array<string, array{etiqueta: string, permisos: array<int, string>}>  $matriz
     */
    private function validarRolesExisten(array $matriz, Collection $roles): void
    {
        $faltantes = array_values(array_diff(array_keys($matriz), $roles->keys()->all()));

        if ($faltantes !== []) {
            throw new RuntimeException(
                'PermissionRoleSeeder: no se encontraron en `roles.nombre_rol` los siguientes '
                .'roles de la matriz: '.implode(', ', $faltantes)
            );
        }
    }

    /**
     * Informe de cobertura exigido antes de insertar ninguna relacion.
     *
     * @param  array<string, array{etiqueta: string, permisos: array<int, string>}>  $matriz
     */
    private function reportarCobertura(array $matriz, Collection $slugsCatalogo, int $totalPermisos): void
    {
        $asignadosPorSlug = [];
        foreach ($matriz as $datos) {
            foreach ($datos['permisos'] as $slug) {
                $asignadosPorSlug[$slug][] = $datos['etiqueta'];
            }
        }

        $sinAsignar = array_values(array_diff($slugsCatalogo->keys()->all(), array_keys($asignadosPorSlug)));
        $multiRol = array_filter($asignadosPorSlug, fn (array $etiquetas) => count($etiquetas) > 1);
        $exclusivosRector = array_keys(array_filter(
            $asignadosPorSlug,
            fn (array $etiquetas) => $etiquetas === ['Rector']
        ));
        $exclusivosAdminTecnico = array_keys(array_filter(
            $asignadosPorSlug,
            fn (array $etiquetas) => $etiquetas === ['Administrador técnico']
        ));

        if (! $this->command) {
            return;
        }

        $this->command->info('=== Informe de cobertura permission_role (previo a insercion) ===');
        $this->command->info("Total de permisos existentes en el catalogo: {$totalPermisos}");

        $this->command->table(
            ['Rol (nombre_rol / etiqueta)', 'Permisos asignados'],
            collect($matriz)->map(
                fn (array $datos, string $nombreRol) => ["{$datos['etiqueta']} ({$nombreRol})", count($datos['permisos'])]
            )->values()->all()
        );

        $this->command->info(
            'Permisos sin asignar a ningun rol ('.count($sinAsignar).'): '
            .($sinAsignar === [] ? '(ninguno)' : implode(', ', $sinAsignar))
        );

        $this->command->info('Permisos asignados a mas de un rol ('.count($multiRol).'):');
        foreach ($multiRol as $slug => $etiquetas) {
            $this->command->line("  - {$slug}: ".implode(', ', $etiquetas));
        }

        $this->command->info(
            'Permisos exclusivos de Rector ('.count($exclusivosRector).'): '
            .($exclusivosRector === [] ? '(ninguno)' : implode(', ', $exclusivosRector))
        );

        $this->command->info(
            'Permisos exclusivos de Administrador técnico ('.count($exclusivosAdminTecnico).'): '
            .($exclusivosAdminTecnico === [] ? '(ninguno)' : implode(', ', $exclusivosAdminTecnico))
        );
    }

    /**
     * Validaciones automaticas tras la insercion.
     *
     * @param  array<string, array{etiqueta: string, permisos: array<int, string>}>  $matriz
     */
    private function validarResultado(array $matriz, Collection $roles): void
    {
        $errores = [];

        foreach ($matriz as $nombreRol => $datos) {
            $idRol = $roles[$nombreRol];
            $esperado = count($datos['permisos']);
            $actual = DB::table('permission_role')->where('id_rol', $idRol)->count();

            if ($actual !== $esperado) {
                $errores[] = "{$datos['etiqueta']} ({$nombreRol}): se esperaban {$esperado} permisos, se encontraron {$actual}";
            }
        }

        $slugsDuplicados = Permission::select('slug')
            ->groupBy('slug')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('slug');

        if ($slugsDuplicados->isNotEmpty()) {
            $errores[] = 'Slugs de permisos duplicados: '.$slugsDuplicados->implode(', ');
        }

        $relacionesDuplicadas = DB::table('permission_role')
            ->select('id_rol', 'id_permiso')
            ->groupBy('id_rol', 'id_permiso')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        if ($relacionesDuplicadas > 0) {
            $errores[] = "Relaciones duplicadas en permission_role: {$relacionesDuplicadas}";
        }

        $fkRolInvalida = DB::table('permission_role')
            ->leftJoin('roles', 'permission_role.id_rol', '=', 'roles.id_rol')
            ->whereNull('roles.id_rol')
            ->count();

        if ($fkRolInvalida > 0) {
            $errores[] = "Relaciones con id_rol sin correspondencia en roles: {$fkRolInvalida}";
        }

        $fkPermisoInvalida = DB::table('permission_role')
            ->leftJoin('permissions', 'permission_role.id_permiso', '=', 'permissions.id_permiso')
            ->whereNull('permissions.id_permiso')
            ->count();

        if ($fkPermisoInvalida > 0) {
            $errores[] = "Relaciones con id_permiso sin correspondencia en permissions: {$fkPermisoInvalida}";
        }

        if ($errores !== []) {
            throw new RuntimeException(
                "PermissionRoleSeeder: validacion automatica fallida:\n".implode("\n", $errores)
            );
        }

        if ($this->command) {
            $this->command->info(
                'Validacion automatica OK: cantidad de permisos por rol correcta, '
                .'sin permisos inexistentes, sin slugs duplicados, sin relaciones '
                .'duplicadas, sin FK invalidas.'
            );
        }
    }
}
