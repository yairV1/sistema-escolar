<?php

namespace Tests\Feature\SuperAdmin;

use App\Modules\Auditoria\Models\AuditLog;
use App\Modules\Instituciones\Models\Institucion;
use App\Modules\Planes\Models\Plan;
use Illuminate\Support\Facades\Schema;

class InstitucionAuditoriaTest extends SuperAdminTestCase
{
    public function test_bloquea_el_aprovisionamiento_si_b2_b8_no_estan_aplicadas(): void
    {
        $superadmin = $this->crearSuperAdmin();
        $plan = Plan::create(['nombre' => 'Básico', 'slug' => 'basico']);
        $columna = collect(Schema::getColumns('colegio_configuracion'))->firstWhere('name', 'id_institucion');

        $response = $this->actingAs($superadmin)->postJson('/superadmin/instituciones', [
            'nombre' => 'Colegio Bloqueado',
            'slug' => 'colegio-bloqueado',
            'id_plan' => $plan->id_plan,
            'administrador_nombres' => 'Admin',
            'administrador_apellidos' => 'Prueba',
            'administrador_tipo_documento' => 'CC',
            'administrador_numero_documento' => 'ADMIN-BLOQUEADO-1',
            'administrador_correo' => 'admin.bloqueado@ejemplo.com',
        ]);

        $response->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'El aprovisionamiento está bloqueado: aplica y verifica B2–B8 en una ventana de mantenimiento antes de crear colegios.');
        $this->assertDatabaseMissing('instituciones', ['slug' => 'colegio-bloqueado']);
        $this->assertDatabaseMissing('usuarios', ['correo' => 'admin.bloqueado@ejemplo.com']);
        $this->assertTrue($columna['nullable'], 'La petición no debe aplicar migraciones.');
    }

    public function test_suspender_institucion_cambia_su_estado(): void
    {
        $superadmin = $this->crearSuperAdmin();
        $institucion = Institucion::create([
            'nombre' => 'Colegio Suspendible',
            'slug' => 'colegio-suspendible',
            'plan' => 'basico',
            'estado' => 'activa',
        ]);

        $response = $this->actingAs($superadmin)->postJson("/superadmin/instituciones/{$institucion->id_institucion}/desactivar");

        $response->assertOk();
        $this->assertSame('suspendida', $institucion->fresh()->estado);

        $log = AuditLog::where('accion', 'institucion.suspender')
            ->where('entidad_id', $institucion->id_institucion)
            ->firstOrFail();
        $this->assertSame(['estado' => 'activa'], $log->datos_antes);
        $this->assertSame(['estado' => 'suspendida'], $log->datos_despues);
    }
}
