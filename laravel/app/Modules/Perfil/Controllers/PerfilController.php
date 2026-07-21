<?php

namespace App\Modules\Perfil\Controllers;

use App\Core\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PerfilController extends Controller
{
    public function show(): View
    {
        return view('Rector.perfil.index', [
            'currentPage' => 'Perfil',
            'usuario' => auth()->user(),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $usuario = auth()->user();

        $data = $request->validate([
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'correo' => ['required', 'email', 'max:150', Rule::unique('usuarios', 'correo')->ignore($usuario->id_usuario, 'id_usuario')],
        ]);

        $usuario->update($data);

        return response()->json(['success' => true, 'message' => 'Perfil actualizado correctamente.']);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $usuario = auth()->user();

        $data = $request->validate([
            'password_actual' => ['required', 'string'],
            'password_nueva' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($data['password_actual'], $usuario->password)) {
            return response()->json(['success' => false, 'message' => 'La contraseña actual no es correcta.'], 422);
        }

        $usuario->update(['password' => Hash::make($data['password_nueva'])]);

        return response()->json(['success' => true, 'message' => 'Contraseña actualizada correctamente.']);
    }
}
