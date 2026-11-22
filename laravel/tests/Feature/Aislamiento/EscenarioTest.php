<?php

namespace Tests\Feature\Aislamiento;

class EscenarioTest extends AislamientoTestCase
{
    public function test_el_escenario_crea_dos_colegios_aislados(): void
    {
        $this->assertNotSame($this->e->a['institucion']->id_institucion, $this->e->b['institucion']->id_institucion);
        $this->assertSame($this->e->a['institucion']->id_institucion, $this->e->a['estudiante']->idInstitucionPropia());
        $this->assertSame($this->e->b['institucion']->id_institucion, $this->e->b['nota']->idInstitucionPropia());
        $this->actingAs($this->e->a['directivo'])->get('/listados/estudiantes')->assertOk()->assertSee('EstudianteAlfa');
    }
}
