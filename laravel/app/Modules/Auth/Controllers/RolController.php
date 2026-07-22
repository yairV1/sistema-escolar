<?php

namespace App\Modules\Auth\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Auth\Models\Rol;
use App\Modules\Auth\Models\Usuario;
use App\Modules\Auth\Requests\RolRequest;
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
            'usuariosPorRol' => Usuario::selectRaw('id_rol, count(*) as total')
                ->groupBy('id_rol')
                ->pluck('total', 'id_rol'),
        ]);
    }

    public function update(RolRequest $request, Rol $rol): JsonResponse
    {
        try {
            $rol->update($request->validated());
        } catch (QueryException $e) {
            return $this->respuestaDuplicado($e, 'Ya existe un rol con ese nombre.');
        }

        return response()->json(['success' => true, 'message' => 'Rol actualizado correctamente.']);
    }

    public function desactivar(Rol $rol): JsonResponse
    {
        $rol->update(['estado' => 'inactivo']);

        return response()->json(['success' => true, 'message' => 'Rol desactivado correctamente.']);
    }

    public function activar(Rol $rol): JsonResponse
    {
        $rol->update(['estado' => 'activo']);

        return response()->json(['success' => true, 'message' => 'Rol reactivado correctamente.']);
    }

    private function respuestaDuplicado(QueryException $e, string $mensaje): JsonResponse
    {
        if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
            return response()->json(['success' => false, 'message' => $mensaje], 409);
        }

        throw $e;
    }
}
