# Etapa 2 — B7: sitio público y admisiones por colegio

**Fecha:** 2026-11-19 · **Rama:** `etapa0-contencion` (sin commit) · **Alcance:** código y bases de prueba. **`colegio`: sin cambios.**

## 1. Situación anterior
| Problema | Efecto |
|---|---|
| `SolicitudAdmisionController` guardaba **todas** las solicitudes con `id_institucion = 1` | Las solicitudes de cualquier colegio (datos personales de familias y estudiantes) llegaban al colegio 1 |
| `/` mostraba la única landing (`LandingContenido::pluck`, todas las noticias y fotos) | Un solo sitio para todos los colegios |
| `EditarLandingController` sin pertenencia | Cualquier rector editaba, creaba, desactivaba y reemplazaba imágenes del sitio único |
| `SolicitudAdmision::$fillable` incluía `id_institucion` | Asignable masivamente |
| `grado_interes` libre (lista fija en la vista) | Sin relación con la oferta del colegio |

## 2. Rutas públicas
| Ruta | Comportamiento |
|---|---|
| `GET /sitio/{slug}` | Sitio de la institución. Slug con formato estricto `^[a-z0-9]+(-[a-z0-9]+)*$`, existente y **activa**; si no, 404 |
| `GET /sitio/{slug}/ingresar` | Login con la marca de esa institución (solo invitados) |
| `POST /sitio/{slug}/admision` | Admisión para esa institución (`throttle:admisiones`) |
| `GET /` | Si `PORTAL_PUBLICO_SLUG` está definido y es válido, el sitio de esa institución; si es inválido o inactivo, **404**; si no está definido, **página neutral** (`website.neutral`) sin ningún colegio. Nunca el primero por defecto |
| `POST /solicitudes-admision` (antigua) | Solo con `PORTAL_PUBLICO_SLUG` válido (va a esa institución); si no, **404**. Nunca la institución 1 |
| `GET /login` | Marca **neutral** de la plataforma (`APP_NAME`) |

La resolución vive en `App\Modules\Landing\Support\SitioInstitucional` (`porSlug`, `portal`). La configuración es `config/sitio.php` (`PORTAL_PUBLICO_SLUG`).

## 3. Contenido del sitio
| Esquema | Textos, noticias, galería | Edición |
|---|---|---|
| **Expandido** | Solo filas con `id_institucion` = la del sitio. Lo compartido previo (sin dueño) **no aparece en ningún sitio** | Solo lo propio; textos por `(id_institucion, clave)` y solo claves conocidas; noticias y fotos con `id_institucion` del servidor; ajeno o sin dueño → 404 |
| **Antiguo, 1 institución** | El contenido único, como antes | Como antes |
| **Antiguo, ≥2 instituciones** | El compartido **no se atribuye**: textos provisionales («Bienvenidos a {nombre}»), sin noticias ni galería | **Bloqueada (409)** con aviso |

- **Estado vacío seguro:** se muestran siempre todas las claves (vacías si no hay valor) más textos provisionales que solo usan el nombre de la propia institución.
- **Identidad:** nombre y logo de `ColegioConfiguracion::deInstitucion()` (B6). No hay campos de colores en el esquema; agregarlos requiere propuesta aparte (pendiente).
- **Archivos nuevos:** `landing/{id}/hero|noticias|galeria` (expandido). Los existentes no se mueven.
- **Composer de vistas:** respeta la configuración fijada por el controlador (sitio, login institucional). Sin sesión ni slug, usa la neutral. Se eliminó `ColegioConfiguracion::publica()`, que era provisional desde B6.

## 4. Flujo de admisiones
1. La institución se resuelve **por la ruta** (`porSlug`): 404 si el slug es inválido, no existe o está inactivo. **No se guarda nada.**
2. Las validaciones existentes se conservan (`SolicitudAdmisionRequest`).
3. **Campo trampa** `sitio_web_contacto`, oculto: si viene relleno, responde como siempre y no guarda.
4. **Grado:** debe estar en la oferta de **esa** institución (sus cursos activos: «7A» → «Grado 7°»; sin cursos, el catálogo general). En otro caso, 422.
5. `id_institucion` se fija con `forceFill` desde el servidor. **Se ignora** cualquier `id_institucion` del formulario; ya no está en `$fillable`.
6. La respuesta **no incluye datos personales**.
7. **Límite:** 5 por minuto y 30 por día, por IP y sitio (`RateLimiter 'admisiones'`).

