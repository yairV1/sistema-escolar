<?php

namespace Tests\Feature\Aislamiento;

use Database\Factories\BoletinFactory;
use Database\Factories\MatriculaFactory;
use Database\Factories\PeriodoFactory;
use Illuminate\Support\Facades\DB;

/**
 * Fase 1 (P1-3): la distribución de notas y el ranking de Estadísticas solo
 * cuentan datos de la institución de la sesión. Se comparan valores numéricos
 * exactos (las pruebas de fugas por texto no detectan cifras agregadas).
 *
 * Colegio A: notas 2.5, 3.5, 3.9, 4.2, 4.7 → bajo 1, básico 2, alto 1, superior 1
 * Colegio B: notas 1.0, 1.5, 2.0, 4.9      → bajo 3, básico 0, alto 0, superior 1
 * Más un detalle cruzado (boletín de A con asignación de B) y uno sin nota,
 * que no deben contar en ningún colegio.
 */
class EstadisticasDistribucionTest extends AislamientoTestCase
{
    private int $periodos = 0;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([2.5, 3.5, 3.9, 4.2, 4.7] as $nota) {
            $this->detalle('a', 'a', $nota);
        }
        foreach ([1.0, 1.5, 2.0, 4.9] as $nota) {
            $this->detalle('b', 'b', $nota);
        }
        $this->detalle('a', 'b', 1.2);   // estudiante de A, asignación de B: dato cruzado
        $this->detalle('b', 'a', 4.8);   // estudiante de B, asignación de A: dato cruzado
        $this->detalle('a', 'a', null);  // sin nota
    }

    /** Un boletín (periodo propio, por la única estudiante+periodo) con un detalle. */
    private function detalle(string $colegioEstudiante, string $colegioAsignacion, ?float $nota): void
    {
        $this->periodos++;
        $periodo = PeriodoFactory::new()->create(['nombre_periodo' => "Periodo F1-{$this->periodos}"]);
        $boletin = BoletinFactory::new()->create([
            'id_estudiante' => $this->e->{$colegioEstudiante}['estudiante']->id_estudiante,
            'id_periodo' => $periodo->id_periodo,
        ]);
        DB::table('boletin_detalle')->insert([
            'id_boletin' => $boletin->id_boletin,
            'id_asignacion' => $this->e->{$colegioAsignacion}['asignacion']->id_asignacion,
            'nota_definitiva' => $nota,
        ]);
    }

    private function distribucion(string $actor): array
    {
        $this->actingAs($this->actor($actor));
        $r = $this->get('/estadisticas');
        $r->assertOk();

        return $r->viewData('distribucionNotas');
    }

    public function test_distribucion_del_colegio_a_es_exacta(): void
    {
        $this->assertSame(['bajo' => 1, 'basico' => 2, 'alto' => 1, 'superior' => 1], $this->distribucion('a.directivo'));
    }

    public function test_distribucion_del_colegio_b_es_exacta(): void
    {
        $this->assertSame(['bajo' => 3, 'basico' => 0, 'alto' => 0, 'superior' => 1], $this->distribucion('b.directivo'));
    }

    public function test_la_suma_de_ambos_colegios_no_incluye_datos_cruzados(): void
    {
        $a = $this->distribucion('a.directivo');
        $b = $this->distribucion('b.directivo');

        $this->assertSame(9, array_sum($a) + array_sum($b));
        // En la tabla hay 12 detalles (incluidos los cruzados y el vacío): ninguno se cuela.
        $this->assertSame(12, DB::table('boletin_detalle')->count());
    }

    public function test_ranking_no_duplica_por_matriculas_activas_de_otros_anios(): void
    {
        // Boletín publicado de A (periodo 2026, curso 6P) y una matrícula activa
        // antigua (2025) en otro curso: con el join anterior contaba dos veces.
        $this->e->a['boletin']->forceFill(['estado' => 'publicado', 'promedio_general' => 4.0])->save();
        MatriculaFactory::new()->create([
            'id_estudiante' => $this->e->a['estudiante']->id_estudiante,
            'id_curso' => $this->e->a['curso7a']->id_curso,
            'anio_lectivo' => 2025,
            'estado_matricula' => 'activa',
        ]);

        $this->actingAs($this->actor('a.directivo'));
        $ranking = $this->get('/estadisticas')->assertOk()->viewData('rankingCursos')
            ->map(fn ($f) => [$f->nombre_curso, round((float) $f->promedio, 2), (int) $f->total_boletines])->all();
        $this->assertSame([['6P', 4.0, 1]], $ranking);

        // El otro colegio no ve ese curso ni ese boletín.
        $this->actingAs($this->actor('b.directivo'));
        $this->assertCount(0, $this->get('/estadisticas')->assertOk()->viewData('rankingCursos'));
    }
}
