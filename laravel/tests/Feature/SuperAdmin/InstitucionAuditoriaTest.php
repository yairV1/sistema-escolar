<?php

namespace Tests\Feature\SuperAdmin;

use App\Modules\Auditoria\Models\AuditLog;
use App\Modules\Instituciones\Models\Institucion;
use App\Modules\Planes\Models\Plan;

class InstitucionAuditoriaTest extends SuperAdminTestCase
{
    public function test_crear_institucion_escribe_un_registro_de_auditoria(): void
    {
        $superadmin = $this->crearSuperAdmin();
        $plan = Plan::create(['nombre' => 'Básico', 'slug' => 'basico']);

        $response = $this->actingAs($superadmin)->postJson('/superadmin/instituciones', [
            'nombre' => 'Colegio Nuevo',
            'slug' => 'colegio-nuevo',
            'id_plan' => $plan->id_plan,
        ]);

        $response->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseHas('instituciones', ['slug' => 'colegio-nuevo']);

        $institucion = Institucion::where('slug', 'colegio-nuevo')->firstOrFail();

        $log = AuditLog::where('accion', 'institucion.crear')
            ->where('entidad_id', $institucion->id_institucion)
            ->first();

        $this->assertNotNull($log, 'Se esperaba un AuditLog para institucion.crear.');
        $this->assertSame($superadmin->id_usuario, $log->id_usuario);
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
    }
}
