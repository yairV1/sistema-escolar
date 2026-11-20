<?php

namespace Tests\Feature\SuperAdmin;

use App\Modules\Auditoria\Services\AuditLogger;
use App\Modules\Auth\Models\Usuario;
use App\Modules\Instituciones\Models\Institucion;
use App\Modules\Instituciones\Support\RequisitosProvisionamientoInstitucion;
use App\Modules\Planes\Models\Plan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class InstitucionProvisionamientoTest extends SuperAdminTestCase
{
    private array $idsIniciales = [];

    protected function connectionsToTransact(): array
    {
        return DB::connection()->getDriverName() === 'mariadb'
            ? []
            : [config('database.default')];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->aplicarB8EnBaseAislada();

        if (DB::connection()->getDriverName() === 'mariadb') {
            $this->idsIniciales = [
                'instituciones' => (int) DB::table('instituciones')->max('id_institucion'),
                'usuarios' => (int) DB::table('usuarios')->max('id_usuario'),
                'audit_logs' => (int) DB::table('audit_logs')->max('id_log'),
                'planes' => (int) DB::table('planes')->max('id_plan'),
            ];
        }
    }

    protected function tearDown(): void
    {
        if ($this->idsIniciales !== []) {
            $institucionesNuevas = DB::table('instituciones')
                ->where('id_institucion', '>', $this->idsIniciales['instituciones'])
                ->pluck('id_institucion');

            DB::table('audit_logs')->where('id_log', '>', $this->idsIniciales['audit_logs'])->delete();
            DB::table('colegio_configuracion')->whereIn('id_institucion', $institucionesNuevas)->delete();
            DB::table('usuarios')->where('id_usuario', '>', $this->idsIniciales['usuarios'])->delete();
            DB::table('instituciones')->whereIn('id_institucion', $institucionesNuevas)->delete();
            DB::table('planes')->where('id_plan', '>', $this->idsIniciales['planes'])->delete();
        }

        parent::tearDown();
    }

    public function test_crea_colegio_configuracion_y_administrador_de_forma_aislada(): void
    {
        $superadmin = $this->crearSuperAdmin();
        $plan = $this->crearPlan();
        $institucionBase = (int) DB::table('instituciones')->orderBy('id_institucion')->value('id_institucion');
        $contenidoBaseAntes = DB::table('landing_contenido')->where('id_institucion', $institucionBase)->count();

        $response = $this->actingAs($superadmin)->postJson('/superadmin/instituciones', $this->datosAlta($plan, [
            'slug' => 'colegio-norte',
            'id_institucion' => $institucionBase,
        ]));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonMissingPath('password')
            ->assertDontSee('ADMIN-PRUEBA-1')
            ->assertDontSee('admin.norte@example.test');

        $institucion = Institucion::where('slug', 'colegio-norte')->firstOrFail();
        $administrador = Usuario::where('correo', 'admin.norte@example.test')->firstOrFail();

        $this->assertSame(1, (int) $administrador->id_rol);
        $this->assertSame((int) $institucion->id_institucion, (int) $administrador->id_institucion);
        $this->assertNotSame($institucionBase, (int) $administrador->id_institucion);
        $this->assertFalse(Hash::check('ADMIN-PRUEBA-1', $administrador->password));
        $this->assertDatabaseHas('colegio_configuracion', [
            'id_institucion' => $institucion->id_institucion,
            'nombre_colegio' => 'Colegio Norte',
        ]);
        $this->assertSame($contenidoBaseAntes, DB::table('landing_contenido')->where('id_institucion', $institucionBase)->count());
        $this->assertSame(0, DB::table('landing_contenido')->where('id_institucion', $institucion->id_institucion)->count());
        $this->assertDatabaseHas('audit_logs', [
            'accion' => 'institucion.crear',
            'entidad_id' => $institucion->id_institucion,
            'id_usuario' => $superadmin->id_usuario,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'accion' => 'institucion.administrador.asignar',
            'entidad_id' => $administrador->id_usuario,
            'id_usuario' => $superadmin->id_usuario,
        ]);

        $this->actingAs($superadmin)->get("/superadmin/instituciones/{$institucion->id_institucion}?tab=usuarios")
            ->assertOk()
            ->assertDontSee('admin.norte@example.test')
            ->assertDontSee('ADMIN-PRUEBA-1')
            ->assertSee('/sitio/colegio-norte')
            ->assertSee('/sitio/colegio-norte/ingresar');
    }

    public function test_rechaza_slugs_invalidos_duplicados_y_colisiones_normalizadas(): void
    {
        $superadmin = $this->crearSuperAdmin();
        $plan = $this->crearPlan();

        $this->actingAs($superadmin)->postJson('/superadmin/instituciones', $this->datosAlta($plan, [
            'slug' => 'Colegio-Invalido',
        ]))->assertUnprocessable();

        $this->actingAs($superadmin)->postJson('/superadmin/instituciones', $this->datosAlta($plan, [
            'slug' => 'colegio-san-cristobal',
        ]))->assertUnprocessable();

        $legado = Institucion::create([
            'nombre' => 'Institución con slug legado',
            'slug' => 'Privada',
            'plan' => 'basico',
            'estado' => 'activa',
        ]);

        $this->actingAs($superadmin)->postJson('/superadmin/instituciones', $this->datosAlta($plan, [
            'slug' => 'privada',
        ]))->assertUnprocessable();

        $this->assertSame('Privada', $legado->fresh()->slug);
        $this->assertFalse(DB::table('instituciones')->pluck('slug')->contains(fn ($slug) => $slug === 'privada'));
    }

    public function test_creacion_y_asignacion_se_revierten_si_falla_el_alta_del_administrador(): void
    {
        $superadmin = $this->crearSuperAdmin();
        $plan = $this->crearPlan();
        $this->mock(AuditLogger::class)
            ->shouldReceive('record')
            ->once()
            ->andThrow(new RuntimeException('Fallo de auditoría simulado.'));

        $this->actingAs($superadmin)->postJson('/superadmin/instituciones', $this->datosAlta($plan, [
            'slug' => 'colegio-rollback',
        ]))->assertInternalServerError();

        $this->assertDatabaseMissing('instituciones', ['slug' => 'colegio-rollback']);
        $this->assertDatabaseMissing('colegio_configuracion', ['nombre_colegio' => 'Colegio Norte']);
        $this->assertDatabaseMissing('usuarios', ['correo' => 'admin.norte@example.test']);
    }

    public function test_activa_y_suspende_con_auditoria_de_transicion(): void
    {
        $superadmin = $this->crearSuperAdmin();
        $institucion = Institucion::create([
            'nombre' => 'Colegio Estado',
            'slug' => 'colegio-estado',
            'plan' => 'basico',
            'estado' => 'suspendida',
        ]);

        $this->actingAs($superadmin)->postJson("/superadmin/instituciones/{$institucion->id_institucion}/activar")->assertOk();
        $this->assertSame('activa', $institucion->fresh()->estado);
        $this->assertDatabaseHas('audit_logs', [
            'accion' => 'institucion.activar',
            'entidad_id' => $institucion->id_institucion,
        ]);

        $this->actingAs($superadmin)->postJson("/superadmin/instituciones/{$institucion->id_institucion}/desactivar")->assertOk();
        $this->assertSame('suspendida', $institucion->fresh()->estado);
        $this->assertDatabaseHas('audit_logs', [
            'accion' => 'institucion.suspender',
            'entidad_id' => $institucion->id_institucion,
        ]);
    }

    public function test_esquema_b8_tiene_las_restricciones_de_provisionamiento_necesarias(): void
    {
        $this->assertSame([], RequisitosProvisionamientoInstitucion::pendientes());
        $this->assertFalse(collect(Schema::getColumns('colegio_configuracion'))->firstWhere('name', 'id_institucion')['nullable']);
    }

    private function crearPlan(): Plan
    {
        return Plan::create(['nombre' => 'Plan B9', 'slug' => 'plan-b9']);
    }

    private function datosAlta(Plan $plan, array $sobrescrituras = []): array
    {
        return array_replace([
            'nombre' => 'Colegio Norte',
            'slug' => 'colegio-norte',
            'id_plan' => $plan->id_plan,
            'administrador_nombres' => 'Admin',
            'administrador_apellidos' => 'Norte',
            'administrador_tipo_documento' => 'CC',
            'administrador_numero_documento' => 'ADMIN-PRUEBA-1',
            'administrador_correo' => 'admin.norte@example.test',
        ], $sobrescrituras);
    }

    private function aplicarB8EnBaseAislada(): void
    {
        if (RequisitosProvisionamientoInstitucion::pendientes() === []) {
            return;
        }

        $idInstitucion = (int) DB::table('instituciones')->orderBy('id_institucion')->value('id_institucion');
        foreach (['colegio_configuracion', 'landing_contenido', 'landing_galeria', 'landing_noticias'] as $tabla) {
            DB::table($tabla)->whereNull('id_institucion')->update(['id_institucion' => $idInstitucion]);
        }

        $ruta = database_path('migrations/etapa2/b8/2026_11_19_120000_etapa2_b8_restricciones_institucionales.php');
        $migracion = require $ruta;
        if (! $migracion instanceof Migration) {
            $this->fail('La migración B8 no se pudo cargar en la base aislada.');
        }
        $migracion->up();
    }
}
