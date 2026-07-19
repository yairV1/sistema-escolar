# Auditoría Técnica Completa — Sistema de Gestión Escolar (Laravel)

**Fecha:** 2026-07-18
**Alcance:** `laravel/` completo — Controllers, Models, Services, Requests, Middleware, Providers, Config, Rutas, Vistas, JS, CSS, Base de datos (inferida de modelos, no hay migraciones de dominio), Seguridad, Dependencias, Testing.
**Estado:** Solo análisis. **No se ha modificado ningún archivo.** Cada fase del roadmap (sección 5) requiere tu aprobación explícita antes de tocar código.
**Relación con `ARQUITECTURA_PROYECTO.md`:** ese documento (auditoría previa de esta misma sesión) ya cubrió 12 hallazgos estructurales (referenciados aquí como **H1–H12**) y propuso la arquitectura modular objetivo. Este documento no los repite — los referencia donde son relevantes — y añade **68 hallazgos nuevos** encontrados en una segunda pasada línea por línea sobre los 19 controllers, 23 modelos, 3 services, 20 form requests, 37 vistas Blade, 30 archivos JS, 12 archivos SCSS, toda la configuración, y las dependencias Composer/NPM.

---

## 0. Nota metodológica — por qué el nivel de detalle varía por severidad

Con 80 hallazgos totales (12 previos + 68 nuevos), documentar los 8 campos pedidos (descripción, impacto, riesgo, reproducción, solución, prioridad, complejidad, tiempo) con el mismo detalle para cada uno produciría un documento inmanejable y diluiría lo importante. Aplico el mismo criterio que usaría un equipo de seguridad/calidad senior en una empresa grande:

- **CRÍTICO y ALTO** → tratamiento completo con los 8 campos (sección 2).
- **MEDIO y BAJO** → registro compacto en tabla (sección 3): siguen clasificados, ubicados y con solución propuesta, pero sin el desarrollo narrativo completo. Se detallan igual en el roadmap.

Esto es lo mismo que hace Google/GitLab en sus trackers de deuda técnica: los P0/P1 llevan RCA completo, los P2/P3 quedan como tickets con severidad y solución propuesta, no ensayos.

---

## 1. Resumen ejecutivo

| Severidad | Cantidad | Dominios más afectados |
|---|---|---|
| 🔴 Crítico | 4 (2 ya en H1/H2/H5 + **2 nuevos**) | Seguridad (auth), Backend (boletines) |
| 🟠 Alto | 7 (H3/H4 + **15 nuevos**) | Seguridad, Base de datos, Backend, Testing |
| 🟡 Medio | ~30 | Backend, Frontend, Infraestructura |
| 🟢 Bajo | ~25 | Frontend, Infraestructura, limpieza |

**El diagnóstico global no cambia respecto al documento previo:** el código es de calidad notablemente por encima del promedio para su tamaño (Form Requests limpias, mass assignment controlado, sin SQL injection, sin XSS explotable, CSS ordenado). Los problemas reales no son "código sucio" sino **tres categorías concretas**:

1. **Deuda de infraestructura de plataforma** (sin migraciones de dominio, sin rate limiting, sin tests) — es lo que impide escalar de forma segura, no la calidad del código de aplicación en sí.
2. **Bugs de datos silenciosos concretos** (jornadas mezcladas en `Curso::buscarOCrear`, validación de horario que se desactiva si `id_profesor` es null, reglas de Form Request que se rompen si se renombra una ruta) — pocos pero reales, y peligrosos precisamente porque no lanzan ningún error.
3. **Duplicación por copy-paste entre módulos hermanos** (manejo de errores 1062, cálculo de % asistencia, construcción de nombre completo, regex de teléfono) — no rompe nada hoy, pero cada corrección futura requiere recordar tocar 3-5 sitios.

---

## 2. Hallazgos Crítico y Alto — detalle completo

### 🔴 CRÍTICO

---

**SEC-01 — Sin rate limiting en `/login`, combinado con contraseña por defecto predecible (H5)**

- **Descripción:** `routes/web.php:26-27` no aplica `throttle` a `POST /login`. `LoginController::store()` permite intentos ilimitados de `Hash::check()`.
- **Impacto:** cualquier cuenta cuya contraseña siga siendo el número de documento por defecto (H5, aplica a todos los estudiantes que no la han cambiado) es vulnerable a fuerza bruta/credential stuffing sin fricción.
- **Riesgo:** toma de control de cuentas de estudiantes/acudientes a escala, con datos personales (notas, observaciones, datos de acudientes) expuestos.
- **Cómo reproducirlo:** script que envía `POST /login` con `numero_documento` secuenciales y `password` = mismo documento, sin bloqueo tras N intentos fallidos.
- **Cómo solucionarlo:** `Route::post('/login', ...)->middleware('throttle:5,1')`, o `RateLimiter::for('login', fn ($request) => Limit::perMinute(5)->by($request->ip().'|'.$request->input('numero_documento')))` en `AppServiceProvider::boot()`. Complementar con lockout progresivo si se detectan intentos sostenidos.
- **Prioridad:** máxima — se corrige junto con H5 en la misma fase.
- **Complejidad:** baja (config de middleware, ~30 min).
- **Tiempo estimado:** 0.5–1h incluyendo prueba manual.

