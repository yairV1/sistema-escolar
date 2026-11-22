<?php

namespace Tests\Support;

use App\Modules\Auth\Models\Usuario;
use App\Modules\Calendario\Models\EventoCategoria;
use Database\Factories\AcudienteFactory;
use Database\Factories\ActividadFactory;
use Database\Factories\AsignacionAcademicaFactory;
use Database\Factories\AsistenciaFactory;
use Database\Factories\BoletinFactory;
use Database\Factories\CursoFactory;
use Database\Factories\EstudianteFactory;
use Database\Factories\EventoFactory;
use Database\Factories\HorarioFactory;
use Database\Factories\InstitucionFactory;
use Database\Factories\MateriaFactory;
use Database\Factories\MatriculaFactory;
use Database\Factories\NotaFactory;
use Database\Factories\ObservacionFactory;
use Database\Factories\PeriodoFactory;
use Database\Factories\ProfesorFactory;
use Database\Factories\SolicitudAdmisionFactory;
use Database\Factories\SoporteFactory;
use Database\Factories\TipoActividadFactory;
use Database\Factories\UsuarioFactory;
use Database\Seeders\EventoCategoriaSeeder;
use Database\Seeders\PermissionRoleSeeder;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\RolesSeeder;
use Illuminate\Support\Facades\DB;

/**
 * Dos colegios ficticios con datos en todos los módulos (equivalente a las
 * matrices HTTP de las Etapas 0 y 1). Marcadores únicos para detectar fugas:
 *   Alfa: EstudianteAlfa, DocenteAlfa, "Observación Alfa", SolicitudAlfa, EventoAlfa, "Quiz Alfa", SoporteAlfa
 *   Zeta: apellido OtroColegio (todas las cuentas), ProfeZeta, "Observación Zeta", SolicitudZeta, EventoZeta, "Taller Zeta", SoporteZeta
 *
 * Acceso: $e->a['directivo'], $e->b['curso'], $e->superadmin, …
 */
class EscenarioDosColegios
{
    public const MARCAS_A = ['/\bEstudianteAlfa\b/', '/\bDocenteAlfa\b/', '/Observación Alfa/', '/SolicitudAlfa/', '/EventoAlfa/', '/Quiz Alfa/'];

    public const MARCAS_B = ['/OtroColegio/', '/ProfeZeta/', '/Observación Zeta/', '/SolicitudZeta/', '/EventoZeta/', '/Taller Zeta/'];

    public array $a = [];

    public array $b = [];

    public Usuario $superadmin;

    public static function crear(): self
    {
        (new RolesSeeder)->run();
        (new PermissionsSeeder)->run();
        (new PermissionRoleSeeder)->run();
        (new EventoCategoriaSeeder)->run();

        $e = new self;
        // Catálogos todavía globales (Etapa 2 los separará por colegio)
        $materia = MateriaFactory::new()->create(['nombre_materia' => 'Matemáticas']);
        $materia2 = MateriaFactory::new()->create(['nombre_materia' => 'Ciencias']);
        $periodo = PeriodoFactory::new()->create(['nombre_periodo' => 'Periodo Prueba']);
        $tipo = TipoActividadFactory::new()->create(['nombre_tipo' => 'Evaluación prueba']);
        $categoria = EventoCategoria::where('slug', 'eventos-institucionales')->firstOrFail();

        $e->a = $e->colegio('Alfa', 'Prueba', '6P', $materia, $materia2, $periodo, $tipo, $categoria, completo: true);
        $e->b = $e->colegio('Zeta', 'OtroColegio', '9Z', $materia, $materia2, $periodo, $tipo, $categoria, completo: false);

        $e->superadmin = UsuarioFactory::new()->create(['nombres' => 'SuperAdmin', 'apellidos' => 'Plataforma', 'id_rol' => 8, 'id_institucion' => null]);
        $e->a['materia'] = $e->b['materia'] = $materia;
        $e->a['periodo'] = $e->b['periodo'] = $periodo;
        $e->a['tipo'] = $e->b['tipo'] = $tipo;

        return $e;
    }

