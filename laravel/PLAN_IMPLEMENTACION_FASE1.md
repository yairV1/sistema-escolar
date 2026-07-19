# Plan de Implementación — Fase 1 (Problemas Críticos)

**Fecha:** 2026-07-18
**Rama:** `feature/Yair`
**Estado:** Planificación. **No se ha modificado ningún archivo.** Este documento se somete a tu aprobación antes de escribir una sola línea de código.
**Alcance:** exclusivamente los 4 hallazgos de Fase 1 según `AUDITORIA_TECNICA_COMPLETA.md` sección 5 — `SEC-01`, `SEC-03`, `H2`+`BE-05`, `H5` (opción "password aleatoria mostrada una vez", ya aprobada). **Fuera de alcance:** cualquier hallazgo de Fase 2 en adelante, y explícitamente `H1` (migraciones baseline) — ningún cambio de este plan toca el esquema de base de datos.

---

## 1. Objetivo

Cerrar los 4 huecos clasificados como **Crítico** en la auditoría, sin tocar nada fuera de ese alcance:

| Hallazgo | Problema actual | Estado al finalizar Fase 1 |
|---|---|---|
| **SEC-01** | `POST /login` sin límite de intentos → fuerza bruta viable | Máx. 5 intentos/minuto por IP+usuario, con mensaje claro |
| **SEC-03** | Un `rector` puede crear/promoverse a `admin` vía el formulario de administrativos | Solo un `admin` real puede asignar el rol `admin` |
| **H2 + BE-05** | `BoletinesController::generar()` ejecuta hasta ~1200 queries sin transacción; puede fallar a medio camino dejando datos inconsistentes | Consulta de notas precargada (1 query en vez de N×M), puestos actualizados en un solo UPDATE, todo el proceso atómico dentro de una transacción |
| **H5** | Password de cuentas nuevas = número de documento (predecible, adivinable) | Password aleatoria de 10 caracteres, mostrada una única vez en pantalla al crear la cuenta, nunca persistida en texto plano ni recuperable después |

**No se resuelve en esta fase** (y es intencional): passwords ya existentes con el patrón antiguo, políticas de complejidad de password (`SEC-08`), rate limit en `/forgot-password` (`SEC-02`), Policies granulares (`H4`) — todos son Fase 2 o posteriores.

---

## 2. Archivos afectados

| Categoría | Archivo | Tipo de cambio |
|---|---|---|
| **Providers** | `app/Providers/AppServiceProvider.php` | Modificado |
| **Routes** | `routes/web.php` | Modificado |
| **Requests** | `app/Http/Requests/Registro/AdministrativoStoreRequest.php` | Modificado |
| **Actions** *(capa nueva, mínima)* | `app/Actions/GenerarBoletinesAction.php` | **Nuevo** |
| **Controllers** | `app/Http/Controllers/Panel/BoletinesController.php` | Modificado |
| **Controllers** | `app/Http/Controllers/Panel/Registro/RegistroAdministrativosController.php` | Modificado |
| **Controllers** | `app/Http/Controllers/Panel/Registro/RegistroEstudiantesController.php` | Modificado |
| **Controllers** | `app/Http/Controllers/Panel/Registro/RegistroDocentesController.php` | Modificado |
| **Services** | `app/Services/EstudianteRegistroService.php` | Modificado |
| **Services** | `app/Services/DocenteRegistroService.php` | Modificado |
| **JS** | `resources/js/components/alerts/sweetAlert.js` | Modificado |
| **JS** | `resources/js/pages/registro/estudiantes.js` | Modificado |
| **JS** | `resources/js/pages/registro/docentes.js` | Modificado |
| **JS** | `resources/js/pages/registro/administrativos.js` | Modificado |
| **CSS** | `resources/css/components/_alerts.scss` | Modificado |

**Categorías explícitamente SIN cambios (y por qué):**