## 5. Pruebas
**Nuevas:**
- `Etapa2B7SitioPublicoTest` (8 pruebas, esquema expandido, SQLite y MariaDB):

  | # | Requisito | Prueba |
  |---|---|---|
  | 1, 3, 4 | Sitios independientes, sin cruces | Textos, noticias y galería de A en su sitio y no en el de B (y viceversa); contenido compartido sembrado en ningún sitio; claves inventadas ignoradas; compartido intacto |
  | — | Estado vacío | Sitio sin contenido → «Bienvenidos a Colegio Zeta», sin nada de A ni compartido |
  | 2 | Slugs | `no-existe`, `Colegio-Alfa`, `colegio_alfa`, `colegio--alfa`, `-colegio-alfa` → 404 (sitio y admisión); institución suspendida → 404; slug duplicado → error de restricción única |
  | — | Raíz y login | `/` sin portal: neutral sin colegios; con portal válido, ese sitio; con portal inválido, 404. `/login` neutral; `/sitio/{slug}/ingresar` con su marca |
  | 5 | Edición | B no edita ni desactiva noticias o fotos de A (404); lo compartido no es editable (404); editor de B sin datos de A; archivos en `landing/{id}/`; SuperAdmin 403 |
  | 6–9 | Admisiones | Guardada en Zeta pese a `id_institucion` de A; respuesta sin datos personales; grado solo de A rechazado en Zeta (422) y aceptado en A; campo trampa; ruta antigua sin portal → 404; datos personales ausentes del sitio y del panel de Matrículas del otro colegio |
  | — | Abuso | La 6.ª solicitud por minuto → 429, no guardada |

- `Etapa2B7EsquemaLegadoTest` (2 pruebas, esquema antiguo real, SQLite):
  - **Con 2 colegios:** sitios provisionales sin contenido compartido; editor con aviso; escrituras 409; contenido intacto; admisión guardada en la institución de la ruta.
  - **Con 1 colegio:** sitio con el contenido de siempre; editor funcional sin filas nuevas.

**Ajustada:** la prueba de B6 sobre el login sin sesión ahora exige la marca neutral.

| Suite | Antes de B7 | Después |
|---|---|---|
| SQLite | 262 | **272 / 272** |
| MariaDB `colegio_tests` | 268 + 8 omitidas | **276 + 10 omitidas** (las 2 nuevas de esquema antiguo, solo SQLite) |

**Ensayo HTTP en MariaDB** (`colegio_tests`, kernel real, escenario ficticio):

| Esquema | Resultado |
|---|---|
| Antiguo (R2a y M4→M1 revertidas) | **14/14**: edición 409; sitio provisional sin contenido compartido ni de otro colegio; slugs inválidos 404; raíz neutral; admisión en la institución de la ruta; grado ajeno 422; ruta antigua 404; nada guardado en rechazos |
| Expandido | **15/15**: lo mismo con edición 200 y contenido propio; contenido compartido intacto |

## 6. `colegio` y compatibilidad con la base real
- **Checksums** iguales al inicio y al final de B7 (52/52). 71 migraciones; sin columnas de la Etapa 2. No se modificaron datos, archivos ni credenciales. Respaldo: `~/Respaldos/sistema-escolar/etapa2-b7-20261119_111712/`.
- **Efecto inmediato** en la base real con el código actual (esquema antiguo, 2 instituciones; visible en el servidor local `:8000`):
  - `/` muestra la **página neutral**, porque `PORTAL_PUBLICO_SLUG` no está definido en `.env`. Antes mostraba la landing de San Cristóbal a cualquier visitante.
  - `/sitio/colegio-san-cristobal` muestra textos **provisionales**: el contenido compartido no se atribuye sin decisión. La edición de la landing está **bloqueada (409)**.
  - **Hallazgo:** la institución 2 tiene el slug **`Privada`**, con mayúscula, que no cumple el formato. `/sitio/Privada` responde 404. Normalizarlo (por ejemplo, `privada`) es un cambio de datos en `colegio` y **requiere autorización**.
  - Las admisiones solo se reciben por `/sitio/{slug}/admision` (o por `/` con portal). El formulario antiguo sin portal responde 404, en vez de enviar todo al colegio 1.

