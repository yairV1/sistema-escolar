<?php

namespace App\Modules\Auth\Models;

use App\Modules\GestionAcademica\Models\AsignacionAcademica;
use App\Modules\Instituciones\Models\Institucion;
use App\Modules\Instituciones\Support\BelongsToInstitucion;
use App\Modules\Instituciones\Support\PerteneceAInstitucion;
use App\Modules\Usuarios\Models\Profesor;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

/**
 * Tabla central de autenticación, heredada del sistema legacy PHP.
 * Mismo hash bcrypt (password_hash/password_verify == Hash::make/Hash::check),
 * así que las cuentas existentes funcionan sin migrar contraseñas.
 */
class Usuario extends Authenticatable
{
    use PerteneceAInstitucion;

    /** Cadena de relaciones hasta la institución (ver PerteneceAInstitucion). */
    public const RUTA_INSTITUCION = '';

    /** Autocompleta id_institucion al crear (Estudiante/Docente/Acudiente/Administrativo) con la del rector/admin autenticado — ver App\Modules\Instituciones\Support\BelongsToInstitucion. SuperAdmin (sin institución) y las altas de InstitucionController (que ya la pasan explícita) quedan intactos. */
    use BelongsToInstitucion;

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
        // id_rol e id_institucion NO son asignables en masa (Etapa 1): se asignan
        // explícitamente con forceCreate()/forceFill() en los servicios autorizados.
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
            get: fn () => $this->foto_perfil ? route('perfil.foto', $this) : null,
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
        return $this->motivoDeBloqueo() === null;
    }

    public const BLOQUEO_CUENTA = 'cuenta';

    public const BLOQUEO_ROL = 'rol';

    public const BLOQUEO_INSTITUCION = 'institucion';

    /**
     * Fase 1 (P1-1/P1-2): por qué esta cuenta no puede operar, o null si puede.
     * Falla cerrado:
     *  - cuenta: estado_usuario distinto de 'activo' (inactivo, bloqueado…);
     *  - rol: inexistente o no 'activo';
     *  - institución: para todo rol institucional, debe existir y estar
     *    'activa' (no 'suspendida' ni 'inactiva'). SuperAdmin no pertenece a
     *    ninguna institución y no se evalúa por ella (capa separada).
     * Las relaciones se cargan una sola vez por instancia (sin consultas repetidas).
     */
    public function motivoDeBloqueo(): ?string
    {
        if ($this->estado_usuario !== 'activo') {
            return self::BLOQUEO_CUENTA;
        }

        if ($this->rol === null || $this->rol->estado !== 'activo') {
            return self::BLOQUEO_ROL;
        }

        if (! $this->esSuperAdmin() && ($this->institucion === null || ! $this->institucion->estaActiva())) {
            return self::BLOQUEO_INSTITUCION;
        }

        return null;
    }

    public static function mensajeDeBloqueo(?string $motivo): string
    {
        return match ($motivo) {
            self::BLOQUEO_INSTITUCION => 'El acceso de tu institución está suspendido. Contacta a la administración del colegio.',
            default => 'Tu cuenta se encuentra inactiva o bloqueada. Contacta al colegio.',
        };
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

    /**
     * Docente: solo sus propias asignaciones. Panel administrativo: solo las
     * asignaciones de SU institución (resuelta por el curso). Antes cualquier
     * Directivo gestionaba asignaciones de cualquier colegio (IDOR, Etapa 0).
     */
    public function puedeGestionarAsignacion(AsignacionAcademica $asignacion): bool
    {
        if ($this->profesor && $this->profesor->id_profesor === $asignacion->id_profesor) {
            return true;
        }

        return $this->tienePanelAdmin()
            && $this->id_institucion !== null
            && (int) $asignacion->curso?->id_institucion === (int) $this->id_institucion;
    }
}
