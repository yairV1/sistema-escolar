<?php

namespace App\Modules\Auditoria\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Auditoria\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditoriaController extends Controller
{
    /** Requiere plataforma.auditoria.ver — ver permission:plataforma.auditoria.ver en routes/web.php. */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', AuditLog::class);

        $logs = AuditLog::query()
            ->with(['usuario', 'institucion'])
            ->when($request->filled('accion'), fn ($q) => $q->where('accion', 'like', '%'.$request->query('accion').'%'))
            ->when($request->filled('id_usuario'), fn ($q) => $q->where('id_usuario', $request->query('id_usuario')))
            ->when($request->filled('desde'), fn ($q) => $q->whereDate('created_at', '>=', $request->query('desde')))
            ->when($request->filled('hasta'), fn ($q) => $q->whereDate('created_at', '<=', $request->query('hasta')))
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        return view('SuperAdmin.auditoria.index', [
            'currentPage' => 'SuperAdminAuditoria',
            'logs' => $logs,
            'filtros' => $request->only(['accion', 'id_usuario', 'desde', 'hasta']),
        ]);
    }
}
