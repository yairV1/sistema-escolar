<?php

namespace App\Modules\Calendario\Services;

use App\Modules\Auth\Models\Usuario;
use App\Modules\Calendario\Models\Evento;
use App\Modules\Calendario\Models\EventoCategoria;
use App\Modules\GestionAcademica\Models\AsignacionAcademica;
use App\Modules\GestionAcademica\Models\Curso;
use App\Modules\Matriculas\Models\Matricula;
use App\Modules\Usuarios\Models\Acudiente;
use App\Modules\Usuarios\Models\Estudiante;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Punto único de "qué cursos puede ver este usuario en el calendario" y
 * "puede ver esta categoría". Inyectado en las 3 occurrence sources para
 * que cada una filtre en la consulta SQL (nunca traer de más y descartar
 * después).
 */
class VisibilidadCalendarioService
{
    /**
     * Roles con acceso administrativo amplio: ven todos los cursos.
     * No es lo mismo que Usuario::tienePanelAdmin() (esa gobierna acceso
     * a rutas del panel; esta gobierna alcance de datos del calendario).
     */
    private const ROLES_VEN_TODO = ['admin', 'rector', 'coordinador', 'secretario'];

    /**
     * @return array<int>|null null significa "todos los cursos"; un array
     *                          (incluso vacío) es la lista real visible.
     */
    public function cursosVisibles(Usuario $usuario): ?array
    {
        if (in_array($usuario->rolSlug, self::ROLES_VEN_TODO, true)) {
            return null;
        }

        return match ($usuario->rolSlug) {
            'docente' => $this->cursosDeDocente($usuario),
            'estudiante' => $this->cursosDeEstudiante($usuario),
            'acudiente' => $this->cursosDeAcudiente($usuario),
            default => [],
        };
    }

    public function puedeVerCategoria(Usuario $usuario, EventoCategoria $categoria): bool
    {
        return $categoria->esVisibleParaRol($usuario->rolSlug);
    }

    /** Cursos reales (no solo IDs) para poblar el <select> del filtro por curso. */
    public function cursosParaFiltro(Usuario $usuario): Collection
    {
        $idsVisibles = $this->cursosVisibles($usuario);

        return Curso::query()
            ->where('estado', 'activo')
            ->when(is_array($idsVisibles), fn ($q) => $q->whereIn('id_curso', $idsVisibles))
            ->orderByDesc('anio_lectivo')
            ->orderBy('nombre_curso')
            ->get(['id_curso', 'nombre_curso', 'anio_lectivo']);
    }

    /**
     * Misma regla que EventoOccurrenceSource aplica en su query (público +
     * categoría visible + curso visible, o propio, o participante en un
     * evento compartido) — factorizada acá para que la Policy y el
     * occurrence source no la dupliquen en dos sitios.
     */
    public function puedeVerEvento(Usuario $usuario, Evento $evento): bool
    {
        if ($evento->id_usuario_creador === $usuario->id_usuario) {
            return true;
        }

        if ($evento->visibilidad === 'compartido') {
            return $evento->participantes()->where('id_usuario', $usuario->id_usuario)->exists();
        }

        if ($evento->visibilidad !== 'publico') {
            return false;
        }

        if (! $evento->categoria || ! $this->puedeVerCategoria($usuario, $evento->categoria)) {
            return false;
        }

        $cursosVisibles = $this->cursosVisibles($usuario);

        return $evento->id_curso === null || $cursosVisibles === null || in_array($evento->id_curso, $cursosVisibles, true);
    }

    /**
     * Personal activo (admin/rector/coordinador/secretario/docente, id_rol
     * 1-5 según Usuario::ROLE_SLUGS) para poblar el selector de
     * participantes al compartir un evento — sin buscador, ver decisión
     * confirmada en el plan de Fase 3.
     */
    public function personalParaCompartir(): Collection
    {
        return Usuario::query()
            ->where('estado_usuario', 'activo')
            ->whereIn('id_rol', [1, 2, 3, 4, 5])
            ->orderBy('nombres')
            ->get(['id_usuario', 'nombres', 'apellidos', 'id_rol']);
    }

    private function cursosDeDocente(Usuario $usuario): array
    {
        $idProfesor = $usuario->profesor?->id_profesor;
        if (! $idProfesor) {
            return [];
        }

        return AsignacionAcademica::where('id_profesor', $idProfesor)
            ->where('estado', 'activo')
            ->pluck('id_curso')
            ->unique()
            ->values()
            ->all();
    }

    private function cursosDeEstudiante(Usuario $usuario): array
    {
        $idEstudiante = Estudiante::where('id_usuario', $usuario->id_usuario)->value('id_estudiante');
        if (! $idEstudiante) {
            return [];
        }

        return Matricula::where('id_estudiante', $idEstudiante)
            ->where('estado_matricula', 'activa')
            ->pluck('id_curso')
            ->unique()
            ->values()
            ->all();
    }

    private function cursosDeAcudiente(Usuario $usuario): array
    {
        $idAcudiente = Acudiente::where('id_usuario', $usuario->id_usuario)->value('id_acudiente');
        if (! $idAcudiente) {
            return [];
        }

        $idsEstudiantes = DB::table('estudiante_acudiente')
            ->where('id_acudiente', $idAcudiente)
            ->where('estado', 'activo')
            ->pluck('id_estudiante');

        if ($idsEstudiantes->isEmpty()) {
            return [];
        }

        return Matricula::whereIn('id_estudiante', $idsEstudiantes)
            ->where('estado_matricula', 'activa')
            ->pluck('id_curso')
            ->unique()
            ->values()
            ->all();
    }
}
