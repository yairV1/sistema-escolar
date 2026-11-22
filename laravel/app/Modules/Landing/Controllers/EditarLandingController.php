<?php

namespace App\Modules\Landing\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Instituciones\Models\Institucion;
use App\Modules\Instituciones\Support\TenantContext;
use App\Modules\Landing\Models\LandingContenido;
use App\Modules\Landing\Models\LandingGaleria;
use App\Modules\Landing\Models\LandingNoticia;
use App\Modules\Landing\Requests\LandingGaleriaRequest;
use App\Modules\Landing\Requests\LandingNoticiaRequest;
use App\Modules\Landing\Support\SitioInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Editor de la página pública — Etapa 2 · B7: cada colegio edita SOLO su sitio.
 * La institución sale de la sesión. Antes cualquier rector editaba el sitio único.
 *
 *  Esquema expandido: textos por (id_institucion, clave) — solo claves conocidas —,
 *    noticias y fotos con id_institucion de la sesión; lo ajeno → 404.
 *  Esquema antiguo: con una institución, como antes; con varias → 409 (el sitio
 *    compartido no se puede editar sin cambiar el de todos).
 *  Archivos nuevos en landing/{id}/… (expandido).
 */
class EditarLandingController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'contenido');
        $tab = in_array($tab, ['contenido', 'noticias', 'galeria'], true) ? $tab : 'contenido';
        $inst = $this->institucion();
        $bloqueo = SitioInstitucional::motivoBloqueoEdicion();

        return view('Rector.landing.index', [
            'currentPage' => 'EditarLanding',
            'tab' => $tab,
            'contenido' => $tab === 'contenido' ? ($bloqueo ? [] : $this->contenidoPropio($inst)) : null,
            'noticias' => $tab === 'noticias' ? SitioInstitucional::noticias($inst, false) : null,
            'galeria' => $tab === 'galeria' ? SitioInstitucional::galeria($inst, false) : null,
            'edicionBloqueada' => $bloqueo,
            'urlSitio' => route('sitio.show', $inst->slug),
        ]);
    }

    public function updateContenido(Request $request): JsonResponse
    {
        $this->asegurarEditable();
        $inst = $this->institucion();
        $data = $request->validate([
            'valores' => ['required', 'array'],
            'valores.*' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($data, $inst) {
            foreach ($data['valores'] as $clave => $valor) {
                if (! in_array($clave, SitioInstitucional::CLAVES, true) || $clave === 'hero_imagen') {
                    continue;   // solo claves conocidas; la imagen tiene su propia ruta
                }
                $this->guardarClave($inst, $clave, $valor);
            }
        });

        return response()->json(['success' => true, 'message' => 'Contenido actualizado correctamente.']);
    }

    public function updateHeroImagen(Request $request): JsonResponse
    {
        $this->asegurarEditable();
        $inst = $this->institucion();
        $request->validate(['hero_imagen' => ['required', 'image', 'max:4096']]);

        $actual = $this->filaClave($inst, 'hero_imagen')?->valor;
        if ($actual) {
            Storage::disk('public')->delete($actual);
        }
        $ruta = $request->file('hero_imagen')->store(SitioInstitucional::carpeta($inst->id_institucion, 'hero'), 'public');
        $this->guardarClave($inst, 'hero_imagen', $ruta);

        return response()->json(['success' => true, 'message' => 'Imagen principal actualizada correctamente.']);
    }

    public function removerHeroImagen(): JsonResponse
    {
        $this->asegurarEditable();
        $inst = $this->institucion();
        $fila = $this->filaClave($inst, 'hero_imagen');
        if ($fila?->valor) {
            Storage::disk('public')->delete($fila->valor);
            $fila->update(['valor' => null, 'actualizado_en' => now()]);
        }

        return response()->json(['success' => true, 'message' => 'Imagen principal eliminada.']);
    }

    public function storeNoticia(LandingNoticiaRequest $request): JsonResponse
    {
        $this->asegurarEditable();
        $inst = $this->institucion();
        $data = $request->validated();
        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store(SitioInstitucional::carpeta($inst->id_institucion, 'noticias'), 'public');
        }
        $noticia = new LandingNoticia;
        $noticia->fill($data + ['estado' => 'activo']);
        $this->asignar($noticia, $inst)->save();

        return response()->json(['success' => true, 'message' => 'Noticia creada correctamente.', 'id' => $noticia->id_noticia]);
    }

    public function updateNoticia(LandingNoticiaRequest $request, LandingNoticia $noticia): JsonResponse
    {
        $inst = $this->propio($noticia);
        $data = $request->validated();
        if ($request->hasFile('imagen')) {
            if ($noticia->imagen) {
                Storage::disk('public')->delete($noticia->imagen);
            }
            $data['imagen'] = $request->file('imagen')->store(SitioInstitucional::carpeta($inst->id_institucion, 'noticias'), 'public');
        }
        $noticia->update($data);

        return response()->json(['success' => true, 'message' => 'Noticia actualizada correctamente.']);
    }

    public function desactivarNoticia(LandingNoticia $noticia): JsonResponse
    {
        $this->propio($noticia);
        $noticia->update(['estado' => 'inactivo']);

        return response()->json(['success' => true, 'message' => 'Noticia desactivada correctamente.']);
    }

    public function activarNoticia(LandingNoticia $noticia): JsonResponse
    {
        $this->propio($noticia);
        $noticia->update(['estado' => 'activo']);

        return response()->json(['success' => true, 'message' => 'Noticia reactivada correctamente.']);
    }

    public function storeGaleria(LandingGaleriaRequest $request): JsonResponse
    {
        $this->asegurarEditable();
        $inst = $this->institucion();
        $data = $request->validated();
        $data['imagen'] = $request->file('imagen')->store(SitioInstitucional::carpeta($inst->id_institucion, 'galeria'), 'public');
        $foto = new LandingGaleria;
        $foto->fill($data + ['estado' => 'activo']);
        $this->asignar($foto, $inst)->save();

        return response()->json(['success' => true, 'message' => 'Foto agregada correctamente.', 'id' => $foto->id_foto]);
    }

    public function updateGaleria(LandingGaleriaRequest $request, LandingGaleria $galeria): JsonResponse
    {
        $inst = $this->propio($galeria);
        $data = $request->validated();
        if ($request->hasFile('imagen')) {
            if ($galeria->imagen) {
                Storage::disk('public')->delete($galeria->imagen);
            }
            $data['imagen'] = $request->file('imagen')->store(SitioInstitucional::carpeta($inst->id_institucion, 'galeria'), 'public');
        }
        $galeria->update($data);

        return response()->json(['success' => true, 'message' => 'Foto actualizada correctamente.']);
    }

    public function desactivarGaleria(LandingGaleria $galeria): JsonResponse
    {
        $this->propio($galeria);
        $galeria->update(['estado' => 'inactivo']);

        return response()->json(['success' => true, 'message' => 'Foto desactivada correctamente.']);
    }

    public function activarGaleria(LandingGaleria $galeria): JsonResponse
    {
        $this->propio($galeria);
        $galeria->update(['estado' => 'activo']);

        return response()->json(['success' => true, 'message' => 'Foto reactivada correctamente.']);
    }

    // ------------------------------------------------------------------
    private function institucion(): Institucion
    {
        $id = TenantContext::idActual();
        abort_if($id === null, 403);

        return Institucion::query()->findOrFail($id);
    }

    private function asegurarEditable(): void
    {
        if ($motivo = SitioInstitucional::motivoBloqueoEdicion()) {
            abort(response()->json(['success' => false, 'message' => $motivo], 409));
        }
    }

    /** El registro debe ser del sitio de la sesión; si no, 404 (no se confirma que exista). */
    private function propio(Model $registro): Institucion
    {
        $this->asegurarEditable();
        $inst = $this->institucion();
        abort_unless(SitioInstitucional::perteneceA($registro, $inst->id_institucion), 404);

        return $inst;
    }

    private function asignar(Model $registro, Institucion $inst): Model
    {
        if (SitioInstitucional::expandido()) {
            $registro->forceFill(['id_institucion' => $inst->id_institucion]);   // del servidor
        }

        return $registro;
    }

    private function contenidoPropio(Institucion $inst): array
    {
        $propio = SitioInstitucional::expandido()
            ? LandingContenido::query()->where('id_institucion', $inst->id_institucion)->pluck('valor', 'clave')->all()
            : LandingContenido::query()->pluck('valor', 'clave')->all();

        return array_merge(array_fill_keys(SitioInstitucional::CLAVES, ''), $propio);
    }

    private function filaClave(Institucion $inst, string $clave): ?LandingContenido
    {
        $q = LandingContenido::query()->where('clave', $clave);
        if (SitioInstitucional::expandido()) {
            $q->where('id_institucion', $inst->id_institucion);
        }

        return $q->first();
    }

    private function guardarClave(Institucion $inst, string $clave, ?string $valor): void
    {
        $fila = $this->filaClave($inst, $clave);
        if ($fila) {
            $fila->update(['valor' => $valor, 'actualizado_en' => now()]);

            return;
        }
        $nueva = new LandingContenido;
        $nueva->forceFill(['clave' => $clave, 'valor' => $valor, 'actualizado_en' => now()]);
        $this->asignar($nueva, $inst)->save();
    }
}