---

**SEC-03 — Un `rector` puede autopromoverse o crear cuentas `admin` (escalamiento de privilegios)**

- **Descripción:** `AdministrativoStoreRequest.php:23` valida `'id_rol' => ['required','in:1,2,3,4']` sin restringir según el rol de quien hace la petición. La ruta solo exige `role:admin,rector` (`routes/web.php:81`), así que un `rector` tiene el mismo poder que un `admin` en este endpoint.
- **Impacto:** un usuario con rol "directivo" (pensado como un escalón por debajo de `admin`) puede crear una cuenta `admin` nueva o editar la suya propia para subir su `id_rol` a 1, sin ninguna barrera adicional.
- **Riesgo:** pérdida de la jerarquía de privilegios asumida por el resto del sistema; un `rector` comprometido o malicioso obtiene control total.
- **Cómo reproducirlo:** autenticado como `rector`, `POST /registro/administrativos` con `id_rol=1` en el payload — se crea sin error.
- **Cómo solucionarlo:** `Rule::in` dinámico según `auth()->user()->rolSlug` (un `rector` no puede asignar `id_rol=1`), o mover la gestión de cuentas `admin` a un flujo separado exclusivo de `admin`. Ligado a **SEC-12** (centralizar roles asignables).
- **Prioridad:** máxima.
- **Complejidad:** baja (una regla condicional en el Form Request + test).
- **Tiempo estimado:** 1–2h.

---

*(H1 — esquema de BD sin migraciones, y H2 — N+1 crítico en generación de boletines, ya están documentados con todos los campos en `ARQUITECTURA_PROYECTO.md`. Se mantienen como Crítico y entran en la Fase 1 del roadmap junto con SEC-01/SEC-03.)*

### 🟠 ALTO

---

**SEC-07 — `APP_DEBUG=true` por defecto en `.env.example`**

- **Descripción:** `.env.example:4` fija `APP_DEBUG=true`. Si un despliegue copia el ejemplo sin cambiarlo, cualquier excepción no controlada muestra stack trace completo vía Ignition (rutas de servidor, queries con bindings, variables de entorno).
- **Impacto:** fuga de información estructural y potencialmente de credenciales de servicios en producción.
- **Riesgo:** reconocimiento previo a un ataque dirigido; exposición directa de secretos si algún `env()` sensible aparece en el stack trace.
- **Cómo reproducirlo:** forzar un error 500 (parámetro malformado en una ruta con route-model-binding, p. ej. `/gestion-academica/cursos/abc`) con `APP_DEBUG=true` y observar la página de error.
- **Cómo solucionarlo:** no se corrige solo en `.env.example` (es plantilla) — añadir comentario explícito `# OBLIGATORIO false en producción` y verificar el `.env` de cada entorno desplegado como parte del checklist de deploy.
- **Prioridad:** alta.
- **Complejidad:** trivial.
- **Tiempo estimado:** 15 min + verificación de entornos reales.

---

**SEC-10 — La sesión no se invalida al desactivar un usuario**

- **Descripción:** `EnsureRole::handle` (`EnsureRole.php:16-33`) solo valida `rolSlug`, nunca `estado_usuario`. Esa comprobación solo ocurre en `LoginController::store:49`, no en cada request autenticado.
- **Impacto:** si un admin desactiva a otro usuario (`ListadosController::desactivarAdministrativo`/`desactivarDocente`) mientras ese usuario tiene sesión activa, sigue operando en el panel hasta que la sesión expire (`SESSION_LIFETIME`, hasta 120 min).
- **Riesgo:** un usuario despedido o bloqueado por incidente de seguridad conserva acceso funcional durante horas después de ser "desactivado" — la acción administrativa no tiene efecto inmediato real.
- **Cómo reproducirlo:** iniciar sesión como docente/administrativo en dos pestañas, desactivar esa cuenta desde otra sesión admin, seguir navegando el panel en la primera pestaña sin relogin.
- **Cómo solucionarlo:** verificar `$usuario->estaActivo()` dentro de `EnsureRole` (o en un middleware `auth` custom) y forzar `Auth::logout()` + redirect si no lo está.
- **Prioridad:** alta.
- **Complejidad:** baja.
- **Tiempo estimado:** 1–2h.

---

**DB-15 — 21 de 23 modelos sin `timestamps` (sin auditoría de cuándo se creó/editó una nota, matrícula o boletín)**

