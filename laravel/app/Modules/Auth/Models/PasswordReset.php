<?php

namespace App\Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tokens de un solo uso para recuperación de contraseña.
 * Solo se persiste el hash SHA-256 del token; el token en texto plano
 * únicamente viaja en el enlace del correo. Réplica 1:1 del comportamiento
 * de app/models/PasswordReset.php del sistema legacy (esquema propio,
 * no el password_reset_tokens por defecto de Laravel).
 */
class PasswordReset extends Model
{
    protected $table = 'password_resets';

    protected $primaryKey = 'id_reset';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'token_hash',
        'expira_en',
        'usado',
    ];

    protected $casts = [
        'usado' => 'boolean',
        'expira_en' => 'datetime',
    ];

    /** Invalida los tokens pendientes del usuario y crea uno nuevo (expira en 30 min). */
    public static function generarPara(Usuario $usuario): string
    {
        static::where('id_usuario', $usuario->id_usuario)
            ->where('usado', false)
            ->update(['usado' => true]);

        $token = bin2hex(random_bytes(32));

        static::create([
            'id_usuario' => $usuario->id_usuario,
            'token_hash' => hash('sha256', $token),
            'expira_en' => now()->addMinutes(30),
            'usado' => false,
        ]);

        return $token;
    }

    public static function validoPorToken(string $token): ?self
    {
        return static::where('token_hash', hash('sha256', $token))
            ->where('usado', false)
            ->where('expira_en', '>', now())
            ->first();
    }

    public function marcarUsado(): void
    {
        $this->update(['usado' => true]);
    }
}
