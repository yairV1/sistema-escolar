<?php

namespace App\Modules\Auth\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Auth\Models\Rol;
use App\Modules\Auth\Models\Usuario;
use App\Modules\Auth\Requests\RolRequest;
use App\Modules\Auth\Services\RevocacionDeSesiones;
use App\Modules\Instituciones\Support\TenantContext;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class RolController extends Controller
{
    public function index(): View
    {
        return view('Rector.roles.index', [
            'currentPage' => 'Roles',
            'roles' => Rol::orderBy('id_rol')->get(),
            // Conteo solo de la propia institución (antes contaba usuarios de todos los colegios).
            'usuariosPorRol' => Usuario::selectRaw('id_rol, count(*) as total')
                ->where('id_institucion', TenantContext::idActual() ?? 0)
                ->groupBy('id_rol')
                ->pluck('total', 'id_rol'),
            'soloLectura' => ! $this->puedeAdministrarCatalogo(),
        ]);
    }

    public function update(RolRequest $request, Rol $rol): JsonResponse
    {
        if ($denegado = $this->denegarSiNoEsPlataforma()) {
            return $denegado;
        }

        try {
            $rol->update($request->validated());
        } catch (QueryException $e) {
            return $this->respuestaDuplicado($e, 'Ya existe un rol con ese nombre.');
        }

        return response()->json(['success' => true, 'message' => 'Rol actualizado correctamente.']);
    }

    public function desactivar(Rol $rol): JsonResponse
    {
        if ($denegado = $this->denegarSiNoEsPlataforma()) {
            return $denegado;
        }

        $rol->update(['estado' => 'inactivo']);
        app(RevocacionDeSesiones::class)->rol((int) $rol->id_rol);

        return response()->json(['success' => true, 'message' => 'Rol desactivado correctamente.']);
    }

    public function activar(Rol $rol): JsonResponse
    {
        if ($denegado = $this->denegarSiNoEsPlataforma()) {
            return $denegado;
        }

        $rol->update(['estado' => 'activo']);

        return response()->json(['success' => true, 'message' => 'Rol reactivado correctamente.']);
    }

    /**
     * El catálogo de roles es GLOBAL: renombrar o (des)activar un rol afecta a
     * todas las instituciones (y Usuario::estaActivo() bloquea el login de
     * todos los usuarios de un rol inactivo). Solo la plataforma (SuperAdmin,
     * desde superadmin/roles) puede modificarlo; el panel institucional queda
     * en solo lectura.
     */
    private function puedeAdministrarCatalogo(): bool
    {
        $usuario = auth()->user();

        return $usuario !== null
            && $usuario->esSuperAdmin()
            && $usuario->hasPermission('plataforma.roles.gestionar');
    }

    private function denegarSiNoEsPlataforma(): ?JsonResponse
    {
        if ($this->puedeAdministrarCatalogo()) {
            return null;
        }

        return response()->json([
            'success' => false,
            'message' => 'Los roles son comunes a toda la plataforma: solo el SuperAdmin puede modificarlos.',
        ], 403);
    }

    private function respuestaDuplicado(QueryException $e, string $mensaje): JsonResponse
    {
        if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
            return response()->json(['success' => false, 'message' => $mensaje], 409);
        }

        throw $e;
    }
}