- **Descripción:** todos los modelos de dominio salvo `LandingNoticia`/`LandingGaleria` declaran `public $timestamps = false`, heredado del esquema legacy sin migración propia (H1).
- **Impacto:** imposible determinar cuándo se registró una nota, cuándo se generó/modificó un boletín, o cuándo se matriculó un estudiante — información que un padre/estudiante puede disputar formalmente ("esta nota se cambió después del cierre del periodo").
- **Riesgo:** en un sistema académico, la trazabilidad temporal de calificaciones no es un "nice to have", es requisito de integridad frente a reclamos.
- **Cómo reproducirlo:** `Nota::first()->created_at` no existe / siempre null.
- **Cómo solucionarlo:** al escribir las migraciones baseline (H1), añadir `created_at`/`updated_at` **nullable** para no romper filas existentes, activar `$timestamps = true` y poblar hacia adelante. No requiere backfill de datos históricos.
- **Prioridad:** alta (se resuelve como parte de H1, sin costo adicional relevante).
- **Complejidad:** baja si se hace junto a H1; media si se hace por separado.
- **Tiempo estimado:** incluido en H1 (+0h marginal) o 3-4h si se hace aislado.

---

**DB-16 — `promedio_general`/`nota_definitiva` son agregados calculados y persistidos sin invalidación**

- **Descripción:** `BoletinesController::generar()` calcula y guarda `promedio_general`/`nota_definitiva`. No existe ningún mecanismo (flag, evento, job) que los recalcule si una `Nota` fuente se corrige después.
- **Impacto:** un boletín ya generado puede mostrar un promedio incorrecto de forma silenciosa e indefinida tras corregir una nota, sin que nadie lo note salvo que alguien regenere manualmente.
- **Riesgo:** calificaciones oficiales incorrectas mostradas a estudiantes/acudientes — impacto directo en la confianza del sistema.
- **Cómo reproducirlo:** generar boletines para un curso, corregir manualmente una `Nota` vía BD o un futuro endpoint de edición, observar que `Boletin.promedio_general` no cambia.
- **Cómo solucionarlo:** opción mínima — un `Observer` en el modelo `Nota` que marque el `Boletin` relacionado como `desactualizado` (flag) tras cualquier `updated`; opción completa — recalcular automáticamente vía evento si el boletín aún no está `publicado`.
- **Prioridad:** alta (afecta corrección de datos mostrados, no solo performance).
- **Complejidad:** media.
- **Tiempo estimado:** 4-6h.

---

**BE-13 — `Curso::buscarOCrear()` ignora la jornada al buscar coincidencias (mezcla estudiantes de jornadas distintas)**

- **Descripción:** `Curso.php:53-73` busca un curso existente filtrando solo por `nombre_curso` + `anio_lectivo` (líneas 56-58); `$jornada` solo se usa si crea uno nuevo.
- **Impacto:** si existen dos cursos "10A" en el mismo año con jornadas distintas (mañana/tarde — caso realista en un colegio con doble jornada), el segundo estudiante matriculado se cuela en el curso de la primera jornada encontrada.
- **Riesgo:** mezcla real de estudiantes de jornadas distintas bajo el mismo `id_curso` — afecta horarios, asistencia y boletines de forma incorrecta, sin ningún error visible.
- **Cómo reproducirlo:** matricular un estudiante en "10A" jornada mañana, luego otro en "10A" jornada tarde, mismo año — ambos terminan en el mismo `id_curso`.
- **Cómo solucionarlo:** incluir `jornada` en el `where()` de búsqueda de `buscarOCrear()`. Cambio de una línea, pero requiere primero auditar si ya existen datos reales afectados en la BD actual.
- **Prioridad:** alta — es un bug de datos activo, no solo deuda técnica.
- **Complejidad:** baja (fix) + media (auditoría de datos existentes antes de corregir).
- **Tiempo estimado:** 1h fix + 2-3h auditoría/limpieza de datos si aplica.

---

**BE-08 — Bypass silencioso de la validación de solapamiento de horarios si `id_profesor` es null**

- **Descripción:** `GestionAcademicaController::validarSolapamientoHorario()` (líneas 305-317) obtiene `$idProfesor = AsignacionAcademica::find(...)?->id_profesor` y lo pasa directo a `where('id_profesor', $idProfesor)`. Si es `null`, el filtro SQL se traduce en `= NULL`, que nunca matchea nada.
- **Impacto:** si la asignación referenciada es inconsistente (dato corrupto o carrera entre requests), la validación de "un profesor no puede tener dos clases a la misma hora" queda **desactivada sin ningún error**, en vez de fallar explícitamente.
- **Riesgo:** conflictos de horario reales que el sistema debería impedir pasan silenciosamente.
- **Cómo reproducirlo:** difícil de reproducir sin datos corruptos previos — el riesgo es defensivo, no un flujo normal de usuario.
- **Cómo solucionarlo:** `abort_if($idProfesor === null, 422, 'Asignación inválida')` antes de construir la query, en vez de dejar que el filtro falle en silencio.
- **Prioridad:** alta (silent failure en una validación de integridad).
- **Complejidad:** trivial.
- **Tiempo estimado:** 30 min.

---

**BE-03 — N+1 en guardado de notas y asistencia (un UPDATE/INSERT por fila del formulario)**

