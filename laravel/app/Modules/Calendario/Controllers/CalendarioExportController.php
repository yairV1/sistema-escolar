<?php

namespace App\Modules\Calendario\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Auth\Models\Usuario;
use App\Modules\Calendario\Services\IcsExportService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class CalendarioExportController extends Controller
{
    private const RANGO_DIAS_ATRAS = 30;

    private const RANGO_DIAS_ADELANTE = 180;

    public function descargar(Request $request, IcsExportService $ics): Response
    {
        [$desde, $hasta] = $this->rango();
        $contenido = $ics->generar($request->user(), $desde, $hasta);

        return response($contenido, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => (new ResponseHeaderBag)->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                'calendario.ics'
            ),
        ]);
    }

    public function generarToken(Request $request): JsonResponse
    {
        $usuario = $request->user();

        if (! $usuario->ics_token) {
            $usuario->ics_token = Str::random(64);
            $usuario->save();
        }

        return response()->json([
            'url' => route('calendario.ics', ['usuario' => $usuario->id_usuario, 'token' => $usuario->ics_token]),
        ]);
    }

    public function suscripcion(Usuario $usuario, string $token, IcsExportService $ics): Response
    {
        abort_unless($usuario->ics_token && hash_equals($usuario->ics_token, $token), 404);

        [$desde, $hasta] = $this->rango();
        $contenido = $ics->generar($usuario, $desde, $hasta);

        return response($contenido, 200, ['Content-Type' => 'text/calendar; charset=utf-8']);
    }

    /** @return array{0: CarbonImmutable, 1: CarbonImmutable} */
    private function rango(): array
    {
        return [
            CarbonImmutable::now()->subDays(self::RANGO_DIAS_ATRAS),
            CarbonImmutable::now()->addDays(self::RANGO_DIAS_ADELANTE),
        ];
    }
}
