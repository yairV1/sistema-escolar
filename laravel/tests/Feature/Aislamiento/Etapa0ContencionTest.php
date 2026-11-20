<?php

namespace Tests\Feature\Aislamiento;

use App\Modules\Comunicados\Models\Notificacion;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * Port de la matriz HTTP de la Etapa 0 (V1 cuentas, V2 roles, V3/V5 notas y
 * planillas, V8 integridad, V4 comunicados). Ver docs/seguridad/etapa0-contencion.md.
 */
class Etapa0ContencionTest extends AislamientoTestCase
{
    private const ADMIN = ['nombres' => 'Coordinador', 'apellidos' => 'Prueba', 'tipo_documento' => 'CC', 'numero_documento' => 'COORD-NUEVO', 'correo' => 'coord.nuevo@prueba.local', 'id_rol' => '3'];

    private const ATAQUE = ['nombres' => 'X', 'apellidos' => 'X', 'tipo_documento' => 'CC', 'numero_documento' => 'ATAQUE-1', 'correo' => 'atacante@prueba.local', 'id_rol' => '2'];

    public static function casos(): array
    {
        $nota = fn ($est, $n) => ['notas' => [['id_estudiante' => $est, 'nota' => $n]]];
        $asis = fn ($est) => ['fecha' => '2026-11-17', 'registros' => [['id_estudiante' => $est, 'estado_asistencia' => 'presente']]];

        return [
            // V1 — cuentas
            'V1-01 legítimo: Directivo edita a su coordinador' => ['a.directivo', 'post', '/registro/administrativos/{a.coordinador}', self::ADMIN, [200], 'usuarios', true],
            'V1-02 legítimo: Directivo abre ficha de su coordinador' => ['a.directivo', 'get', '/registro/administrativos/{a.coordinador}/editar', null, [200], null, null],
            'V1-03 otra institución edita docente de A' => ['b.directivo', 'post', '/registro/administrativos/{a.docente_usuario}', self::ATAQUE, [404], 'usuarios', false],
            'V1-04 otra institución abre edición de cuenta de A' => ['b.directivo', 'get', '/registro/administrativos/{a.docente_usuario}/editar', null, [404], null, null],
            'V1-05 Directivo abre edición del SuperAdmin' => ['a.directivo', 'get', '/registro/administrativos/{superadmin}/editar', null, [404], null, null],
            'V1-06 Directivo intenta editar al SuperAdmin' => ['a.directivo', 'post', '/registro/administrativos/{superadmin}', self::ATAQUE, [404], 'usuarios', false],
            'V1-07 misma institución pero cuenta de estudiante' => ['a.directivo', 'post', '/registro/administrativos/{a.estudiante_usuario}', self::ATAQUE, [404], 'usuarios', false],
            'V1-08 escalada id_rol=8 sobre su coordinador' => ['a.directivo', 'post', '/registro/administrativos/{a.coordinador}', ['id_rol' => '8'] + self::ADMIN, [422], 'usuarios', false],
            'V1-09 otra institución: ficha de estudiante' => ['b.directivo', 'get', '/registro/estudiantes/{a.estudiante}', null, [404], null, null],
            'V1-10 otra institución: editar estudiante' => ['b.directivo', 'get', '/registro/estudiantes/{a.estudiante}/editar', null, [404], null, null],
            'V1-11 otra institución: ficha de docente' => ['b.directivo', 'get', '/registro/docentes/{a.profesor}', null, [404], null, null],
            'V1-12 legítimo: ficha de su estudiante' => ['a.directivo', 'get', '/registro/estudiantes/{a.estudiante}', null, [200], null, null],
            'V1-13 legítimo: ficha de su docente' => ['a.directivo', 'get', '/registro/docentes/{a.profesor}', null, [200], null, null],
            'V1-14 otra institución: desactivar estudiante' => ['b.directivo', 'post', '/listados/estudiantes/{a.estudiante}/desactivar', [], [404], 'estudiantes', false],
            'V1-15 otra institución: desactivar docente' => ['b.directivo', 'post', '/listados/docentes/{a.profesor}/desactivar', [], [404], 'usuarios', false],
            'V1-16 desactivar al SuperAdmin vía administrativos' => ['a.directivo', 'post', '/listados/administrativos/{superadmin}/desactivar', [], [404], 'usuarios', false],
            'V1-17 otra institución: desactivar coordinador' => ['b.directivo', 'post', '/listados/administrativos/{a.coordinador}/desactivar', [], [404], 'usuarios', false],
            'V1-18 legítimo: desactivar su estudiante' => ['a.directivo', 'post', '/listados/estudiantes/{a.estudiante_sin_curso}/desactivar', [], [200], 'estudiantes', true],
            // V2 — roles globales
            'V2-01 Directivo desactiva rol Docente' => ['a.directivo', 'post', '/roles/5/desactivar', [], [403], 'roles', false],
            'V2-02 Directivo desactiva rol SuperAdmin' => ['b.directivo', 'post', '/roles/8/desactivar', [], [403], 'roles', false],
            'V2-03 Directivo renombra rol' => ['a.directivo', 'post', '/roles/5', ['nombre_rol' => 'Hackeado', 'descripcion' => 'x'], [403], 'roles', false],
            'V2-04 Directivo consulta roles' => ['a.directivo', 'get', '/roles', null, [200], null, null],
            // V3/V5 — notas y planillas
            'V3-01 legítimo: Directivo ve planilla' => ['a.directivo', 'get', '/calificaciones/asignaciones/{a.asignacion}/planilla', null, [200], null, null],
            'V3-02 legítimo: Docente ve su planilla' => ['a.docente_usuario', 'get', '/calificaciones/asignaciones/{a.asignacion}/planilla', null, [200], null, null],
            'V3-03 otra institución: ver planilla' => ['b.directivo', 'get', '/calificaciones/asignaciones/{a.asignacion}/planilla', null, [403, 404], null, null],
            'V3-04 otra institución: descargar planilla' => ['b.directivo', 'get', '/calificaciones/asignaciones/{a.asignacion}/planilla/exportar', null, [403, 404], null, null],
            'V3-05 otra institución: cambiar nota' => ['b.directivo', 'post', '/calificaciones/actividades/{a.actividad}/notas', $nota('{a.estudiante}', 1.0), [403, 404], 'detalle_notas', false],
            'V3-06 otra institución: crear actividad' => ['b.directivo', 'post', '/calificaciones/asignaciones/{a.asignacion}/actividades', ['id_periodo' => '{a.periodo}', 'id_tipo_actividad' => '{a.tipo}', 'titulo' => 'X', 'porcentaje' => 10], [403, 404], 'actividades', false],
            'V3-07 otra institución: ver asistencia' => ['b.directivo', 'get', '/asistencia/asignaciones/{a.asignacion}', null, [403, 404], null, null],
            'V3-08 otra institución: registrar asistencia' => ['b.directivo', 'post', '/asistencia/asignaciones/{a.asignacion}', $asis('{a.estudiante}'), [403, 404], 'asistencia', false],
            'V3-09 Docente: asignación de otro docente' => ['a.docente_usuario', 'get', '/calificaciones/asignaciones/{a.asignacion2}/planilla', null, [403], null, null],
            'V3-10 legítimo: Directivo registra nota' => ['a.directivo', 'post', '/calificaciones/actividades/{a.actividad}/notas', $nota('{a.estudiante2}', 3.5), [200], 'detalle_notas', true],
            // V8 — integridad
            'V8-01 legítimo: Docente pone nota a matriculado' => ['a.docente_usuario', 'post', '/calificaciones/actividades/{a.actividad}/notas', $nota('{a.estudiante}', 4.8), [200], 'detalle_notas', true],
            'V8-02 estudiante de su colegio sin matrícula en el curso' => ['a.docente_usuario', 'post', '/calificaciones/actividades/{a.actividad}/notas', $nota('{a.estudiante_sin_curso}', 5.0), [422], 'detalle_notas', false],
            'V8-03 estudiante de OTRA institución' => ['a.docente_usuario', 'post', '/calificaciones/actividades/{a.actividad}/notas', $nota('{b.estudiante}', 5.0), [422], 'detalle_notas', false],
            'V8-04 mezcla válido + ajeno: todo o nada' => ['a.docente_usuario', 'post', '/calificaciones/actividades/{a.actividad}/notas', ['notas' => [['id_estudiante' => '{a.estudiante2}', 'nota' => 0.5], ['id_estudiante' => '{b.estudiante}', 'nota' => 5.0]]], [422], 'detalle_notas', false],
            'V8-05 asistencia a estudiante no matriculado' => ['a.docente_usuario', 'post', '/asistencia/asignaciones/{a.asignacion}', $asis('{a.estudiante_sin_curso}'), [422], 'asistencia', false],
            'V8-06 legítimo: asistencia a matriculado' => ['a.docente_usuario', 'post', '/asistencia/asignaciones/{a.asignacion}', $asis('{a.estudiante}'), [200], 'asistencia', true],
        ];
    }