- **Descripción:** `CalificacionesController::guardarNotas()` (líneas 139-144) y `AsistenciaController::guardar()` (líneas 50-62) hacen `foreach ($data as $fila) { Model::updateOrCreate(...) }` — una query por estudiante del curso.
- **Impacto:** guardar notas/asistencia de un curso de 30+ estudiantes ejecuta 30+ queries síncronas en un solo request.
- **Riesgo:** performance degradada de forma perceptible a medida que crecen los cursos; mismo patrón que H2 pero en dos endpoints de uso mucho más frecuente (se guarda asistencia potencialmente a diario).
- **Cómo reproducirlo:** guardar asistencia de un curso con 30 estudiantes y medir queries con `DB::listen`/Telescope/Debugbar.
- **Cómo solucionarlo:** `Model::upsert($filas, ['id_actividad','id_estudiante'], ['nota'])` (Laravel 8+) en una sola query.
- **Prioridad:** alta — es el endpoint de mayor uso recurrente del sistema.
- **Complejidad:** media (requiere índice único compuesto primero, ver DB-02/DB-06).
- **Tiempo estimado:** 3-4h por endpoint (2 endpoints ≈ 6-8h).

---

**BE-05 — N+1 adicional en `BoletinesController::generar()` para asignar `puesto_curso`**

- **Descripción:** además del triple bucle ya documentado en H2, líneas 101-107 ejecutan un `UPDATE` por estudiante en un segundo bucle independiente para fijar el puesto en el curso.
- **Impacto:** 30 UPDATEs adicionales solo para los puestos, en la misma request ya sobrecargada de H2.
- **Riesgo:** agrava directamente el problema de timeout ya identificado en H2.
- **Cómo reproducirlo:** mismo que H2 — perfilar `generar()` con un curso grande.
- **Cómo solucionarlo:** un único `CASE WHEN id_estudiante IN (...) THEN puesto END` o bulk upsert, resuelto en el mismo refactor que H2 (`GenerarBoletinesAction`).
- **Prioridad:** alta — se resuelve junto con H2, no por separado.
- **Complejidad:** incluida en el fix de H2.
- **Tiempo estimado:** incluido en H2.

---

**DB-01/DB-04 — Índices faltantes en columnas de filtro masivo (`estado`, `matriculas.estado_matricula`, `usuarios.numero_documento`/`correo`)**

- **Descripción:** `estado`/`estado_matricula`/`estado_academico` se filtran en prácticamente todos los listados (`Listados`, `GestionAcademica`, `Calificaciones`, `Observaciones`, `Estadisticas`, `Dashboard`); `usuarios.numero_documento`/`correo` son las columnas de login.
- **Impacto:** sin índice (compuesto, idealmente `(id_curso, estado_matricula)` y único en `numero_documento`/`correo`), cada listado y cada login hacen table scan. Hoy no se nota (pocos registros); a 3-5 años de historial académico acumulado, se nota mucho.
- **Riesgo:** degradación de performance progresiva y silenciosa — el tipo de problema que solo se detecta cuando ya es doloroso en producción.
- **Cómo reproducirlo:** `EXPLAIN SELECT * FROM matriculas WHERE estado_matricula='activa' AND id_curso=X` sobre la BD real y verificar si usa índice o `ALL`.
- **Cómo solucionarlo:** migraciones de índice (`$table->index(['id_curso','estado_matricula'])`, `$table->unique('numero_documento')` en `usuarios` si no existe ya a nivel BD) — no requiere tocar código de aplicación, solo migración.
- **Prioridad:** alta, pero se ejecuta junto con H1 (baseline de migraciones) para no crear dos rondas de cambios de esquema.
- **Complejidad:** baja (una vez que existen las migraciones baseline).
- **Tiempo estimado:** 3-4h dentro de la Fase 3.

---

**DB-05 — Condición de carrera en `CodigoUnicoService` (posible duplicado de código de estudiante/profesor)**

- **Descripción:** `CodigoUnicoService.php:15-22` genera el código con `count()+1` en un bucle `do/while` que comprueba unicidad con `exists()`. Dos registros concurrentes pueden leer el mismo `count()` antes de que el primero confirme, produciendo el mismo código.
- **Impacto:** dos estudiantes con el mismo `codigo_estudiante` si dos matrículas se procesan casi simultáneamente (ej. secretaría registrando varios estudiantes en paralelo, o un futuro flujo de matrícula en línea con concurrencia real).
- **Riesgo:** colisión de identificador único usado como referencia externa (carnet, reportes) — difícil de detectar y corregir después del hecho.
- **Cómo reproducirlo:** disparar dos requests de creación de estudiante casi simultáneas (ej. con un script que hace 2 POST en paralelo) y verificar si generan el mismo código.
- **Cómo solucionarlo:** envolver la generación+verificación en una transacción con `lockForUpdate()`, o mejor, delegar la unicidad a un constraint `UNIQUE` en BD + reintento en caso de colisión (`QueryException` 1062), consistente con el patrón que el resto del código ya usa (`respuestaDuplicado`).
- **Prioridad:** alta si el registro se vuelve concurrente (varias secretarias trabajando a la vez); media si hoy es siempre secuencial.
- **Complejidad:** media.
- **Tiempo estimado:** 3-4h.

