<?php

namespace App\Modules\Landing\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Colegio\Models\ColegioConfiguracion;
use App\Modules\Landing\Models\LandingContenido;
use App\Modules\Landing\Models\LandingGaleria;
use App\Modules\Landing\Models\LandingNoticia;
use App\Modules\Landing\Requests\LandingGaleriaRequest;
use App\Modules\Landing\Requests\LandingNoticiaRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EditarLandingController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'contenido');
        $tab = in_array($tab, ['contenido', 'noticias', 'galeria'], true) ? $tab : 'contenido';

        return view('Rector.landing.index', [
            'currentPage' => 'EditarLanding',
            'tab' => $tab,
            'contenido' => $tab === 'contenido' ? LandingContenido::pluck('valor', 'clave') : null,
            'colegio' => $tab === 'contenido' ? ColegioConfiguracion::singleton() : null,
            'noticias' => $tab === 'noticias' ? LandingNoticia::orderBy('orden')->get() : null,
            'galeria' => $tab === 'galeria' ? LandingGaleria::orderBy('orden')->get() : null,
        ]);
    }

    public function updateContenido(Request $request): JsonResponse
    {
        $data = $request->validate([
            'valores' => ['required', 'array'],
            'valores.*' => ['nullable', 'string', 'max:2000'],
        ]);

        foreach ($data['valores'] as $clave => $valor) {
            LandingContenido::where('clave', $clave)->update([
                'valor' => $valor,
                'actualizado_en' => now(),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Contenido actualizado correctamente.']);
    }

    public function updateHeroImagen(Request $request): JsonResponse
    {
        $data = $request->validate([
            'imagen' => ['nullable', 'image', 'max:2048'],
            'imagen_removida' => ['nullable', 'boolean'],
        ]);

        $configuracion = ColegioConfiguracion::singleton();

        if ($request->hasFile('imagen')) {
            if ($configuracion->imagen_hero) {
                Storage::disk('public')->delete($configuracion->imagen_hero);
            }
            $configuracion->update(['imagen_hero' => $request->file('imagen')->store('colegio', 'public')]);
        } elseif ($request->boolean('imagen_removida') && $configuracion->imagen_hero) {
            Storage::disk('public')->delete($configuracion->imagen_hero);
            $configuracion->update(['imagen_hero' => null]);
        }

        return response()->json(['success' => true, 'message' => 'Imagen del hero actualizada correctamente.']);
    }

    public function storeNoticia(LandingNoticiaRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store('landing/noticias', 'public');
        }

        $noticia = LandingNoticia::create($data + ['estado' => 'activo']);

        return response()->json(['success' => true, 'message' => 'Noticia creada correctamente.', 'id' => $noticia->id_noticia]);
    }

    public function updateNoticia(LandingNoticiaRequest $request, LandingNoticia $noticia): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('imagen')) {
            if ($noticia->imagen) {
                Storage::disk('public')->delete($noticia->imagen);
            }
            $data['imagen'] = $request->file('imagen')->store('landing/noticias', 'public');
        }

        $noticia->update($data);

        return response()->json(['success' => true, 'message' => 'Noticia actualizada correctamente.']);
    }

    public function desactivarNoticia(LandingNoticia $noticia): JsonResponse
    {
        $noticia->update(['estado' => 'inactivo']);

        return response()->json(['success' => true, 'message' => 'Noticia desactivada correctamente.']);
    }

    public function activarNoticia(LandingNoticia $noticia): JsonResponse
    {
        $noticia->update(['estado' => 'activo']);

        return response()->json(['success' => true, 'message' => 'Noticia reactivada correctamente.']);
    }

    public function storeGaleria(LandingGaleriaRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['imagen'] = $request->file('imagen')->store('landing/galeria', 'public');

        $foto = LandingGaleria::create($data + ['estado' => 'activo']);

        return response()->json(['success' => true, 'message' => 'Foto agregada correctamente.', 'id' => $foto->id_foto]);
    }

    public function updateGaleria(LandingGaleriaRequest $request, LandingGaleria $galeria): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('imagen')) {
            if ($galeria->imagen) {
                Storage::disk('public')->delete($galeria->imagen);
            }
            $data['imagen'] = $request->file('imagen')->store('landing/galeria', 'public');
        }

        $galeria->update($data);

        return response()->json(['success' => true, 'message' => 'Foto actualizada correctamente.']);
    }

    public function desactivarGaleria(LandingGaleria $galeria): JsonResponse
    {
        $galeria->update(['estado' => 'inactivo']);

        return response()->json(['success' => true, 'message' => 'Foto desactivada correctamente.']);
    }

    public function activarGaleria(LandingGaleria $galeria): JsonResponse
    {
        $galeria->update(['estado' => 'activo']);

        return response()->json(['success' => true, 'message' => 'Foto reactivada correctamente.']);
    }
}
