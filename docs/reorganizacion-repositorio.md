<!-- title: Reorganización del Repositorio — legacy/ vs laravel/ -->

# Reorganización del Repositorio
## Separar `legacy/` de `laravel/` sin ambigüedad de nombres

**Alcance de este informe:** exclusivamente la estructura de carpetas **de la raíz del repositorio** (`colegio/`) — dónde vive cada sistema, no su contenido interno. `laravel/` no se toca por dentro, tal como pediste. Nada de lo que sigue se ejecutó todavía.

---

## 🚨 Hallazgo urgente, independiente de esta reorganización

Antes de la propuesta: mientras auditaba el `.gitignore` de la raíz encontré algo que está pasando **ahora mismo**, en cada sesión, y que conviene arreglar ya, apruebes o no el resto de este informe.

El `.gitignore` de la raíz tiene esta línea:

```
config/
```

Un patrón de `.gitignore` **sin `/` inicial coincide con cualquier carpeta con ese nombre, a cualquier profundidad del repositorio** — no solo con `colegio/config/` (el config del legacy, que es la intención real). Verifiqué el efecto real:

```
$ git check-ignore -v laravel/config/panel_menu.php laravel/config/legacy.php
.gitignore:9:config/	laravel/config/panel_menu.php
.gitignore:9:config/	laravel/config/legacy.php
```

**`laravel/config/` completo — los 13 archivos, incluidos `panel_menu.php` (la fuente de verdad del sidebar) y `legacy.php` (la URL del sistema legacy para el cutover) — nunca estuvo en git.** Todo lo que configuramos ahí en las últimas sesiones existe solo en el disco de esta máquina. Si el repo se clona de nuevo, o si se pierde el disco, se pierde toda esa configuración sin ningún historial para recuperarla.

**Recomendación:** corregir el patrón a `/config/` (con `/` inicial, ancla el patrón a la raíz exacta del repo) y hacer `git add laravel/config/` para empezar a versionarlo. Es una corrección de una línea, aditiva, cero riesgo de romper nada — no requiere esperar a la reorganización grande. Te pregunto al final si querés que la haga ya.

---

## FASE 1 — Análisis

### Estructura actual de la raíz

```
colegio/                          (raíz del repo git)
├── .claude/                       (config de tooling — no es código de ninguna app)
├── .git/
├── .gitignore                     (con el bug de arriba + entradas obsoletas)
├── .htaccess                      (RewriteBase /colegio/ — acopla la app al nombre de esta carpeta)
├── README.md                      (desactualizado, ver hallazgo R8)
├── app/                           (LEGACY: controllers/, helpers/, models/, views/)
├── config/                        (LEGACY: config.php, database.php, mail.php, menu.php — sin versionar, ver R2)
├── composer.json / composer.lock  (LEGACY: solo phpmailer, sin autoload PSR-4)
├── composer.phar                  (binario portátil de Composer, 3.6 MB, gitignored)
├── index.php                      (router legacy)
├── public/                        (LEGACY: solo assets estáticos — no es un front controller)
├── vendor/                        (LEGACY: dependencias de composer.json)
└── laravel/                       (sistema moderno, estructura Laravel estándar completa e intacta)
```

### Hallazgos

**R1 — Ambigüedad de nombres (el problema que mostraste en la captura).** `app/`, `config/`, `public/` en la raíz son indistinguibles a simple vista de `laravel/app/`, `laravel/config/`, `laravel/public/`. Cualquiera que abra el explorador de archivos —vos, alguien nuevo en el equipo, o yo en una sesión sin este contexto cargado— tiene que entrar a cada carpeta para saber a qué sistema pertenece.

**R2 — `config/` (legacy) no está versionado en git** (ver hallazgo urgente arriba — mismo patrón, efecto real distinto: acá la intención probablemente SÍ era ignorar `config/database.php` por las credenciales, pero el patrón de una sola línea se llevó puesto también `config.php` y `menu.php`, que no tienen secretos). Consecuencia: un clon nuevo del repo no tiene `config/` del legacy en absoluto y la app no arranca hasta recrearlo a mano.

**R3 — `.htaccess` y `config/config.php` acoplan la app al nombre literal `colegio/`.**
- `.htaccess`: `RewriteBase /colegio/`
- `config/config.php`: `$baseFolder = '/colegio/'` — usado para construir `BASE_URL` y como `path` de la cookie de sesión.

