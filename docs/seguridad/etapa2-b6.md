# Etapa 2 — B6: configuración independiente por colegio

**Fecha:** 2026-11-19 · **Rama:** `etapa0-contencion` (sin commit) · **Alcance:** código y bases de prueba. **`colegio`: sin cambios.** La landing y las rutas públicas por slug quedan para B7.

## 1. Situación anterior
`ColegioConfiguracion::singleton()` devolvía **la fila 1** (`findOrFail(1)`) para todos:

| Uso | Efecto con varios colegios |
|---|---|
| Composer de vistas (layouts, dashboard, sitio) | Todos los colegios veían nombre, logo y contacto de la fila 1 |
| `RecuperarPasswordMail`, `ComunicadoMail`, `RecordatorioEventoMail` | Los correos de cualquier colegio salían con la identidad de la fila 1 |
| `IcsExportService` | El `PRODID` del calendario exportado llevaba el nombre de la fila 1 |
| Boletines en PDF (`find(1)`) | Cabecera con los datos de la fila 1 |
| `errors/404` | Ídem |
| `ConfiguracionColegioController` | Cualquier rector editaba la fila 1, que cambiaba la de todos |

Además, por falta de pertenencia: **`destroyImagen` y `reordenarImagenes` aceptaban cualquier imagen**, así que un colegio podía borrar o reordenar la galería de otro.

## 2. Diseño
La institución sale siempre del servidor: la sesión (`TenantContext`) o el dueño del recurso (destinatario, evento, usuario del feed).

| Método | Esquema expandido | Antiguo, 1 colegio | Antiguo, ≥2 colegios |
|---|---|---|---|
| `deInstitucion($id)` | Fila con `id_institucion = $id`; si no hay, **derivada** en memoria (nombre y logo de `instituciones`) | La fila existente | **Derivada**: la fila compartida no se atribuye a nadie |
| `actual()` | `deInstitucion(sesión)`; sin institución (SuperAdmin) → **neutra** (`APP_NAME`) | igual | igual |
| `publica()` (sin sesión: login, sitio, 404) | Fila sin dueño → la del único colegio → neutra | La fila existente | La fila existente (como antes; **B7**) |
| Edición (`propiaDe`) | Solo la propia; se crea al guardar por primera vez con `id_institucion` del servidor | La fila existente | **Bloqueada (409)** con aviso en pantalla |

- **Sin asignaciones automáticas:** la fila compartida existente nunca se asigna a un colegio ni se copia. Un colegio sin fila propia ve una configuración derivada de su institución, o empieza la suya desde ella al guardar.
- **Sin filtraciones:** ningún camino devuelve la fila de otro colegio. Sin institución se usa la marca neutra, no la fila 1.
- **Imágenes:** solo las de la configuración propia (otra → 404; reordenar con una ajena → 422). En el esquema expandido llevan `id_institucion`.
- **Archivos separados por colegio:** `colegio/{id}/…` (antes, una carpeta común).
- **Correos en cola:** la institución se fija al construir el correo (`ComunicadoMail` recibe la del remitente), no al enviarlo.
- `id_institucion` no es asignable masivamente; los campos `id_institucion` o `id_configuracion` enviados en el formulario se ignoran.
- `singleton()` se **eliminó**: cualquier uso olvidado falla de forma visible en vez de mostrar datos de otro colegio. Hoy quedan 0 usos directos (verificado por `dependenciasSingleton()`).

## 3. Dependencias corregidas
| Dependencia | Ahora |
|---|---|
| Composer (`layouts.auth/panel/rector/docente`, dashboard, sitio) | `actual()` con sesión; `publica()` sin ella. Se añadió `layouts.docente`, que antes no recibía la variable |
| `errors/404` | `actual()` / `publica()` |
| `RecuperarPasswordMail` | Institución del usuario que recupera |
| `ComunicadoMail` | Institución del remitente (nuevo parámetro; `ComunicadosController` la pasa) |
| `RecordatorioEventoMail` | Institución del evento (`idInstitucionPropia()`) |
| `IcsExportService` | Institución del dueño del feed (sin sesión: token) |
| `BoletinesController` (PDF individual y masivo) | `actual()` |
| `ConfiguracionColegioController` | Reescrito (ver §2) |

## 4. Pruebas
**Nuevas:**
- `Etapa2B6ConfiguracionTest` (6 pruebas, esquema expandido, ambos motores):
  - configuración independiente y edición repetida sobre la misma fila;
  - la fila compartida queda intacta y sin dueño;
  - lectura por colegio sin filtraciones en la pantalla de configuración y en el layout;
  - manipulación de `id_institucion` / `id_configuracion` en el formulario;
  - imágenes ajenas o compartidas: borrar → 404, reordenar → 422; archivos en `colegio/{id}/`;
  - los tres correos y el ICS con el colegio correcto y sin datos de otro (incluida la dirección compartida); sin institución → neutra;
  - SuperAdmin: 403 y configuración neutra.
