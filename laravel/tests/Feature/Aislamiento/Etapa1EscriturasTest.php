<?php

namespace Tests\Feature\Aislamiento;

use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Etapa 1 — escrituras cruzadas: el colegio B intenta modificar recursos de A.
 * Debe recibir 404 y la tabla afectada debe quedar idéntica (huella completa).
 */
class Etapa1EscriturasTest extends AislamientoTestCase
{
    public static function cruzadas(): array
    {
        return [
            'matrícula: cambiar estado' => ['/matriculas/{a.matricula}/estado', ['estado' => 'retirada'], 'matriculas'],
            'solicitud: cambiar estado' => ['/matriculas/solicitudes/{a.solicitud}/estado', ['estado' => 'descartada'], 'solicitudes_admision'],
            'observación: editar' => ['/observaciones/{a.observacion}', ['id_estudiante' => '{b.estudiante}', 'id_profesor' => '{b.profesor}', 'tipo_observacion' => 'academica', 'descripcion' => 'X', 'fecha' => '2026-11-16', 'nivel_gravedad' => 'alta'], 'observaciones'],
            'observación: desactivar' => ['/observaciones/{a.observacion}/desactivar', [], 'observaciones'],
            'curso: editar' => ['/gestion-academica/cursos/{a.curso}', ['nombre_curso' => 'HACK', 'nivel_academico' => 'secundaria', 'jornada' => 'manana', 'anio_lectivo' => 2026], 'cursos'],
            'curso: desactivar' => ['/gestion-academica/cursos/{a.curso}/desactivar', [], 'cursos'],
            'asignación: desactivar' => ['/gestion-academica/asignaciones/{a.asignacion}/desactivar', [], 'asignaciones_academicas'],
            'horario: editar' => ['/gestion-academica/horarios/{a.horario}', ['id_asignacion' => '{b.asignacion}', 'dia_semana' => 'viernes', 'hora_inicio' => '10:00', 'hora_fin' => '11:00'], 'horarios'],
            'horario: desactivar' => ['/gestion-academica/horarios/{a.horario}/desactivar', [], 'horarios'],
            'boletín: publicar' => ['/boletines/{a.boletin}/publicar', [], 'boletines'],
            'boletín: anular' => ['/boletines/{a.boletin}/anular', [], 'boletines'],
            'actividad: desactivar' => ['/calificaciones/actividades/{a.actividad}/desactivar', [], 'actividades'],
            'evento: desactivar' => ['/calendario/{a.evento}/desactivar', [], 'eventos'],
            'evento: comentar' => ['/calendario/{a.evento}/comentarios', ['comentario' => 'intrusión'], 'evento_comentarios'],
        ];
    }

    #[DataProvider('cruzadas')]
    public function test_escritura_cruzada_rechazada_sin_cambios(string $url, array $datos, string $tabla): void
    {
        $this->ejecutarCaso('b.directivo', 'post', $url, $datos, [404], $tabla, false);
    }

    public static function legitimas(): array
    {
        return [
            'observación: desactivar y reactivar' => ['/observaciones/{a.observacion}/desactivar', '/observaciones/{a.observacion}/activar', 'observaciones', 'id_observacion', 'observacion', 'estado'],
            'curso 7A: desactivar y reactivar' => ['/gestion-academica/cursos/{a.curso7a}/desactivar', '/gestion-academica/cursos/{a.curso7a}/activar', 'cursos', 'id_curso', 'curso7a', 'estado'],
            'boletín: publicar y volver a borrador' => ['/boletines/{a.boletin}/publicar', '/boletines/{a.boletin}/borrador', 'boletines', 'id_boletin', 'boletin', 'estado'],
        ];
    }

    #[DataProvider('legitimas')]
    public function test_escritura_legitima_y_reversion(string $ida, string $vuelta, string $tabla, string $pk, string $clave, string $columna): void
    {
        $this->actingAs($this->e->a['directivo']);
        $id = $this->e->a[$clave]->getKey();
        $original = DB::table($tabla)->where($pk, $id)->value($columna);

        $this->postJson($this->resolver($ida))->assertOk();
        $this->assertNotSame($original, DB::table($tabla)->where($pk, $id)->value($columna), 'la ida debe cambiar el registro');
        $this->postJson($this->resolver($vuelta))->assertOk();
        $this->assertSame($original, DB::table($tabla)->where($pk, $id)->value($columna), 'la vuelta debe restaurarlo');
    }
}
