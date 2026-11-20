# Etapa 1 — Aislamiento y autorización por recurso

**Fecha:** 2026-11-18 · **Rama:** `etapa0-contencion` (sin commit) · **Migraciones:** ninguna.

> ⚠️ El aislamiento entre colegios **no está completo**: los catálogos siguen siendo globales (§5) y faltan las pruebas automatizadas permanentes. El sistema **no está listo para producción**.

## 1. Diseño: un solo mapa, tres capas

`PerteneceAInstitucion` (trait) declara cómo llega cada modelo a su institución (`RUTA_INSTITUCION`):

| Modelo | Ruta | Modelo | Ruta |
|---|---|---|---|
| Usuario, Curso, SolicitudAdmision | columna propia | Horario, Actividad, Asistencia | `asignacion.curso` |
| Estudiante, Profesor, Acudiente | `usuario` | Nota | `actividad.asignacion.curso` |
| Soporte | `remitente` | Observacion, Boletin | `estudiante.usuario` |
| AsignacionAcademica, Matricula | `curso` | BoletinDetalle | `boletin.estudiante.usuario` |
| Notificacion | `destino` | Evento | `creador` |
| EventoAdjunto, EventoComentario | `evento.creador` | | |

Ese mapa alimenta:
1. **Rutas** — `EnsureRecursosDeInstitucion` (grupo `web`, después de `SubstituteBindings`): todo modelo de la URL debe ser de la institución del usuario; si no, o si no puede determinarse, **404**. SuperAdmin exento (plataforma; las rutas académicas se lo niegan por rol). Cubre **63** de 103 parámetros de URL; el resto: 22 catálogos globales y 18 rutas del SuperAdmin.
2. **Consultas** — scope `deInstitucion()` (falla cerrado sin institución).
3. **Formularios** — `TenantGuard::existe(Modelo::class)` en lugar de `exists:`.

## 2. Cambios
- **Listados, conteos, búsquedas, estadísticas, exportaciones:** Listados (12), Gestión académica (13), Matrículas (8), Observaciones (7), Boletines (5), Estadísticas (6), Anotaciones (1), Soporte (4, salvo SuperAdmin), `Estudiante::idsEnRiesgo()` (ahora siempre acotado; corrige 6 llamadores). Dashboard y Búsqueda ya filtraban: se corrige `where(col, null)` ⇒ `IS NULL` con `?? 0`.
- **Calendario:** `cursosVisibles()` de roles administrativos = cursos de *su* institución (antes: todos); eventos públicos solo dentro de la institución del creador (consulta y Policy); "compartir con" solo personal propio.
- **Formularios:** 15 reglas `exists:` institucionales + participantes de eventos acotados.
- **Asignación masiva:** `id_rol` e `id_institucion` fuera de `Usuario::$fillable`; `id_institucion` fuera de `Curso::$fillable`. Los 9 consumidores legítimos usan `forceCreate()` / `forceFill()` explícitos.
- **Hallazgo crítico:** `Curso::buscarOCrear()` busca solo en la institución; si el nombre está ocupado por otra institución (restricción única global), **bloquea** con un error de validación controlado (transacción revertida). `Acudiente::buscarOCrear()` no vincula cuentas de otra institución. Sin institución por defecto, sin tocar históricos.
- Precarga de solicitud de admisión acotada.

Archivos: 51 modificados, 3 nuevos (lista exacta en el respaldo, `etapa1_modificados.txt` / `etapa1_nuevos.txt`).

## 3. Pruebas (HTTP real, base aislada, datos ficticios; 2 colegios con datos en todos los módulos)

| Prueba | Resultado |
|---|---|
| Matriz Etapa 1: URL (46), escrituras cruzadas (17), formularios con IDs ajenos (13), exportaciones (2), listados/feeds/búsquedas sin fugas (52), registro y acudiente (4), asignación masiva (1), SuperAdmin (5) | **142 / 142** |
| Misma matriz contra el código **anterior** | **73 fallos** (valida que la matriz detecta las vulnerabilidades) |
| Matriz Etapa 0 (regresión) | 43 / 43 |
| Recorrido GET × 8 roles | 0 errores 500; solo cambian accesos ajenos |
| Navegador: 18 pantallas + calendario (feed y pintado) | sin errores JS (salvo WebSocket de Reverb, preexistente) |
| Base real `colegio` | idéntica al respaldo (CHECKSUM TABLE) |

**Pruebas automatizadas permanentes (PHPUnit): NO ejecutadas** — falta `php8.3-sqlite3` y no hay autorización para preparar una base de tests (`migrate:fresh`). Se actualizó `tests/Feature/SuperAdmin/SuperAdminTestCase.php` (`forceCreate`) para que siga siendo válido cuando el entorno exista; no se ha podido ejecutar.

## 4. Vulnerabilidades cerradas
V1–V4, V8 (Etapa 0) · **V5, V6, V9, V10, V12** · cruce de matrícula/acudiente · calendario entre colegios · soportes del admin institucional.

## 5. Abiertas (dependen de la Etapa 2 u otras fases)
| Tema | Detalle |
|---|---|
| **V7 catálogos globales** | Materias, periodos, tipos de actividad, categorías del calendario, landing, imágenes y configuración del colegio son uno solo: un Directivo puede editar lo que ven todos. 22 rutas. |
| **V13 restricciones únicas globales** | Cursos, materias, periodos, códigos, documento y correo: un colegio "ocupa" nombres para los demás (hoy bloqueado con mensaje controlado) |
| Acudiente en dos colegios | No soportado (documento único global) |
| Mensajes de duplicado | "Ya existe un usuario con ese documento" revela existencia en otro colegio |
| **V11 archivos** | Adjuntos y capturas en disco `public` (Etapa 3) |
| Solicitudes de admisión públicas | La landing las asigna a la institución 1 (una sola landing) |
| Portales Estudiante/Acudiente | Muestran datos de ejemplo (`PortalFamiliaFixtures`), no reales — brecha funcional |
| PHPUnit | Sin entorno (ver §3) |

## 6. Riesgos de regresión
- Registros con institución **nula** (datos antiguos): ahora se niegan (404 / no aparecen). Revisar antes de importar el dump real: `cursos.id_institucion`, `usuarios.id_institucion`.
- Un 403 previo (asignación ajena) ahora es 404.
- Registrar un estudiante en un curso cuyo nombre ya usa otro colegio devuelve 422 hasta la Etapa 2.
- SuperAdmin con filtro de curso en el calendario: sin institución, la validación lo rechaza.
- `RolController`/Dashboard/Búsqueda: sin institución no muestran datos (antes mostraban los "sin institución").

## 7. Reversión (solo Etapa 1)
```bash
R=~/Respaldos/sistema-escolar/etapa1-20261118_142331
"$R/revertir-etapa1.sh" /ruta/al/repo            # simulación
"$R/revertir-etapa1.sh" /ruta/al/repo --aplicar  # aplica
```
Verifica el respaldo (SHA-256), restaura los 51 archivos y borra los 3 nuevos. No usar `git checkout`/`reset`.
