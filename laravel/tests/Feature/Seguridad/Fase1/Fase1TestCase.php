<?php

namespace Tests\Feature\Seguridad\Fase1;

use App\Modules\Auth\Models\Usuario;
use App\Modules\Instituciones\Models\Institucion;
use Database\Factories\InstitucionFactory;
use Database\Factories\UsuarioFactory;
use Database\Seeders\PermissionRoleSeeder;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Base de las regresiones de la Fase 1 (docs/seguridad/fase1-contencion.md).
 * Solo datos ficticios; SQLite en memoria (o colegio_tests), verificado por
 * GuardiaBaseDePruebas antes de RefreshDatabase.
 */
abstract class Fase1TestCase extends TestCase
{
    use RefreshDatabase;

    protected const CLAVE = 'Clave-Ficticia-123';

    protected function setUp(): void
    {
        parent::setUp();
        (new RolesSeeder)->run();
        (new PermissionsSeeder)->run();
        (new PermissionRoleSeeder)->run();
    }

    protected function institucion(string $slug, string $estado = 'activa'): Institucion
    {
        return InstitucionFactory::new()->create(['nombre' => "Colegio {$slug}", 'slug' => $slug, 'estado' => $estado]);
    }

    protected function usuario(int $idRol, ?Institucion $institucion, array $extra = []): Usuario
    {
        return UsuarioFactory::new()->create(array_merge([
            'password' => Hash::make(self::CLAVE),
            'id_rol' => $idRol,
            'id_institucion' => $institucion?->id_institucion,
            'estado_usuario' => 'activo',
        ], $extra));
    }

    protected function iniciarSesion(Usuario $usuario, bool $recordar = false)
    {
        return $this->postJson('/login', ['usuario' => $usuario->correo, 'password' => self::CLAVE, 'remember' => $recordar]);
    }

    /** Simula otro navegador: sin cookies, sin sesión y sin usuario en memoria. */
    protected function nuevoNavegador(): void
    {
        $this->defaultCookies = [];
        $this->unencryptedCookies = [];
        Auth::forgetGuards();
        $this->flushSession();
    }

    protected function cookieRecordarme(Usuario $usuario, ?string $token = null): array
    {
        $usuario->refresh();

        return [Auth::guard('web')->getRecallerName(), $usuario->id_usuario.'|'.($token ?? $usuario->remember_token).'|'.$usuario->password];
    }
}