| Categoría | Por qué no cambia |
|---|---|
| **Models** | Ningún hallazgo de Fase 1 requiere tocar un modelo Eloquent. `Usuario`, `Boletin`, `Nota`, etc. se usan tal como están. |
| **Middleware** | `EnsureRole` no se toca. El rate limiting usa el middleware `throttle` nativo de Laravel (ya incluido en el framework, no hay que crear ni registrar nada nuevo salvo el *limiter* nombrado, que vive en el Provider). |
| **Views (Blade)** | El modal de contraseña temporal se construye 100% en JS sobre el DOM que ya existe (`#formAdministrativo`, `#wizardForm`, `#btnGuardar`). Ninguna vista `.blade.php` requiere edición. |
| **Config (`config/*.php`)** | No se toca `config/auth.php`, `config/session.php` ni ningún archivo de config — el rate limiter se define en código (Provider), no en config. |
| **Database / Migrations** | Cero cambios de esquema. Es la razón por la que `H2` se resuelve solo con reordenar/agrupar queries, no con índices nuevos (eso es `DB-01`/`DB-02`, Fase 3). |
| **Tests automatizados** | No se añaden tests nuevos en esta fase — bloqueado técnicamente por `H1`/`INF-17`/`INF-18` (sin migraciones de dominio ni factories, `RefreshDatabase` no funciona). En su lugar, sección 6 define el checklist de verificación manual. |

**Total: 14 archivos modificados + 1 archivo nuevo = 15.**

---

## 3. Análisis de impacto por archivo

### Bloque A — SEC-01 (Rate limiting)

**`app/Providers/AppServiceProvider.php`**
- **Qué cambia:** en `boot()`, se registra `RateLimiter::for('login', function (Request $request) { return Limit::perMinute(5)->by($request->ip().'|'.mb_strtolower((string) $request->input('usuario')))->response(fn () => response()->json(['success' => false, 'message' => 'Demasiados intentos. Espera un minuto e inténtalo de nuevo.'], 429)); });`
- **Por qué:** es el único lugar correcto en Laravel para definir un *named rate limiter* — se ejecuta una vez al boot de la app, no en cada request.
- **Módulos afectados:** solo login. Ningún otro rate limiter existe hoy, así que no hay colisión de nombres.
- **Riesgo:** 🟢 Bajo. El archivo está vacío hoy (`register()`/`boot()` sin cuerpo), no hay lógica previa que se pueda romper.

**`routes/web.php`**
- **Qué cambia:** línea 27, `Route::post('/login', [LoginController::class, 'store'])` gana `->middleware('throttle:login')`.
- **Por qué:** aplica el limiter definido arriba a la ruta específica de login (no a `/forgot-password` ni a ninguna otra — eso es `SEC-02`, Fase 2).
- **Módulos afectados:** solo el formulario de login (`auth/login.blade.php` + `login.js`), que ya maneja errores no-200 vía `response?.data?.message` — el mensaje 429 personalizado caerá ahí sin cambios adicionales en el JS.
- **Riesgo:** 🟢 Bajo. Cambio de una línea, aditivo.

### Bloque B — SEC-03 (Escalamiento de privilegios)

**`app/Http/Requests/Registro/AdministrativoStoreRequest.php`**
- **Qué cambia:** la regla `'id_rol' => ['required', 'in:1,2,3,4']` pasa a `'id_rol' => ['required', Rule::in($this->rolesAsignablesPorRolActual())]`, con un método privado que devuelve `[1,2,3,4]` si `$this->user()?->rolSlug === 'admin'`, o `[2,3,4]` en caso contrario (incluye `rector`, único otro rol que llega a esta ruta).
- **Por qué:** hoy la validación es la misma sin importar quién hace la petición; con esto, el propio Form Request rechaza con 422 cualquier intento de asignar `id_rol=1` si quien lo pide no es `admin`.
- **Módulos afectados:** `AdministrativoUpdateRequest` hereda `rules()` sin override propio (`AdministrativoUpdateRequest.php:5-8`, solo un comentario) — el fix aplica automáticamente también a edición, sin tocar ese archivo.
- **Riesgo:** 🟢 Bajo. Es una restricción, no una apertura — el único caso que podría "romperse" es si hoy existe un flujo real donde un `rector` legítimamente crea cuentas `admin`, cosa que la propia auditoría marca como el bug a corregir.

### Bloque C — H5 (Password aleatoria mostrada una vez)

