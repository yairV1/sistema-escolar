<?php

namespace Tests\Feature\SuperAdmin;

use App\Modules\Auth\Models\Usuario;
use Database\Seeders\PermissionRoleSeeder;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

abstract class SuperAdminTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        $this->seed(PermissionsSeeder::class);
        $this->seed(PermissionRoleSeeder::class);
    }

    protected function crearUsuario(int $idRol, ?int $idInstitucion = 1): Usuario
    {
        static $contador = 0;
        $contador++;

        return Usuario::create([
            'nombres' => 'Test',
            'apellidos' => "Usuario{$contador}",
            'tipo_documento' => 'CC',
            'numero_documento' => "test-doc-{$contador}",
            'correo' => "test{$contador}@ejemplo.com",
            'password' => Hash::make('password123'),
            'id_rol' => $idRol,
            'id_institucion' => $idInstitucion,
            'estado_usuario' => 'activo',
        ]);
    }

    protected function crearSuperAdmin(): Usuario
    {
        return $this->crearUsuario(8, null);
    }
}
