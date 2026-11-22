<?php

namespace App\Modules\Landing\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Instituciones\Models\Institucion;
use App\Modules\Landing\Requests\SolicitudAdmisionRequest;
use App\Modules\Landing\Support\SitioInstitucional;
use App\Modules\Matriculas\Models\SolicitudAdmision;
use Illuminate\Http\JsonResponse;

/**
 * Admisiones públicas — Etapa 2 · B7.
 *
 * Antes: TODAS las solicitudes se guardaban con id_institucion = 1, sin importar
 * el colegio. Ahora la institución se resuelve en el servidor desde la ruta
 * (/sitio/{slug}/admision; la ruta antigua solo con PORTAL_PUBLICO_SLUG). Lo que
 * envíe el navegador como institución se ignora. El grado se valida contra la
 * oferta de ESA institución. La respuesta no devuelve ningún dato personal.
 */
class SolicitudAdmisionController extends Controller
{
    public function store(SolicitudAdmisionRequest $request, string $slug): JsonResponse
    {
        return $this->guardar($request, SitioInstitucional::porSlug($slug));
    }

    /** Ruta antigua POST /solicitudes-admision: solo si hay portal configurado; si no, 404. */
    public function storePortal(SolicitudAdmisionRequest $request): JsonResponse
    {
        $portal = SitioInstitucional::portal();
        abort_if($portal === null, 404);

        return $this->guardar($request, $portal);
    }

    private function guardar(SolicitudAdmisionRequest $request, Institucion $inst): JsonResponse
    {
        $datos = $request->validated();

        // Campo trampa para bots (oculto en el formulario): si viene relleno, se responde
        // igual que siempre pero no se guarda nada.
        if (filled($request->input('sitio_web_contacto'))) {
            return $this->respuesta();
        }

        if (! in_array($datos['grado_interes'], SitioInstitucional::grados($inst), true)) {
            return response()->json([
                'success' => false,
                'message' => 'El grado seleccionado no está disponible en esta institución.',
                'errors' => ['grado_interes' => ['El grado seleccionado no está disponible en esta institución.']],
            ], 422);
        }

        $solicitud = new SolicitudAdmision;
        $solicitud->fill($datos);
        $solicitud->forceFill(['id_institucion' => $inst->id_institucion])->save();

        return $this->respuesta();
    }

    private function respuesta(): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Solicitud enviada. Nos comunicaremos contigo pronto.']);
    }
}