**`app/Services/EstudianteRegistroService.php`**
- **Qué cambia:** línea 38, `'password' => Hash::make($d['numero_documento'])` → `'password' => Hash::make($passwordTemporal)` donde `$passwordTemporal = Str::password(10, symbols: false, spaces: false)` se genera al inicio de `crear()`. El `return` del método (línea 61) agrega `'password_temporal' => $passwordTemporal` al array.
- **Por qué:** elimina la contraseña predecible; el valor en texto plano solo existe en memoria durante este request, nunca se guarda sin hashear.
- **Módulos afectados:** `RegistroEstudiantesController::store()` (consume el array de retorno).
- **Riesgo:** 🟡 Medio. Es lógica de negocio real (generación de credenciales) — el riesgo no es que rompa algo existente, sino operativo: si nadie anota la contraseña mostrada, la cuenta queda sin forma de acceso conocida (ya señalado en el plan anterior).

**`app/Services/DocenteRegistroService.php`**
- **Qué cambia:** mismo patrón que arriba, línea 33 y el `return` de `crear()` (línea 46).
- **Por qué / riesgo:** idéntico al anterior.
- **Módulos afectados:** `RegistroDocentesController::store()`.

**`app/Http/Controllers/Panel/Registro/RegistroAdministrativosController.php`**
- **Qué cambia:** en `store()` (líneas 21-52), se genera `$passwordTemporal` antes del `Usuario::create()`, se usa en `Hash::make()`, y se agrega `'password_temporal' => $passwordTemporal` a la respuesta JSON (línea ~47-51). `update()` **no se toca** — no genera password nueva.
- **Por qué:** este controller no usa un Service (es el único de los tres registrado directo en el controller), así que el cambio va inline aquí en vez de en una clase de servicio.
- **Módulos afectados:** ninguno adicional — es el punto final de este flujo.
- **Riesgo:** 🟡 Medio, mismas razones que los Services.

**`app/Http/Controllers/Panel/Registro/RegistroEstudiantesController.php`**
- **Qué cambia:** línea 37-41, el `response()->json([...])` de `store()` agrega `'password_temporal' => $resultado['password_temporal']`.
- **Por qué:** el controller es quien arma la respuesta HTTP; el Service ya la calculó, aquí solo se propaga.
- **Módulos afectados:** `resources/js/pages/registro/estudiantes.js` (nuevo campo `data.password_temporal` a consumir).
- **Riesgo:** 🟢 Bajo — es un passthrough de un valor ya calculado, sin lógica nueva.

**`app/Http/Controllers/Panel/Registro/RegistroDocentesController.php`**
- **Qué cambia / por qué / riesgo:** idéntico al anterior, línea 36-40.
- **Módulos afectados:** `resources/js/pages/registro/docentes.js`.

**`resources/js/components/alerts/sweetAlert.js`**
- **Qué cambia:** se añade una función exportada nueva `showTemporaryPassword(password)` que abre un `Swal.fire` bloqueante (`allowOutsideClick: false`, `allowEscapeKey: false`) mostrando la contraseña en un bloque monoespaciado, con botón único "Ya la copié / anoté". No se modifica `confirmAction()` ni `confirmLogout()` (ya existentes).
- **Por qué:** reutiliza la librería y el patrón de estilo (`cs-swal`) que ya usa el resto de la app para confirmaciones, en vez de introducir una librería nueva.
- **Módulos afectados:** cualquier página futura que también necesite mostrar un secreto una sola vez podrá reutilizar esta función — hoy solo la usan los 3 flujos de registro.
- **Riesgo:** 🟢 Bajo — es una función aditiva, no modifica el comportamiento de las 2 funciones existentes en el archivo.

**`resources/js/pages/registro/estudiantes.js`, `docentes.js`, `administrativos.js`**
- **Qué cambia:** en el bloque `try` del `submit` handler, tras la respuesta exitosa, en vez de `toast.success(...) + setTimeout(redirect)` inmediato, se llama `await showTemporaryPassword(data.password_temporal)` y **solo después** de que el usuario cierre el modal se hace el `toast.success` + redirect.
- **Por qué:** es la pieza de UX que hace útil el cambio de backend — sin esto, el password se generaría pero nadie lo vería.
- **Módulos afectados:** flujo de creación de estudiantes/docentes/administrativos únicamente. Los flujos de `update()` de estas 3 páginas (edición) no se tocan porque el backend no genera password nueva ahí.
- **Riesgo:** 🟡 Medio — cambia el flujo de UX post-guardado (ya no hay redirect automático inmediato); si el modal no se cierra bien en algún navegador antiguo podría bloquear la navegación. Mitigado probando en el navegador real como parte de la verificación (sección 6).

