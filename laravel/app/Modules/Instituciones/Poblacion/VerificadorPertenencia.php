<?php

namespace App\Modules\Instituciones\Poblacion;

use Illuminate\Database\Connection;

/**
 * Coherencia institucional basada en las columnas id_institucion de B2
 * (solo SELECT). Una violación = una fila cuya institución no coincide con la
 * de un registro relacionado, o un catálogo usado que quedó sin institución.
 *
 * Lo usa el aplicador DENTRO de su transacción: con cualquier violación, la
 * población se revierte completa.
 */
class VerificadorPertenencia
{
    /** [regla, tabla, sql que devuelve id de la fila infractora] */
    private const REGLAS = [
        ['asignacion_materia_de_otro_colegio', 'asignaciones_academicas', 'SELECT a.id_asignacion AS id FROM asignaciones_academicas a JOIN cursos c ON c.id_curso = a.id_curso JOIN materias m ON m.id_materia = a.id_materia WHERE m.id_institucion <> c.id_institucion'],
        ['asignacion_profesor_de_otro_colegio', 'asignaciones_academicas', 'SELECT a.id_asignacion AS id FROM asignaciones_academicas a JOIN cursos c ON c.id_curso = a.id_curso JOIN profesores p ON p.id_profesor = a.id_profesor WHERE p.id_institucion <> c.id_institucion'],
        ['profesor_materia_de_otro_colegio', 'profesor_materia', 'SELECT pm.id_profesor AS id FROM profesor_materia pm JOIN profesores p ON p.id_profesor = pm.id_profesor JOIN materias m ON m.id_materia = pm.id_materia WHERE m.id_institucion <> p.id_institucion'],
        ['actividad_periodo_de_otro_colegio', 'actividades', 'SELECT ac.id_actividad AS id FROM actividades ac JOIN asignaciones_academicas a ON a.id_asignacion = ac.id_asignacion JOIN cursos c ON c.id_curso = a.id_curso JOIN periodos_academicos p ON p.id_periodo = ac.id_periodo WHERE p.id_institucion <> c.id_institucion'],
        ['actividad_tipo_de_otro_colegio', 'actividades', 'SELECT ac.id_actividad AS id FROM actividades ac JOIN asignaciones_academicas a ON a.id_asignacion = ac.id_asignacion JOIN cursos c ON c.id_curso = a.id_curso JOIN tipos_actividad t ON t.id_tipo_actividad = ac.id_tipo_actividad WHERE t.id_institucion <> c.id_institucion'],
        ['boletin_periodo_de_otro_colegio', 'boletines', 'SELECT b.id_boletin AS id FROM boletines b JOIN estudiantes e ON e.id_estudiante = b.id_estudiante JOIN periodos_academicos p ON p.id_periodo = b.id_periodo WHERE p.id_institucion <> e.id_institucion'],
        ['matricula_de_otro_colegio', 'matriculas', 'SELECT m.id_matricula AS id FROM matriculas m JOIN estudiantes e ON e.id_estudiante = m.id_estudiante JOIN cursos c ON c.id_curso = m.id_curso WHERE e.id_institucion <> c.id_institucion'],
        ['evento_categoria_de_otro_colegio', 'eventos', 'SELECT ev.id_evento AS id FROM eventos ev JOIN evento_categorias c ON c.id_categoria = ev.id_categoria WHERE c.id_institucion IS NOT NULL AND c.id_institucion <> ev.id_institucion'],
        ['materia_area_de_otro_colegio', 'materias', 'SELECT m.id_materia AS id FROM materias m JOIN areas a ON a.id_area = m.id_area WHERE a.id_institucion <> m.id_institucion'],
        ['materia_padre_de_otro_colegio', 'materias', 'SELECT m.id_materia AS id FROM materias m JOIN materias p ON p.id_materia = m.id_materia_padre WHERE p.id_institucion <> m.id_institucion'],
        ['imagen_de_otra_configuracion', 'colegio_imagenes', 'SELECT i.id_imagen AS id FROM colegio_imagenes i JOIN colegio_configuracion c ON c.id_configuracion = i.id_configuracion WHERE c.id_institucion <> i.id_institucion'],
        ['estudiante_distinto_de_su_usuario', 'estudiantes', 'SELECT t.id_estudiante AS id FROM estudiantes t JOIN usuarios u ON u.id_usuario = t.id_usuario WHERE t.id_institucion <> u.id_institucion'],
        ['profesor_distinto_de_su_usuario', 'profesores', 'SELECT t.id_profesor AS id FROM profesores t JOIN usuarios u ON u.id_usuario = t.id_usuario WHERE t.id_institucion <> u.id_institucion'],
        ['acudiente_distinto_de_su_usuario', 'acudientes', 'SELECT t.id_acudiente AS id FROM acudientes t JOIN usuarios u ON u.id_usuario = t.id_usuario WHERE t.id_institucion <> u.id_institucion'],
        ['soporte_distinto_de_su_usuario', 'soportes', 'SELECT t.id_soporte AS id FROM soportes t JOIN usuarios u ON u.id_usuario = t.id_usuario WHERE t.id_institucion <> u.id_institucion'],
        // Catálogos usados que quedaron sin institución (no plantilla)
        ['materia_usada_sin_institucion', 'materias', 'SELECT DISTINCT m.id_materia AS id FROM materias m JOIN asignaciones_academicas a ON a.id_materia = m.id_materia WHERE m.id_institucion IS NULL'],
        ['periodo_usado_sin_institucion', 'periodos_academicos', 'SELECT DISTINCT p.id_periodo AS id FROM periodos_academicos p JOIN actividades ac ON ac.id_periodo = p.id_periodo WHERE p.id_institucion IS NULL'],
        ['tipo_usado_sin_institucion', 'tipos_actividad', 'SELECT DISTINCT t.id_tipo_actividad AS id FROM tipos_actividad t JOIN actividades ac ON ac.id_tipo_actividad = t.id_tipo_actividad WHERE t.id_institucion IS NULL'],
    ];

    public function __construct(private Connection $db) {}

    /** @return list<array{regla:string,tabla:string,id:mixed}> */
    public function violaciones(): array
    {
        $res = [];
        foreach (self::REGLAS as [$regla, $tabla, $sql]) {
            foreach ($this->db->select($sql.' ORDER BY 1') as $f) {
                $res[] = ['regla' => $regla, 'tabla' => $tabla, 'id' => $f->id];
            }
        }

        return $res;
    }
}
