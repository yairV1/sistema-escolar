# Etapa 0 — Contención urgente del aislamiento entre instituciones

**Fecha:** 2026-11-18 · **Rama:** `etapa0-contencion` (sin commit) · **Migraciones:** ninguna.

> ⚠️ Esta etapa **contiene** las vulnerabilidades más graves. El aislamiento entre colegios **no está resuelto**: ver §4. El sistema **no está listo para producción**.

## 1. Archivos modificados (12)

| Archivo | Cambio |
|---|---|
| `app/Modules/Instituciones/Support/TenantContext.php` | `esDeLaActual()` y `asegurar()`: regla única, niega por defecto, responde 404 |
| `app/Modules/Auth/Models/Usuario.php` | `puedeGestionarAsignacion()`: el panel administrativo solo gestiona asignaciones de **su** institución |
| `app/Modules/GestionAcademica/Models/AsignacionAcademica.php` | `estudiantesFueraDelCurso()`: mismo criterio que la planilla (matrícula activa en el curso) |
| `app/Modules/Calificaciones/Controllers/CalificacionesController.php` | `guardarNotas`: rechaza (422, todo o nada) estudiantes no matriculados en el curso |
| `app/Modules/Rector/Controllers/AsistenciaController.php` | `guardar`: mismo control que las notas |
| `app/Modules/Usuarios/Controllers/Registro/RegistroAdministrativosController.php` | `edit`/`show`/`update`: solo administrativos (roles 1–4) de la propia institución |
| `app/Modules/Usuarios/Controllers/Registro/RegistroEstudiantesController.php` | `edit`/`show`/`update`: solo estudiantes de la propia institución |
| `app/Modules/Usuarios/Controllers/Registro/RegistroDocentesController.php` | `edit`/`show`/`update`: solo docentes de la propia institución |
| `app/Modules/Usuarios/Controllers/ListadosController.php` | (des)activar estudiante/docente/administrativo: solo propia institución; administrativos solo roles 1–4 |
| `app/Modules/Auth/Controllers/RolController.php` | renombrar/(des)activar roles: solo SuperAdmin con `plataforma.roles.gestionar` (403 para el panel institucional); conteo por rol solo de la propia institución |
| `resources/views/Rector/roles/index.blade.php` | modo solo lectura; se corrige el texto que decía que el estado del rol "no afecta el acceso" (falso: `estaActivo()` bloquea el login) |
| `app/Modules/Comunicados/Controllers/ComunicadosController.php` | destinatarios solo de la institución del remitente; listado y detalle solo de la propia institución |

Respaldo de los originales: `~/Respaldos/sistema-escolar/etapa0-20261118_135502/` (13 archivos, `SHA256SUMS`).

## 2. Vulnerabilidades contenidas

| ID | Antes | Ahora |
|---|---|---|
| **V1** Toma de cuentas | Cualquier Directivo editaba correo/rol de cualquier cuenta (incl. SuperAdmin) y (des)activaba cuentas de otros colegios | 404 fuera de su institución; la pantalla de administrativos solo actúa sobre roles 1–4; `id_rol` sigue limitado a 1–4 (422) |
| **V2** Bloqueo global por roles | Cualquier Directivo desactivaba/renombraba roles globales (bloqueaba el login de todos los colegios, incl. SuperAdmin) | 403 para todo el panel institucional; el SuperAdmin conserva su módulo `superadmin/roles` |
| **V3/V5** Notas, planillas y asistencia | Cualquier Directivo veía, descargaba y modificaba asignaciones de cualquier colegio | 403 fuera de su institución |
| **V4** Comunicados | Difusión a usuarios de todos los colegios | Solo usuarios de la institución del remitente (validado en el servidor) |
| **V8** Integridad | Notas/asistencia aceptaban cualquier `id_estudiante` existente | 422 si algún estudiante no está matriculado en el curso; no se guarda nada |

## 3. Pruebas (HTTP real, base aislada `colegio_restauracion_prueba`, solo datos ficticios)

PHPUnit no se ejecutó: falta `php8.3-sqlite3` y no se autorizó `migrate:fresh` sobre ninguna base.

