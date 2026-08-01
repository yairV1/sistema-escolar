<?php

namespace App\Modules\SuperAdmin\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Auditoria\Services\AuditLogger;
use App\Modules\Modulos\Models\Modulo;
use App\Modules\Planes\Models\Plan;
use App\Modules\Planes\Requests\PlanStoreRequest;
use App\Modules\Planes\Requests\PlanUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function __construct(private AuditLogger $auditLogger) {}

    /** Requiere plataforma.planes.ver. */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Plan::class);

        $planes = Plan::query()
            ->withCount('instituciones')
            ->with('modulos')
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->query('estado')))
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        return view('SuperAdmin.planes.index', [
            'currentPage' => 'SuperAdminPlanes',
            'planes' => $planes,
            'filtros' => $request->only(['estado']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Plan::class);

        return view('SuperAdmin.planes.create', [
            'currentPage' => 'SuperAdminPlanes',
            'modulos' => Modulo::where('estado', 'activo')->orderBy('categoria')->orderBy('nombre')->get(),
        ]);
    }

    public function store(PlanStoreRequest $request): JsonResponse
    {
        $this->authorize('create', Plan::class);

        $datos = $request->safe()->except('modulos', 'beneficios');
        $datos['beneficios'] = $this->beneficiosDesdeTexto($request->input('beneficios'));

        $plan = Plan::create($datos);
        $plan->modulos()->sync($request->input('modulos', []));

        $this->auditLogger->record('plan.crear', $plan, [], $plan->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Plan creado correctamente.',
            'redirect' => route('superadmin.planes.index'),
        ]);
    }

    public function edit(Plan $plan): View
    {
        $this->authorize('update', $plan);

        $plan->load('modulos');

        return view('SuperAdmin.planes.edit', [
            'currentPage' => 'SuperAdminPlanes',
            'plan' => $plan,
            'modulos' => Modulo::where('estado', 'activo')->orderBy('categoria')->orderBy('nombre')->get(),
            'modulosSeleccionados' => $plan->modulos->pluck('id_modulo')->all(),
        ]);
    }

    public function update(PlanUpdateRequest $request, Plan $plan): JsonResponse
    {
        $this->authorize('update', $plan);

        $antes = $plan->toArray();

        $datos = $request->safe()->except('modulos', 'beneficios');
        $datos['beneficios'] = $this->beneficiosDesdeTexto($request->input('beneficios'));

        $plan->update($datos);
        $plan->modulos()->sync($request->input('modulos', []));

        $this->auditLogger->record('plan.editar', $plan, $antes, $plan->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Plan actualizado correctamente.',
            'redirect' => route('superadmin.planes.index'),
        ]);
    }

    /** Duplica un plan (nombre y slug con sufijo, sin instituciones ni estadísticas). */
    public function duplicar(Plan $plan): JsonResponse
    {
        $this->authorize('create', Plan::class);

        $copia = $plan->replicate(['slug']);
        $copia->nombre = $plan->nombre.' (copia)';
        $copia->slug = $plan->slug.'-copia-'.substr(md5((string) microtime(true)), 0, 5);
        $copia->estado = 'inactivo';
        $copia->save();
        $copia->modulos()->sync($plan->modulos->pluck('id_modulo'));

        $this->auditLogger->record('plan.duplicar', $copia, [], $copia->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Plan duplicado. Revísalo y actívalo cuando esté listo.',
            'redirect' => route('superadmin.planes.index'),
        ]);
    }

    public function activar(Plan $plan): JsonResponse
    {
        $this->authorize('activate', $plan);

        $plan->update(['estado' => 'activo']);
        $this->auditLogger->record('plan.activar', $plan);

        return response()->json(['success' => true, 'message' => 'Plan activado.']);
    }

    public function desactivar(Plan $plan): JsonResponse
    {
        $this->authorize('deactivate', $plan);

        $plan->update(['estado' => 'inactivo']);
        $this->auditLogger->record('plan.desactivar', $plan);

        return response()->json(['success' => true, 'message' => 'Plan desactivado.']);
    }

    /** El formulario envía un textarea (un beneficio por línea); se guarda como array en `beneficios` (json). */
    private function beneficiosDesdeTexto(?string $texto): array
    {
        if (! $texto) {
            return [];
        }

        return collect(explode("\n", $texto))
            ->map(fn ($linea) => trim($linea))
            ->filter()
            ->values()
            ->all();
    }
}