Mover el legacy a `legacy/` **cambia la URL pública** de la app (de `localhost/colegio/...` a lo que sea que resuelva el nuevo `DocumentRoot`). No es un simple `git mv`: hay que actualizar estos 2 archivos como parte del mismo cambio, no después.

**R4 — Dependencia directa con el VirtualHost pendiente de la sesión anterior.** Ya dejé preparado (pero no activado) un vhost `legacy.colegio.test → DocumentRoot C:/xampp/htdocs/colegio`. Verifiqué ahora mismo que **sigue sin activar** (el hosts file no tiene las entradas, Apache tiene los mismos procesos de antes). Esto importa porque:
- Si movemos `legacy/` **antes** de activar y ajustar ese vhost, la app legacy queda sin ninguna URL que funcione hasta terminar todo el cambio junto.
- El `DocumentRoot` de ese vhost también hay que actualizarlo de `colegio/` a `colegio/legacy/` como parte de este mismo trabajo.

Esto es el riesgo más alto de todo el informe y define el orden de ejecución (ver Plan de migración).

**R5 — `public/` del legacy no es un front controller, solo assets.** El punto de entrada real es `index.php`, al mismo nivel que `public/`, no dentro de ella (a diferencia de Laravel, donde `public/index.php` sí es el front controller y el `DocumentRoot` real). Mantener el nombre `public/` dentro de `legacy/` — como en tu propio ejemplo — es válido y no rompe nada, pero quiero dejar explícito que no cumplen el mismo rol, para que no se asuma que son intercambiables en el futuro.

**R6 — `composer.phar`** es una herramienta de desarrollo (Composer portátil), no código de ninguna de las 2 apps. Hoy vive suelto en la raíz sin un lugar que indique eso.

**R7 — Sin autoload real en el legacy** (ya señalado en la auditoría anterior): todo se carga con `require_once` encadenados y rutas relativas (`__DIR__ . '/../../config/database.php'`). Esto es una buena noticia para ESTA fase específica: si `app/`, `config/`, `public/`, etc. se mueven **todos juntos, un nivel más abajo, sin cambiar su posición relativa entre sí**, ningún `require_once` se rompe — la profundidad relativa entre `app/models/Estudiante.php` y `config/database.php` es exactamente la misma dentro de `legacy/` que en la raíz.

**R8 — `README.md` desactualizado.** Describe una etapa "solo base de datos" con un archivo `colegio_db.sql` que no existe en el repo actual, y una carpeta `backend/`/`frontend/` futura que nunca se creó con esos nombres. No es un problema de organización de carpetas, pero es lo primero que lee cualquiera que abra el repo hoy, y no refleja la arquitectura dual real.

**R9 — `vendor/`, `composer.json`, `composer.lock` del legacy** no tienen ambigüedad de contenido (declaran solo `phpmailer/phpmailer`) — el único problema es el nombre de la carpeta contenedora.

### Riesgos de reorganizar

1. **Ventana de indisponibilidad del legacy** si el movimiento de carpetas y la actualización del VirtualHost no se hacen como una sola operación coordinada (ver R4).
2. **`BASE_URL`/cookie de sesión con `path` incorrecto** si `config.php`/`.htaccess` no terminan reflejando exactamente el `DocumentRoot` real del vhost activo.
3. **`laravel/config/legacy.php`** (`config('legacy.url')`, usado por los enlaces a los módulos legacy que aún no se migraron — Observaciones, Reportes, Comunicados, EditarLanding, Perfil, Estadísticas) apunta hoy a la URL vieja. Si la URL del legacy cambia, este archivo hay que actualizarlo en el mismo cambio o esos enlaces quedan rotos.
4. **`git mv` de carpetas con muchos archivos binarios** (`public/assets/` tiene imágenes/íconos) — mecánicamente seguro, pero hay que confirmar con `git status` que Git detectó los renames y no un delete+add que pierda el historial.
5. Cualquier atajo o marcador ya guardado apuntando a `localhost/colegio/...` (navegador, notas) deja de funcionar — bajo riesgo hoy porque es desarrollo local, pero confirmalo si alguien más ya está probando el sistema.

---

