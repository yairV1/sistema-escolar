<?php

namespace App\Modules\Landing\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Landing\Requests\SolicitudAdmisionRequest;
use App\Modules\Matriculas\Models\SolicitudAdmision;
use Illuminate\Http\JsonResponse;

class SolicitudAdmisionController extends Controller
{
    public function store(SolicitudAdmisionRequest $request): JsonResponse
    {
        // Portal público sin sesión: BelongsToInstitucion no puede inferir
        // el tenant desde Auth::user() (no existe). Institución #1 es el
        // único colegio con portal público activo hoy; cuando el portal
        // sea multi-tenant real (dominios/slugs por institución) esto debe
        // resolverse desde el propio request, no quedar fijo en 1.
        SolicitudAdmision::create($request->validated() + ['id_institucion' => 1]);

        return response()->json([
            'success' => true,
            'message' => 'Solicitud enviada. Nos comunicaremos contigo pronto.',
        ]);
    }
}
