<?php

namespace App\Modules\SuperAdmin\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Auditoria\Services\AuditLogger;
use App\Modules\Auth\Models\Usuario;
use App\Modules\Instituciones\Models\Institucion;
use App\Modules\Instituciones\Requests\InstitucionStoreRequest;
use App\Modules\Instituciones\Requests\InstitucionUpdateRequest;
use App\Modules\Instituciones\Support\RequisitosProvisionamientoInstitucion;
use App\Modules\Instituciones\Support\SlugInstitucion;
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
        $requisitosPendientes = RequisitosProvisionamientoInstitucion::pendientes();

        return view('SuperAdmin.instituciones.create', [
            'currentPage' => 'SuperAdminInstituciones',
            'requisitosPendientes' => $requisitosPendientes,
            'planes' => $requisitosPendientes === []
                ? Plan::where('estado', 'activo')->orderBy('orden')->get()
                : collect(),
        ]);
    }

    /** Crea en una transacción la institución, su configuración y el administrador inicial (id_rol=1). */
    public function store(InstitucionStoreRequest $request): JsonResponse
    {
        $this->authorize('create', Institucion::class);

        $requisitosPendientes = RequisitosProvisionamientoInstitucion::pendientes();
        if ($requisitosPendientes !== []) {
            return response()->json([
                'success' => false,
                'message' => 'El aprovisionamiento está bloqueado: aplica y verifica B2–B8 en una ventana de mantenimiento antes de crear colegios.',
                'requisitos' => $requisitosPendientes,
            ], 409);
        }

        $d = $request->validated();

        $institucion = DB::transaction(function () use ($d) {
            $plan = Plan::query()
                ->where('id_plan', $d['id_plan'])
                ->where('estado', 'activo')
                ->lockForUpdate()
                ->firstOrFail();

            if (! DB::table('roles')->where('id_rol', 1)->where('estado', 'activo')->exists()) {
                abort(409, 'No está disponible el rol institucional Administrador.');
            }

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
                'estado' => $d['estado'] ?? 'activa',
            ]);

            DB::table('colegio_configuracion')->insert([
                'id_institucion' => $institucion->id_institucion,
                'nombre_colegio' => $institucion->nombre,
                'direccion' => $institucion->direccion,
                'ciudad' => $institucion->ciudad,
                'pais' => $institucion->pais,
                'telefono' => $institucion->telefono,
                'email_institucional' => $institucion->email_contacto,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $administrador = Usuario::forceCreate([
                'nombres' => $d['administrador_nombres'],
                'apellidos' => $d['administrador_apellidos'],
                'tipo_documento' => $d['administrador_tipo_documento'],
                'numero_documento' => $d['administrador_numero_documento'],
                'correo' => $d['administrador_correo'],
                'telefono' => $d['administrador_telefono'] ?? null,
                'password' => Hash::make(Str::random(64)),
                'id_rol' => 1,
                'id_institucion' => $institucion->id_institucion,
            ]);

            $this->auditLogger->record('institucion.crear', $institucion, [], [
                'nombre' => $institucion->nombre,
                'slug' => $institucion->slug,
                'estado' => $institucion->estado,
                'id_plan' => $institucion->id_plan,
            ]);
            $this->auditLogger->record('institucion.administrador.asignar', $administrador, [], [
                'id_rol' => 1,
                'id_institucion' => $institucion->id_institucion,
            ]);

            return $institucion;
        });

        return response()->json([
            'success' => true,
            'message' => 'Institución y administrador creados. El administrador debe establecer su contraseña mediante recuperación de acceso.',
            'redirect' => route('superadmin.instituciones.show', $institucion),
        ]);
    }

    /** La vista central solo muestra información institucional y métricas agregadas. */
    public function show(Institucion $institucion, Request $request): View
    {
        $this->authorize('view', $institucion);

        $institucion->loadCount('usuarios');

        return view('SuperAdmin.instituciones.show', [
            'currentPage' => 'SuperAdminInstituciones',
            'institucion' => $institucion,
            'tab' => in_array($request->query('tab'), ['info', 'metricas'], true)
                ? $request->query('tab')
                : 'info',
            'slugCanonico' => SlugInstitucion::esCanonico($institucion->slug),
        ]);
    }

    public function edit(Institucion $institucion): View
    {
        $this->authorize('update', $institucion);

        return view('SuperAdmin.instituciones.edit', [
            'currentPage' => 'SuperAdminInstituciones',
            'institucion' => $institucion,
            'planes' => Plan::where('estado', 'activo')->orderBy('orden')->get(),
        ]);
    }

    /** Actualiza únicamente los datos institucionales permitidos, sin consultar ni cambiar usuarios. */
    public function update(InstitucionUpdateRequest $request, Institucion $institucion): JsonResponse
    {
        $this->authorize('update', $institucion);

        $d = $request->validated();
        DB::transaction(function () use ($d, $institucion) {
            $plan = Plan::query()
                ->where('id_plan', $d['id_plan'])
                ->where('estado', 'activo')
                ->lockForUpdate()
                ->firstOrFail();

            $antes = $institucion->only(['nombre', 'slug', 'estado', 'id_plan']);
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

            $this->auditLogger->record(
                'institucion.editar',
                $institucion,
                $antes,
                $institucion->fresh()->only(['nombre', 'slug', 'estado', 'id_plan']),
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'Institución actualizada correctamente.',
            'redirect' => route('superadmin.instituciones.show', $institucion),
        ]);
    }

    public function activar(Institucion $institucion): JsonResponse
    {
        $this->authorize('activate', $institucion);

        return $this->cambiarEstado($institucion, 'activa', 'institucion.activar', 'Institución activada.');
    }

    public function desactivar(Institucion $institucion): JsonResponse
    {
        $this->authorize('deactivate', $institucion);

        return $this->cambiarEstado($institucion, 'suspendida', 'institucion.suspender', 'Institución suspendida.');
    }

    private function cambiarEstado(Institucion $institucion, string $estado, string $accion, string $mensaje): JsonResponse
    {
        $anterior = $institucion->estado;
        if ($anterior === $estado) {
            return response()->json(['success' => true, 'message' => 'La institución ya tiene ese estado.']);
        }

        DB::transaction(function () use ($institucion, $estado, $accion, $anterior) {
            $institucion->update(['estado' => $estado]);
            $this->auditLogger->record($accion, $institucion, ['estado' => $anterior], ['estado' => $estado]);
        });

        return response()->json(['success' => true, 'message' => $mensaje]);
    }
}
