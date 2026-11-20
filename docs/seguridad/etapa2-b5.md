# Etapa 2 — B5: catálogos y recursos institucionales en el código

**Fecha:** 2026-11-19 · **Rama:** `etapa0-contencion` (sin commit) · **Alcance:** modelos, consultas, validaciones y operaciones de catálogos, perfiles, eventos y soportes. **Sin migraciones ni cambios de datos en `colegio`.** Configuración y landing quedan para B6/B7.

## 1. Transición entre esquemas
`colegio` todavía no tiene las columnas de B2. El código decide **por tabla y según el esquema real** (`Schema::hasColumn`, en caché por petición; `EsquemaInstitucional`, registrado como instancia *scoped*), nunca por configuración ni por una institución ficticia:

| | **Legado** (sin `id_institucion`: la base real hoy) | **Expandido** (B2 aplicada) |
|---|---|---|
| Catálogos (materias, periodos, tipos, categorías): lectura | Compartidos, como antes (Etapa 1, V7) | Lo propio + lo que no tiene dueño (plantillas o pendientes de poblar) |
| Catálogos: alta | **Bloqueada (409)** si hay más de una institución; con una sola, igual que antes | `id_institucion` = institución de la sesión; lo del formulario se ignora |
| Catálogos: edición, activación y desactivación | **Bloqueadas (409)** si hay más de una institución | Lo propio: sí · ajeno: 404 · sin dueño: 409 · plantilla: 403 |
| Perfiles, eventos, soportes | Etapa 1 sin cambios (por relación) | Al crear se copia la institución del dueño; al leer, la columna con valor debe coincidir con la relación |

**Por qué se bloquea la escritura de catálogos en el esquema antiguo con varios colegios:** sin columna no hay forma segura de hacerla. Un registro nuevo sería visible y utilizable por todos los colegios, y una edición cambiaría lo que ven los demás. Se habilita sola en cuanto la tabla tiene la columna (B2 + población B4).

> **Efecto en la base real hoy:** tiene 2 instituciones y 0 materias, periodos y tipos. Mientras no se migre, **no se pueden crear catálogos académicos en `colegio`** (respuesta 409 con mensaje explicativo). Es el bloqueo pedido («documenta y bloquea esa operación hasta completar la migración»).

## 2. Reglas obligatorias y cómo se cumplen
| # | Regla | Implementación |
|---|---|---|
| 1 | Creación con institución del servidor | `CatalogoInstitucional::bootCatalogoInstitucional` (catálogos) y `PerteneceAInstitucion::bootPerteneceAInstitucion` (perfiles, eventos, soportes): la columna se fija en el evento `creating`, desde la sesión o el dueño; los valores del formulario se ignoran |
| 2 | Consultas por institución | `scopeDeInstitucion` en los 4 catálogos; 22 consultas de controladores, servicios y requests pasan por él |
| 3 | Actualizar y eliminar con pertenencia | Middleware de la Etapa 1 (404 si no es visible) + `asegurarModificable()` en las 12 acciones de escritura de catálogos |
| 4 | Unicidad por institución | `UnicoEnInstitucion`, coherente con el índice real: global si el único es global; visible para el colegio si es compuesto (R2a) |
| 5 | Globales vs institucionales | `esPlantilla()` / `esCompartido()`; `scopePropiosDeInstitucion` para lo exclusivamente propio |
| 6 | Plantillas de solo lectura | Categorías de sistema (cualquier esquema) y categorías sin institución (expandido) → 403 al editar, desactivar o activar |
| 7 | SuperAdmin sin acceso académico | Rutas por rol (403) + `deInstitucion` sin institución → vacío + alta de catálogo sin institución → excepción |
| 8 | Sin institución por defecto | Sin sesión con institución: catálogo → rechazo; perfil o evento de dueño sin institución → queda `NULL` |
| 9 | Asignación masiva | `id_institucion` no está en `$fillable` de ningún modelo; con sesión, el valor se sobrescribe igualmente |
| 10 | Compatibilidad | Modo por tabla; probado en ambos esquemas (§4) |

Además, las **9 reglas `exists:`** sobre tablas de catálogo, por las que se podían usar ids de catálogos ajenos, se sustituyeron por `TenantGuard::existe()`:
- asignaciones;
- actividades (periodo y tipo);
- boletines ×2;
- materia padre;
- materias del docente;
- eventos;
- filtro del calendario.

## 3. Comportamiento antes / después
| Operación | Antes de B5 | Después (expandido) | Después (legado, ≥2 colegios) |
|---|---|---|---|
| A lista materias | Todas, de todos los colegios | Las suyas + compartidas | Todas (sin cambio) |
| A crea «Química» con `id_institucion` de B en el formulario | Global | Queda de A | 409 |
| B edita o desactiva una materia de A | Permitido | 404 | — |
| A edita una materia compartida | Permitido (afecta a todos) | 409 | 409 |
| Editar categoría de sistema | Permitido (salvo roles) | 403 | 403 |
| A usa en una asignación o actividad la materia o el periodo de B | Aceptado | 422 | — (compartidos) |
| Nombre repetido | 500 / «duplicado» del motor | 422 en el propio colegio; B puede repetirlo | 422 (global) |
| Ventanas de periodo en el calendario de horarios | Periodos de todos los colegios | Solo los del colegio (y la clave de caché incluye la institución) | Sin cambio |
| Perfil, evento o soporte nuevo | Sin columna | Con la institución del dueño; dueño de otro colegio → rechazo | Sin cambio |
| Estudiante con columna contradictoria | — | Tratado como ajeno (404) | — |

