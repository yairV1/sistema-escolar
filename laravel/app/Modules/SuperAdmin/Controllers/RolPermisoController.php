<?php

namespace App\Modules\SuperAdmin\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Auditoria\Services\AuditLogger;
use App\Modules\Auth\Models\Permission;
use App\Modules\Auth\Models\Rol;
use App\Modules\SuperAdmin\Policies\RolPermisoPolicy;
use App\Modules\SuperAdmin\Requests\RolPermisoUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Primer editor real de la matriz `permission_role` (docs/arquitectura/03-rbac.md
 * §3-4): hasta ahora esas tablas solo existían sembradas por
 * PermissionRoleSeeder, sin interfaz. Bloquea editar el propio rol
 * SuperAdmin desde aquí — sus permisos `plataforma.*` son fijos por
 * diseño (ver PermissionRoleSeeder), no se gestionan desde este panel
 * para evitar que un SuperAdmin se quite acceso a sí mismo por error.
 */
class RolPermisoController extends Controller
{
    private const ID_ROL_SUPERADMIN = 8;

    public function __construct(private RolPermisoPolicy $policy, private AuditLogger $auditLogger) {}

    public function index(Request $request): View
    {
        abort_unless($this->policy->gestionar($request->user()), 403);

        $roles = Rol::where('id_rol', '!=', self::ID_ROL_SUPERADMIN)->orderBy('id_rol')->get();
        $permisos = Permission::orderBy('modulo')->orderBy('slug')->get()->groupBy('modulo');
        $asignaciones = DB::table('permission_role')->get()->groupBy('id_rol')->map(fn ($filas) => $filas->pluck('id_permiso')->all());

        return view('SuperAdmin.roles.index', [
            'currentPage' => 'SuperAdminRoles',
            'roles' => $roles,
            'permisosPorModulo' => $permisos,
            'asignaciones' => $asignaciones,
        ]);
    }

    public function update(RolPermisoUpdateRequest $request): JsonResponse
    {
        abort_unless($this->policy->gestionar($request->user()), 403);

        $permisosPorRol = collect($request->validated('permisos'))
            ->map(fn ($ids) => array_map('intval', $ids))
            ->reject(fn ($ids, $idRol) => (int) $idRol === self::ID_ROL_SUPERADMIN);

        DB::transaction(function () use ($permisosPorRol) {
            foreach ($permisosPorRol as $idRol => $idsPermisos) {
                $idRol = (int) $idRol;
                $antes = DB::table('permission_role')->where('id_rol', $idRol)->pluck('id_permiso')->all();

                DB::table('permission_role')->where('id_rol', $idRol)->delete();
                if ($idsPermisos !== []) {
                    DB::table('permission_role')->insert(array_map(
                        fn ($idPermiso) => ['id_rol' => $idRol, 'id_permiso' => $idPermiso],
                        $idsPermisos,
                    ));
                }

                if ($antes !== $idsPermisos) {
                    // Cualquier usuario con este rol pierde/gana permisos: su sesión activa se invalida (EnsureSessionFresh).
                    DB::table('usuarios')->where('id_rol', $idRol)->update(['sesion_valida_desde' => now()]);
                    $this->auditLogger->record('rol.permisos.actualizar', new Rol(['id_rol' => $idRol]), ['permisos' => $antes], ['permisos' => $idsPermisos]);
                }
            }
        });

        return response()->json(['success' => true, 'message' => 'Matriz de permisos actualizada.']);
    }

    /**
     * Desactivar un rol bloquea el acceso de todos sus usuarios: invalida
     * sus sesiones activas (EnsureSessionFresh, mismo mecanismo que al
     * cambiar permisos arriba) y, vía Usuario::estaActivo(), les impide
     * volver a iniciar sesión hasta que el rol se reactive.
     */
    public function desactivar(Request $request, Rol $rol): JsonResponse
    {
        abort_unless($this->policy->gestionar($request->user()), 403);
        abort_if($rol->id_rol === self::ID_ROL_SUPERADMIN, 403, 'El rol SuperAdmin no se puede desactivar.');

        $antes = ['estado' => $rol->estado];
        $rol->update(['estado' => 'inactivo']);
        DB::table('usuarios')->where('id_rol', $rol->id_rol)->update(['sesion_valida_desde' => now()]);
        $this->auditLogger->record('rol.desactivar', $rol, $antes, ['estado' => 'inactivo']);

        return response()->json([
            'success' => true,
            'message' => 'Rol desactivado. Los usuarios con este rol perdieron su sesión activa y no podrán volver a iniciar sesión hasta que se reactive.',
        ]);
    }

    public function activar(Request $request, Rol $rol): JsonResponse
    {
        abort_unless($this->policy->gestionar($request->user()), 403);
        abort_if($rol->id_rol === self::ID_ROL_SUPERADMIN, 403);

        $antes = ['estado' => $rol->estado];
        $rol->update(['estado' => 'activo']);
        $this->auditLogger->record('rol.activar', $rol, $antes, ['estado' => 'activo']);

        return response()->json(['success' => true, 'message' => 'Rol reactivado correctamente.']);
    }
}