## FASE 2 — Propuesta

| Carpeta | Se mueve a | Por qué | Beneficio | Problema que resuelve | Rompe compatibilidad |
|---|---|---|---|---|---|
| `app/` | `legacy/app/` | Elimina la ambigüedad con `laravel/app/` | Identificación instantánea del sistema por la ruta, sin abrir la carpeta | R1 | Indirectamente — ver R3/R4 |
| `config/` | `legacy/config/` | Idem + oportunidad de corregir el `.gitignore` (anclarlo a `/legacy/config/database.php` si se quiere seguir ocultando solo las credenciales, no todo el árbol) | Idem + termina R2 de raíz | R1, R2 | Indirectamente |
| `public/` | `legacy/public/` | Idem, con la aclaración de R5 | Idem | R1 | No (es solo assets estáticos, ninguna ruta relativa interna cambia) |
| `vendor/`, `composer.json`, `composer.lock` | `legacy/vendor/`, etc. | Idem. Sin autoload PSR-4 real, no hay nada que regenerar | Idem | R1, R9 | No |
| `index.php`, `.htaccess` | `legacy/index.php`, `legacy/.htaccess` | Idem. Estos SÍ necesitan **editar contenido**, no solo mover (`RewriteBase`, `BASE_URL`) | Punto de entrada del legacy claramente ubicado | R1, R3 | **Sí — cambia la URL pública, ver Plan de migración** |
| `composer.phar` | `scripts/composer.phar` (nuevo) | Es una herramienta de desarrollo, no código de ninguna app — vive naturalmente al mismo nivel que ambas | Deja explícito que es tooling compartido | R6 | No — nada lo referencia por ruta relativa, se invoca a mano |
| — | `docs/` (nuevo) | Centralizar documentación técnica real (los informes de auditoría ya generados) en vez de que solo existan como Artifacts sueltos | Documentación versionada y consultable desde el repo | — | No |
| `laravel/` | **sin cambios** | Pediste explícitamente no tocar la estructura interna de Laravel | — | — | No |
| `README.md` | se **reescribe**, no se mueve | Fuera del alcance estricto de "solo mover carpetas" — lo dejo como recomendación aparte, no como parte obligatoria de este cambio | Refleja la arquitectura dual real | R8 | No |

### Por qué NO propongo crear `shared/`, `docker/`, `api/`, `mobile/` vacías todavía

Es la misma razón que ya establecimos en la auditoría anterior de `app/Services` y `app/Policies` en Laravel: una carpeta vacía "por si acaso" es el mismo antipatrón que señalé en el legacy (`app/services/`, `app/lang/` vacías, nunca usadas). Cada una de las cosas que mencionás que hay que soportar a futuro **ya tiene un lugar natural sin necesidad de crear nada ahora**:

| Futuro | Dónde iría | Por qué no crearlo ya |
|---|---|---|
| API | `laravel/routes/api.php` | Laravel ya lo soporta de fábrica, es un archivo, no una carpeta nueva en la raíz |
| App móvil | `mobile/` hermana de `legacy/`/`laravel/` | Se crea el día que exista código real ahí — la raíz ya está preparada para agregarla sin reorganizar nada más |
| Microservicios | Cada uno, su propia carpeta hermana | Idem |
| Docker | `docker/` con Dockerfiles + `docker-compose.yml` | Se crea cuando se dockerice de verdad, apuntando a `legacy/` y `laravel/` como contexto de build |
| CI/CD | `.github/workflows/` | Carpeta estándar de la plataforma que se use, se crea al configurar el primer workflow |
| Documentación | `docs/` | **Esta sí la creo ya** — hay contenido real (los informes de auditoría) desde el día uno |
| Scripts | `scripts/` | **Esta sí la creo ya** — hay contenido real (`composer.phar`) desde el día uno |
| Pruebas | `laravel/tests/` (ya existe) | El legacy no tiene tests hoy; no invento una carpeta vacía para él |
| Monitoreo / Deploy | Se agregan cuando exista config real (healthchecks, Forge, Envoyer, etc.) | — |

La regla general: la raíz queda con exactamente 2 carpetas de aplicación (`legacy/`, `laravel/`) + 2 de soporte con contenido real desde ya (`docs/`, `scripts/`). Todo lo demás se agrega como hermana de estas cuando exista, sin tener que volver a mover nada de lo que ya está.

