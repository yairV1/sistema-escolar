<?php

namespace Tests\Feature\Aislamiento;

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Etapa 1 — identificadores ajenos en formularios y exportaciones.
 * Respuesta 422 con el error en el campo manipulado y ninguna fila nueva.
 */
class Etapa1FormulariosTest extends AislamientoTestCase
{
    private const EVENTO = ['titulo' => 'EvtPrueba', 'fecha_inicio' => '2026-11-22', 'fecha_fin' => '2026-11-22', 'todo_el_dia' => 1, 'prioridad' => 'media', 'tipo_recurrencia' => 'ninguna'];

    public static function casos(): array
    {
        return [
            'asignación con curso de A' => ['b.directivo', '/gestion-academica/asignaciones', ['id_profesor' => '{b.profesor}', 'id_materia' => '{a.materia}', 'id_curso' => '{a.curso}', 'anio_lectivo' => 2026], 'id_curso', 'asignaciones_academicas'],
            'asignación con profesor de A' => ['b.directivo', '/gestion-academica/asignaciones', ['id_profesor' => '{a.profesor}', 'id_materia' => '{a.materia}', 'id_curso' => '{b.curso}', 'anio_lectivo' => 2026], 'id_profesor', 'asignaciones_academicas'],
            'horario con asignación de A' => ['b.directivo', '/gestion-academica/horarios', ['id_asignacion' => '{a.asignacion}', 'dia_semana' => 'viernes', 'hora_inicio' => '10:00', 'hora_fin' => '11:00'], 'id_asignacion', 'horarios'],
            'curso con director de A' => ['b.directivo', '/gestion-academica/cursos', ['nombre_curso' => '9Y', 'nivel_academico' => 'secundaria', 'jornada' => 'tarde', 'anio_lectivo' => 2026, 'id_director_grupo' => '{a.profesor}'], 'id_director_grupo', 'cursos'],
            'observación sobre estudiante de A' => ['b.directivo', '/observaciones', ['id_estudiante' => '{a.estudiante}', 'id_profesor' => '{b.profesor}', 'tipo_observacion' => 'academica', 'descripcion' => 'x', 'fecha' => '2026-11-16', 'nivel_gravedad' => 'baja'], 'id_estudiante', 'observaciones'],
            'observación con profesor de A' => ['b.directivo', '/observaciones', ['id_estudiante' => '{b.estudiante}', 'id_profesor' => '{a.profesor}', 'tipo_observacion' => 'academica', 'descripcion' => 'x', 'fecha' => '2026-11-16', 'nivel_gravedad' => 'baja'], 'id_profesor', 'observaciones'],
            'anotación del docente B sobre estudiante de A' => ['b.docente_usuario', '/mis-anotaciones', ['id_estudiante' => '{a.estudiante}', 'tipo_observacion' => 'academica', 'descripcion' => 'x', 'fecha' => '2026-11-16', 'nivel_gravedad' => 'baja'], 'id_estudiante', 'observaciones'],
            'generar boletines del curso de A' => ['b.directivo', '/boletines/generar', ['id_curso' => '{a.curso}', 'id_periodo' => '{a.periodo}'], 'id_curso', 'boletines'],
            'evento público ligado al curso de A' => ['b.directivo', '/calendario', self::EVENTO + ['id_categoria' => 'CAT', 'visibilidad' => 'publico', 'id_curso' => '{a.curso}'], 'id_curso', 'eventos'],
            'evento compartido con usuario de A' => ['b.directivo', '/calendario', self::EVENTO + ['id_categoria' => 'CAT', 'visibilidad' => 'compartido', 'participantes' => ['{a.coordinador}']], 'participantes', 'eventos'],
        ];
    }

    #[DataProvider('casos')]
    public function test_identificador_ajeno_en_formulario(string $actor, string $url, array $datos, string $campo, string $tabla): void
    {
        $datos = $this->conCategoria($datos);
        $r = $this->ejecutarCaso($actor, 'post', $url, $datos, [422], $tabla, false);

        $errores = array_keys($r->json('errors') ?? []);
        $this->assertNotEmpty(array_filter($errores, fn ($k) => $k === $campo || str_starts_with($k, $campo.'.')), "Se esperaba error en '{$campo}', hubo: ".implode(', ', $errores));
    }

    public function test_exportacion_pdf_masivo_de_otro_colegio_rechazada(): void
    {
        $this->actingAs($this->e->b['directivo'])
            ->getJson($this->resolver('/boletines/pdf-masivo?id_curso={a.curso}&id_periodo={a.periodo}'))
            ->assertStatus(422);
    }

    public function test_exportacion_pdf_masivo_de_su_curso(): void
    {
        $r = $this->actingAs($this->e->a['directivo'])->get($this->resolver('/boletines/pdf-masivo?id_curso={a.curso}&id_periodo={a.periodo}'));
        $r->assertOk();
        $this->assertStringContainsString('zip', (string) $r->headers->get('content-type'));
    }

    public function test_formulario_legitimo_con_ids_propios(): void
    {
        $antes = \DB::table('asignaciones_academicas')->count();
        $this->actingAs($this->e->a['directivo'])->postJson('/gestion-academica/asignaciones', $this->resolverDatos(['id_profesor' => '{a.profesor}', 'id_materia' => '{a.materia}', 'id_curso' => '{a.curso7a}', 'anio_lectivo' => 2026]))->assertOk();
        $this->assertSame($antes + 1, \DB::table('asignaciones_academicas')->count());
    }

    private function conCategoria(array $datos): array
    {
        if (($datos['id_categoria'] ?? null) === 'CAT') {
            $datos['id_categoria'] = \App\Modules\Calendario\Models\EventoCategoria::where('slug', 'eventos-institucionales')->value('id_categoria');
        }

        return $datos;
    }
}