**Revisado sin código que adaptar:**
- **`areas` y `escala_notas`:** no tienen modelo, rutas ni escrituras en la aplicación. Su pertenencia la gestiona la población (B4).
- **Perfiles, eventos y soportes:** ya estaban aislados por la Etapa 1. B5 solo añade la copia de la institución al crear y la coherencia al leer.

## 4. Pruebas
**Nuevas:**
- `Etapa2B5CatalogosTest`: 12 pruebas HTTP y de modelo, en esquema expandido, en ambos motores.
- `Etapa2B5EsquemaLegadoTest`: 5 pruebas. Cada una revierte R2a y M4→M1 **dentro de su transacción** y ejecuta la aplicación contra tablas **sin** la columna. Solo SQLite, porque requiere DDL transaccional.

| Requisito | Cubierto por |
|---|---|
| Creación en el colegio correcto | Materia, periodo, tipo y categoría con `id_institucion` ajeno en el formulario → quedan en el de la sesión |
| Lectura institucional | Listado HTTP de A y B (ve lo propio + lo compartido, no lo ajeno) |
| Actualización y eliminación institucional | Ajeno → 404 sin cambios; propio → 200 |
| Accesos entre colegios / ids manipulados | Asignación, actividad, materia padre y docente con ids de B → 422 |
| Unicidad | Repetido en el propio colegio → 422; en el otro colegio → 200; choque con lo compartido → 422; nombre+año del periodo; editar sin cambiar el nombre |
| Plantillas | Categoría de sistema y global → 403 al editar y desactivar; siguen visibles |
| SuperAdmin | 403 en pantallas y altas; `deInstitucion` vacío; sin alta por modelo |
| Asignación masiva | `create()` con `id_institucion` ajeno → sesión; no está en `$fillable` |
| Perfiles, eventos y soportes | Copian la institución del dueño; dueño de otro colegio → rechazo; SuperAdmin → `NULL` |
| Columna contradictoria | 404 y excluido del listado |
| Esquema antiguo | 5 pantallas sin errores SQL; altas y ediciones → 409; plantilla → 403; controles de la Etapa 1 intactos; con una sola institución, alta, edición y unicidad funcionan como antes |

Se ajustaron 4 pruebas de B2–B4 que suponían que todo nacía en `NULL` (ahora perfiles, eventos y soportes nuevos copian la institución de su dueño). Además, un ajuste en el simulador: un registro ya inconsistente conserva su motivo original aunque tenga valor.

| Suite | Antes de B5 | Después |
|---|---|---|
| SQLite | 230 | **247 / 247** |
| MariaDB `colegio_tests` | 243 + 1 omitida | **255 superadas + 6 omitidas** (5 de esquema antiguo y la de R2a: requieren DDL transaccional) |

### Ensayo HTTP en MariaDB (`colegio_tests`, peticiones reales por el kernel de Laravel)
| Estado del esquema | Resultado |
|---|---|
| **Antiguo** (R2a y M4→M1 revertidas; escenario ficticio sembrado) | 11/11 casos: 5 pantallas 200, SuperAdmin 403, alta 409, edición y desactivación de compartida 409, categoría de sistema 403, curso ajeno 422 |
| **Expandido sin poblar** (migraciones reaplicadas) | Los mismos 11 + 6: alta → queda en A pese al formulario (verificado en la base); B edita la de A → 404; repetido → 422; B repite el nombre → 200; materia de B en asignación de A → 422; A edita la suya → 200 |
| **Expandido poblado** (B4 aplicado con decisiones ficticias) | Cada colegio edita su «Matemáticas» (200) y no la del otro (404); 0 violaciones de coherencia |

En el estado *sin poblar*, el verificador marca 4 catálogos **en uso sin institución**: es lo esperado, y la población (B4) lo corrige, como se comprobó en el tercer ensayo.

## 5. Archivos
- **Nuevos:**
  - `app/Modules/Instituciones/Support/{EsquemaInstitucional,CatalogoInstitucional,UnicoEnInstitucion}.php`
  - `tests/Feature/Aislamiento/{Etapa2B5CatalogosTest,Etapa2B5EsquemaLegadoTest}.php`
  - este documento