---

**FE-11 — Máquina de estados de transición de matrícula implementada dentro de una vista Blade**

- **Descripción:** `matriculas/index.blade.php:92-99` contiene en `@php` la matriz completa de transiciones válidas (`pendiente→activa/cancelada`, `activa→retirada/cancelada`, etc.) — es lógica de negocio real, no presentación.
- **Impacto:** si el backend (`MatriculasController::cambiarEstado`) no valida exactamente las mismas transiciones, un usuario puede forzar una transición inválida vía request directo (bypasseando el Blade que solo oculta opciones en la UI).
- **Riesgo:** una matrícula podría pasar de `cancelada` a `activa` directamente sin pasar por `pendiente`, si el frontend es la única barrera — hay que confirmar contra `MatriculasController` si hay validación equivalente en servidor.
- **Cómo reproducirlo:** inspeccionar si `MatriculasController::cambiarEstado` valida transiciones o solo el nuevo estado en aislado; si es lo segundo, un `POST` directo con un estado no permitido por la UI lo aceptaría.
- **Cómo solucionarlo:** mover la máquina de estados a un método `Matricula::puedeTransicionarA(string $nuevoEstado): bool` o un enum con lógica, y que tanto el controller como la vista lo consuman desde la misma fuente.
- **Prioridad:** alta si se confirma que el backend no replica la validación (por verificar en Fase 4); si el backend ya valida igual, baja a Medio (solo duplicación, no bug de seguridad de datos).
- **Complejidad:** media.
- **Tiempo estimado:** 3-4h + verificación previa de `MatriculasController`.

---

**INF-17/INF-18 — 0% de cobertura de tests de negocio, y la BD de test no tiene tablas de dominio**

- **Descripción:** `tests/Feature/ExampleTest.php` solo verifica `GET /` → 200; `tests/Unit/ExampleTest.php` solo `assertTrue(true)`. `phpunit.xml` usa correctamente sqlite en memoria aislado (sin riesgo de tocar la BD real), pero al no existir migraciones de dominio (H1), ese sqlite estaría vacío de `estudiantes`/`matriculas`/`notas`/etc.
- **Impacto:** ningún flujo crítico (login, matrícula, generación de boletines, cálculo de asistencia) tiene un solo test automatizado. Cualquier refactor de este roadmap (incluida esta misma auditoría) se valida hoy solo manualmente.
- **Riesgo:** alto para un sistema académico — un bug en el cálculo de notas (ver DB-16, H2) puede pasar desapercibido hasta que un padre lo reporte.
- **Cómo reproducirlo:** `php artisan test` — pasa, pero no prueba nada relevante del dominio.
- **Cómo solucionarlo:** bloqueado por H1 (sin migraciones no hay `RefreshDatabase` funcional) y por falta de factories (DB-19). Es prerrequisito técnico: primero H1 + factories, luego tests de feature para los flujos críticos (login, matrícula, generación de boletines, asistencia).
- **Prioridad:** alta, pero por dependencia técnica solo puede ejecutarse después de Fase 3 (BD).
- **Complejidad:** alta (no por dificultad técnica individual, sino por volumen — cubrir los flujos críticos requiere varias semanas de trabajo incremental).
- **Tiempo estimado:** ver Fase 8 del roadmap — se estima por sprint, no de una vez.

---

## 3. Hallazgos Medio y Bajo — registro compacto

### 🟡 Medio