    private function colegio(string $marca, string $apellido, string $nombreCurso, $materia, $materia2, $periodo, $tipo, $categoria, bool $completo): array
    {
        $inst = InstitucionFactory::new()->create(['nombre' => "Colegio {$marca}", 'slug' => strtolower("colegio-{$marca}")]);
        $u = fn (string $nombres, int $rol) => UsuarioFactory::new()->create(['nombres' => $nombres, 'apellidos' => $apellido, 'id_rol' => $rol, 'id_institucion' => $inst->id_institucion]);
        $docenteNombre = $marca === 'Alfa' ? 'DocenteAlfa' : 'ProfeZeta';

        $c = ['institucion' => $inst];
        $c['directivo'] = $u("Directivo{$marca}", 2);
        $c['docente_usuario'] = $u($docenteNombre, 5);
        $c['profesor'] = ProfesorFactory::new()->create(['id_usuario' => $c['docente_usuario']->id_usuario]);
        $c['curso'] = CursoFactory::new()->create(['nombre_curso' => $nombreCurso, 'id_director_grupo' => $c['profesor']->id_profesor, 'id_institucion' => $inst->id_institucion]);
        $c['estudiante_usuario'] = $u("Estudiante{$marca}", 6);
        $c['estudiante'] = EstudianteFactory::new()->create(['id_usuario' => $c['estudiante_usuario']->id_usuario]);
        $c['matricula'] = MatriculaFactory::new()->create(['id_estudiante' => $c['estudiante']->id_estudiante, 'id_curso' => $c['curso']->id_curso]);
        $c['asignacion'] = AsignacionAcademicaFactory::new()->create(['id_profesor' => $c['profesor']->id_profesor, 'id_materia' => $materia->id_materia, 'id_curso' => $c['curso']->id_curso]);
        $c['horario'] = HorarioFactory::new()->create(['id_asignacion' => $c['asignacion']->id_asignacion]);
        $c['actividad'] = ActividadFactory::new()->create(['id_asignacion' => $c['asignacion']->id_asignacion, 'id_periodo' => $periodo->id_periodo, 'id_tipo_actividad' => $tipo->id_tipo_actividad, 'titulo' => $marca === 'Alfa' ? 'Quiz Alfa' : 'Taller Zeta']);
        $c['nota'] = NotaFactory::new()->create(['id_actividad' => $c['actividad']->id_actividad, 'id_estudiante' => $c['estudiante']->id_estudiante, 'nota' => 4.5]);
        $c['asistencia'] = AsistenciaFactory::new()->create(['id_estudiante' => $c['estudiante']->id_estudiante, 'id_asignacion' => $c['asignacion']->id_asignacion]);
        $c['observacion'] = ObservacionFactory::new()->create(['id_estudiante' => $c['estudiante']->id_estudiante, 'id_profesor' => $c['profesor']->id_profesor, 'descripcion' => "Observación {$marca}"]);
        $c['boletin'] = BoletinFactory::new()->create(['id_estudiante' => $c['estudiante']->id_estudiante, 'id_periodo' => $periodo->id_periodo]);
        $c['solicitud'] = SolicitudAdmisionFactory::new()->create(['id_institucion' => $inst->id_institucion, 'nombre_acudiente' => "Solicitud{$marca}"]);
        $c['evento'] = EventoFactory::new()->create(['id_categoria' => $categoria->id_categoria, 'id_usuario_creador' => $c['directivo']->id_usuario, 'titulo' => "Evento{$marca}", 'fecha_inicio' => '2026-11-20', 'fecha_fin' => '2026-11-20']);
        $c['soporte'] = SoporteFactory::new()->create(['id_usuario' => $c['directivo']->id_usuario, 'asunto' => "Soporte{$marca}"]);

        if ($completo) {
            $c['coordinador'] = $u('CoordinadorAlfa', 3);
            $c['secretario'] = $u('SecretarioAlfa', 4);
            $c['docente2_usuario'] = $u('DocenteDos', 5);
            $c['profesor2'] = ProfesorFactory::new()->create(['id_usuario' => $c['docente2_usuario']->id_usuario]);
            $c['estudiante2_usuario'] = $u('EstudianteDos', 6);
            $c['estudiante2'] = EstudianteFactory::new()->create(['id_usuario' => $c['estudiante2_usuario']->id_usuario]);
            MatriculaFactory::new()->create(['id_estudiante' => $c['estudiante2']->id_estudiante, 'id_curso' => $c['curso']->id_curso]);
            $c['estudiante_sin_curso_usuario'] = $u('EstudianteSinCurso', 6);
            $c['estudiante_sin_curso'] = EstudianteFactory::new()->create(['id_usuario' => $c['estudiante_sin_curso_usuario']->id_usuario]);
            $c['curso7a'] = CursoFactory::new()->create(['nombre_curso' => '7A', 'id_institucion' => $inst->id_institucion]);
            $c['asignacion2'] = AsignacionAcademicaFactory::new()->create(['id_profesor' => $c['profesor2']->id_profesor, 'id_materia' => $materia2->id_materia, 'id_curso' => $c['curso']->id_curso]);
            $c['actividad2'] = ActividadFactory::new()->create(['id_asignacion' => $c['asignacion2']->id_asignacion, 'id_periodo' => $periodo->id_periodo, 'id_tipo_actividad' => $tipo->id_tipo_actividad]);
            $c['observacion2'] = ObservacionFactory::new()->create(['id_estudiante' => $c['estudiante2']->id_estudiante, 'id_profesor' => $c['profesor2']->id_profesor, 'descripcion' => 'Observación privada de DocenteDos']);
            $c['acudiente_usuario'] = $u('AcudienteAlfa', 7);
            $c['acudiente'] = AcudienteFactory::new()->create(['id_usuario' => $c['acudiente_usuario']->id_usuario]);
            DB::table('estudiante_acudiente')->insert(['id_estudiante' => $c['estudiante']->id_estudiante, 'id_acudiente' => $c['acudiente']->id_acudiente, 'parentesco' => 'madre', 'es_principal' => 1, 'estado' => 'activo']);
        }

        return $c;
    }
}