**`resources/css/components/_alerts.scss`**
- **Qué cambia:** se añade una clase `.cs-swal-password-box` (fuente monoespaciada, fondo sutil, `letter-spacing`, `user-select: all` para facilitar copiar) siguiendo el mismo patrón de sección `// ---- SweetAlert2 ----` ya existente.
- **Por qué:** consistencia visual con el resto de modales de la app en vez de estilos inline en el JS.
- **Módulos afectados:** ninguno fuera del modal nuevo — es una clase nueva, no se modifica ninguna regla existente.
- **Riesgo:** 🟢 Bajo.

### Bloque D — H2 + BE-05 (Boletines)

**`app/Actions/GenerarBoletinesAction.php` (nuevo)**
- **Qué contiene:** la lógica completa hoy en `BoletinesController::generar()` (líneas 41-110), reescrita así:
  1. Precarga en una consulta todas las `Nota` de los estudiantes/actividades del curso (`whereIn`), indexadas en un array `[id_actividad][id_estudiante] => nota` para lookup en memoria en vez de query por combinación.
  2. Mantiene `Boletin::firstOrCreate(...)` y `BoletinDetalle::updateOrCreate(...)` **exactamente igual que hoy** (sin `upsert`, por la razón ya explicada: no hay índices únicos todavía).
  3. Calcula puestos igual que hoy (`arsort` sobre el array de promedios) pero los persiste con **un solo** `UPDATE ... CASE WHEN id_estudiante THEN puesto END` en vez de un `UPDATE` por estudiante.
  4. Todo el método envuelto en `DB::transaction()`.
- **Por qué una clase nueva y no dejarlo en el controller:** aislar la lógica de negocio del HTTP la hace reutilizable (ej. desde un comando artisan más adelante) y es el primer paso hacia lo que `ARQUITECTURA_PROYECTO.md` ya propuso para este módulo — sin adelantar el resto de la reestructuración de Fase 4.
- **Módulos afectados:** ninguno además de `BoletinesController` — es lógica pura, sin vistas ni rutas nuevas.
- **Riesgo:** 🟠 Medio-Alto — es el cambio de mayor riesgo lógico del plan (ver sección 7).

**`app/Http/Controllers/Panel/BoletinesController.php`**
- **Qué cambia:** el método `generar()` (líneas 41-110) se reduce a: validar el request (igual que hoy, líneas 43-46), resolver `$curso` (igual, línea 48), y delegar `app(GenerarBoletinesAction::class)->execute($curso, $idPeriodo)`, devolviendo la misma respuesta JSON de éxito que hoy.
- **Por qué:** el controller deja de contener lógica de cálculo, solo orquesta HTTP → Action → respuesta.
- **Módulos afectados:** ninguno — la ruta (`POST /boletines/generar`), el contrato de entrada/salida JSON y el resto de métodos del controller (`index`, `show`, `publicar`, `anular`, `volverBorrador`) quedan intactos.
- **Riesgo:** 🟢 Bajo por sí mismo (es solo la delegación) — el riesgo real vive en la Action.

---

## 4. Estrategia de implementación

Cada paso se implementa, se verifica (sección 6) y se commitea **antes** de pasar al siguiente. Orden pensado para aislar el cambio de mayor riesgo lógico (Boletines) al final, después de validar el ritmo de trabajo en los 3 bloques más mecánicos.

**Paso 1 — Implementar Rate Limiting (SEC-01)**
Editar `AppServiceProvider.php` + `routes/web.php`.

**Paso 2 — Probar Login**
Casos ✓Login correcto / ✓Login incorrecto / ✓6 intentos fallidos (sección 6, bloque A). Commit: `fix(security): rate limit en /login para prevenir fuerza bruta`.

**Paso 3 — Corregir escalamiento de privilegios (SEC-03)**
Editar `AdministrativoStoreRequest.php`.

**Paso 4 — Probar creación/edición de administrativos**
Casos ✓Rector creando admin (debe fallar) / ✓Admin creando admin (debe funcionar) / ✓Admin creando rector / ✓Rector creando secretario (sección 6, bloque B). Commit: `fix(security): impedir que rector escale privilegios a admin`.

**Paso 5 — Implementar password aleatoria en backend (H5, parte 1/2)**
Editar los 2 Services + 3 Controllers de registro (Estudiantes, Docentes, Administrativos).

