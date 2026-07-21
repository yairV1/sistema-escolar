<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Los 7 actores institucionales de docs/arquitectura/02-arquitectura-funcional.md
 * (seccion 5) y docs/arquitectura/03-rbac.md. La tabla `roles` ya existe con
 * datos reales del sistema legacy (id_rol 1 a 7 ya poblados), por lo que este
 * seeder es idempotente por id_rol: solo inserta lo que falte, nunca
 * actualiza una fila existente (no se modifican datos existentes).
 */
class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $ahora = now();

        $roles = [
            ['id_rol' => 1, 'nombre_rol' => 'Administrador Técnico', 'descripcion' => 'Operatividad tecnica del sistema y gestion de accesos', 'estado' => 'activo', 'fecha_creacion' => $ahora],
            ['id_rol' => 2, 'nombre_rol' => 'Rector', 'descripcion' => 'Maxima autoridad institucional', 'estado' => 'activo', 'fecha_creacion' => $ahora],
            ['id_rol' => 3, 'nombre_rol' => 'Coordinador', 'descripcion' => 'Continuidad y calidad del proceso academico diario', 'estado' => 'activo', 'fecha_creacion' => $ahora],
            ['id_rol' => 4, 'nombre_rol' => 'Secretaría', 'descripcion' => 'Operacion administrativa formal del colegio', 'estado' => 'activo', 'fecha_creacion' => $ahora],
            ['id_rol' => 5, 'nombre_rol' => 'Docente', 'descripcion' => 'Proceso de enseñanza, evaluacion y seguimiento del estudiante', 'estado' => 'activo', 'fecha_creacion' => $ahora],
            ['id_rol' => 6, 'nombre_rol' => 'Estudiante', 'descripcion' => 'Participante de su propio proceso formativo', 'estado' => 'activo', 'fecha_creacion' => $ahora],
            ['id_rol' => 7, 'nombre_rol' => 'Acudiente', 'descripcion' => 'Acompañamiento del proceso educativo desde la familia', 'estado' => 'activo', 'fecha_creacion' => $ahora],
        ];

        DB::table('roles')->insertOrIgnore($roles);
    }
}
