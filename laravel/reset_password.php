<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$usuario = App\Modules\Auth\Models\Usuario::where('correo', 'superadmin@plataforma.local')->first();
if ($usuario) {
    $usuario->password = Illuminate\Support\Facades\Hash::make('Admin1234!');
    $usuario->save();
    echo "OK";
} else {
    echo "NO_USER";
}