- **Modificados:**
  - **Soporte de la Etapa 1:** `PerteneceAInstitucion.php` (creación por dueño, coherencia de columna) y `TenantGuard.php` (visibilidad de catálogos).
  - **Modelos:** `Materia`, `Periodo`, `TipoActividad`, `EventoCategoria` (trait; plantillas).
  - **Controladores:** `GestionAcademicaController`, `CalificacionesController`, `EventoCategoriaController`, `CalendarioController`, `BoletinesController`, `RegistroDocentesController`.
  - **Servicios:** `HorarioOccurrenceSource` (periodos y caché por institución), `ActividadOccurrenceSource`.
  - **Requests:** `MateriaRequest`, `PeriodoRequest`, `TipoActividadRequest`, `ActividadRequest`, `AsignacionRequest`, `DocenteStoreRequest`, `EventoRequest`, `CalendarioFeedRequest`.
  - **`AppServiceProvider`:** registro *scoped*.
  - **`SimuladorPoblacion`:** conserva el motivo de los inconsistentes.
  - **Migraciones M1–M3:** solo `down()`, que ahora borra la FK por columna. En MariaDB resuelve al mismo nombre (verificado en el ensayo); antes no se podía revertir en SQLite.
  - **Pruebas de B2–B4:** 4 ajustes.

Respaldo previo: `~/Respaldos/sistema-escolar/etapa2-b5-20261119_093118/` (`arbol-antes.tgz`, `git-status-antes.txt`, checksums de `colegio` al inicio y al final, `SHA256SUMS`).

## 6. Base real `colegio`
- **Al inicio de B5:** coincidía con la línea base verificada (52/52).
- **Sin cambios de esquema:** 71 migraciones, 5 columnas `id_institucion` previas y sin `migracion_pertenencia`. No se ejecutó ninguna migración ni escritura sobre ella.
- **Al final de B5, `usuarios` difiere de la línea base en 1 fila:** el usuario 1 (SuperAdmin, rol 8) tiene **otra contraseña** y `sesion_valida_desde = 2026-11-19 10:04:55`. Las otras 51 tablas son idénticas. El token de recuperación pendiente sigue sin usar (`usado = 0`), así que fue un cambio de contraseña directo, no un restablecimiento.
- **Origen:** no es de este trabajo. Todos los comandos de B5 contra bases usaron el usuario `colegio_tests`, sin privilegios sobre `colegio`; las únicas operaciones con `root` sobre `colegio` fueron lecturas (`CHECKSUM TABLE`, `SELECT`), y ninguna petición del ensayo toca contraseñas.
- **Contexto:** a las 10:11:22 se inició un `php artisan serve` en `127.0.0.1:8000` contra la base de la aplicación (no lo inició este trabajo). La línea base **no** se actualizó: queda pendiente que el propietario confirme el cambio.
- Bases conservadas sin cambios: `colegio_verificacion` y `colegio_restauracion_prueba`. `colegio_tests` quedó con el escenario ficticio poblado del ensayo.

## 7. Riesgos pendientes
1. **Base real sin catálogos editables** mientras no se migre (bloqueo deliberado). Si se necesita operar antes, la alternativa segura es migrar (B2 + B4), no relajar el bloqueo.
2. **Filas sin dueño en esquema expandido** (antes de poblar) son de solo lectura; un colegio no puede «adoptarlas» desde la UI. Se resuelve con la población (B4).
3. **Vistas:** los listados aún no distinguen visualmente lo propio, lo compartido y las plantillas; los botones de edición existen y la acción responde 403/409 con mensaje. Mejora de UI pendiente.
4. **Escrituras fuera de Eloquent:** `DB::table(...)->insert()` no pasa por los eventos `creating`. Hoy no hay ninguna sobre estas tablas en `app/` (solo el seeder de categorías, que crea plantillas); debe vigilarse en revisiones.
5. **`slug` de categorías:** se genera único de forma global (más estricto de lo necesario con R2a): sin riesgo de choque, pero dos colegios no comparten el mismo slug para nombres iguales.
6. El servidor de desarrollo en el puerto 8000 escribe en `colegio` (ver §6).

## 8. Dependencias para B6–B10
- **B6 (configuración por colegio):** sustituir `ColegioConfiguracion::singleton()` (7 usos directos y 7 vistas) por la configuración de la institución activa; añadir `CatalogoInstitucional`, o un equivalente sin plantillas, a configuración e imágenes.
- **B7 (landing por slug y admisiones):** `LandingContenido` por `(id_institucion, clave)` (incluido `EditarLandingController::updateOrCreate`); rutas públicas `/sitio/{slug}`.
- **B8 (restricciones):** R2a en la base real, y únicos de `codigo_estudiante` / `codigo_profesor` por colegio; luego, la validación de unicidad pasa sola al modo compuesto (`UnicoEnInstitucion` lee el índice real).
- **B9 (alta de colegio):** copiar plantillas (categorías, escala) al crear un colegio, o usarlas como globales de solo lectura, como ya admite B5.
- **B10 (base real):** respaldo → M1–M4 → R2a → simulación → aprobación → población → verificación. Tras ello, los bloqueos 409 de B5 desaparecen solos, porque la tabla pasa a modo expandido.
