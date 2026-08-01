<?php

namespace Database\Seeders;

use App\Modules\Auth\Models\Permission;
use Illuminate\Database\Seeder;

/**
 * Catálogo oficial de permisos aprobado en docs/arquitectura/03-rbac.md,
 * seccion 3. No agrega ni renombra ningun permiso respecto a ese documento.
 *
 * `perfil.*` se excluye a proposito: la seccion 3.13 de ese documento
 * establece explicitamente que perfil.ver/editar/cambiar_password no son
 * permisos de rol (se resuelven con un Gate de propiedad, seccion 6), por
 * lo que no pertenecen al catalogo de permisos por rol.
 *
 * Idempotente por slug: correr este seeder varias veces no duplica filas.
 */
class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->catalogo() as $modulo => $permisos) {
            foreach ($permisos as $slug => $descripcion) {
                Permission::updateOrCreate(
                    ['slug' => $slug],
                    ['modulo' => $modulo, 'descripcion' => $descripcion],
                );
            }
        }
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function catalogo(): array
    {
        return [
            // 3.1 — Identidad y acceso (modulo Auth)
            'cuentas' => [
                'cuentas.ver' => 'Consultar cuentas de usuario y su estado.',
                'cuentas.crear' => 'Crear una cuenta de acceso.',
                'cuentas.editar' => 'Modificar datos de acceso de una cuenta.',
                'cuentas.activar' => 'Reactivar una cuenta inactiva.',
                'cuentas.desactivar' => 'Inactivar una cuenta sin borrar su historial.',
                'cuentas.asignar_rol' => 'Cambiar el rol institucional de una cuenta.',
                'cuentas.resetear_password' => 'Forzar el restablecimiento de contraseña de otra cuenta.',
            ],

            // 3.2 — Personas y vinculos institucionales (modulo Usuarios)
            'usuarios' => [
                'usuarios.listados.ver' => 'Ver el listado consolidado de personas.',
                'usuarios.estudiantes.ver' => 'Consultar fichas de estudiantes.',
                'usuarios.estudiantes.crear' => 'Registrar un nuevo estudiante.',
                'usuarios.estudiantes.editar' => 'Editar datos de un estudiante.',
                'usuarios.estudiantes.activar' => 'Activar un estudiante.',
                'usuarios.estudiantes.desactivar' => 'Inactivar un estudiante sin borrar su historial.',
                'usuarios.estudiantes.exportar' => 'Exportar el listado de estudiantes.',
                'usuarios.docentes.ver' => 'Consultar fichas de docentes.',
                'usuarios.docentes.crear' => 'Registrar un nuevo docente.',
                'usuarios.docentes.editar' => 'Editar datos de un docente.',
                'usuarios.docentes.activar' => 'Activar un docente.',
                'usuarios.docentes.desactivar' => 'Inactivar un docente.',
                'usuarios.administrativos.ver' => 'Consultar fichas de personal administrativo.',
                'usuarios.administrativos.crear' => 'Registrar un nuevo administrativo.',
                'usuarios.administrativos.editar' => 'Editar datos de un administrativo.',
                'usuarios.administrativos.activar' => 'Activar un administrativo.',
                'usuarios.administrativos.desactivar' => 'Inactivar un administrativo.',
                'usuarios.acudientes.ver' => 'Consultar acudientes.',
                'usuarios.acudientes.vincular' => 'Vincular un acudiente a un estudiante.',
                'usuarios.acudientes.editar' => 'Editar datos de contacto de un acudiente.',
            ],

            // 3.3 — Admisiones y matricula (modulo Matriculas)
            'matriculas' => [
                'matriculas.ver' => 'Consultar matriculas.',
                'matriculas.crear' => 'Formalizar una nueva matricula.',
                'matriculas.editar' => 'Editar datos de una matricula existente.',
                'matriculas.cambiar_estado' => 'Cambiar el estado de una matricula.',
                'matriculas.aprobar' => 'Aprobar formalmente una solicitud de matricula.',
                'matriculas.exportar' => 'Exportar el listado de matriculas.',
            ],

            // 3.4 — Estructura academica y gestion pedagogica (modulo GestionAcademica)
            'gestion_academica' => [
                'gestion_academica.materias.ver' => 'Consultar asignaturas.',
                'gestion_academica.materias.crear' => 'Crear una asignatura.',
                'gestion_academica.materias.editar' => 'Editar una asignatura.',
                'gestion_academica.materias.activar' => 'Activar una asignatura.',
                'gestion_academica.materias.desactivar' => 'Inactivar una asignatura.',
                'gestion_academica.cursos.ver' => 'Consultar cursos/grupos.',
                'gestion_academica.cursos.crear' => 'Crear un curso/grupo.',
                'gestion_academica.cursos.editar' => 'Editar un curso/grupo.',
                'gestion_academica.cursos.activar' => 'Activar un curso/grupo.',
                'gestion_academica.cursos.desactivar' => 'Inactivar un curso/grupo.',
                'gestion_academica.asignaciones.ver' => 'Consultar asignaciones docente-asignatura-curso.',
                'gestion_academica.asignaciones.crear' => 'Crear una asignacion academica.',
                'gestion_academica.asignaciones.activar' => 'Activar una asignacion academica.',
                'gestion_academica.asignaciones.desactivar' => 'Inactivar una asignacion academica.',
                'gestion_academica.horarios.ver' => 'Consultar horarios.',
                'gestion_academica.horarios.crear' => 'Crear un horario.',
                'gestion_academica.horarios.editar' => 'Editar un horario.',
                'gestion_academica.horarios.activar' => 'Activar un horario.',
                'gestion_academica.horarios.desactivar' => 'Inactivar un horario.',
            ],

            // 3.5 — Evaluacion y desempeño academico (modulo Calificaciones)
            'calificaciones' => [
                'calificaciones.periodos.ver' => 'Consultar periodos academicos.',
                'calificaciones.periodos.crear' => 'Definir un periodo academico.',
                'calificaciones.periodos.editar' => 'Modificar un periodo academico.',
                'calificaciones.periodos.cerrar_periodo' => 'Cerrar oficialmente un periodo de evaluacion.',
                'calificaciones.tipos_actividad.ver' => 'Consultar tipos de actividad evaluativa.',
                'calificaciones.tipos_actividad.crear' => 'Crear un tipo de actividad evaluativa.',
                'calificaciones.tipos_actividad.editar' => 'Editar un tipo de actividad evaluativa.',
                'calificaciones.actividades.ver' => 'Consultar actividades evaluativas.',
                'calificaciones.actividades.crear' => 'Definir una actividad evaluativa.',
                'calificaciones.actividades.editar' => 'Modificar una actividad evaluativa.',
                'calificaciones.actividades.activar' => 'Activar una actividad evaluativa.',
                'calificaciones.actividades.desactivar' => 'Inactivar una actividad evaluativa.',
                'calificaciones.notas.ver' => 'Consultar calificaciones.',
                'calificaciones.notas.registrar' => 'Registrar calificaciones de una actividad.',
                'calificaciones.notas.editar' => 'Corregir una calificacion ya registrada.',
            ],

            // 3.6 — Evaluacion y desempeño academico (modulo Reportes / boletines)
            'boletines' => [
                'boletines.ver' => 'Consultar boletines.',
                'boletines.generar' => 'Generar el boletin oficial de un periodo.',
                'boletines.publicar' => 'Publicar un boletin para estudiante/acudiente.',
                'boletines.anular' => 'Anular un boletin generado por error.',
            ],

            // 3.7 — Asistencia y permanencia del estudiante (modulo Asistencia)
            'asistencia' => [
                'asistencia.ver' => 'Consultar registros de asistencia.',
                'asistencia.registrar' => 'Registrar asistencia de una sesion/jornada.',
                'asistencia.editar' => 'Corregir un registro de asistencia.',
                'asistencia.ver_historial' => 'Consultar historial consolidado de asistencia.',
            ],

            // 3.8 — Convivencia y seguimiento integral (modulo Observaciones)
            'observaciones' => [
                'observaciones.ver' => 'Consultar observaciones.',
                'observaciones.crear' => 'Registrar una observacion o incidencia.',
                'observaciones.editar' => 'Editar una observacion existente.',
                'observaciones.activar' => 'Activar una observacion.',
                'observaciones.desactivar' => 'Inactivar una observacion sin eliminarla.',
            ],

            // 3.9 — Reportes e inteligencia institucional (modulo Reportes)
            'reportes' => [
                'reportes.estadisticas.ver' => 'Consultar estadisticas del sistema.',
                'reportes.institucionales.ver' => 'Consultar reportes consolidados de nivel institucional.',
                'reportes.exportar' => 'Exportar un reporte.',
            ],

            // 3.10 — Comunicacion institucional (modulo Comunicados)
            'comunicados' => [
                'comunicados.ver' => 'Consultar comunicados emitidos.',
                'comunicados.crear' => 'Redactar un comunicado.',
                'comunicados.publicar' => 'Difundir un comunicado a su publico destinatario.',
                'comunicados.ver_recibidos' => 'Consultar los comunicados dirigidos al propio usuario.',
            ],

            // 3.11 — Portal publico e imagen institucional (modulo Landing)
            'landing' => [
                'landing.contenido.editar' => 'Editar el contenido institucional del portal publico.',
                'landing.noticias.crear' => 'Crear una noticia publica.',
                'landing.noticias.editar' => 'Editar una noticia publica.',
                'landing.noticias.activar' => 'Activar una noticia publica.',
                'landing.noticias.desactivar' => 'Inactivar una noticia publica.',
                'landing.galeria.crear' => 'Crear un elemento de galeria publica.',
                'landing.galeria.editar' => 'Editar un elemento de galeria publica.',
                'landing.galeria.activar' => 'Activar un elemento de galeria publica.',
                'landing.galeria.desactivar' => 'Inactivar un elemento de galeria publica.',
            ],

            // 3.12 — modulo Dashboard
            'dashboard' => [
                'dashboard.ver' => 'Acceder al panel de indicadores.',
            ],

            // Modulo plataforma (Fase A del pivote multi-tenant, ver
            // docs/arquitectura/10-superadmin-plataforma.md). Exclusivo del
            // rol SuperAdmin (id_rol=8) — ver PermissionRoleSeeder.
            //
            // planes.* y modulos.* (catalogo comercial, seccion "Catalogo
            // de planes y modulos" del mismo documento) se suman aqui
            // porque son plataforma.* igual que el resto: exclusivos de
            // SuperAdmin, sin alcance por institucion.
            'plataforma' => [
                'plataforma.instituciones.ver' => 'Consultar instituciones (tenants) de la plataforma.',
                'plataforma.instituciones.crear' => 'Registrar una nueva institucion en la plataforma.',
                'plataforma.instituciones.editar' => 'Editar los datos de una institucion.',
                'plataforma.instituciones.activar' => 'Reactivar una institucion suspendida o inactiva.',
                'plataforma.instituciones.desactivar' => 'Suspender o inactivar una institucion.',
                'plataforma.usuarios_globales.ver' => 'Consultar usuarios de todas las instituciones.',
                'plataforma.usuarios_globales.gestionar' => 'Cambiar rol o estado de un usuario de cualquier institucion.',
                'plataforma.roles.gestionar' => 'Editar la matriz de roles y permisos de la plataforma.',
                'plataforma.auditoria.ver' => 'Consultar la bitacora de auditoria de la plataforma.',
                'plataforma.metricas.ver' => 'Consultar metricas globales de uso y crecimiento.',
                'plataforma.configuracion.editar' => 'Editar la configuracion global de la plataforma.',
                'plataforma.planes.ver' => 'Consultar el catalogo de planes comerciales.',
                'plataforma.planes.crear' => 'Crear o duplicar un plan comercial.',
                'plataforma.planes.editar' => 'Editar un plan comercial y sus modulos incluidos.',
                'plataforma.planes.activar' => 'Reactivar un plan comercial.',
                'plataforma.planes.desactivar' => 'Desactivar un plan comercial (deja de poder contratarse).',
                'plataforma.modulos.ver' => 'Consultar el catalogo de modulos funcionales.',
                'plataforma.modulos.crear' => 'Crear un modulo funcional.',
                'plataforma.modulos.editar' => 'Editar un modulo funcional.',
                'plataforma.modulos.activar' => 'Reactivar un modulo funcional.',
                'plataforma.modulos.desactivar' => 'Desactivar un modulo funcional.',
            ],
        ];
    }
}
