<?php

namespace App\Modules\SuperAdmin\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Services\PlataformaMetricasService;
use Illuminate\View\View;

class SuperAdminDashboardController extends Controller
{
    public function __construct(private PlataformaMetricasService $metricas) {}

    /** Requiere superadmin + plataforma.metricas.ver (ver permission:plataforma.metricas.ver en routes/web.php). */
    public function index(): View
    {
        return view('SuperAdmin.dashboard.index', [
            'currentPage' => 'SuperAdminDashboard',
            'resumen' => $this->metricas->resumen(),
            'crecimientoInstituciones' => $this->metricas->institucionesNuevasPorMes(),
            'comunicadosPorMes' => $this->metricas->comunicadosPorMes(),
            'institucionesProximasAVencer' => $this->metricas->institucionesProximasAVencer(),
            'planesDistribucion' => $this->metricas->planesDistribucion(),
        ]);
    }
}