**Paso 6 — Probar backend de H5 con una herramienta HTTP directa (sin UI todavía)**
Verificar que la respuesta JSON de `POST /registro/estudiantes` (y docentes/administrativos) incluye `password_temporal` y que el login con esa contraseña funciona, antes de tocar el frontend.

**Paso 7 — Implementar modal de contraseña en frontend (H5, parte 2/2)**
Editar `sweetAlert.js`, los 3 archivos `pages/registro/*.js`, y `_alerts.scss`. Ejecutar `npm run build` (o mantener `npm run dev` corriendo) para que Vite recompile.

**Paso 8 — Probar flujo completo de registro en navegador**
Casos ✓Crear estudiante (modal aparece, contraseña funciona) / ✓Crear docente / ✓Crear administrativo / ✓Editar estudiante existente (NO debe pedir/mostrar password) (sección 6, bloque C). Commit: `feat(security): generar password aleatoria en vez de usar el documento (H5)`.

**Paso 9 — Extraer y optimizar `GenerarBoletinesAction` (H2 + BE-05)**
Crear `app/Actions/GenerarBoletinesAction.php`, migrar y optimizar la lógica.

**Paso 10 — Actualizar `BoletinesController::generar()` para delegar en la Action**

**Paso 11 — Probar generación de boletines con datos reales**
Casos ✓Generar boletines primera vez / ✓Regenerar boletines (ya existentes) / ✓Verificar promedio igual al calculado manualmente para 1-2 estudiantes de control / ✓Verificar orden de puestos / ✓Medir cantidad de queries antes/después (sección 6, bloque D). Commit: `perf(boletines): eliminar N+1 y envolver generación en transacción (H2, BE-05)`.

**Paso 12 — Verificación final de regresión**
`php artisan test`, `vendor/bin/pint --test`, `php artisan route:list`, smoke test de navegación general del panel (sección 6, bloque E).

---

## 5. Plan de rollback

Como ningún paso toca base de datos ni archivos fuera de `app/`, `routes/`, `resources/js/` y `resources/css/`, el rollback es puramente de código y de bajo riesgo:

1. **Por commit individual (recomendado):** cada paso de la sección 4 es un commit atómico → `git revert <hash>` del commit específico que causó el problema, sin afectar los demás. Es la opción preferida porque permite mantener las 3 correcciones que sí funcionaron.
2. **Rollback total de la fase:** si algo crítico se detecta y no hay tiempo de aislar el commit responsable, `git reset --hard <hash-antes-del-paso-1>` (o `git revert` en cadena de todos los commits de la fase, más seguro si ya se hizo push) vuelve exactamente al estado actual del branch.
3. **Después de cualquier rollback que toque `AppServiceProvider.php` o `routes/web.php`:** ejecutar `php artisan config:clear && php artisan route:clear` — Laravel cachea config/rutas y un rollback de archivo sin limpiar caché puede dejar el rate limiter "fantasma" activo un tiempo.
4. **Después de cualquier rollback que toque JS/CSS:** volver a ejecutar `npm run build` (o reiniciar `npm run dev`) — el navegador puede tener el bundle nuevo cacheado hasta que se recompile el viejo.
5. **Nada que revertir en base de datos:** ninguna migración se ejecuta en esta fase, así que no hay riesgo de tener que restaurar un backup de `colegio`. Las contraseñas ya generadas con el nuevo esquema (H5) seguirían siendo válidas aunque se revierta el código — revertir el código no invalida hashes ya guardados, solo detiene la generación de nuevas.

---

## 6. Casos de prueba manual

Cada bloque se prueba en navegador contra la BD real de desarrollo (`colegio`), ya que no hay entorno de test automatizado disponible todavía (H1). Ejecutar en orden, dentro de cada bloque.

### Bloque A — Rate limiting (Paso 2)
- ✓ Login correcto con credenciales válidas → entra normalmente.
- ✓ Login incorrecto (password mal) → mensaje "Usuario o contraseña incorrectos.", sin bloqueo.
- ✓ 5 intentos fallidos seguidos en menos de 1 minuto → el 5º sigue mostrando el error normal de credenciales.
- ✓ 6º intento en el mismo minuto → mensaje "Demasiados intentos. Espera un minuto e inténtalo de nuevo." (429), no el mensaje genérico de credenciales.
- ✓ Esperar 60 segundos y reintentar → vuelve a aceptar intentos normalmente.
- ✓ Login exitoso NO cuenta contra el límite de forma que bloquee sesiones futuras del mismo usuario (verificar que tras un login correcto, un logout+login inmediato no está bloqueado).