    #[DataProvider('casos')]
    public function test_caso(string $actor, string $metodo, string $url, ?array $datos, array $esperados, ?string $tabla, ?bool $debeCambiar): void
    {
        $this->ejecutarCaso($actor, $metodo, $url, $datos, $esperados, $tabla, $debeCambiar);
    }

    public function test_V1_19_desactivar_y_reactivar_su_estudiante(): void
    {
        $this->actingAs($this->e->a['directivo']);
        $id = $this->e->a['estudiante_sin_curso']->id_estudiante;
        $this->postJson("/listados/estudiantes/{$id}/desactivar")->assertOk();
        $this->assertSame('inactivo', DB::table('estudiantes')->where('id_estudiante', $id)->value('estado_academico'));
        $this->postJson("/listados/estudiantes/{$id}/activar")->assertOk();
        $this->assertSame('activo', DB::table('estudiantes')->where('id_estudiante', $id)->value('estado_academico'));
    }

    #[Group('mariadb')]
    public function test_V2_05_superadmin_conserva_su_modulo_de_roles(): void
    {
        $this->actingAs($this->e->superadmin)->get('/superadmin/roles')->assertOk();
    }

    public function test_UI_01_vista_de_roles_del_directivo_sin_acciones_de_escritura(): void
    {
        $html = $this->actingAs($this->e->a['directivo'])->get('/roles')->assertOk()->getContent();
        $this->assertStringNotContainsString('/roles/5/desactivar', $html);
        $this->assertStringNotContainsString('data-crud-form', $html);
    }

