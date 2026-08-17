<?php

namespace App\Modules\Perfil\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Shared\Fixtures\PortalFamiliaFixtures;
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

        $datosFamilia = match ($usuario->rolSlug) {
            'estudiante' => ['acudiente' => PortalFamiliaFixtures::acudienteDe()],
            'acudiente' => ['hijos' => PortalFamiliaFixtures::hijos()->map(fn ($h) => (object) array_merge((array) $h, [
                'parentesco' => 'Madre',
                'es_principal' => $h->id === 1,
            ]))],
            default => [],
        };

        return view('Perfil.index', [
            'currentPage' => 'Perfil',
            'usuario' => $usuario,
            'dosFactoresActivo' => $usuario->tieneDosFactoresActivos(),
            ...$datosFamilia,
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
            'foto_perfil_removido' => ['nullable', 'boolean'],
        ]);
        unset($data['foto_perfil'], $data['foto_perfil_removido']);

        if ($request->hasFile('foto_perfil')) {
            if ($usuario->foto_perfil) {
                Storage::disk('public')->delete($usuario->foto_perfil);
            }
            $data['foto_perfil'] = $request->file('foto_perfil')->store('perfiles', 'public');
        } elseif ($request->boolean('foto_perfil_removido') && $usuario->foto_perfil) {
            Storage::disk('public')->delete($usuario->foto_perfil);
            $data['foto_perfil'] = null;
        }

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

    /**
     * Validación `sometimes` a propósito: este mismo endpoint lo usan tanto
     * el formulario completo de la pestaña Configuración (idioma+tema+
     * notificaciones) como el toggle de sol/luna de la topbar, que solo
     * manda `tema` — ver resources/js/core/theme.js.
     */
    public function updatePreferencias(Request $request): JsonResponse
    {
        $data = $request->validate([
            'idioma' => ['sometimes', 'in:es,en'],
            'tema' => ['sometimes', 'nullable', 'in:light,dark'],
            'notificaciones_email' => ['sometimes', 'boolean'],
            'color_acento' => ['sometimes', 'nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        auth()->user()->update($data);

        return response()->json(['success' => true, 'message' => 'Preferencias actualizadas correctamente.']);
    }
}
