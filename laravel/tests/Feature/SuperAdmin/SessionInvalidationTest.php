<?php

namespace Tests\Feature\SuperAdmin;

class SessionInvalidationTest extends SuperAdminTestCase
{
    public function test_cambiar_el_rol_de_un_usuario_invalida_su_sesion_activa(): void
    {
        $superadmin = $this->crearSuperAdmin();
        $docente = $this->crearUsuario(5);
        // /mi-panel exige un perfil de profesor asociado (si no, 404 a propósito).
        \App\Modules\Usuarios\Models\Profesor::create(['id_usuario' => $docente->id_usuario, 'codigo_profesor' => 'TEST-DOC-1', 'estado_laboral' => 'activo']);

        // El docente inicia sesión "de verdad" (auth_at queda en su sesión, igual que LoginController::store()).
        $this->actingAs($docente)->withSession(['auth_at' => now()->timestamp])
            ->get('/mi-panel')
            ->assertOk();

        // El SuperAdmin, en otra sesión, le cambia el rol.
        $this->actingAs($superadmin)
            ->postJson("/superadmin/usuarios/{$docente->id_usuario}/rol", ['id_rol' => 3])
            ->assertOk();

        $this->assertNotNull($docente->fresh()->sesion_valida_desde);

        // La siguiente petición del docente (misma sesión de antes) debe forzar el logout.
        // La app recarga al usuario desde la BD en cada petición: se usa ->fresh()
        // (la instancia en memoria no tiene el sesion_valida_desde recién guardado).
        $respuesta = $this->actingAs($docente->fresh())->withSession(['auth_at' => now()->subMinute()->timestamp])
            ->get('/mi-panel');

        $respuesta->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