    public function test_V4_01_comunicado_de_B_no_llega_a_otras_instituciones(): void
    {
        $this->actingAs($this->e->b['directivo'])->postJson('/comunicados', $this->comunicado())->assertOk();
        $this->assertSame(0, $this->destinatariosFueraDe($this->e->b['institucion']->id_institucion));
    }

    public function test_V4_02_comunicado_legitimo_llega_solo_a_su_institucion(): void
    {
        $this->actingAs($this->e->a['directivo'])->postJson('/comunicados', $this->comunicado())->assertOk();
        $this->assertGreaterThan(0, Notificacion::where('titulo', 'PRUEBA')->count());
        $this->assertSame(0, $this->destinatariosFueraDe($this->e->a['institucion']->id_institucion));
    }

    private function comunicado(): array
    {
        return ['titulo' => 'PRUEBA', 'mensaje' => 'prueba', 'tipo_notificacion' => 'informativa', 'canal' => 'interno', 'audiencias' => ['estudiantes', 'acudientes']];
    }

    private function destinatariosFueraDe(int $idInstitucion): int
    {
        return Notificacion::where('titulo', 'PRUEBA')
            ->whereHas('destino', fn ($q) => $q->where('id_institucion', '!=', $idInstitucion)->orWhereNull('id_institucion'))
            ->count();
    }
}