### Bloque B — Escalamiento de privilegios (Paso 4)
- ✓ Autenticado como `rector`, intentar crear un administrativo con rol "Administrador" (`id_rol=1`) → debe rechazar con error de validación (422), campo `id_rol`.
- ✓ Autenticado como `rector`, crear un administrativo con rol "Secretario" (`id_rol=4`) → debe funcionar normalmente.
- ✓ Autenticado como `admin`, crear un administrativo con rol "Administrador" (`id_rol=1`) → debe funcionar normalmente (el `admin` conserva el permiso completo).
- ✓ Autenticado como `admin`, crear un administrativo con rol "Rector" (`id_rol=2`) → debe funcionar.
- ✓ Autenticado como `rector`, editar su propia cuenta intentando cambiar su `id_rol` a 1 vía el formulario de edición → debe rechazar igual que en creación.
- ✓ Confirmar que el mensaje de error mostrado al `rector` es comprensible (no un JSON crudo) en el formulario de la vista `panel/registro/administrativos.blade.php`.

### Bloque C — Password aleatoria (Paso 8)
- ✓ Crear un estudiante nuevo → al guardar, aparece el modal con una contraseña de 10 caracteres alfanuméricos (sin símbolos), el modal no se puede cerrar haciendo clic afuera ni con Esc.
- ✓ Clic en "Ya la copié / anoté" → el modal cierra y redirige a `/listados?tab=estudiantes` como antes.
- ✓ Iniciar sesión con el número de documento del estudiante recién creado y la contraseña mostrada en el modal → debe entrar correctamente.
- ✓ Intentar iniciar sesión con el número de documento como contraseña (el comportamiento viejo) → debe **rechazar** (confirma que ya no se usa el documento como password).
- ✓ Repetir los 4 puntos anteriores para creación de **docente**.
- ✓ Repetir los 4 puntos anteriores para creación de **administrativo**.
- ✓ Editar un estudiante/docente/administrativo **existente** → el modal de contraseña **no debe aparecer** (edición no genera password nueva), y el estudiante debe poder seguir entrando con su contraseña anterior sin cambios.
- ✓ Verificar en la tabla `usuarios` (solo lectura, sin modificar) que el campo `password` de la cuenta nueva es un hash bcrypt distinto entre dos estudiantes creados seguidos (confirma que la contraseña es aleatoria y no un valor fijo).

### Bloque D — Boletines (Paso 11)
- ✓ Seleccionar un curso y periodo que **nunca** haya generado boletines → generar y confirmar mensaje de éxito.
- ✓ Abrir el boletín de 1-2 estudiantes del curso y verificar manualmente (calculadora) que el promedio ponderado coincide con las notas/porcentajes reales de sus actividades.
- ✓ Verificar que el `puesto_curso` asignado corresponde al orden real de promedios (mayor promedio = puesto 1).
- ✓ Volver a generar boletines para el **mismo** curso/periodo (regeneración) → no debe crear boletines duplicados, debe actualizar los existentes.
- ✓ Con Laravel Debugbar/Telescope si está disponible, o `DB::listen` temporal, contar las queries ejecutadas antes vs. después del cambio para un mismo curso — debe ser sustancialmente menor (referencia: de ~cientos a un puñado de decenas para un curso de 30 estudiantes).
- ✓ Simular un fallo a mitad de proceso si es posible (ej. curso sin ninguna asignación activa) → no debe dejar boletines a medio crear gracias a la transacción; o bien debe completar el caso vacío sin error.

### Bloque E — Regresión general (Paso 12)
- ✓ `php artisan test` → los 2 tests de ejemplo existentes siguen en verde (confirma que la app arranca sin errores de sintaxis/config).
- ✓ `vendor/bin/pint --test` → sin diferencias de estilo en los archivos modificados.
- ✓ `php artisan route:list --path=login` y `--path=registro` y `--path=boletines` → todas las rutas siguen resolviendo a los controllers correctos.
- ✓ Navegación manual rápida por el panel (dashboard, listados, gestión académica) → confirmar que ningún módulo no tocado por este plan se ve afectado (nada debería cambiar ahí, pero se verifica).

---

## 7. Riesgos

