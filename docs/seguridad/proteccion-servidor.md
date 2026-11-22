# Protección del servidor y de archivos confidenciales

**Fecha:** 2026-11-18 · **Estado:** implementado en local, sin commit · **Alcance:** solo publicación web de archivos.

> ⚠️ Esta fase **no** resuelve la falta de aislamiento de datos entre colegios (vulnerabilidad crítica, fase independiente). El sistema **no está listo para producción**.

## 1. Problema

El proyecto se publicaba con la **raíz del repositorio** como carpeta web (XAMPP: repo dentro de `htdocs/`). El `.htaccess` raíz servía "cualquier archivo o carpeta real tal cual", y XAMPP tiene `Options Indexes` activo. Prueba con Apache 2.4.58 de XAMPP (línea base, antes del cambio):

| Recurso | Resultado antes |
|---|---|
| `laravel/.env` (y variantes `laravel/public/../.env`, `laravel/%2e%65%6e%76`, `index.php/../laravel/.env`) | **200 — descargable** |
| `.git/HEAD`, `.git/config` (todo el código e historial) | **200** |
| `laravel/storage/logs/laravel.log` | **200** (191 KB) |
| `laravel/composer.json`, `composer.lock`, `artisan`, `.env.example`, `phpunit.xml` | **200** |
| `docs/`, `estructura.txt`, `README.md`, `scripts/*.bat` | **200** |
| Listados `laravel/`, `laravel/storage/`, `storage/logs/`, `storage/framework/sessions/` | **200 (Index of)** |
| `laravel/config/*.php`, `routes/web.php`, `vendor/autoload.php`… | **ejecutados por PHP** (500 / 200) |

**Evidencia de explotación:** ninguna. El log de acceso de XAMPP (15-ago → 29-oct-2026) solo registra clientes `::1` y `127.0.0.1` y ninguna petición a `.env`, logs, `.git` ni `composer` de este proyecto. Por eso **no se exige rotar secretos** (ver §6).

## 2. Solución

### A. Configuración definitiva (no aplicada: requiere administrar el servidor)
- [`docs/despliegue/apache-sistema-escolar.conf.example`](../despliegue/apache-sistema-escolar.conf.example):
  - **VirtualHost** con `DocumentRoot = laravel/public` (recomendada).
  - **Alias** `/sistema-escolar → laravel/public` (conserva la URL actual; usa `FallbackResource` porque el `.htaccess` de Laravel no funciona bajo `Alias` sin `RewriteBase`).
- [`docs/despliegue/nginx-sistema-escolar.conf.example`](../despliegue/nginx-sistema-escolar.conf.example): `root laravel/public`, bloqueo de ocultos, solo `index.php` ejecutable.

Con A, el `.htaccess` y el `index.php` de la raíz del repo dejan de usarse. **Un `.htaccess` no sustituye esta configuración.**

### B. `.htaccess` raíz como lista blanca (defensa en profundidad)
- `Options -Indexes -MultiViews`.
- Sin `mod_rewrite` ⇒ `Require all denied` (falla cerrado).
- `<FilesMatch>` niega ocultos, `*.env|log|sql|sqlite|bak|lock|yml|ini|md|bat|sh…`, `artisan`, `composer.json`, `package.json`, `phpunit.xml`, `estructura.txt` — funciona incluso sin `mod_rewrite`.
- 404 para: cualquier segmento oculto, `laravel/` salvo `laravel/public/`, `docs/`, `scripts/`, `estructura.txt`, `README.md`.
- Solo se sirven: el shim `index.php`, archivos existentes en `laravel/public/` y rutas de Laravel. **Se eliminó la regla que servía cualquier archivo real.**
- Se conserva el nombre de carpeta dinámico (`/colegio/` o `/sistema-escolar/`).

### C. Segunda barrera
- `laravel/.htaccess` (nuevo): `Require all denied`.
- `laravel/public/.htaccess`: se añadió `Require all granted` al inicio (sin esto, `public/` heredaría la denegación). Verificado: la herencia funciona y el symlink `public/storage` sigue sirviendo.

## 3. Pruebas (Apache 2.4.58 de XAMPP, usuario normal, solo 127.0.0.1)

Puertos usados: **8199** (estilo htdocs), **8198** (VirtualHost), **8197** (Alias). El 8099 previsto estaba ocupado por otro proyecto local (`corehuman-local`), que no se tocó. Base de datos de las pruebas: `colegio_restauracion_prueba` (copia), cuenta de prueba; la base real no recibió ninguna escritura (0 sesiones nuevas).

| Configuración | 38 recursos privados | Filtraciones | 10 recursos públicos (HTML, CSS, JS, SVG, fuentes, favicon, robots) | Login real (CSRF / clave mala / clave buena / panel) |
|---|---|---|---|---|
| Antes (htdocs, original) | 22 accesibles | **15** | 200 | — |
| Solo B (htdocs) | 403/404 | 0 | 200 | — |
| **B + C (htdocs)** | 403/404 | **0** | 200 | 419 / 401 / 200→`/mi-panel` / 200 |
| B + C **sin mod_rewrite** | 403 (todo) | 0 | 403 (falla cerrado) | — |
| **A: VirtualHost** | 404/403 (+5 × 301 de barra final → 404) | 0 | 200 | 419 / 401 / 200 / 200 |
| **A: Alias** | 403/404 | 0 | 200 | 419 / 401 / 200 / 200 |

