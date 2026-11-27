# Fase 1 — Contención de seguridad

**Rama:** `fix/fase1-contencion-seguridad`, creada desde `origin/main` (`58c10d2`, PR #36).
**Alcance:** P0-1, P0-2 y P1-1 a P1-4 de la auditoría de la Fase 0. Sin cambios de esquema ni de datos.

## Resumen de correcciones

| Id | Problema | Corrección |
|---|---|---|
| P0-1 | El enlace de recuperación se armaba con el `Host` de la petición | `UrlPublica` arma los enlaces desde `APP_URL` validada: absoluta, sin credenciales ni consulta, https en producción. Si `APP_URL` no es válida, no se genera token ni se envía correo. `ValidarHostConfiable` rechaza con 400 los Host que no sean del servidor |
| P0-2 | `APP_ENV=local` / `APP_DEBUG=true` | Plantilla documentada en `.env.example` y compuerta `php artisan seguridad:verificar-exposicion` (sale con 1 si algo bloquea). En producción, `APP_DEBUG=true` se fuerza a `false` y queda un registro crítico (salvaguarda, no sustituye corregir el `.env`) |
| P1-1 | Cuenta desactivada seguía operando | `Usuario::motivoDeBloqueo()` como única regla (cuenta, rol, institución) y `EnsureCuentaHabilitada` en el grupo `web`. `RevocacionDeSesiones` se aplica al desactivar, cambiar rol o permisos y desactivar un rol: estampa `sesion_valida_desde`, anula `remember_token` y borra las filas de `sessions` |
| P1-2 | Institución suspendida seguía operando | La misma regla bloquea las instituciones `suspendida` e `inactiva`, y también a un usuario institucional sin institución. Suspender revoca las sesiones de todos sus usuarios. SuperAdmin no se evalúa por institución |
| P1-3 | Estadísticas contaba notas de todos los colegios | Distribución con `BoletinDetalle::deInstitucion()` + asignación del mismo colegio (EXISTS, una consulta). El ranking une la matrícula del mismo año lectivo que el periodo (sin duplicados) |
| P1-4 | Sin proxies ni hosts de confianza | `ConfiarProxiesConfigurados` acepta solo `TRUSTED_PROXIES` validados; nunca `*`, `0.0.0.0/0` ni el atajo de Laravel para `*.on-forge.com`. Cabeceras aceptadas: For, Proto y Port; Host solo con opción explícita. Límite `throttle:recuperacion` en `forgot-password` y `reset-password` |

## Variables de entorno

| Variable | Local (sin cambios) | Expuesto (servidor o ngrok) |
|---|---|---|
| `APP_ENV` | `local` | `production` |
| `APP_DEBUG` | `true` | `false` |
| `APP_URL` | `http://localhost:8000` | URL pública exacta, `https://…` |
| `APP_TRUSTED_HOSTS` | vacío | hosts adicionales exactos (opcional) |
| `APP_VALIDATE_HOSTS` | vacío (automático = inactivo en local) | vacío (automático = activo) o `true` |
| `TRUSTED_PROXIES` | vacío | IP o rango del proxy; con ngrok en la misma máquina, `127.0.0.1,::1` |
| `TRUSTED_PROXY_FORWARDED_HOST` | `false` | `false` |
| `SESSION_SECURE_COOKIE` | vacío | `true` |
| `LOG_LEVEL` | `debug` | `info` o `warning` |

Después de cambiar el `.env`: `php artisan config:clear` y luego `php artisan seguridad:verificar-exposicion` (con ngrok, `--ngrok`). **No exponer si el comando informa BLOQUEANTE.**

## Requisitos específicos de ngrok (no activado)

- La exposición pública **sigue bloqueada**: requiere un authtoken válido, un dominio fijo (`--url`), un `.env` de exposición y la verificación en verde.
- ngrok termina TLS y reenvía a `localhost:8000` desde `127.0.0.1`. Por eso `TRUSTED_PROXIES=127.0.0.1,::1`: sin eso, todos los clientes comparten la IP local y un solo atacante agota el límite de login de todos.
- ngrok conserva el `Host` público (`<dominio>.ngrok-free.app`). `APP_URL` debe ser exactamente `https://<dominio fijo>`. Con un dominio aleatorio que cambia en cada arranque, los enlaces de correo quedarían rotos: usar un dominio estático.
- No usar `--host-header=rewrite` (rompe la validación de Host) ni `TRUSTED_PROXIES=*`.
- `php artisan serve` no aplica `public/.htaccess`: los bloqueos de `storage/(perfiles|soporte|eventos)` solo existen con Apache (riesgo latente P3-3; hoy 0 archivos).

## Producción real

- `TRUSTED_PROXIES`: solo la IP o el rango del balanceador o proxy inverso, nunca `*`.
- En el servidor web, un vhost por defecto que rechace Host desconocidos, además de `ValidarHostConfiable`.
- HTTPS con certificado válido y redirección de http a https en el servidor web; `SESSION_SECURE_COOKIE=true`.
- Pendiente de la Fase 3: cabeceras de seguridad (CSP, HSTS, X-Frame-Options), worker, scheduler y logs sin datos personales.

## Comportamiento visible

- Cuenta o institución bloqueada: en rutas protegidas se cierra la sesión y se redirige a `/login` con el motivo en `session('status')`; si la petición es JSON, 401. En rutas públicas, la petición continúa como invitado.
- **La vista de login no muestra `session('status')` hoy** (ya pasaba con `EnsureSessionFresh`). Mostrarla es un cambio de interfaz pendiente.
- Reactivar una cuenta o una institución no revive sesiones ni cookies anteriores: hay que iniciar sesión de nuevo.
- Login con institución suspendida: 403 con el mensaje «El acceso de tu institución está suspendido…».

## Pruebas

51 pruebas nuevas en `tests/Feature/Seguridad/Fase1/` (5 clases) y `tests/Feature/Aislamiento/EstadisticasDistribucionTest.php`, todas en SQLite en memoria. Contra el código anterior (`origin/main`), 38 fallan; las otras 13 son de no-regresión y pasan en ambas versiones. Cada commit de la fase pasa la suite completa por sí solo.

| Hallazgo | Clase de pruebas |
|---|---|
| P0-1 | `RecuperacionHostConfiableTest` (Host y X-Forwarded-Host maliciosos, APP_URL inválida, https en producción, flujo completo) |
| P0-2 | `ExposicionSeguraTest` (páginas 500 y 404 sin detalles, compuerta de exposición) |
| P1-1 | `CuentaDesactivadaTest` (sesión, «recordarme», JSON, rutas fuera del panel, login, recuperación, .ics, cambio de rol, rol inactivo) |
| P1-2 | `InstitucionSuspendidaTest` (suspendida e inactiva, reactivación, SuperAdmin, otra institución, rutas públicas, usuario sin institución) |
| P1-3 | `EstadisticasDistribucionTest` (valores exactos por colegio, datos cruzados, ranking sin duplicados) |
| P1-4 | `HostsYProxiesTest` (hosts exactos, comodines, proxies, «todos» rechazado, on-forge, límites de login y recuperación, APP_VALIDATE_HOSTS vacío) |

`/inicio` y los horarios usan `FIELD()` y solo se prueban en el grupo MariaDB (no ejecutado en esta fase).
