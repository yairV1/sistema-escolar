<?php

namespace App\Modules\Dashboard\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Calendario\Models\Evento;
use App\Modules\Comunicados\Models\Notificacion;
use App\Modules\Matriculas\Models\Matricula;
use App\Modules\Matriculas\Models\SolicitudAdmision;
use App\Modules\Usuarios\Models\Estudiante;
use App\Modules\Usuarios\Models\Profesor;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $anioActual = now()->year;
        // Todas las consultas de este dashboard se acotan a la institución
        // del admin/rector logueado — ningún dato debe filtrarse entre
        // instituciones (ver docs/arquitectura/10-superadmin-plataforma.md,
        // Fase A). estudiantes/profesores/matriculas/boletines/asistencia no
        // tienen columna propia: se filtran vía el usuario asociado. cursos
        // y solicitudes_admision sí la tienen (no cuelgan de ningún usuario).
        $idInstitucion = auth()->user()->id_institucion;

        $estudiantesMatriculados = Matricula::where('anio_lectivo', $anioActual)
            ->where('estado_matricula', 'activa')
            ->whereHas('estudiante.usuario', fn ($q) => $q->where('id_institucion', $idInstitucion))
            ->count();

        $docentesActivos = Profesor::where('estado_laboral', 'activo')
            ->whereHas('usuario', fn ($q) => $q->where('id_institucion', $idInstitucion))
            ->count();

        $solicitudesPendientes = SolicitudAdmision::where('estado', 'pendiente')
            ->where('id_institucion', $idInstitucion)
            ->count();

        // Un boletín en borrador todavía no tiene promedio_general calculado
        // (NULL hasta que se publica) — promediarlo junto a los publicados
        // arrastra el KPI hacia un valor engañoso en vez de "N/D".
        $promedioInstitucional = DB::table('boletines')
            ->join('estudiantes', 'estudiantes.id_estudiante', '=', 'boletines.id_estudiante')
            ->join('usuarios', 'usuarios.id_usuario', '=', 'estudiantes.id_usuario')
            ->where('usuarios.id_institucion', $idInstitucion)
            ->where('boletines.estado', 'publicado')
            ->avg('boletines.promedio_general');

        $asistenciaPromedio = DB::table('asistencia')
            ->join('estudiantes', 'estudiantes.id_estudiante', '=', 'asistencia.id_estudiante')
            ->join('usuarios', 'usuarios.id_usuario', '=', 'estudiantes.id_usuario')
            ->where('usuarios.id_institucion', $idInstitucion)
            ->selectRaw('SUM(estado_asistencia = "presente") / NULLIF(COUNT(*), 0) * 100 as porcentaje')
            ->value('porcentaje');

        $estudiantesEnRiesgo = Estudiante::idsEnRiesgo($idInstitucion)->count();

        $promedioPorGrado = DB::table('cursos')
            ->join('matriculas', 'matriculas.id_curso', '=', 'cursos.id_curso')
            ->join('boletines', 'boletines.id_estudiante', '=', 'matriculas.id_estudiante')
            ->where('cursos.id_institucion', $idInstitucion)
            ->where('boletines.estado', 'publicado')
            ->select('cursos.nombre_curso', 'cursos.nivel_academico', DB::raw('AVG(boletines.promedio_general) as promedio'))
            ->groupBy('cursos.nombre_curso', 'cursos.nivel_academico')
            ->orderByRaw("FIELD(cursos.nivel_academico, 'preescolar','primaria','secundaria','media')")
            ->orderBy('cursos.nombre_curso')
            ->get();

        // Conteo de matriculados por curso para la tabla del dashboard — en
        // una consulta aparte de $promedioPorGrado porque esa cuenta solo
        // cursa con boletín publicado (undercuenta matrícula real); acá se
        // cuenta la matrícula activa del año en curso, mismo criterio que
        // $estudiantesMatriculados arriba.
        $estudiantesPorCurso = DB::table('cursos')
            ->join('matriculas', 'matriculas.id_curso', '=', 'cursos.id_curso')
            ->where('cursos.id_institucion', $idInstitucion)
            ->where('matriculas.anio_lectivo', $anioActual)
            ->where('matriculas.estado_matricula', 'activa')
            ->select('cursos.nombre_curso', DB::raw('COUNT(DISTINCT matriculas.id_estudiante) as total'))
            ->groupBy('cursos.nombre_curso')
            ->pluck('total', 'cursos.nombre_curso');

        $promedioPorGrado = $promedioPorGrado->map(function ($curso) use ($estudiantesPorCurso) {
            $curso->total_estudiantes = $estudiantesPorCurso[$curso->nombre_curso] ?? 0;

            return $curso;
        });

        // Calendario no filtra por institución en ningún punto reutilizable
        // hoy (ver VisibilidadCalendarioService — para admin/rector devuelve
        // "ve todo curso", no "ve todo lo de su institución"), así que ese
        // aislamiento se hace acá mismo, vía el creador del evento — mismo
        // patrón que $comunicadosRecientes más abajo. fecha_fin >= hoy (no
        // fecha_inicio) para no perder un evento multi-día ya iniciado.
        $proximosEventos = Evento::activos()
            ->whereHas('creador', fn ($q) => $q->where('id_institucion', $idInstitucion))
            ->where('fecha_fin', '>=', now()->toDateString())
            ->with('categoria')
            ->orderBy('fecha_inicio')
            ->orderBy('hora_inicio')
            ->limit(4)
            ->get();

        $matriculasRecientes = Matricula::with(['estudiante.usuario', 'curso'])
            ->whereHas('estudiante.usuario', fn ($q) => $q->where('id_institucion', $idInstitucion))
            ->orderByDesc('fecha_matricula')
            ->limit(5)
            ->get();

        $docentesLista = Profesor::with(['usuario', 'asignaciones.materia', 'asignaciones.curso'])
            ->where('estado_laboral', 'activo')
            ->whereHas('usuario', fn ($q) => $q->where('id_institucion', $idInstitucion))
            ->limit(6)
            ->get();

        $estudiantesMuestra = Estudiante::with(['usuario', 'matriculas' => fn ($q) => $q->latest('fecha_matricula')->with('curso')])
            ->where('estado_academico', 'activo')
            ->whereHas('usuario', fn ($q) => $q->where('id_institucion', $idInstitucion))
            ->limit(6)
            ->get();

        // Un "comunicado" es un envío masivo: N filas en notificaciones (una
        // por destinatario) agrupadas por (titulo, fecha_envio,...), mismo
        // criterio que ComunicadosController::index(). El origen (quien lo
        // redactó) determina de qué institución es, no el destinatario.
        $comunicadosRecientes = DB::table('notificaciones')
            ->join('usuarios', 'usuarios.id_usuario', '=', 'notificaciones.id_usuario_origen')
            ->where('usuarios.id_institucion', $idInstitucion)
            ->select('notificaciones.titulo', 'notificaciones.tipo_notificacion', 'notificaciones.fecha_envio')
            ->groupBy('notificaciones.titulo', 'notificaciones.tipo_notificacion', 'notificaciones.fecha_envio')
            ->orderByDesc('notificaciones.fecha_envio')
            ->limit(5)
            ->get();

        return view('Rector.dashboard.index', [
            'currentPage' => 'inicio',
            'kpis' => [
                'estudiantesMatriculados' => $estudiantesMatriculados,
                'docentesActivos' => $docentesActivos,
                'solicitudesPendientes' => $solicitudesPendientes,
                'promedioInstitucional' => $promedioInstitucional,
                'asistenciaPromedio' => $asistenciaPromedio,
                'estudiantesEnRiesgo' => $estudiantesEnRiesgo,
            ],
            'promedioPorGrado' => $promedioPorGrado,
            'proximosEventos' => $proximosEventos,
            'matriculasRecientes' => $matriculasRecientes,
            'docentesLista' => $docentesLista,
            'estudiantesMuestra' => $estudiantesMuestra,
            'comunicadosRecientes' => $comunicadosRecientes,
        ]);
    }
}
