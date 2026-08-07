<?php

namespace Database\Seeders;

use App\Modules\Auth\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Crea el primer usuario SuperAdmin (id_rol=8, id_institucion=null — no
 * pertenece a ninguna institución, opera la plataforma completa) para que
 * el panel tenga con quién entrar apenas se instala el sistema. Idempotente
 * por correo: correr el seeder de nuevo no duplica ni resetea la
 * contraseña de una cuenta ya existente.
 *
 * La contraseña generada aquí debe cambiarse en el primer inicio de sesión
 * — se imprime una sola vez en consola, nunca se guarda en texto plano.
 */
class SuperAdminInicialSeeder extends Seeder
{
    public function run(): void
    {
        $correo = 'superadmin@plataforma.local';

        if (Usuario::where('correo', $correo)->exists()) {
            return;
        }

        $passwordTemporal = str()->password(16);

        Usuario::create([
            'nombres' => 'Super',
            'apellidos' => 'Admin',
            'tipo_documento' => 'CC',
            'numero_documento' => '00000000',
            'correo' => $correo,
            'password' => Hash::make($passwordTemporal),
            'id_rol' => 8,
            'id_institucion' => null,
            'estado_usuario' => 'activo',
        ]);

        if ($this->command) {
            $this->command->warn("SuperAdmin creado: {$correo} / contraseña temporal: {$passwordTemporal}");
            $this->command->warn('Cambia esta contraseña inmediatamente después del primer inicio de sesión.');
        }
    }
}
