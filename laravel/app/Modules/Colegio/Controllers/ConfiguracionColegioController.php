<?php

namespace App\Modules\Colegio\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Colegio\Models\ColegioConfiguracion;
use App\Modules\Colegio\Models\ColegioImagen;
use App\Modules\Colegio\Requests\ColegioImagenRequest;
use App\Modules\Colegio\Requests\ConfiguracionColegioRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ConfiguracionColegioController extends Controller
{
    private const MAX_IMAGENES = 12;

    public function index(): View
    {
        $configuracion = ColegioConfiguracion::singleton();

        return view('Rector.configuracion-colegio.index', [
            'currentPage' => 'ConfiguracionColegio',
            'configuracion' => $configuracion,
            'imagenes' => $configuracion->imagenes,
            'tipos' => ColegioImagen::TIPOS_LABELS,
        ]);
    }

    public function update(ConfiguracionColegioRequest $request): JsonResponse
    {
        $configuracion = ColegioConfiguracion::singleton();
        $data = $request->safe()->except(['logo', 'logo_removido']);

        if ($request->hasFile('logo')) {
            if ($configuracion->logo) {
                Storage::disk('public')->delete($configuracion->logo);
            }
            $data['logo'] = $request->file('logo')->store('colegio', 'public');
        } elseif ($request->boolean('logo_removido') && $configuracion->logo) {
            Storage::disk('public')->delete($configuracion->logo);
            $data['logo'] = null;
        }

        $configuracion->update($data);

        // El sidebar del panel institucional muestra el logo de la Institucion
        // del usuario logueado (modelo multi-tenant), no el de este singleton
        // legacy — sin este sync, subir/quitar el logo acá nunca se reflejaba
        // ahí y parecía que el logo se había quedado "pegado".
        if (array_key_exists('logo', $data)) {
            auth()->user()?->institucion?->update(['logo' => $data['logo']]);
        }

        return response()->json(['success' => true, 'message' => 'Configuración actualizada correctamente.']);
    }

    public function storeImagen(ColegioImagenRequest $request): JsonResponse
    {
        $configuracion = ColegioConfiguracion::singleton();

        if ($configuracion->imagenes()->count() >= self::MAX_IMAGENES) {
            return response()->json([
                'success' => false,
                'message' => 'Ya alcanzaste el máximo de '.self::MAX_IMAGENES.' imágenes en la galería.',
            ], 422);
        }

        $data = $request->validated();
        $data['imagen'] = $request->file('imagen')->store('colegio/galeria', 'public');
        $data['orden'] = $configuracion->imagenes()->max('orden') + 1;

        $imagen = $configuracion->imagenes()->create($data);

        return response()->json(['success' => true, 'message' => 'Imagen agregada correctamente.', 'id' => $imagen->id_imagen]);
    }

    public function destroyImagen(ColegioImagen $imagen): JsonResponse
    {
        Storage::disk('public')->delete($imagen->imagen);
        $imagen->delete();

        return response()->json(['success' => true, 'message' => 'Imagen eliminada correctamente.']);
    }

    public function reordenarImagenes(Request $request): JsonResponse
    {
        $data = $request->validate([
            'orden' => ['required', 'array'],
            'orden.*' => ['integer', 'exists:colegio_imagenes,id_imagen'],
        ]);

        foreach ($data['orden'] as $posicion => $idImagen) {
            ColegioImagen::where('id_imagen', $idImagen)->update(['orden' => $posicion + 1]);
        }

        return response()->json(['success' => true, 'message' => 'Orden actualizado correctamente.']);
    }
}
