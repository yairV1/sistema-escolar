<?php

namespace App\Modules\Perfil\Controllers;

use App\Core\Archivos\ArchivoPrivado;
use App\Core\Http\Controllers\Controller;
use App\Modules\Auth\Models\Usuario;
use App\Modules\Auth\Services\CambioDeContrasena;
use App\Shared\Fixtures\PortalFamiliaFixtures;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PerfilController extends Controller
{
    public function foto(Request $request, Usuario $usuario, ArchivoPrivado $archivos): BinaryFileResponse
    {
        $actor = $request->user();
        $esPropia = $actor->id_usuario === $usuario->id_usuario;
        $mismaInstitucion = ! $actor->esSuperAdmin()
            && $actor->id_institucion !== null
            && (int) $actor->id_institucion === (int) $usuario->id_institucion;

        abort_unless($esPropia || $mismaInstitucion, 404);
        abort_unless($usuario->foto_perfil, 404);

        return $archivos->inline($usuario->foto_perfil, 'perfiles');
    }

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
            $data['foto_perfil'] = $request->file('foto_perfil')->store('perfiles', 'private');
        } elseif ($request->boolean('foto_perfil_removido') && $usuario->foto_perfil) {
            $data['foto_perfil'] = null;
        }

        $usuario->update($data);

        return response()->json(['success' => true, 'message' => 'Perfil actualizado correctamente.']);
    }

    public function updatePassword(Request $request, CambioDeContrasena $cambio): JsonResponse
    {
        $usuario = auth()->user();

        $data = $request->validate([
            'password_actual' => ['required', 'string'],
            'password_nueva' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($data['password_actual'], $usuario->password)) {
            return response()->json(['success' => false, 'message' => 'La contraseña actual no es correcta.'], 422);
        }

        // Auditado + invalida las demás sesiones y cookies «recordarme» (incidente 2026-11-19).
        $cambio->aplicar($usuario, $data['password_nueva'], CambioDeContrasena::ORIGEN_PERFIL);

        // Esta sesión sigue válida: nuevo id y marca de autenticación = momento del cambio.
        $request->session()->regenerate();
        $request->session()->put('auth_at', $usuario->sesion_valida_desde->timestamp);

        return response()->json(['success' => true, 'message' => 'Contraseña actualizada correctamente. Se cerraron las demás sesiones abiertas.']);
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
