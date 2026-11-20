<?php

namespace App\Modules\Colegio\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Colegio\Models\ColegioConfiguracion;
use App\Modules\Colegio\Models\ColegioImagen;
use App\Modules\Colegio\Requests\ColegioImagenRequest;
use App\Modules\Colegio\Requests\ConfiguracionColegioRequest;
use App\Modules\Instituciones\Support\EsquemaInstitucional;
use App\Modules\Instituciones\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * «Configuración del colegio» — Etapa 2 · B6: cada colegio edita SOLO su
 * configuración. La institución sale de la sesión (TenantContext), nunca de
 * la petición. Ver ColegioConfiguracion para la resolución por esquema.
 *
 *  Esquema expandido: la fila con id_institucion = sesión; si aún no existe, se
 *    crea al guardar por primera vez (sin copiar la configuración compartida).
 *  Esquema antiguo: con una sola institución, la fila única como antes; con
 *    varias, la fila compartida no puede editarse sin afectar a todos → 409.
 *  Imágenes: solo las de la configuración propia (las demás → 404).
 */
class ConfiguracionColegioController extends Controller
{
    private const MAX_IMAGENES = 12;

    public function index(): View
    {
        $id = $this->institucion();
        $propia = ColegioConfiguracion::propiaDe($id);
        $configuracion = $propia ?? ColegioConfiguracion::deInstitucion($id);

        return view('Rector.configuracion-colegio.index', [
            'currentPage' => 'ConfiguracionColegio',
            'configuracion' => $configuracion,
            'imagenes' => $propia ? $propia->imagenes : collect(),
            'tipos' => ColegioImagen::TIPOS_LABELS,
            'edicionBloqueada' => $this->motivoBloqueo(),
        ]);
    }

    public function update(ConfiguracionColegioRequest $request): JsonResponse
    {
        $this->asegurarEditable();
        $id = $this->institucion();
        $data = $request->safe()->except(['logo', 'logo_removido']);

        $configuracion = DB::transaction(function () use ($id) {
            return ColegioConfiguracion::propiaDe($id) ?? $this->crearPropia($id);
        });

        if ($request->hasFile('logo')) {
            if ($configuracion->logo) {
                Storage::disk('public')->delete($configuracion->logo);
            }
            $data['logo'] = $request->file('logo')->store($this->carpeta($id), 'public');
        } elseif ($request->boolean('logo_removido') && $configuracion->logo) {
            Storage::disk('public')->delete($configuracion->logo);
            $data['logo'] = null;
        }

        $configuracion->update($data);

        // El sidebar muestra el logo de la Institucion del usuario: se sincroniza
        // solo la propia (la institución sale de la sesión, no de la petición).
        if (array_key_exists('logo', $data)) {
            auth()->user()?->institucion?->update(['logo' => $data['logo']]);
        }

        return response()->json(['success' => true, 'message' => 'Configuración actualizada correctamente.']);
    }

    public function storeImagen(ColegioImagenRequest $request): JsonResponse
    {
        $this->asegurarEditable();
        $id = $this->institucion();
        $configuracion = DB::transaction(fn () => ColegioConfiguracion::propiaDe($id) ?? $this->crearPropia($id));

        if ($configuracion->imagenes()->count() >= self::MAX_IMAGENES) {
            return response()->json([
                'success' => false,
                'message' => 'Ya alcanzaste el máximo de '.self::MAX_IMAGENES.' imágenes en la galería.',
            ], 422);
        }

        $data = $request->validated();
        $data['imagen'] = $request->file('imagen')->store($this->carpeta($id).'/galeria', 'public');
        $data['orden'] = $configuracion->imagenes()->max('orden') + 1;

        $imagen = $configuracion->imagenes()->create($data);
        if ($this->expandido('colegio_imagenes')) {
            $imagen->forceFill(['id_institucion' => $id])->save();
        }

        return response()->json(['success' => true, 'message' => 'Imagen agregada correctamente.', 'id' => $imagen->id_imagen]);
    }

    public function destroyImagen(ColegioImagen $imagen): JsonResponse
    {
        $this->asegurarEditable();
        abort_unless($this->esPropia($imagen), 404);

        Storage::disk('public')->delete($imagen->imagen);
        $imagen->delete();

        return response()->json(['success' => true, 'message' => 'Imagen eliminada correctamente.']);
    }

    public function reordenarImagenes(Request $request): JsonResponse
    {
        $this->asegurarEditable();
        $propias = ColegioConfiguracion::propiaDe($this->institucion())?->imagenes()->pluck('id_imagen')->map(fn ($i) => (int) $i)->all() ?? [];

        $data = $request->validate([
            'orden' => ['required', 'array'],
            'orden.*' => ['integer', function (string $atributo, mixed $valor, \Closure $fallar) use ($propias) {
                if (! in_array((int) $valor, $propias, true)) {
                    $fallar('El valor seleccionado no es válido.');
                }
            }],
        ]);

        foreach ($data['orden'] as $posicion => $idImagen) {
            ColegioImagen::where('id_imagen', $idImagen)->whereIn('id_imagen', $propias)->update(['orden' => $posicion + 1]);
        }

        return response()->json(['success' => true, 'message' => 'Orden actualizado correctamente.']);
    }

    // ------------------------------------------------------------------
    private function institucion(): int
    {
        $id = TenantContext::idActual();
        abort_if($id === null, 403);

        return $id;
    }

    private function expandido(string $tabla): bool
    {
        return EsquemaInstitucional::actual()->tieneColumna($tabla);
    }

    private function motivoBloqueo(): ?string
    {
        if (! $this->expandido('colegio_configuracion') && EsquemaInstitucional::actual()->totalInstituciones() > 1) {
            return 'La configuración todavía es compartida por varias instituciones: editarla cambiaría la de todas. Se habilita al completar la migración institucional (Etapa 2).';
        }

        return null;
    }

    private function asegurarEditable(): void
    {
        if ($motivo = $this->motivoBloqueo()) {
            abort(response()->json(['success' => false, 'message' => $motivo], 409));
        }
    }

    /** Solo esquema expandido (en el antiguo con una institución la fila ya existe). */
    private function crearPropia(int $id): ColegioConfiguracion
    {
        $base = ColegioConfiguracion::derivadaDe($id);
        $nueva = new ColegioConfiguracion;
        $nueva->forceFill([
            'id_institucion' => $id,   // del servidor, nunca de la petición
            'nombre_colegio' => $base->nombre_colegio,
            'logo' => $base->logo,
        ])->save();

        return $nueva;
    }

    private function esPropia(ColegioImagen $imagen): bool
    {
        $propia = ColegioConfiguracion::propiaDe($this->institucion());
        if (! $propia || (int) $imagen->id_configuracion !== (int) $propia->id_configuracion) {
            return false;
        }

        return ! $this->expandido('colegio_imagenes')
            || $imagen->id_institucion === null
            || (int) $imagen->id_institucion === $this->institucion();
    }

    /** Archivos separados por colegio (antes: una carpeta común). */
    private function carpeta(int $id): string
    {
        return $this->expandido('colegio_configuracion') ? "colegio/{$id}" : 'colegio';
    }
}
