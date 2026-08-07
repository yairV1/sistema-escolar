<?php

namespace Tests\Feature\SuperAdmin;

use Illuminate\Support\Facades\DB;

class AutorizacionTest extends SuperAdminTestCase
{
    public function test_un_admin_institucional_no_puede_acceder_al_panel_superadmin(): void
    {
        $admin = $this->crearUsuario(1);

        $response = $this->actingAs($admin)->get('/superadmin/instituciones');

        $response->assertForbidden();
    }

    public function test_un_superadmin_con_permisos_accede_al_listado_de_instituciones(): void
    {
        $superadmin = $this->crearSuperAdmin();

        $response = $this->actingAs($superadmin)->get('/superadmin/instituciones');

        $response->assertOk();
    }

    public function test_un_superadmin_sin_el_permiso_puntual_recibe_403(): void
    {
        $superadmin = $this->crearSuperAdmin();

        // Simula una matriz mal poblada: le quita a SuperAdmin el permiso puntual.
        DB::table('permission_role')
            ->where('id_rol', 8)
            ->whereIn('id_permiso', function ($query) {
                $query->select('id_permiso')->from('permissions')->where('slug', 'plataforma.instituciones.ver');
            })
            ->delete();

        $response = $this->actingAs($superadmin)->get('/superadmin/instituciones');

        $response->assertForbidden();
    }

    public function test_usuario_no_autenticado_es_redirigido_al_login(): void
    {
        $response = $this->get('/superadmin/instituciones');

        $response->assertRedirect(route('login'));
    }
}
