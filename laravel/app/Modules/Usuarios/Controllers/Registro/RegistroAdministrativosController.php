<?php

namespace App\Modules\Usuarios\Controllers\Registro;

use App\Core\Http\Controllers\Controller;
use App\Modules\Auth\Models\Usuario;
use App\Modules\Usuarios\Requests\Registro\AdministrativoStoreRequest;
use App\Modules\Usuarios\Requests\Registro\AdministrativoUpdateRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegistroAdministrativosController extends Controller
{
    public function create(): View
    {
        return view('Rector.usuarios.registro.administrativos', ['currentPage' => 'RegistroAdministrativos', 'admin' => null]);
    }

    public function store(AdministrativoStoreRequest $request): JsonResponse
    {
        $d = $request->validated();

        try {
            $usuario = Usuario::create([
                'nombres' => $d['nombres'],
                'apellidos' => $d['apellidos'],
                'tipo_documento' => $d['tipo_documento'],
                'numero_documento' => $d['numero_documento'],
                'correo' => $d['correo'],
                'telefono' => $d['telefono'] ?? null,
                'password' => Hash::make($d['numero_documento']),
                'id_rol' => $d['id_rol'],
            ]);
        } catch (QueryException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un usuario con ese documento o correo.',
                ], 409);
            }

            throw $e;
        }

        return response()->json([
            'success' => true,
            'message' => 'Usuario administrativo registrado correctamente.',
            'id' => $usuario->id_usuario,
        ]);
    }

    public function edit(Usuario $usuario): View
    {
        return view('Rector.usuarios.registro.administrativos', ['currentPage' => 'RegistroAdministrativos', 'admin' => $usuario]);
    }

    public function update(AdministrativoUpdateRequest $request, Usuario $usuario): JsonResponse
    {
        $d = $request->validated();

        try {
            $usuario->update([
                'nombres' => $d['nombres'],
                'apellidos' => $d['apellidos'],
                'tipo_documento' => $d['tipo_documento'],
                'numero_documento' => $d['numero_documento'],
                'correo' => $d['correo'],
                'telefono' => $d['telefono'] ?? null,
                'id_rol' => $d['id_rol'],
            ]);
        } catch (QueryException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un usuario con ese documento o correo.',
                ], 409);
            }

            throw $e;
        }

        return response()->json([
            'success' => true,
            'message' => 'Usuario administrativo actualizado correctamente.',
            'id' => $usuario->id_usuario,
        ]);
    }
}
