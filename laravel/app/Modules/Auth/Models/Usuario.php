<?php

namespace App\Modules\Auth\Models;

use App\Modules\GestionAcademica\Models\AsignacionAcademica;
use App\Modules\Instituciones\Models\Institucion;
use App\Modules\Instituciones\Support\BelongsToInstitucion;
use App\Modules\Usuarios\Models\Profesor;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * Tabla central de autenticación, heredada del sistema legacy PHP.
 * Mismo hash bcrypt (password_hash/password_verify == Hash::make/Hash::check),
 * así que las cuentas existentes funcionan sin migrar contraseñas.
 */
class Usuario extends Authenticatable
{
    use Notifiable;
    /** Autocompleta id_institucion al crear (Estudiante/Docente/Acudiente/Administrativo) con la del rector/admin autenticado — ver App\Modules\Instituciones\Support\BelongsToInstitucion. SuperAdmin (sin institución) y las altas de InstitucionController (que ya la pasan explícita) quedan intactos. */
    use BelongsToInstitucion;

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
        'id_institucion',
        'estado_usuario',
        'foto_perfil',
        'idioma',
        'notificaciones_email',
        'tema',
        'color_acento',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'two_factor_secret' => 'encrypted',
        'two_factor_recovery_codes' => 'encrypted:array',
        'two_factor_confirmed_at' => 'datetime',
        'sesion_valida_desde' => 'datetime',
        'notificaciones_email' => 'boolean',
    ];

    private ?Collection $permisosEfectivosCache = null;

    /**
     * Mapeo id_rol -> slug, heredado tal cual de app/helpers/Auth.php
     * del sistema legacy (no viene de la tabla `roles`). id_rol=8
     * (superadmin) es la excepción: se suma en la Fase A de la plataforma
     * multi-tenant (docs/arquitectura/10-superadmin-plataforma.md), no
     * viene del legacy.
     */
    private const ROLE_SLUGS = [
        1 => 'admin',
        2 => 'rector',
        3 => 'coordinador',
        4 => 'secretario',
        5 => 'docente',
        6 => 'estudiante',
        7 => 'acudiente',
        8 => 'superadmin',
    ];

    public const ROLES_PANEL_ADMIN = ['admin', 'rector'];

    /** SuperAdmin nunca es parte de ROLES_PANEL_ADMIN: es una capa de autorización separada (EnsureSuperAdmin), no el panel institucional. */
    public const ROL_SUPERADMIN = 'superadmin';

    private const ROLE_LABELS = [
        'admin' => 'Administrador',
        'rector' => 'Directivo',
        'coordinador' => 'Coordinador',
        'secretario' => 'Secretaría',
        'docente' => 'Profesor',
        'estudiante' => 'Estudiante',
        'acudiente' => 'Acudiente',
        'superadmin' => 'SuperAdmin',
    ];

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class, 'id_institucion', 'id_institucion');
    }

    /** Mismo patrón que Institucion::logoUrl(). Null si el usuario no cargó foto — el sidebar cae a las iniciales. */
    protected function fotoPerfilUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->foto_perfil ? Storage::disk('public')->url($this->foto_perfil) : null,
        );
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

    public function esSuperAdmin(): bool
    {
        return $this->rolSlug === self::ROL_SUPERADMIN;
    }

    /** Los 8 slugs de rol válidos — usado para validar entradas dinámicas (ej. permisos por categoría). */
    public static function allRoleSlugs(): array
    {
        return array_values(self::ROLE_SLUGS);
    }

    public static function superAdmins(): Collection
    {
        $idRol = array_search(self::ROL_SUPERADMIN, self::ROLE_SLUGS, true);

        return self::where('id_rol', $idRol)->get();
    }

    /**
     * También bloquea cuentas cuyo rol fue desactivado desde SuperAdmin
     * (Rol::estado) — antes solo se consultaba estado_usuario, así que un
     * rol "inactivo" no impedía iniciar sesión a nadie. Único punto de
     * verdad: login (LoginController), EnsureSuperAdmin y recuperación de
     * contraseña (PasswordResetController) ya pasan todos por aquí.
     */
    public function estaActivo(): bool
    {
        return $this->estado_usuario === 'activo' && $this->rol?->estado !== 'inactivo';
    }

    /**
     * Primera implementación real del catálogo permissions/permission_role
     * (docs/arquitectura/03-rbac.md §7.2, EnsurePermission). Cacheada en la
     * instancia porque una misma petición puede consultar varios permisos
     * (middleware de ruta + Policy del recurso).
     */
    public function hasPermission(string $slug): bool
    {
        return $this->permisosEfectivos()->contains($slug);
    }

    private function permisosEfectivos(): Collection
    {
        return $this->permisosEfectivosCache ??= $this->rol?->permissions()->pluck('slug') ?? collect();
    }

    public function tieneDosFactoresActivos(): bool
    {
        return $this->two_factor_confirmed_at !== null;
    }

    public function puedeGestionarAsignacion(AsignacionAcademica $asignacion): bool
    {
        return $this->tienePanelAdmin() || $this->profesor?->id_profesor === $asignacion->id_profesor;
    }
}