Navegador (Chromium): en las 3 variantes cargan 5 hojas CSS, la fuente DM Sans, el avatar y el login llega a `/mi-panel`; sin recursos fallidos de la aplicación.

Herramientas usadas (fuera del repositorio): `generar.sh` (config temporal de Apache) y `probar.sh` (solo imprime código HTTP, tamaño y si hay huellas de contenido privado; nunca el contenido).

## 4. Compatibilidad y diferencias de comportamiento
- URLs públicas sin cambios: `/`, `/login`, `/build/…`, `/vendor/fonts/…`, `/storage/…`, `/favicon.ico`, `/robots.txt`.
- `/sistema-escolar/laravel/public/…` (acceso directo, nadie lo usa: `asset()` genera `/sistema-escolar/build/…`) ahora pasa por Laravel y responde 404.
- Con la capa C, lo interno de `laravel/` responde **403** (denegación de permisos) en vez de 404: revela que la carpeta existe, sin dar acceso.
- `/.well-known/` queda bloqueado en el esquema htdocs. Si se necesitara validar certificados ACME en ese esquema, habría que permitirlo explícitamente; con la configuración A (nginx) ya está permitido.
- Si el servidor tiene `AllowOverride` sin `Options`, la línea `Options -Indexes` del `.htaccess` provocaría un 500: mover esa opción a la configuración del servidor.

## 5. Reversión (solo esta fase)
Respaldo de los originales: `~/Respaldos/sistema-escolar/fase-servidor-20261118_114240/` (con `SHA256SUMS`).

```bash
R=~/Respaldos/sistema-escolar/fase-servidor-20261118_114240
cp "$R/raiz.htaccess.orig"            .htaccess
cp "$R/laravel-public.htaccess.orig"  laravel/public/.htaccess
rm laravel/.htaccess
rm -r docs/despliegue docs/seguridad/proteccion-servidor.md   # opcional
```
No usar `git checkout`/`git reset`: hay otros cambios sin commit en el árbol que no pertenecen a esta fase. Ningún cambio toca base de datos ni configuración del sistema.

## 6. Secretos

| Secreto | Valor | ¿Rotar? |
|---|---|---|
| `APP_KEY` (repo y clon de `htdocs`) | Con valor | **No por evidencia.** Rotar solo si XAMPP estuvo accesible en una red compartida antes del 15-ago-2026 (no hay logs anteriores). Rotarla cierra todas las sesiones y obliga a reconfigurar el 2FA del SuperAdmin. |
| `DB_PASSWORD` | Vacío (`root` sin clave) | No es fuga; **en producción es obligatorio** un usuario propio con clave. |
| Correo, Reverb, AWS, Redis | Vacíos | No aplica. |
| Enlace de recuperación en `laravel.log` | 1, de prueba, ya usado y vencido | No; con `MAIL_MAILER=log` los enlaces se escriben en el log: en producción usar un servidor de correo real. |

## 7. Requisitos para producción (lista de verificación)
- [ ] Raíz web = `laravel/public` (configuración A). Nunca la raíz del repositorio.
- [ ] `APP_ENV=production`, `APP_DEBUG=false`, `LOG_LEVEL` ≥ `warning`.
- [ ] HTTPS; `SESSION_SECURE_COOKIE=true`; considerar `SESSION_ENCRYPT=true`.
- [ ] Usuario de base de datos propio, con clave y privilegios mínimos.
- [ ] `.env` con permisos `600` y propietario el usuario del servicio; `storage/` y `bootstrap/cache/` escribibles solo por ese usuario.
- [ ] `MAIL_MAILER` real (no `log`).
- [ ] `ServerTokens Prod`, `ServerSignature Off`, `expose_php = Off`.
- [ ] `composer install --no-dev --optimize-autoloader`, `php artisan config:cache route:cache view:cache`.
- [ ] Copias de seguridad cifradas de la base de datos y de `storage/app`.
- [ ] Cabeceras de seguridad (CSP en Report-Only primero): fase posterior.
- [ ] **Aislamiento entre colegios: pendiente (crítico).**

## 8. Riesgos pendientes
1. 🔴 Aislamiento de datos entre instituciones (IDOR/BOLA) — sin cambios en esta fase.
2. 🔴 El clon `/opt/lampp/htdocs/sistema-escolar` **sigue con el `.htaccess` antiguo** (y 3 commits sin subir). Si se enciende XAMPP, ese clon sigue expuesto hasta actualizarlo o migrar a la configuración A.
3. 🟠 Adjuntos privados (eventos, soporte) en el disco `public`.
4. 🟠 Sin cabeceras de seguridad; `X-Powered-By` visible.
5. 🟠 Dependencias con avisos (`symfony/yaml`, paquetes npm).
6. 🟡 XAMPP escucha en todas las interfaces (`Listen 80`): en redes compartidas, limitar a `127.0.0.1:80`.
