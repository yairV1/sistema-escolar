<?php

namespace Tests\Feature\Aislamiento;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * Etapa 1 — manipulación de identificadores en la URL (lectura).
 * Cada recurso: su institución lo ve (200); la otra recibe 404 sin revelar existencia.
 */
class Etapa1UrlTest extends AislamientoTestCase
{
    private const RECURSOS_A = [
        '/registro/estudiantes/{a.estudiante}', '/registro/estudiantes/{a.estudiante}/editar', '/registro/docentes/{a.profesor}',
        '/registro/administrativos/{a.coordinador}', '/gestion-academica/cursos/{a.curso}', '/boletines/{a.boletin}',
        '/boletines/{a.boletin}/pdf', '/calificaciones/asignaciones/{a.asignacion}', '/calificaciones/asignaciones/{a.asignacion}/planilla',
        '/calificaciones/asignaciones/{a.asignacion}/planilla/exportar', '/calificaciones/actividades/{a.actividad}/notas',
        '/asistencia/asignaciones/{a.asignacion}', '/asistencia/asignaciones/{a.asignacion}/historial', '/calendario/{a.evento}',
    ];

    private const RECURSOS_B = [
        '/registro/estudiantes/{b.estudiante}', '/registro/docentes/{b.profesor}', '/gestion-academica/cursos/{b.curso}',
        '/boletines/{b.boletin}', '/boletines/{b.boletin}/pdf', '/calificaciones/asignaciones/{b.asignacion}/planilla',
        '/calificaciones/asignaciones/{b.asignacion}/planilla/exportar', '/asistencia/asignaciones/{b.asignacion}', '/calendario/{b.evento}',
    ];

    public static function casos(): array
    {
        $casos = [];
        foreach (self::RECURSOS_A as $u) {
            if (! self::soloMariadb($u)) {
                $casos["A→A legítimo {$u}"] = ['a.directivo', $u, 200];
            }
            $casos["B→A cruzado {$u}"] = ['b.directivo', $u, 404];
        }
        foreach (self::RECURSOS_B as $u) {
            if (! self::soloMariadb($u)) {
                $casos["B→B legítimo {$u}"] = ['b.directivo', $u, 200];
            }
            $casos["A→B cruzado {$u}"] = ['a.directivo', $u, 404];
        }
        $casos['Docente A → planilla del colegio B'] = ['a.docente_usuario', '/calificaciones/asignaciones/{b.asignacion}/planilla', 404];
        $casos['Docente B → su planilla'] = ['b.docente_usuario', '/calificaciones/asignaciones/{b.asignacion}/planilla', 200];

        return $casos;
    }

    /** El detalle del curso ordena horarios con FIELD() (solo MySQL/MariaDB). */
    private static function soloMariadb(string $url): bool
    {
        return str_starts_with($url, '/gestion-academica/cursos/');
    }

    public static function casosMariadb(): array
    {
        return [
            'A→A legítimo /gestion-academica/cursos/{a.curso}' => ['a.directivo', '/gestion-academica/cursos/{a.curso}', 200],
            'B→B legítimo /gestion-academica/cursos/{b.curso}' => ['b.directivo', '/gestion-academica/cursos/{b.curso}', 200],
        ];
    }

    #[DataProvider('casosMariadb')]
    #[Group('mariadb')]
    public function test_lectura_por_url_mariadb(string $actor, string $url, int $esperado): void
    {
        $this->test_lectura_por_url($actor, $url, $esperado);
    }

    #[DataProvider('casos')]
    public function test_lectura_por_url(string $actor, string $url, int $esperado): void
    {
        $this->actingAs($this->actor($actor));
        $url = $this->resolver($url);
        $r = str_starts_with($url, '/calendario/') ? $this->getJson($url) : $this->get($url);

        $this->assertSame($esperado, $r->getStatusCode(), "GET {$url}");
    }
}