| Prueba | Resultado |
|---|---|
| Matriz de 43 casos con el código **corregido** (legítimo, otra institución, SuperAdmin, manipulación de IDs en URL y cuerpo, escalada `id_rol=8`, todo o nada, UI de roles; verificación de la BD antes/después en cada escritura) | **43 / 43 OK** |
| Misma matriz contra el código **original** (línea base) | **32 fallos**: confirma que la matriz detecta las vulnerabilidades |
| Comunicados remitente × destinatario | diagonal: colegio 1→1, colegio B→B |
| Regresión: 80 rutas GET × 8 roles | **0 errores 500**; solo cambian los accesos ajenos (200 → 403/404) |
| UI real (navegador): guardar notas, guardar asistencia, roles en solo lectura | 200 / 200 / sin botones; sin errores JS |
| Base real `colegio` | idéntica al respaldo (CHECKSUM TABLE) |
| Copia de prueba | restaurada al estado previo (CHECKSUM TABLE) |

## 4. Vulnerabilidades que siguen ABIERTAS

| ID | Descripción | Etapa |
|---|---|---|
| V5 (resto) | Listados, matrículas, observaciones, boletines y gestión académica siguen **mostrando y modificando** datos de otros colegios (≈60 métodos sin control) | 1 |
| V6 | Escritura sin control: estado de matrículas, observaciones, cursos, materias, horarios, asignaciones, boletines | 1 |
| V7 | Catálogos globales compartidos (periodos, tipos de actividad, materias, categorías, landing, configuración del colegio) | 2 |
| V9 | Reglas `exists:` sin institución en formularios | 1 |
| V10 | Filtro "en riesgo" de listados sin institución | 1 |
| V11 | Adjuntos y capturas en disco `public` | 3 |
| V12 | `$fillable` con `id_rol`/`id_institucion` | 1 |
| V13 | Restricciones únicas globales | 2 |
| **Nuevo** | `Curso::buscarOCrear()` busca el curso por nombre/año/jornada **sin institución**: un estudiante del colegio B registrado en "7A" queda matriculado en el "7A" del colegio A | 2 (depende de V13) |
| Coherencia | Los listados (aún sin filtrar) muestran filas de otros colegios cuyos enlaces ahora responden 404 | 1 |

## 5. Posibles regresiones
- El panel institucional ya **no puede renombrar ni (des)activar roles**. El SuperAdmin puede (des)activarlos en `superadmin/roles`, pero ese módulo **no tiene renombrar**: si se necesita, agregarlo en la Etapa 1.
- Abrir la ficha de un **docente o estudiante** desde la URL de *administrativos* responde 404 (sus pantallas propias siguen funcionando).
- Guardar notas/asistencia de una lista donde un estudiante perdió la matrícula activa después de cargar la página devuelve 422 para todo el lote (antes se guardaba).
- Un Directivo/Administrador **sin institución asignada** ya no gestiona asignaciones ni envía comunicados (antes accedía a todo).
- Los 403/404 nuevos se muestran en la interfaz con el mensaje genérico de error existente.

## 6. Reversión (solo esta etapa)
```bash
R=~/Respaldos/sistema-escolar/etapa0-20261118_135502
(cd "$R" && sha256sum -c SHA256SUMS)          # verificar respaldo
cd laravel && (cd "$R" && find . -type f -name '*.php') | while read f; do cp "$R/$f" "$f"; done
```
No usar `git checkout`/`git reset`: el árbol contiene otros cambios sin commit (login, `.htaccess`) que no pertenecen a esta etapa. Sin cambios de base de datos que revertir.

## 7. Recomendaciones para la Etapa 1
1. Servicio central (`TenantGuard`) que resuelva la institución de cualquier modelo por su cadena de relaciones, y *policies* por recurso, aplicadas a los ≈60 métodos restantes.
2. Filtrar por institución todos los listados (`whereHas`), incluido `idsEnRiesgo()`.
3. Reglas `exists:` acotadas a la institución (`Rule::exists(...)->where(...)`) en todos los formularios.
4. Reducir `$fillable` de `Usuario` y `Curso`.
5. Agregar "renombrar rol" al módulo SuperAdmin si se necesita.
6. Entorno de pruebas automatizadas: instalar `php8.3-sqlite3` (o autorizar una base dedicada a tests) y convertir esta matriz en tests de PHPUnit.
