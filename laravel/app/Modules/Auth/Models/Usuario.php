<?php

namespace App\Modules\Auth\Models;

use App\Modules\GestionAcademica\Models\AsignacionAcademica;
use App\Modules\Usuarios\Models\Profesor;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Tabla central de autenticación, heredada del sistema legacy PHP.
 * Mismo hash bcrypt (password_hash/password_verify == Hash::make/Hash::check),
 * así que las cuentas existentes funcionan sin migrar contraseñas.
 */
class Usuario extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuarios';

    protected $primaryKey = 'id_usuario';

    public $timestamps = false;

    protected $fillable = [
        'nombres',
        'apellidos',
        'tipo_documento',
        'numero_documento',
        'correo',
        'telefono',
        'password',
        'id_rol',
        'estado_usuario',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Mapeo id_rol -> slug, heredado tal cual de app/helpers/Auth.php
     * del sistema legacy (no viene de la tabla `roles`).
     */
    private const ROLE_SLUGS = [
        1 => 'admin',
        2 => 'rector',
        3 => 'coordinador',
        4 => 'secretario',
        5 => 'docente',
        6 => 'estudiante',
        7 => 'acudiente',
    ];

    public const ROLES_PANEL_ADMIN = ['admin', 'rector'];

    private const ROLE_LABELS = [
        'admin' => 'Administrador',
        'rector' => 'Directivo',
        'coordinador' => 'Coordinador',
        'secretario' => 'Secretario',
        'docente' => 'Profesor',
        'estudiante' => 'Estudiante',
        'acudiente' => 'Acudiente',
    ];

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    public function profesor(): HasOne
    {
        return $this->hasOne(Profesor::class, 'id_usuario', 'id_usuario');
    }

    protected function rolSlug(): Attribute
    {
        return Attribute::make(
            get: fn () => self::ROLE_SLUGS[$this->id_rol] ?? 'desconocido',
        );
    }

    protected function rolLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => self::ROLE_LABELS[$this->rolSlug] ?? 'Panel',
        );
    }

    public function tienePanelAdmin(): bool
    {
        return in_array($this->rolSlug, self::ROLES_PANEL_ADMIN, true);
    }

    /** Los 7 slugs de rol válidos — usado para validar entradas dinámicas (ej. permisos por categoría). */
    public static function allRoleSlugs(): array
    {
        return array_values(self::ROLE_SLUGS);
    }

    public function estaActivo(): bool
    {
        return $this->estado_usuario === 'activo';
    }

    public function puedeGestionarAsignacion(AsignacionAcademica $asignacion): bool
    {
        return $this->tienePanelAdmin() || $this->profesor?->id_profesor === $asignacion->id_profesor;
    }
}
