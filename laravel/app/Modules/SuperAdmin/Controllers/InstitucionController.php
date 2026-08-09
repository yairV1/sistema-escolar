<?php

namespace App\Modules\SuperAdmin\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Auditoria\Services\AuditLogger;
use App\Modules\Auth\Models\Usuario;
use App\Modules\Instituciones\Models\Institucion;
use App\Modules\Instituciones\Requests\InstitucionStoreRequest;
use App\Modules\Instituciones\Requests\InstitucionUpdateRequest;
use App\Modules\Planes\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InstitucionController extends Controller
{
    public function __construct(private AuditLogger $auditLogger) {}

    /** Requiere plataforma.instituciones.ver. */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Institucion::class);

        $instituciones = Institucion::query()
            ->with('planCatalogo')
            ->withCount('usuarios')
            ->when($request->filled('buscar'), fn ($q) => $q->where('nombre', 'like', '%'.$request->query('buscar').'%'))
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->query('estado')))
            ->when($request->filled('id_plan'), fn ($q) => $q->where('id_plan', $request->query('id_plan')))
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('SuperAdmin.instituciones.index', [
            'currentPage' => 'SuperAdminInstituciones',
            'instituciones' => $instituciones,
            'planes' => Plan::where('estado', 'activo')->orderBy('orden')->get(),
            'filtros' => $request->only(['buscar', 'estado', 'id_plan']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Institucion::class);

        return view('SuperAdmin.instituciones.create', [
            'currentPage' => 'SuperAdminInstituciones',
            'planes' => Plan::where('estado', 'activo')->orderBy('orden')->get(),
        ]);
    }

    /** Crea la institución junto con su rector (id_rol=2, ver Usuario::ROLE_SLUGS) en una sola transacción. */
    public function store(InstitucionStoreRequest $request): JsonResponse
    {
        $this->authorize('create', Institucion::class);

        $d = $request->validated();
        $plan = Plan::findOrFail($d['id_plan']);

        $institucion = DB::transaction(function () use ($d, $plan) {
            $institucion = Institucion::create([
                'nombre' => $d['nombre'],
                'slug' => $d['slug'],
                'nit' => $d['nit'] ?? null,
                'email_contacto' => $d['email_contacto'] ?? null,
                'telefono' => $d['telefono'] ?? null,
                'direccion' => $d['direccion'] ?? null,
                'ciudad' => $d['ciudad'] ?? null,
                'pais' => $d['pais'] ?? null,
                'id_plan' => $d['id_plan'],
                'limite_usuarios' => $d['limite_usuarios'] ?? null,
                'fecha_inicio' => $d['fecha_inicio'] ?? null,
                'fecha_vencimiento' => $d['fecha_vencimiento'] ?? null,
                'plan' => Str::slug($plan->nombre),
                'estado' => 'activa',
            ]);

            Usuario::create([
                'nombres' => $d['rector_nombres'],
                'apellidos' => $d['rector_apellidos'],
                'tipo_documento' => $d['rector_tipo_documento'],
                'numero_documento' => $d['rector_numero_documento'],
                'correo' => $d['rector_correo'],
                'telefono' => $d['rector_telefono'] ?? null,
                'password' => Hash::make($d['rector_numero_documento']),
                'id_rol' => 2, // Rector — ver Usuario::ROLE_SLUGS
                'id_institucion' => $institucion->id_institucion,
            ]);

            return $institucion;
        });

        $this->auditLogger->record('institucion.crear', $institucion, [], $institucion->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Institución creada correctamente.',
            'redirect' => route('superadmin.instituciones.show', $institucion),
        ]);
    }

    /** Pestañas por query string (?tab=info|usuarios|metricas|configuracion), mismo patrón que Soporte/index.blade.php. */
    public function show(Institucion $institucion, Request $request): View
    {
        $this->authorize('view', $institucion);

        $institucion->loadCount('usuarios');

        return view('SuperAdmin.instituciones.show', [
            'currentPage' => 'SuperAdminInstituciones',
            'institucion' => $institucion,
            'tab' => $request->query('tab', 'info'),
            'usuarios' => $institucion->usuarios()->with('rol')->paginate(15, ['*'], 'usuarios_page'),
        ]);
    }

    public function edit(Institucion $institucion): View
    {
        $this->authorize('update', $institucion);

        return view('SuperAdmin.instituciones.edit', [
            'currentPage' => 'SuperAdminInstituciones',
            'institucion' => $institucion,
            'planes' => Plan::where('estado', 'activo')->orderBy('orden')->get(),
            'rector' => $institucion->usuarios()->where('id_rol', 2)->first(),
        ]);
    }

    /** Actualiza la institución y su rector (id_rol=2); si la institución no tenía rector todavía, lo crea. */
    public function update(InstitucionUpdateRequest $request, Institucion $institucion): JsonResponse
    {
        $this->authorize('update', $institucion);

        $d = $request->validated();
        $plan = Plan::findOrFail($d['id_plan']);
        $antes = $institucion->toArray();

        DB::transaction(function () use ($d, $plan, $institucion) {
            $institucion->update([
                'nombre' => $d['nombre'],
                'slug' => $d['slug'],
                'nit' => $d['nit'] ?? null,
                'email_contacto' => $d['email_contacto'] ?? null,
                'telefono' => $d['telefono'] ?? null,
                'direccion' => $d['direccion'] ?? null,
                'ciudad' => $d['ciudad'] ?? null,
                'pais' => $d['pais'] ?? null,
                'id_plan' => $d['id_plan'],
                'limite_usuarios' => $d['limite_usuarios'] ?? null,
                'fecha_inicio' => $d['fecha_inicio'] ?? null,
                'fecha_vencimiento' => $d['fecha_vencimiento'] ?? null,
                'plan' => Str::slug($plan->nombre),
            ]);

            $datosRector = [
                'nombres' => $d['rector_nombres'],
                'apellidos' => $d['rector_apellidos'],
                'tipo_documento' => $d['rector_tipo_documento'],
                'numero_documento' => $d['rector_numero_documento'],
                'correo' => $d['rector_correo'],
                'telefono' => $d['rector_telefono'] ?? null,
            ];

            $rector = $institucion->usuarios()->where('id_rol', 2)->first();
            \Illuminate\Support\Facades\Log::info('DIAG rector update', ['rector_existente' => $rector?->id_usuario, 'numero_documento' => $d['rector_numero_documento']]);
            if ($rector) {
                $rector->update($datosRector);
            } else {
                $hash = Hash::make($d['rector_numero_documento']);
                \Illuminate\Support\Facades\Log::info('DIAG rector create', ['numero_documento' => $d['rector_numero_documento'], 'hash' => $hash, 'verifica' => Hash::check($d['rector_numero_documento'], $hash)]);
                Usuario::create([
                    ...$datosRector,
                    'password' => $hash,
                    'id_rol' => 2,
                    'id_institucion' => $institucion->id_institucion,
                ]);
            }
        });

        $this->auditLogger->record('institucion.editar', $institucion, $antes, $institucion->fresh()->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Institución actualizada correctamente.',
            'redirect' => route('superadmin.instituciones.show', $institucion),
        ]);
    }

    public function activar(Institucion $institucion): JsonResponse
    {
        $this->authorize('activate', $institucion);

        $institucion->update(['estado' => 'activa']);
        $this->auditLogger->record('institucion.activar', $institucion);

        return response()->json(['success' => true, 'message' => 'Institución activada.']);
    }

    public function desactivar(Institucion $institucion): JsonResponse
    {
        $this->authorize('deactivate', $institucion);

        $institucion->update(['estado' => 'suspendida']);
        $this->auditLogger->record('institucion.suspender', $institucion);

        return response()->json(['success' => true, 'message' => 'Institución suspendida.']);
    }
}
