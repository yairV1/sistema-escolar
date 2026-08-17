<?php

namespace App\Modules\Comunicados\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Auth\Models\Usuario;
use App\Modules\Comunicados\Mail\ComunicadoMail;
use App\Modules\Comunicados\Models\Notificacion;
use App\Modules\Comunicados\Requests\ComunicadoRequest;
use App\Modules\Comunicados\Services\WhatsappCloudService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ComunicadosController extends Controller
{
    /** Mapeo de audiencia elegida en el form -> ids de rol reales de la BD. */
    private const AUDIENCIAS = [
        'estudiantes' => [6],
        'docentes' => [5],
        'administrativos' => [1, 2, 3, 4],
        'acudientes' => [7],
    ];

    public function __construct(private WhatsappCloudService $whatsapp) {}

    public function index(): View
    {
        $comunicados = DB::table('notificaciones')
            ->select(
                'titulo',
                'mensaje',
                'tipo_notificacion',
                'canal',
                'fecha_envio',
                DB::raw('COUNT(*) as total_destinatarios'),
                DB::raw('SUM(leida) as total_leidos')
            )
            ->groupBy('titulo', 'mensaje', 'tipo_notificacion', 'canal', 'fecha_envio')
            ->orderByDesc('fecha_envio')
            ->paginate(15);

        return view('Rector.comunicados.index', [
            'currentPage' => 'Comunicados',
            'comunicados' => $comunicados,
        ]);
    }

    /** Reconstruye el "comunicado" a partir del mismo criterio de agrupación que index(): no hay un id de lote propio, así que titulo+tipo+canal+fecha_envio identifican el envío. */
    public function detalle(Request $request): View
    {
        $filtros = $request->only(['titulo', 'tipo_notificacion', 'canal', 'fecha_envio']);

        $notificaciones = Notificacion::query()
            ->where('titulo', $filtros['titulo'] ?? null)
            ->where('tipo_notificacion', $filtros['tipo_notificacion'] ?? null)
            ->where('canal', $filtros['canal'] ?? null)
            ->where('fecha_envio', $filtros['fecha_envio'] ?? null)
            ->with('destino')
            ->orderBy('leida')
            ->orderBy('id_notificacion')
            ->get();

        abort_if($notificaciones->isEmpty(), 404);

        return view('Rector.comunicados.detalle', [
            'currentPage' => 'Comunicados',
            'comunicado' => $notificaciones->first(),
            'notificaciones' => $notificaciones,
        ]);
    }

    public function store(ComunicadoRequest $request): JsonResponse
    {
        $data = $request->validated();

        $idsRoles = collect($data['audiencias'])->flatMap(fn ($a) => self::AUDIENCIAS[$a])->unique()->values();

        $destinatarios = Usuario::where('estado_usuario', 'activo')
            ->whereIn('id_rol', $idsRoles)
            ->get(['id_usuario', 'correo', 'telefono', 'notificaciones_email']);

        if ($destinatarios->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No hay usuarios activos para los destinatarios seleccionados.'], 422);
        }

        $ahora = now();
        $filas = $destinatarios->map(fn (Usuario $usuario) => [
            'titulo' => $data['titulo'],
            'mensaje' => $data['mensaje'],
            'id_usuario_origen' => auth()->id(),
            'id_usuario_destino' => $usuario->id_usuario,
            'tipo_notificacion' => $data['tipo_notificacion'],
            'canal' => $data['canal'],
            'fecha_envio' => $ahora,
            'leida' => false,
            'estado' => 'enviada',
        ])->all();

        Notificacion::insert($filas);

        if (in_array($data['canal'], ['correo', 'todos'], true)) {
            $this->enviarPorCorreo($destinatarios, $data['titulo'], $data['mensaje'], $data['tipo_notificacion']);
        }

        if (in_array($data['canal'], ['whatsapp', 'todos'], true)) {
            $this->enviarPorWhatsapp($destinatarios, $data['titulo'], $data['mensaje']);
        }

        return response()->json(['success' => true, 'message' => 'Comunicado enviado a '.count($filas).' destinatario(s).']);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Usuario>  $destinatarios
     */
    private function enviarPorWhatsapp($destinatarios, string $titulo, string $mensaje): void
    {
        foreach ($destinatarios as $usuario) {
            if (empty($usuario->telefono)) {
                continue;
            }

            $this->whatsapp->enviarComunicado($usuario->telefono, $titulo, $mensaje);
        }
    }

    /**
     * Envio real por correo. Cada destinatario se envia por separado para
     * que un correo invalido no descarte el resto del lote; los fallos se
     * registran en el log sin interrumpir la respuesta al usuario.
     *
     * @param  \Illuminate\Support\Collection<int, Usuario>  $destinatarios
     */
    private function enviarPorCorreo($destinatarios, string $titulo, string $mensaje, string $tipoNotificacion): void
    {
        foreach ($destinatarios as $usuario) {
            if (empty($usuario->correo) || ! $usuario->notificaciones_email) {
                continue;
            }

            try {
                Mail::to($usuario->correo)->send(new ComunicadoMail($titulo, $mensaje, $tipoNotificacion));
            } catch (\Throwable $e) {
                Log::warning('No se pudo enviar el comunicado por correo.', [
                    'id_usuario' => $usuario->id_usuario,
                    'correo' => $usuario->correo,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