| Riesgo | Probabilidad | Impacto | Mitigación |
|---|---|---|---|
| El rate limiter bloquea a un usuario legítimo que solo tecleó mal su contraseña varias veces | Media | Bajo (se resuelve solo en 1 minuto) | Límite generoso (5/min, no 3), mensaje claro con indicación de esperar |
| La regla `Rule::in` dinámica de SEC-03 depende de `$this->user()` — si por algún motivo el Form Request se ejecuta sin usuario autenticado resuelto todavía | Baja | Medio (bloquearía creación de administrativos) | La ruta ya exige `role:admin,rector` antes de llegar al Form Request, así que `$this->user()` siempre estará poblado; se verifica explícitamente en Bloque B |
| Refactor de `GenerarBoletinesAction` introduce una diferencia sutil en el cálculo del promedio o el desempate de puestos respecto al código original | Media | **Alto** — datos de calificaciones incorrectos mostrados a estudiantes/acudientes | Es el paso con más tiempo dedicado a verificación manual (Bloque D); se recomienda ejecutarlo primero contra un curso de prueba/no oficial antes que contra un curso real con estudiantes reales, si existe esa posibilidad en el entorno actual |
| El modal bloqueante de contraseña (H5) interrumpe un flujo que el personal de secretaría no espera, generando confusión inicial | Alta (es un cambio de UX real) | Bajo | Es un cambio de comportamiento esperado y aprobado por decisión de producto; se recomienda avisar al personal antes de desplegar, no es un riesgo técnico |
| Si se cierra el modal de contraseña sin anotarla y el correo del estudiante es el autogenerado (no real) | Media | Medio (cuenta sin forma de acceso conocida) | Riesgo operativo ya señalado en el plan aprobado, no corregible solo con código en esta fase — considerar como candidato a Fase 2 (flujo de "regenerar password" para admins) |
| Cambios de JS/CSS no se ven reflejados si no se recompila con Vite | Alta si se olvida el paso | Bajo (fácil de detectar: nada cambia visualmente) | Paso 7 incluye explícitamente `npm run build` / confirmar `npm run dev` activo antes de probar |
| Sin tests automatizados de regresión, un efecto colateral no evidente en las pruebas manuales pasa desapercibido | Media | Medio | Bloque E de verificación + revisión de código línea por línea contra el original en cada diff antes de commitear |

---

## 8. Estimación

| Paso | Complejidad | Tiempo estimado |
|---|---|---|
| 1-2 — Rate limiting + pruebas | Baja | 1-1.5 h |
| 3-4 — Escalamiento de privilegios + pruebas | Baja | 1-1.5 h |
| 5-6 — Password backend + pruebas de API | Media (3 archivos con el mismo patrón, pero hay que repetirlo con cuidado) | 2-2.5 h |
| 7-8 — Password frontend + pruebas en navegador | Media (UX nueva, 4 archivos JS/CSS + rebuild) | 2.5-3 h |
| 9-10 — Refactor de boletines + delegación en controller | **Alta** (es lógica de negocio sensible) | 3-4 h |
| 11 — Pruebas de boletines con datos reales | Alta (requiere verificación matemática manual) | 1.5-2 h |
| 12 — Verificación final de regresión | Baja | 0.5-1 h |
| **Total** | — | **≈ 12-16 horas de trabajo efectivo** |

**Orden recomendado:** el de la sección 4 (SEC-01 → SEC-03 → H5 → H2). Justificación: los dos primeros son cambios de una línea con riesgo mínimo, ideales para validar el ritmo de commits/pruebas antes de tocar algo más grande. H5 le sigue porque, aunque toca 9 archivos, cada cambio individual es mecánico (mismo patrón repetido 3 veces) y de bajo riesgo lógico. `GenerarBoletinesAction` queda al final porque es el único cambio que altera un cálculo de negocio real — conviene abordarlo con el contexto ya "calentado" por los pasos anteriores y con tiempo dedicado exclusivamente a su verificación, sin mezclarlo con otros cambios en el mismo tramo de trabajo.

---

## 9. Qué necesito de ti para empezar

¿Apruebas este plan completo (los 12 pasos, en este orden), o prefieres que ajuste algo — por ejemplo, el orden, el nivel de detalle de alguna prueba, o excluir algún paso de esta fase?

Una vez confirmes, empiezo por el **Paso 1** y te muestro el resultado de cada bloque antes de continuar al siguiente.