- `Etapa2B6EsquemaLegadoTest` (2 pruebas, esquema antiguo real, SQLite):
  - **con varios colegios:** aviso, `update` 409, imágenes 409, fila compartida intacta; correos e ICS con la derivada de cada colegio; login público como antes;
  - **con un colegio:** edición como antes, sobre la única fila.

La fila compartida de las pruebas recibe datos de contacto reconocibles: la sembrada por la migración solo trae el nombre, y sin ellos la detección de filtraciones no detectaría nada.

**Ajustada:** la prueba de B3 sobre dependencias del singleton ahora exige **0 usos directos**.

| Suite | Antes de B6 | Después |
|---|---|---|
| SQLite | 254 | **262 / 262** |
| MariaDB `colegio_tests` | 262 + 6 omitidas | **268 + 8 omitidas** (las 2 nuevas de esquema antiguo, solo SQLite) |

**Ensayo HTTP en MariaDB** (`colegio_tests`, kernel real, escenario ficticio):

| Esquema | Casos | Resultado |
|---|---|---|
| **Antiguo** (R2a y M4→M1 revertidas) | Pantalla 200 con aviso y sin datos compartidos; guardar 409; imagen 409; login 200; fila compartida intacta | **7/7** |
| **Expandido** | Lo anterior y además: guardar 200 creando la fila de A pese a `id_institucion` de B en el formulario; B no ve los datos de A; B borra la imagen de A → 404; B reordena → 422; correo de B sin datos de A | **12/12** |

## 5. `colegio`
Checksums idénticos al inicio y al final de B6. Frente a la línea base del 10:52 solo difiere `usuarios.ultimo_acceso` del SuperAdmin (10:57:19, ya presente antes de B6 y documentado en el informe del incidente). 71 migraciones; sin columnas de la Etapa 2. Respaldo previo: `~/Respaldos/sistema-escolar/etapa2-b6-20261119_110552/`.

**Efecto en la base real tal como está hoy** (esquema antiguo, 2 instituciones):
- Los paneles de cada colegio muestran el nombre y el logo de **su** institución, no los de la fila compartida.
- Los datos de contacto de esa fila no aparecen en paneles, correos, ICS ni PDF hasta migrar y decidir (grupo `configuracion` de B4).
- La edición de la configuración queda **bloqueada (409)** con aviso.
- Las páginas públicas (login, sitio, 404) siguen mostrando la fila existente hasta B7.

## 6. Riesgos pendientes
1. **Páginas públicas:** `publica()` es provisional. Con varios colegios y esquema expandido, una vez asignada la fila compartida, el sitio y el login mostrarán la marca neutra hasta B7.
2. **Sin restricción única** en `colegio_configuracion.id_institucion` (prevista para B8). La creación usa una transacción y la lectura toma la fila más antigua, pero dos peticiones simultáneas podrían crear dos filas.
3. **Datos de contacto en la base real:** quedan ocultos para todos hasta la decisión del grupo `configuracion` (B4/B10). Es deliberado: no se adivina el dueño.
4. **Archivos ya subidos** (logo y galería en `colegio/`) no se mueven: siguen accesibles por su ruta pública; solo los nuevos van a `colegio/{id}/`.
5. **Sincronización del logo:** el logo editado se copia al registro `instituciones` del propio colegio (como antes), y existen dos fuentes de verdad. Unificar en B7 o posterior.

## 7. Recomendaciones para B7
- **Rutas públicas `/sitio/{slug}`:** resolver la institución por slug (activa y con el módulo) y usar `ColegioConfiguracion::deInstitucion()` en lugar de `publica()`. Para `/`, solo si `PORTAL_PUBLICO_SLUG` está configurado; si no, 404 o la marca neutra.
- **Login:** decidir la marca de `/login` (neutra o por slug o dominio). Hoy usa `publica()`.
- **Landing:** `landing_contenido`, `landing_galeria` y `landing_noticias` por institución, con `EditarLandingController::updateOrCreate(['clave' => …])` acotado a `(id_institucion, clave)`. Admisiones con la institución resuelta por el servidor.
- **Retirar `publica()`** cuando todas las páginas públicas resuelvan por slug.
- **Pruebas** con dos colegios: sitio, login y admisiones por slug sin cruces.

## 8. Archivos
- **Nuevos:**
  - `tests/Feature/Aislamiento/{Etapa2B6ConfiguracionTest,Etapa2B6EsquemaLegadoTest}.php`
  - este documento
- **Modificados:**
  - `app/Modules/Colegio/Models/ColegioConfiguracion.php`: resolución por institución; `singleton()` eliminado.
  - `app/Modules/Colegio/Controllers/ConfiguracionColegioController.php`: reescrito.
  - `app/Providers/AppServiceProvider.php`: composer.
  - Correos: `RecuperarPasswordMail`, `ComunicadoMail`, `RecordatorioEventoMail`; `ComunicadosController` (pasa la institución); `IcsExportService`; `BoletinesController`.
  - Vistas: `resources/views/errors/404.blade.php` y `resources/views/Rector/configuracion-colegio/index.blade.php` (aviso y botón deshabilitado si está bloqueada).
  - `tests/Feature/Aislamiento/Etapa2SimulacionPoblacionTest.php`: dependencias del singleton = 0.