---

## FASE 3 — Árbol completo del nuevo repositorio

```
colegio/
├── .claude/                        (sin cambios)
├── .git/
├── .gitignore                      (corregido: /config/ en vez de config/)
├── README.md                       (reescrito — recomendado, no obligatorio)
│
├── docs/                            ← NUEVO, con contenido real desde ya
│   └── auditoria-arquitectura.md    (los informes ya generados, si querés versionarlos)
│
├── scripts/                         ← NUEVO, con contenido real desde ya
│   └── composer.phar
│
├── legacy/                          ← NUEVO — todo el sistema PHP puro, sin ambigüedad
│   ├── app/
│   │   ├── controllers/
│   │   ├── helpers/
│   │   ├── models/
│   │   └── views/
│   ├── config/
│   │   ├── config.php               (editado: $baseFolder actualizado)
│   │   ├── database.php
│   │   ├── mail.php
│   │   └── menu.php
│   ├── public/
│   │   └── assets/
│   ├── vendor/
│   ├── composer.json
│   ├── composer.lock
│   ├── index.php
│   └── .htaccess                    (editado: RewriteBase actualizado)
│
└── laravel/                         ← SIN CAMBIOS internos
    ├── app/
    ├── bootstrap/
    ├── config/                      (ahora sí versionado en git, ver hallazgo urgente)
    ├── database/
    ├── public/
    ├── resources/
    ├── routes/
    ├── storage/
    ├── tests/
    ├── vendor/
    ├── .env
    └── artisan
```

---

## Plan de migración (orden de ejecución, cuando apruebes)

El orden importa — no son pasos independientes, hay una dependencia real con el VirtualHost pendiente de la sesión anterior.

1. **Corregir el `.gitignore`** (`config/` → `/config/`) y versionar `laravel/config/` — independiente de todo lo demás, cero riesgo, lo puedo hacer ya si me confirmás.
2. **Confirmar que estás listo para completar el VirtualHost pendiente** (agregar `colegio.test`/`legacy.colegio.test` al hosts file como Administrador — vos, yo no tengo permisos) — es un prerequisito de este cambio, no algo que se hace después.
3. Con el hosts file listo, `git mv` de `app/`, `config/`, `public/`, `vendor/`, `composer.json`, `composer.lock`, `index.php`, `.htaccess` → `legacy/`, todo en el mismo commit/paso (no parcial, para no dejar el legacy roto a mitad de camino).
4. Mover `composer.phar` → `scripts/composer.phar`.
5. Editar `legacy/.htaccess` (`RewriteBase`) y `legacy/config/config.php` (`$baseFolder`) para que coincidan con el `DocumentRoot` real del vhost `legacy.colegio.test` (que también hay que actualizar, de `colegio/` a `colegio/legacy/`).
6. Reiniciar Apache (vos, desde el Panel de Control de XAMPP).
7. Actualizar `laravel/config/legacy.php` (`config('legacy.url')`) a la nueva URL.
8. Verificar con Playwright: legacy accesible en su nueva URL, Laravel sin cambios, enlaces cruzados hacia los módulos legacy aún no migrados (Observaciones, Reportes, Comunicados, EditarLanding, Perfil, Estadísticas) siguen funcionando.
9. (Opcional, aparte) Reescribir `README.md`.

## Checklist para ejecutar

- [ ] `.gitignore` corregido y `laravel/config/` versionado
- [ ] Hosts file con `colegio.test` y `legacy.colegio.test` (tu parte, admin)
- [ ] `git mv` de las 8 rutas del legacy hacia `legacy/` en un solo paso
- [ ] `composer.phar` movido a `scripts/`
- [ ] `legacy/.htaccess` actualizado
- [ ] `legacy/config/config.php` actualizado
- [ ] `httpd-vhosts.conf`: `DocumentRoot` de `legacy.colegio.test` apuntando a `colegio/legacy/`
- [ ] Apache reiniciado
- [ ] `laravel/config/legacy.php` actualizado
- [ ] Verificación Playwright: legacy + Laravel + enlaces cruzados
- [ ] `git status` final revisado (nada quedó fuera de lugar, renames detectados correctamente)
- [ ] (Opcional) `README.md` reescrito
