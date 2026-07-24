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
        SolicitudAdmision::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Solicitud enviada. Nos comunicaremos contigo pronto.',
        ]);
    }
}
