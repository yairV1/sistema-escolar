<?php

namespace Tests\Feature\Aislamiento;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\EscenarioDosColegios;

/**
 * Etapa 1 — listados, búsquedas, feeds, conteos y precargas: ninguna marca del
 * otro colegio puede aparecer; las del propio sí (los listados siguen útiles).
 */
class Etapa1ListadosTest extends AislamientoTestCase
{
    /** Páginas que usan SQL propio de MySQL/MariaDB (FIELD, DATE_FORMAT…). */
    private const SOLO_MARIADB = [
        '/inicio',                                       // Dashboard: ORDER BY FIELD(nivel_academico…)
        '/gestion-academica/horarios?curso={a.curso}',   // horarios: ORDER BY FIELD(dia_semana…)
        '/gestion-academica/horarios?curso={b.curso}',
    ];

    private const PAGINAS = [
        '/listados/estudiantes', '/listados/docentes', '/listados/administrativos', '/matriculas', '/observaciones',
        '/gestion-academica/cursos', '/gestion-academica/asignaciones', '/gestion-academica/horarios?curso={a.curso}',
        '/gestion-academica/horarios?curso={b.curso}', '/boletines?curso={a.curso}&periodo={a.periodo}',
        '/boletines?curso={b.curso}&periodo={a.periodo}', '/estadisticas', '/inicio', '/comunicados', '/calendario',
        '/buscar?q=Estudiante', '/buscar?q=Prof', '/calendario/feed?desde=2026-11-01&hasta=2026-11-30',
        '/registro/estudiantes?solicitud={a.solicitud}', '/registro/estudiantes?solicitud={b.solicitud}', '/roles',
    ];

    private static function generar(bool $mariadb): array
    {
        $casos = [];
        foreach (['a' => 'b', 'b' => 'a'] as $propio => $ajeno) {
            foreach (self::PAGINAS as $u) {
                if (in_array($u, self::SOLO_MARIADB, true) === $mariadb) {
                    $casos[strtoupper($propio)." {$u}"] = ["{$propio}.directivo", $u, $ajeno];
                }
            }
        }
        if (! $mariadb) {
            foreach (['/mis-anotaciones', '/mi-panel', '/calendario/feed?desde=2026-11-01&hasta=2026-11-30'] as $u) {
                $casos["Docente B {$u}"] = ['b.docente_usuario', $u, 'a'];
                $casos["Docente A {$u}"] = ['a.docente_usuario', $u, 'b'];
            }
        }

        return $casos;
    }

    public static function casos(): array
    {
        return self::generar(false);
    }

    public static function casosMariadb(): array
    {
        return self::generar(true);
    }

    #[DataProvider('casos')]
    public function test_sin_fugas(string $actor, string $url, string $colegioAjeno): void
    {
        $this->assertSinFugas($actor, $url, $colegioAjeno);
    }

    #[DataProvider('casosMariadb')]
    #[Group('mariadb')]
    public function test_sin_fugas_mariadb(string $actor, string $url, string $colegioAjeno): void
    {
        $this->assertSinFugas($actor, $url, $colegioAjeno);
    }

    public static function propios(): array
    {
        return [
            'A ve sus estudiantes' => ['a.directivo', '/listados/estudiantes', '/\bEstudianteAlfa\b/'],
            'B ve sus estudiantes' => ['b.directivo', '/listados/estudiantes', '/OtroColegio/'],
            'A ve sus observaciones' => ['a.directivo', '/observaciones', '/Observación Alfa/'],
            'B ve sus solicitudes' => ['b.directivo', '/matriculas', '/SolicitudZeta/'],
            'A ve su evento en el calendario' => ['a.directivo', '/calendario/feed?desde=2026-11-01&hasta=2026-11-30', '/EventoAlfa/'],
            'B ve su evento en el calendario' => ['b.directivo', '/calendario/feed?desde=2026-11-01&hasta=2026-11-30', '/EventoZeta/'],
        ];
    }

    #[DataProvider('propios')]
    public function test_lo_propio_sigue_visible(string $actor, string $url, string $marca): void
    {
        $r = $this->peticion($actor, $url);
        $r->assertOk();
        $this->assertMatchesRegularExpression($marca, $r->getContent());
    }

    private function assertSinFugas(string $actor, string $url, string $colegioAjeno): void
    {
        $r = $this->peticion($actor, $url);
        $this->assertSame(200, $r->getStatusCode(), 'GET '.$this->resolver($url));

        $marcas = $colegioAjeno === 'a' ? EscenarioDosColegios::MARCAS_A : EscenarioDosColegios::MARCAS_B;
        $fugas = array_filter($marcas, fn ($m) => preg_match($m, $r->getContent()));
        $this->assertSame([], array_values($fugas), 'Fuga en '.$this->resolver($url));
    }

    private function peticion(string $actor, string $url)
    {
        $this->actingAs($this->actor($actor));
        $url = $this->resolver($url);

        return (str_starts_with($url, '/buscar') || str_contains($url, '/feed')) ? $this->getJson($url) : $this->get($url);
    }
}
