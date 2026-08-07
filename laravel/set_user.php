<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$correo = 'yandrey2007@gmail.com';
$usuario = App\Modules\Auth\Models\Usuario::where('correo', $correo)->first();

if (! $usuario) {
    $usuario = new App\Modules\Auth\Models\Usuario();
    $usuario->nombres = 'Yandrey';
    $usuario->apellidos = '';
    $usuario->tipo_documento = 'CC';
    $usuario->numero_documento = '0000000000';
    $usuario->correo = $correo;
    $usuario->password = '';
    $usuario->id_rol = 8;
    $usuario->estado_usuario = 'activo';
}

$usuario->password = Illuminate\Support\Facades\Hash::make('Admin1234!');
$usuario->save();

echo 'OK';
