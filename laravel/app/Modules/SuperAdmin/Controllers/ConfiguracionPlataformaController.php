<?php

namespace App\Modules\SuperAdmin\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Auditoria\Services\AuditLogger;
use App\Modules\SuperAdmin\Models\PlataformaConfiguracion;
use App\Modules\SuperAdmin\Policies\ConfiguracionPlataformaPolicy;
use App\Modules\SuperAdmin\Requests\ConfiguracionPlataformaUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConfiguracionPlataformaController extends Controller
{
    public function __construct(private ConfiguracionPlataformaPolicy $policy, private AuditLogger $auditLogger) {}

    public function index(Request $request): View
    {
        abort_unless($this->policy->editar($request->user()), 403);

        return view('SuperAdmin.configuracion.index', [
            'currentPage' => 'SuperAdminConfiguracion',
            'configuracion' => PlataformaConfiguracion::singleton(),
            // Estado de integración WhatsApp Cloud API: solo lectura, se edita en .env (ver config/services.php).
            'whatsapp' => config('services.whatsapp_cloud'),
        ]);
    }

    public function update(ConfiguracionPlataformaUpdateRequest $request): JsonResponse
    {
        abort_unless($this->policy->editar($request->user()), 403);

        $configuracion = PlataformaConfiguracion::singleton();
        $antes = $configuracion->toArray();
        $configuracion->update($request->validated());

        $this->auditLogger->record('configuracion_plataforma.editar', $configuracion, $antes, $configuracion->toArray());

        return response()->json(['success' => true, 'message' => 'Configuración actualizada.']);
    }
}