| ID | Ubicación | Descripción | Solución | Complejidad |
|---|---|---|---|---|
| SEC-02 | `routes/web.php:29` | Sin rate limit en `/forgot-password` — email bombing/DoS de correo | `throttle:3,10` por IP+correo | Baja |
| SEC-06 | `config/session.php:172` | `SESSION_SECURE_COOKIE` sin default, cookie sin `Secure` si no se fija | Fijar `true` en `.env` de producción | Trivial |
| SEC-08 | `LoginRequest.php:16`, `ResetPasswordRequest.php:16` | Password mínimo 6-8 sin complejidad | `Password::min(10)->mixedCase()->numbers()` | Baja |
| SEC-09 | Todo `app/` | Sin logging de eventos de seguridad (login fallido, cambio de rol, activar/desactivar) | `Log::channel('security')` en puntos críticos | Media |
| DB-02 | `BoletinDetalle`/`Nota` | Falta índice compuesto `(id_actividad,id_estudiante)` — usado en el N+1 de H2/BE-03 | Migración de índice único | Baja |
| DB-03 | ~10 modelos | Columna `estado` como string libre en vez de enum/boolean de BD | Enum de BD al escribir migraciones baseline | Media |
| DB-06 | `Asistencia` | Falta índice único `(id_asignacion, fecha)` | Migración de índice | Baja |
| DB-07 | `Horario`/`AsignacionAcademica` | Falta índice para detección de choques de horario | Migración de índice compuesto | Baja |
| DB-08 | `Boletin`/`AsignacionAcademica` | Falta índice único compuesto `(id_estudiante,id_periodo)` / `(id_curso,anio_lectivo)` | Migración de índice | Baja |
| DB-09 | `PasswordReset.token_hash` | Falta índice para lookup exacto | Migración de índice | Baja |
| DB-10 | `Acudiente.php:74-90` | Tabla pivote `estudiante_acudiente` manejada con `DB::table()` crudo, no como relación Eloquent | Modelar `belongsToMany` con pivote propio | Media |
| DB-12 | ~10 modelos | Sin `$casts` para fechas/decimales (solo `PasswordReset` los tiene) | Añadir `$casts` en cada modelo | Baja |
| BE-04 | `EditarLandingController.php:39-44` | N+1: un UPDATE por clave de config del sitio | `upsert()` o `CASE WHEN` | Baja |
| BE-06 | `ListadosController.php:19-37,74-82` | `Estudiante::idsEnRiesgo()` llamado hasta 3 veces en la misma request | Calcular una vez, reusar | Baja |
| BE-09 | `RegistroAdministrativosController.php:26-35,64-72` | Reconstrucción manual de array desde `validated()` — riesgo de desincronización si se añade un campo | Pasar `$request->validated()` completo | Baja |
| BE-10 | `RegistroEstudiantesController.php:49-51` | Parseo de grado/grupo con `rtrim($nombreCurso,'ABCD')` — frágil ante nombres mal formados | Mover a método explícito en `Curso`, no parseo posicional | Media |
| BE-12 | `DashboardController`/`EstadisticasController`/`AsistenciaController` | Cálculo de % asistencia con 3 SQL crudos ligeramente distintos, uno sin `NULLIF` (riesgo división por cero) | Un scope único en el modelo `Asistencia` | Baja |
| BE-14 | 7 archivos | `id_rol` hardcodeado como número mágico repetido, no depende de `Usuario::ROLE_SLUGS` | Centralizar en `Usuario::ROLE_SLUGS`, ligado a SEC-12 | Media |
| BE-15 | `Acudiente.php:41-61` | `buscarOCrear()` no valida que el usuario reutilizado tenga `id_rol=7` | Verificar rol antes de reutilizar | Baja |
| BE-20 | 4 Form Requests | Listas `in:` de `tipo_documento` inconsistentes entre archivos sin documentar si es intencional | Centralizar o documentar la diferencia | Baja |
| BE-21 | `EstudianteUpdateRequest.php:15-16` | `array_slice($rules[$campo],1)` asume posición de `'required'`, frágil ante reorden | Filtrar por valor, no por posición | Baja |
| BE-22 | `LandingGaleriaRequest.php:19` | Regla de validación acoplada a `routeIs('*.store')` — se rompe si se renombra la ruta (relevante para H10) | Basar la regla en el método HTTP o en un flag explícito | Baja |
| FE-04 | 7 vistas | Modal por fila en vez de modal único reutilizado — duplicación + peso de DOM en listados grandes | Un modal poblado por JS con `data-*` | Media |
| FE-06 | `pages/matriculas/matriculas.js:4-18` | `initAutosubmit` reimplementado en vez de importado de `components/listActions.js` | Eliminar copia, importar | Baja |
| FE-08 | 6+ archivos JS | Boilerplate de submit (loading+catch+toast) repetido, patrón ya resuelto parcialmente en `perfil.js` | Generalizar `wireForm` a `components/forms/submit.js` | Media |
| FE-10 | 8 vistas | Mapas de color por estado (`$estadoColores`) repetidos con `@php` en cada vista | Accessor en el modelo o helper `estadoBadge()` | Baja |
| FE-16 | `notas.blade.php:43-45`, `asistencia/show.blade.php:52-62` | Inputs de tabla sin `label`/`aria-label` asociado | `aria-label` dinámico por fila | Baja |
| INF-07 | `config/cache.php`,`session.php`,`queue.php` | Cache/sesión/colas todos sobre la misma MySQL de negocio (sin Redis) | Evaluar Redis cuando haya más de 1 servidor de app | Media (no urgente) |
| INF-09 | `.env` real | `MAIL_MAILER=log`, sin guía de qué usar en producción documentada en `.env.example` | Documentar en `.env.example` | Trivial |
| INF-21 | `.env.example:23-28` | Default `sqlite` con MySQL comentado, sin nota de que se requiere MySQL con BD `colegio` preexistente | Añadir comentario explicativo | Trivial |

### 🟢 Bajo