## 7. Riesgos pendientes
1. **Visibilidad en la base real:** hasta migrar (B10) y decidir los grupos `landing`, `galeria` y `noticias` (archivo de correspondencia de B4), ningún colegio muestra su landing real. Opción operativa sin tocar datos: definir `PORTAL_PUBLICO_SLUG`. Aun así, con 2 instituciones en esquema antiguo, el contenido sigue siendo provisional (no se atribuye).
2. **Slug `Privada`** inválido (ver §6).
3. **Sin control por módulo o plan:** se exige «institución activa», pero la aplicación no modela qué plan incluye sitio o admisiones (dependencia de B9).
4. **Límite por IP:** detrás de un proxy, todas las peticiones compartirían IP. Configurar `TrustProxies` en el despliegue.
5. **Unicidad de `(id_institucion, clave)`:** depende de R2a (solo en pruebas). En la base real llegará con B8.
6. **Archivos antiguos** en `landing/…` siguen accesibles por URL pública aunque ningún sitio los muestre (también antes).
7. **Colores e identidad visual:** no hay campos; requieren propuesta de esquema.
8. **Página 404:** sin sesión usa la marca neutral (antes, la fila compartida).

## 8. Dependencias para B8 y B9
- **B8 (restricciones):**
  - R2a en la base real: `landing_contenido(id_institucion, clave)` y demás únicos compuestos.
  - `UNIQUE colegio_configuracion(id_institucion)`.
  - `NOT NULL` en las tablas pobladas.
  - `cursos/solicitudes_admision.id_institucion`: de `SET NULL` a `RESTRICT`.
  - Normalizar o validar el formato de `instituciones.slug` (minúsculas) con una restricción, tras corregir `Privada` con autorización.
- **B9 (alta de colegio):**
  - Al crear una institución: slug válido y único según `SitioInstitucional::FORMATO_SLUG`, configuración inicial propia (B6), landing vacía o desde plantilla (no copiar la de otro colegio) y plantillas globales de categorías y escala.
  - Definir si el sitio y las admisiones dependen de un módulo del plan.
- **B10:** decidir los grupos `configuracion`, `imagenes`, `landing`, `galeria` y `noticias` del archivo de correspondencia. Definir `PORTAL_PUBLICO_SLUG` (o dominios) en el despliegue.

## 9. Archivos
- **Nuevos:**
  - `app/Modules/Landing/Support/SitioInstitucional.php`
  - `config/sitio.php`
  - `resources/views/website/neutral.blade.php`
  - `tests/Feature/Aislamiento/{Etapa2B7SitioPublicoTest,Etapa2B7EsquemaLegadoTest}.php`
  - este documento
- **Modificados:**
  - `app/Modules/Landing/Controllers/{WebsiteController,SolicitudAdmisionController,EditarLandingController}.php` (reescritos)
  - `app/Modules/Matriculas/Models/SolicitudAdmision.php` (`id_institucion` fuera de `$fillable`)
  - `app/Modules/Colegio/Models/ColegioConfiguracion.php` (sin `publica()`)
  - `app/Providers/AppServiceProvider.php` (composer neutral y que respeta al controlador; limitador `admisiones`)
  - `routes/web.php` (rutas `/sitio/{slug}…`; ruta antigua de admisión)
  - `resources/views/website/index.blade.php` (URL de admisión e ingreso por sitio, grados de la institución, campo trampa)
  - `resources/views/Rector/landing/index.blade.php` (aviso de bloqueo, enlace al sitio propio)
  - `resources/views/errors/404.blade.php` (neutral sin sesión)
  - `tests/Feature/Aislamiento/Etapa2B6EsquemaLegadoTest.php` (login neutral)
