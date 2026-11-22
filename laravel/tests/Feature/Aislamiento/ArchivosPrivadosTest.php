<?php

namespace Tests\Feature\Aislamiento;

use App\Modules\Calendario\Models\EventoAdjunto;
use App\Modules\Soporte\Models\Soporte;
use Database\Factories\UsuarioFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ArchivosPrivadosTest extends AislamientoTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
        Storage::fake('public');
    }

    public function test_foto_de_perfil_se_almacena_privada_y_no_cruza_colegios(): void
    {
        $usuario = $this->e->a['directivo'];

        $this->actingAs($usuario)->postJson(route('perfil.update'), [
            'nombres' => $usuario->nombres,
            'apellidos' => $usuario->apellidos,
            'correo' => $usuario->correo,
            'foto_perfil' => UploadedFile::fake()->image('foto.png'),
        ])->assertOk();

        $usuario->refresh();
        $this->assertStringStartsWith('perfiles/', $usuario->foto_perfil);
        Storage::disk('private')->assertExists($usuario->foto_perfil);
        Storage::disk('public')->assertMissing($usuario->foto_perfil);

        $url = $usuario->fotoPerfilUrl;
        $respuesta = $this->actingAs($usuario)->get($url)
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringContainsString('private', $respuesta->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', $respuesta->headers->get('Cache-Control'));
        $this->actingAs($this->e->b['directivo'])->get($url)->assertNotFound();
    }

    public function test_archivo_heredado_sigue_entregandose_autorizadamente_y_no_se_borra(): void
    {
        $usuario = $this->e->a['directivo'];
        $ruta = 'perfiles/legado.png';
        Storage::disk('public')->put($ruta, 'contenido ficticio');
        $usuario->update(['foto_perfil' => $ruta]);

        $this->actingAs($usuario)->get($usuario->fotoPerfilUrl)->assertOk();
        Storage::disk('public')->assertExists($ruta);
        $this->actingAs($this->e->b['directivo'])->get($usuario->fotoPerfilUrl)->assertNotFound();
    }

    public function test_captura_de_soporte_no_es_publica_y_exige_el_colegio_o_el_equipo_de_soporte(): void
    {
        $adminA = UsuarioFactory::new()->create([
            'id_rol' => 1,
            'id_institucion' => $this->e->a['institucion']->id_institucion,
        ]);
        $adminB = UsuarioFactory::new()->create([
            'id_rol' => 1,
            'id_institucion' => $this->e->b['institucion']->id_institucion,
        ]);
        $this->actingAs($this->e->a['directivo'])->postJson('/soporte', [
            'asunto' => 'Incidencia ficticia',
            'mensaje' => 'Captura de prueba',
            'imagen' => UploadedFile::fake()->image('captura.png'),
        ])->assertOk();

        $soporte = Soporte::query()->where('asunto', 'Incidencia ficticia')->firstOrFail();
        Storage::disk('private')->assertExists($soporte->imagen);
        Storage::disk('public')->assertMissing($soporte->imagen);

        $this->actingAs($adminA)->get($soporte->imagenUrl)->assertOk();
        $this->actingAs($adminB)->get($soporte->imagenUrl)->assertNotFound();
        $this->actingAs($this->e->superadmin)->get($soporte->imagenUrl)->assertOk();
    }

    public function test_adjunto_de_evento_se_entrega_solo_a_usuarios_que_pueden_verlo(): void
    {
        $evento = $this->e->a['evento'];
        $respuesta = $this->actingAs($this->e->a['directivo'])
            ->postJson(route('calendario.adjuntos.store', $evento), [
                'adjunto' => UploadedFile::fake()->create('material.pdf', 5, 'application/pdf'),
            ])
            ->assertOk();

        $adjunto = EventoAdjunto::query()->findOrFail($respuesta->json('adjunto.id_adjunto'));
        Storage::disk('private')->assertExists($adjunto->ruta);
        Storage::disk('public')->assertMissing($adjunto->ruta);

        $url = $respuesta->json('adjunto.url');
        $this->assertStringContainsString('/calendario/adjuntos/'.$adjunto->id_adjunto, $url);
        $this->actingAs($this->e->a['directivo'])->get($url)->assertOk();
        $this->actingAs($this->e->b['directivo'])->get($url)->assertNotFound();
    }
}
