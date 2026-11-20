<?php

namespace Tests\Feature\Aislamiento;

use App\Modules\Auth\Models\Usuario;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Group;

/**
 * Etapa 1 — hallazgo crítico (matrícula/acudiente en otro colegio), asignación
 * masiva y alcance del SuperAdmin.
 */
class Etapa1RegistroYAlcanceTest extends AislamientoTestCase
{
    private function alumno(string $doc, string $grado, string $grupo, string $docAcudiente): array
    {
        return ['primer_nombre' => 'Nuevo', 'primer_apellido' => $doc, 'tipo_documento' => 'TI', 'numero_documento' => $doc,
            'fecha_nacimiento' => '2014-01-01', 'genero' => 'F', 'direccion' => 'x', 'localidad' => 'x', 'ciudad' => 'x',
            'tipo_matricula' => 'nueva', 'anio_lectivo' => 2026, 'grado' => $grado, 'grupo' => $grupo, 'jornada' => 'manana',
            'nee' => 'no', 'acudiente_nombres' => 'Acudiente '.$doc, 'acudiente_parentesco' => 'madre',
            'acudiente_tipo_documento' => 'CC', 'acudiente_numero_documento' => $docAcudiente, 'acudiente_telefono' => '3001234567',
            'acudiente_correo' => strtolower("acu{$doc}@prueba.local")];
    }

    public function test_B_no_puede_matricular_en_el_7A_de_A(): void
    {
        $matriculasEn7A = fn () => DB::table('matriculas')->where('id_curso', $this->e->a['curso7a']->id_curso)->count();

        $r = $this->actingAs($this->e->b['directivo'])->postJson('/registro/estudiantes', $this->alumno('T-2001', '7', 'A', 'T-3001'));

        $r->assertStatus(422)->assertJsonValidationErrors('grado');
        $this->assertSame(0, Usuario::where('numero_documento', 'T-2001')->count(), 'la transacción debe revertir el usuario');
        $this->assertSame(0, $matriculasEn7A());
    }

    public function test_B_registra_en_un_curso_libre_y_queda_en_su_colegio(): void
    {
        $this->actingAs($this->e->b['directivo'])->postJson('/registro/estudiantes', $this->alumno('T-2002', '8', 'B', 'T-3002'))->assertOk();

        $this->assertSame($this->e->b['institucion']->id_institucion, (int) DB::table('cursos')->where('nombre_curso', '8B')->value('id_institucion'));
    }

    public function test_A_registra_en_su_7A_y_reutiliza_su_curso(): void
    {
        $this->actingAs($this->e->a['directivo'])->postJson('/registro/estudiantes', $this->alumno('T-2003', '7', 'A', 'T-3003'))->assertOk();

        $idEstudiante = DB::table('estudiantes')->join('usuarios', 'usuarios.id_usuario', '=', 'estudiantes.id_usuario')
            ->where('usuarios.numero_documento', 'T-2003')->value('estudiantes.id_estudiante');
        $this->assertSame($this->e->a['curso7a']->id_curso, (int) DB::table('matriculas')->where('id_estudiante', $idEstudiante)->value('id_curso'));
        $this->assertSame(1, DB::table('cursos')->where('nombre_curso', '7A')->count(), 'no debe crear un segundo 7A');
    }

    public function test_B_no_puede_vincular_un_acudiente_de_A(): void
    {
        $docAcudienteA = $this->e->a['acudiente_usuario']->numero_documento;

        $r = $this->actingAs($this->e->b['directivo'])->postJson('/registro/estudiantes', $this->alumno('T-2004', '8', 'B', $docAcudienteA));

        $r->assertStatus(422)->assertJsonValidationErrors('acudiente_numero_documento');
        $this->assertSame(0, Usuario::where('numero_documento', 'T-2004')->count());
        $this->assertSame(1, DB::table('estudiante_acudiente')->where('id_acudiente', $this->e->a['acudiente']->id_acudiente)->count());
    }

    public function test_asignacion_masiva_de_id_institucion_se_ignora(): void
    {
        $this->actingAs($this->e->a['directivo'])->postJson('/registro/administrativos', [
            'nombres' => 'Masiva', 'apellidos' => 'Prueba', 'tipo_documento' => 'CC', 'numero_documento' => 'T-4001',
            'correo' => 't4001@prueba.local', 'id_rol' => '3', 'id_institucion' => $this->e->b['institucion']->id_institucion,
        ])->assertOk();

        $this->assertSame($this->e->a['institucion']->id_institucion, (int) Usuario::where('numero_documento', 'T-4001')->value('id_institucion'));
    }

    public function test_asignacion_masiva_en_el_modelo_no_asigna_rol_ni_institucion(): void
    {
        $u = new Usuario(['nombres' => 'x', 'id_rol' => 8, 'id_institucion' => 99]);
        $this->assertNull($u->id_rol);
        $this->assertNull($u->id_institucion);
    }

    #[Group('mariadb')]
    public function test_superadmin_conserva_su_panel(): void
    {
        $this->actingAs($this->e->superadmin);
        foreach (['/superadmin', '/superadmin/instituciones', '/superadmin/usuarios'] as $u) {
            $this->get($u)->assertOk();
        }
    }

    public function test_superadmin_mesa_de_soporte_global(): void
    {
        $this->actingAs($this->e->superadmin)->get('/soportes')->assertOk()->assertSee('SoporteAlfa')->assertSee('SoporteZeta');
    }

    public function test_superadmin_sin_acceso_academico(): void
    {
        $this->assertContains(
            $this->actingAs($this->e->superadmin)->get($this->resolver('/registro/estudiantes/{a.estudiante}'))->getStatusCode(),
            [403, 404]
        );
    }
}