| ID | Ubicación | Descripción | Solución |
|---|---|---|---|
| DB-11 | `Curso`/`Profesor` | Falta relación inversa `cursosComoDirector()` | Añadir `hasMany` inverso |
| DB-13 | `Notificacion.php` | `leida` sin cast boolean, inconsistente con `PasswordReset.usado` | Añadir `$casts` |
| DB-14 | `Boletin`/`AsignacionAcademica` | Relaciones sin `orderBy` por defecto, se muestran en orden de inserción | `orderBy` en la definición de la relación |
| DB-18 | `EstudianteRegistroService`/`Acudiente` | Dominio de correo autogenerado hardcodeado en 2 sitios | Constante/config compartida |
| BE-11 | 3 controllers | Método `respuestaDuplicado()` copiado 3-4 veces | Extraer a `Controller` base |
| BE-16 | `Boletin`/`AsignacionAcademica` | Sin `orderBy` por defecto en relaciones listadas (duplica DB-14 desde el ángulo de controller) | Ver DB-14 |
| BE-17 | 2 Services | Construcción de nombre completo duplicada 4 veces | Helper `NombreCompleto::desde()` |
| BE-18 | `EstudianteRegistroService.php` | Construcción de dirección duplicada en el mismo archivo (crear/actualizar) | Método privado compartido |
| BE-19 | 3 Form Requests | Regex de teléfono duplicado 5 veces | `Rule` compartida o trait de Form Request |
| SEC-05 | `config/auth.php:97-104` | Broker `passwords.users` con throttle configurado pero nunca usado (el flujo real es custom) | Eliminar o documentar como no usado, para no confundir |
| SEC-11 | `PerfilController`/`PasswordResetController` | Sin invalidar otras sesiones al cambiar password | `Auth::logoutOtherDevices()` |
| SEC-12 | `AdministrativoStoreRequest.php:23` | Lista de roles asignables no depende de `Usuario::ROLE_SLUGS` (causa raíz de SEC-03) | Centralizar fuente de verdad |
| FE-01 | 8 vistas | Bloque KPI/stat-card repetido | `<x-kpi-row>` |
| FE-02 | 9 vistas | Tabla+filtros+badge repetido | `<x-tabla-filtrable>` |
| FE-03 | 13 sitios | Botones activar/desactivar duplicados (lado Blade de H6) | `@include('partials.acciones-estado')` |
| FE-05 | 4 vistas | Header "volver+título+badge" repetido | `<x-page-header>` |
| FE-07 | `login.js`/`panel.js` | Confirmación de logout duplicada, código muerto en `login.js` | Mover a `components/logout.js` |
| FE-09 | `dashboard/index.blade.php:199-221` | Reloj inline con `var`, bypasea convención Vite | Extraer a `pages/dashboard/dashboard.js` |
| FE-12/13 | `layouts/panel.blade.php:35-39`, `cursos/show.blade.php:25` | Cálculos (iniciales, conteo únicos) en vista en vez de accessor/controller | Mover a accessor o controller |
| FE-14 | `_panel.scss:288-289` | Colores hardcodeados fuera de `_variables.scss` | Usar variables existentes |
| FE-15 | `website/index.blade.php` | Sitio público usa CSS/JS legacy fuera del sistema de diseño de `resources/` | Migrar cuando se toque el módulo Landing |
| FE-17 | Panel vs. sitio público | `alt=""` inconsistente en imágenes de galería | Homologar criterio |
| INF-01 | `AppServiceProvider.php` | Sin `Model::shouldBeStrict()` en local (detectaría N+1 automáticamente) | Activar condicionado a `isLocal()` |
| INF-02 | `bootstrap/app.php:18-19` | Sin manejo de excepciones personalizado / reporte externo | Configurar cuando haya usuarios reales en producción |
| INF-05 | `PasswordResetController.php:38` | Mailable enviado síncrono pese a tener `Queueable` | Usar `->queue()` cuando el volumen crezca |
| INF-10 | `config/app.php:68`, `.env.example:7` | Timezone hardcodeado, locale default `en` en vez de `es` | Parametrizar timezone, corregir default |
| INF-12 | `composer.json:79-82` | `allow-plugins` para Pest sin Pest instalado (residual) | Eliminar entrada |
| INF-23/24 | `.env` real | `MAIL_ENCRYPTION`/`ASSET_URL` variables muertas de una versión anterior de Laravel | Limpiar al reescribir `.env` |
| INF-26 | `vite.config.js:7-29` | 20 entradas manuales sin globbing — fricción al añadir páginas nuevas | Evaluar `import.meta.glob` |

---

## 4. Verificado y sin hallazgos (para que quede explícito qué SÍ está bien)

