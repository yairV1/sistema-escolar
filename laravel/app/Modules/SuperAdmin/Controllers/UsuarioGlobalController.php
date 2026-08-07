<?php

namespace App\Modules\SuperAdmin\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Auditoria\Services\AuditLogger;
use App\Modules\Auth\Models\Rol;
use App\Modules\Auth\Models\Usuario;
use App\Modules\Instituciones\Models\Institucion;
use App\Modules\SuperAdmin\Policies\UsuarioGlobalPolicy;
use App\Modules\SuperAdmin\Requests\UsuarioGlobalCambiarRolRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Gestión de usuarios de todas las instituciones. UsuarioGlobalPolicy se
 * invoca manualmente (no vía $this->authorize()) — ver justificación en la
 * propia Policy.
 */
class UsuarioGlobalController extends Controller
{
    public function __construct(
        private UsuarioGlobalPolicy $policy,
        private AuditLogger $auditLogger,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($this->policy->viewAny($request->user()), 403);

        $usuarios = Usuario::query()
            ->with(['rol', 'institucion'])
            ->where('id_rol', '!=', 8)
            ->when($request->filled('buscar'), fn ($q) => $q->where(fn ($qq) => $qq
                ->where('nombres', 'like', '%'.$request->query('buscar').'%')
                ->orWhere('apellidos', 'like', '%'.$request->query('buscar').'%')
                ->orWhere('correo', 'like', '%'.$request->query('buscar').'%')
            ))
            ->when($request->filled('id_institucion'), fn ($q) => $q->where('id_institucion', $request->query('id_institucion')))
            ->when($request->filled('id_rol'), fn ($q) => $q->where('id_rol', $request->query('id_rol')))
            ->when($request->filled('estado'), fn ($q) => $q->where('estado_usuario', $request->query('estado')))
            ->orderBy('nombres')
            ->paginate(20)
            ->withQueryString();

        return view('SuperAdmin.usuarios.index', [
            'currentPage' => 'SuperAdminUsuarios',
            'usuarios' => $usuarios,
            'instituciones' => Institucion::orderBy('nombre')->get(['id_institucion', 'nombre']),
            'roles' => Rol::where('id_rol', '!=', 8)->orderBy('id_rol')->get(),
            'filtros' => $request->only(['buscar', 'id_institucion', 'id_rol', 'estado']),
        ]);
    }

    public function cambiarRol(UsuarioGlobalCambiarRolRequest $request, Usuario $usuario): JsonResponse
    {
        abort_unless($this->policy->cambiarRol($request->user()), 403);

        $antes = ['id_rol' => $usuario->id_rol];
        $usuario->forceFill([
            'id_rol' => $request->integer('id_rol'),
            'sesion_valida_desde' => now(),
        ])->save();

        $this->auditLogger->record('usuario.cambiar_rol', $usuario, $antes, ['id_rol' => $usuario->id_rol]);

        return response()->json(['success' => true, 'message' => 'Rol actualizado. La sesión activa del usuario se invalidará.']);
    }

    public function cambiarEstado(Request $request, Usuario $usuario): JsonResponse
    {
        abort_unless($this->policy->cambiarEstado($request->user()), 403);

        $nuevoEstado = $usuario->estado_usuario === 'activo' ? 'inactivo' : 'activo';
        $antes = ['estado_usuario' => $usuario->estado_usuario];

        $usuario->forceFill([
            'estado_usuario' => $nuevoEstado,
            'sesion_valida_desde' => now(),
        ])->save();

        $this->auditLogger->record('usuario.cambiar_estado', $usuario, $antes, ['estado_usuario' => $nuevoEstado]);

        return response()->json(['success' => true, 'message' => "Usuario marcado como {$nuevoEstado}."]);
    }
}
