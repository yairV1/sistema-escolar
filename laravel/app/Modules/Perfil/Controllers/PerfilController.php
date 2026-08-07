<?php

namespace App\Modules\Perfil\Controllers;

use App\Core\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PerfilController extends Controller
{
    public function show(): View
    {
        $usuario = auth()->user();

        $vista = $usuario->esSuperAdmin() ? 'SuperAdmin.perfil.index' : 'Rector.perfil.index';

        return view($vista, [
            'currentPage' => 'Perfil',
            'usuario' => $usuario,
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
            'foto_perfil' => ['nullable', 'image', 'max:2048'],
        ]);
        unset($data['foto_perfil']);

        if ($request->hasFile('foto_perfil')) {
            if ($usuario->foto_perfil) {
                Storage::disk('public')->delete($usuario->foto_perfil);
            }
            $data['foto_perfil'] = $request->file('foto_perfil')->store('perfiles', 'public');
        } elseif ($request->boolean('foto_removida') && $usuario->foto_perfil) {
            Storage::disk('public')->delete($usuario->foto_perfil);
            $data['foto_perfil'] = null;
        }

        $usuario->update($data);

        return response()->json(['success' => true, 'message' => 'Perfil actualizado correctamente.', 'foto_perfil_url' => $usuario->fresh()->fotoPerfilUrl]);
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