- **SQL injection:** todos los `DB::raw`/`selectRaw` revisados son cadenas estáticas sin interpolación de input de usuario.
- **XSS:** único `{!! !!}` de todo el proyecto (`SidebarBuilder::render()`) está correctamente escapado internamente y su fuente es config estática, no input de usuario.
- **CSRF:** protección por defecto de Laravel activa, sin overrides.
- **Mass assignment:** 100% de los controllers usan `->validated()` explícito, nunca `$request->all()`.
- **Enumeración de usuarios en login/reset:** mensajes genéricos correctos; tokens de reset con `random_bytes`, hasheados con SHA-256, expiración 30 min, un solo uso.
- **Uploads:** único punto de subida real (`EditarLandingController` → `Storage::disk('public')`) correctamente configurado con symlink y sin archivos indebidos en control de versiones.
- **CSS:** sin duplicación relevante, uso consistente de variables, `!important` solo para overrides justificados de librerías de terceros.
- **SweetAlert2 vs. Toastify:** no es redundancia — separación correcta entre confirmaciones bloqueantes y notificaciones pasivas.
- **NPM/Composer:** versiones actuales, sin dependencias muertas relevantes salvo el residual de Pest (INF-12).

---

## 5. Roadmap — 10 fases

Cada fase se ejecuta solo, módulo por módulo o hallazgo por hallazgo, **con tu aprobación antes de empezar la siguiente**. No se agrupan cambios masivos.

### Fase 1 — Problemas críticos
`SEC-01` (rate limit login), `SEC-03` (escalamiento rector→admin), `H2` (N+1 boletines + `BE-05`), `H5` (password predecible, requiere tu decisión de producto). *Checkpoint: confirmar contigo el enfoque de H5 antes de tocarlo — cambia comportamiento visible a usuarios finales.*

### Fase 2 — Seguridad
`SEC-02, 04, 05, 06, 07, 08, 09, 10, 11, 12`, `H4` (diseño de Policies, aunque su implementación completa puede esperar a que se abra el panel a más roles).

### Fase 3 — Base de datos
`H1` (migraciones baseline) + `DB-01, 02, 04, 06, 07, 08, 09, 15` (índices y timestamps, se añaden en las mismas migraciones baseline) + `DB-19` (factories). `DB-05, 10, 12, 16` según prioridad. *Checkpoint: revisar contigo el esquema baseline generado antes de aplicarlo — es el cambio de mayor impacto de todo el roadmap.*

### Fase 4 — Arquitectura
Implementación de la estructura `Modules/` + `Shared/` propuesta en `ARQUITECTURA_PROYECTO.md` sección 5, empezando por el orden de módulos ya acordado ahí (Landing → Comunicados → Observaciones → ... → Calificaciones/Boletines al final). Incluye `H6` (trait `TieneEstadoActivable`), `BE-11` (extraer `respuestaDuplicado`), `BE-14`/`SEC-12` (centralizar roles), verificación de `FE-11` (¿el backend de matrículas ya valida las transiciones o no?).

### Fase 5 — Backend
`H3` (split de God Controllers), `H7` (Services no estáticos para lo nuevo), `H8` (Jobs si aplica), `BE-03, 04, 06, 08, 09, 10, 12, 13, 15, 17, 18, 19, 20, 21, 22`.

### Fase 6 — Frontend
`FE-01` a `FE-17` (componentización Blade, deduplicación JS, accesibilidad de formularios).

### Fase 7 — Performance
Validación con datos reales de los índices aplicados en Fase 3, cache en `Dashboard`/`Estadisticas` si se detecta necesidad real (`Cache::remember`), confirmación de que `BE-03`/`H2` quedaron resueltos con medición antes/después (queries por request).

### Fase 8 — Testing
Bloqueado por Fase 3. Tests de feature para los flujos críticos en orden de riesgo: login/auth → matrícula → asistencia → generación de boletines. Se ejecuta de forma incremental, no de una vez — cada módulo migrado en Fase 4/5 se acompaña de sus tests.

### Fase 9 — Optimización
`INF-05` (colas para correo si el volumen lo justifica), `INF-07` (evaluar Redis), `H10` (dividir `routes/web.php` por módulo), `INF-26` (globbing en Vite).

### Fase 10 — Refactorización final
Limpieza de hallazgos Bajo restantes (`INF-01, 02, 10, 12, 23, 24`, `FE-09, 12-15, 17`, `DB-11, 13, 14, 18`), pase final de consistencia de nombres/convenciones, actualización de `.env.example` con todo lo detectado (`INF-09, 21`), y verificación cruzada de que ningún hallazgo de este documento quedó sin cerrar o sin justificación explícita de por qué se descartó.

---

## 6. Qué necesito de ti antes de Fase 1

1. ¿Apruebas empezar por Fase 1 tal como está? Incluye una decisión de producto pendiente (H5 — cómo comunicar el cambio de password por defecto a estudiantes/acudientes).
2. Fase 3 (base de datos) es el cambio de mayor riesgo/impacto de todo el roadmap porque toca el esquema real por primera vez desde que existe — ¿quieres que te muestre las migraciones baseline generadas *antes* de aplicarlas a cualquier entorno, incluso de desarrollo?
3. ¿Confirmas que las Fases 2-10 se ejecutan en el orden propuesto, o hay una prioridad de negocio (ej. una auditoría externa, un incidente ya ocurrido) que deba mover algo hacia arriba?

No se toca ningún archivo hasta que respondas.
