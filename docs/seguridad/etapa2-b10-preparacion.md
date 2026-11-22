# Etapa 2 — B10: preparación del archivo legado (los 45 registros heredados)

**Fecha:** 2026-11-19/20 · **Rama:** `etapa0-contencion` (sin commit) · **Alcance:** diseño, código y ensayo en copia aislada. **`colegio` no se modificó**: sin migraciones, sin escrituras, sin cambios de propietario ni de archivos.

> **Contexto:** B8, B9 y una «B10 Fase 1» los implementó **otro agente (GitHub Copilot, sesión `2ac0c5b0`)**, que recibió las mismas autorizaciones dirigidas a «Claude» (ver `etapa2-b8.md`, `etapa2-b9.md` y `etapa2-b10-fase1.md`). Este documento parte de esa base:
> - la **audita** (§1);
> - **completa** lo que faltaba de esta solicitud: manifiesto de archivos, ensayo con datos reales copiados, correspondencia y pasos;
> - **no rehace** su trabajo.

## 1. Auditoría del trabajo existente (B8–B10 Fase 1)
| Hallazgo | Valoración |
|---|---|
| Migraciones de B8 y B10 en subcarpetas (`database/migrations/etapa2/b8`, `…/b10`), protegidas por `GuardiaBaseDePruebas` | **Correcto y deliberado:** `php artisan migrate` no las aplica, así que no pueden llegar a `colegio` por accidente. En la base real exigirán una decisión explícita (`--path`) y otra guardia |
| `ArchivoLegado` + tabla `migracion_legado_archivos` (fila completa en JSON, SHA-256, verificación antes de borrar; la restauración exige checksum, el mismo id, que la fila no exista y que B8 esté revertido) | **Correcto.** Faltaba la verificación de los archivos referenciados: se añadió (§2) |
| Correspondencia v4 con `archivar_sin_propietario` y validación padre–hijo (no se archiva una configuración sin sus imágenes) | **Correcto** |
| Propuesta `b4-colegio-correspondencia-v4-propuesta.json`: categorías → plantilla, configuración y landing → archivar; sin aprobación y con base ficticia | **Correcto:** coincide con la dirección funcional y no es ejecutable |
| `Etapa2ArchivoLegadoTest::setUp` ejecuta `migrate:fresh` **sin** `RefreshDatabase` | **Observación:** la guardia de `TestCase::setUpTraits` no la cubre; sí la cubre `AppServiceProvider`, que prohíbe `migrate:fresh` fuera de la base de pruebas. Seguro, pero conviene migrarla a `RefreshDatabase` |
| El `down()` de B8 **conserva** el índice y la FK de `usuarios.id_institucion` | **Correcto:** es una reparación (la FK nunca se creó en la base real); queda como única diferencia de esquema tras revertir B8 |
| Se **eliminó** `resources/views/SuperAdmin/instituciones/partials/_campos-rector.blade.php` (versionado) | **Revisado el 20-nov:** sustituido por `_campos-administrador.blade.php` (B9); no quedan referencias. Mejora de seguridad: ya no se usa el documento como contraseña inicial. Cambio funcional **deliberado y documentado en B9**: la edición de una institución ya no lee ni modifica la cuenta del responsable (antes, 6 campos `rector_*`) |
| Documentos de etapas anteriores (`etapa0`, `etapa1`, `b0-b1`, `b2`, `b3`, `b4`, `b5`, `b6`, incidente) con fecha de modificación posterior | **Revisado el 20-nov: contenido idéntico** al de antes del otro agente (solo cambió la fecha del archivo). Corrige lo informado el 19-nov |
| Una prueba de B3 fijaba el formato v3; tras el cambio a v4 fallaba | **Corregida:** ahora usa `Correspondencia::FORMATO` |

## 2. Diseño del archivo legado
**Qué se archiva:** los 38 registros demostrativos sin propietario:

| Tabla | Registros |
|---|---|
| `colegio_configuracion` | 1 |
| `colegio_imagenes` | 4 |
| `landing_contenido` | 24 |
| `landing_galeria` | 6 |
| `landing_noticias` | 3 |

**Tabla `migracion_legado_archivos`** (fuera de las tablas operativas, sin FK, para que el historial sobreviva):

| Columna | Contenido |
|---|---|
| `lote` | Ejecución (UUID) |
| `tabla_origen`, `id_origen` | Origen exacto; **el id original se conserva** |
| `payload` | Fila **completa** en JSON: todos los valores, incluidos el id, `id_configuracion` (relación imagen→configuración) y las rutas de archivos |
| `checksum_sha256` | SHA-256 del payload, verificado tras escribir y antes de borrar |
| `archivos` (**nuevo**) | Manifiesto de archivos públicos referenciados: `ruta`, `existe`, `bytes`, `sha256`. Solo lectura, nunca se mueven ni borran; fuera del payload para no alterar su checksum |
| `archivado_at`, `restaurado_at` | Trazabilidad |

**Operación** (`institucion:poblar --aplicar`, una sola transacción con cerrojo y bloqueo de filas, como en B4):
1. Archiva primero las imágenes y después la configuración (las FK `CASCADE` nunca llegan a actuar).
2. Escribe el payload, comprueba su checksum, guarda el manifiesto y borra la fila operativa.
3. Registra cada operación en `migracion_pertenencia` (`archivar_sin_propietario`).

**Reversión** (`--revertir=<lote>`): primero hay que revertir B8. Luego se reinserta cada fila desde su payload con su id original, comprobando el checksum, en orden inverso (la configuración antes que sus imágenes). El historial se conserva (`restaurado_at`).

**Verificación de archivos:** `ArchivoLegado::verificarArchivos($lote)` compara el manifiesto con el disco actual y devuelve las diferencias (vacío = intacto).

## 3. Pruebas de conservación y restauración

### Automatizadas
`Etapa2ArchivoLegadoTest` (4 pruebas, SQLite y MariaDB):
- payload, ids y checksums con restauración exacta;
- la configuración exige archivar todas sus imágenes;
- B8 aplicable, landing vacía y recuperación antes de B8;
- **nueva:** manifiesto con archivo existente y referencia rota; verificación limpia y detección de una alteración; archivar y restaurar **no modifican el disco**.

| Suite | Resultado |
|---|---|
| SQLite | **293 / 293** |
| MariaDB `colegio_tests` (completa) | **54 fallos**, todos por contaminación: `Etapa2ArchivoLegadoTest` deja datos confirmados (§8) |
| MariaDB sin `Etapa2ArchivoLegadoTest` | **293 superadas + 10 omitidas, 0 fallos** |
| `Etapa2ArchivoLegadoTest` sola, en MariaDB | **4 / 4** (pero deja 3 instituciones y 13 usuarios ficticios confirmados) |

### Ensayo con los datos reales, en copia aislada (MariaDB, `colegio_tests`)
`colegio` se volcó **en solo lectura** (`mysqldump --single-transaction --skip-lock-tables`) y se cargó en `colegio_tests`. En la copia se **anularon las credenciales**: contraseñas, `remember_token`, secretos 2FA, `ics_token`, tokens de recuperación y sesiones. Resultado: 55 tablas, 71 migraciones, 2 instituciones, 38 registros demostrativos y 7 categorías genéricas.

| Paso | Resultado |
|---|---|
| M1–M4, R2a y la migración de B10 (por `--path`) en la copia | Aplicadas; 0 cambios de datos (solo la columna nueva en `NULL`) |
| Simulación | 45 decisiones; huella **`650368a9…`**, **idéntica a la de `colegio` real** en B4: la copia es fiel |
| Correspondencia v4 con la dirección funcional + aprobación de ámbito «pruebas» | Válida: 38 `archivar_sin_propietario`, 13 `plantilla` (7 categorías genéricas, 2 de sistema, 4 de escala) |
| `--aplicar` | **38 archivados**, 38/38 checksums correctos, 38/38 con manifiesto; **0 filas** demostrativas en las tablas operativas; 9 categorías siguen como plantilla `NULL` |
| Archivos reales referenciados (lectura) | 5, los 5 existen; `verificarArchivos` → **0 diferencias**; disco público (6 archivos) idéntico |
| **B8** en la copia (por `--path`) | Aplicado: `id_institucion` **NOT NULL en 14 tablas**; solo `escala_notas` y `evento_categorias` admiten `NULL` (plantillas) |
| Sitios públicos | `/sitio/colegio-san-cristobal` y `/sitio/Privada`: HTTP 200, landing **vacía con texto provisional**, sin contenido demostrativo ni de otro colegio |
| Plantilla compartida | Un rector intenta editar «Comunicados» → **403**, sin cambios |
| Reversión de B8 + `--revertir` | Datos de las 52 tablas no volátiles **idénticos** a los anteriores al archivado (ids y relaciones incluidos); única diferencia de esquema: la FK e índice de `usuarios` que conserva B8 (§1); 38/38 `restaurado_at`; disco idéntico |
| Simulación tras restaurar | Huella **`650368a9…`**, igual que antes |

## 4. Compatibilidad con B8
- **R1** (`NOT NULL` en 14 tablas) **se niega** mientras queden filas sin institución. Archivar los 38 registros demostrativos es lo que lo permite **sin relajar ninguna restricción** y sin asignar propietarios.
- Las **plantillas** (`evento_categorias`, `escala_notas`) siguen admitiendo `NULL` por diseño. Siguen siendo de **solo lectura** para todos los colegios (B5: 403 al editar o desactivar, comprobado también en la copia).
- **R2** (únicos compuestos, `UNIQUE colegio_configuracion(id_institucion)`) y **R3** (`RESTRICT`) no se ven afectados: tras el archivado esas tablas quedan vacías.
- **Orden obligatorio:** archivar → B8. Para revertir: B8 `down()` → `--revertir`. `ArchivoLegado::restaurar` se niega si la columna ya es `NOT NULL`.

## 5. Correspondencia propuesta (45 registros)
| Grupo | Registros | Decisión propuesta | Efecto |
|---|---|---|---|
| `categorias_calendario` | 7: Comunicados, Personal, Reuniones, Eventos Institucionales, Evaluaciones, Recordatorios, Festivos | `plantilla` | Globales de solo lectura; ningún colegio las modifica |
| `configuracion` | 1 (configuración «Colegio San Cristóbal») | `archivar_sin_propietario` | Fuera de las tablas operativas; conservada en el archivo |
| `imagenes` | 4 (fachada, patio, aulas, deportes) | `archivar_sin_propietario` | Archivadas con su configuración; archivos intactos |
| `landing` | 24 claves | `archivar_sin_propietario` | Landings nuevas vacías |
| `galeria` | 6 fotos | `archivar_sin_propietario` | Ídem |
| `noticias` | 3 noticias | `archivar_sin_propietario` | Ídem |

Archivo: `laravel/storage/app/auditoria/b4-colegio-correspondencia-v4-propuesta.json` (Copilot), **no ejecutable**: sin aprobación y con base ficticia. Para usarlo hay que regenerarlo contra la base de destino y aprobarlo (§7).

## 6. Landings nuevas y constructor de landings
- **Comprobado:** tras archivar, cada colegio tiene una landing vacía con texto provisional seguro (B7 `SitioInstitucional::provisional`) y nada de otro colegio.
- **Dependencias para un futuro constructor de landings con diseños independientes:**
  1. **Esquema:** tabla de plantillas visuales (`landing_plantillas`: id, nombre, versión, esquema de secciones) y selección por colegio (`landing_sitio`: `id_institucion` UNIQUE, plantilla, versión, colores y tipografías con valores validados, estado borrador/publicado). Hoy no existen campos de colores: requiere propuesta aprobada.
  2. **Contenido estructurado:** secciones por colegio (`landing_secciones`: `id_institucion`, tipo, orden, datos JSON validados por un esquema de la plantilla) en lugar de las 24 claves fijas; con migración desde `landing_contenido`.
  3. **Borrador y publicación:** versiones con fecha, publicación atómica y vuelta atrás.
  4. **Archivos:** prefijo `landing/{id}/…` (B7) más un controlador que compruebe la pertenencia al servir y al borrar; límites de tamaño y tipo; los archivos antiguos siguen en rutas compartidas (riesgo §7.4).
  5. **Seguridad del contenido:** sin HTML libre (o saneado con lista blanca); validar URLs de redes y enlaces; cabeceras CSP por sitio.
  6. **Aislamiento:** toda lectura por `SitioInstitucional` (slug → institución); el editor solo con la institución de la sesión; pruebas de cruce entre colegios para cada tipo de sección.
  7. **Plantillas globales de solo lectura:** un colegio copia la plantilla a su sitio, nunca la edita.

## 7. Riesgos pendientes
1. **Dos agentes en el mismo proyecto:** Copilot trabajó en paralelo, implementó B8, B9 y B10 Fase 1 y sustituyó un archivo de vistas (revisado: coherente). Antes de B10 Fase 2, **un solo agente** y revisión humana del diff completo.
2. **Copia de datos reales en `colegio_tests`:** el ensayo cargó la copia, con credenciales anuladas. **Verificado el 20-nov:** ya no está; la base fue recreada (§8).
3. **Usuarios reales en la copia:** se anularon las credenciales, pero los nombres y correos de 2 usuarios estuvieron en `colegio_tests` durante el ensayo.
4. **Archivos públicos antiguos** (`colegio/…`, `landing/…`) siguen accesibles por URL aunque no se muestren; el archivo no los mueve.
5. **El slug `Privada`** (mayúscula) se acepta tal cual (cambio de Copilot); normalizarlo requiere autorización.
6. **Sin log binario ni general en MariaDB:** la aplicación real no tendría registro del motor; el registro está en `migracion_pertenencia` y `migracion_legado_archivos`.
7. **XAMPP tras cada reinicio:** el MySQL del sistema ocupa el 3306 y el MariaDB del proyecto queda detenido.

## 8. Verificaciones del 20-nov (solo lectura sobre `colegio`)
Servidor confirmado: MariaDB 10.4.32 de XAMPP (`datadir /opt/lampp/var/mysql/`, MySQL del sistema inactivo); la base se comprobó con `SELECT DATABASE()` antes de cada operación. Evidencias en `~/Respaldos/sistema-escolar/b10-verificacion-20261120_090511/`: metadatos, conteos y checksums de `colegio_tests`, salidas de las suites y checksums de `colegio` (`SHA256SUMS`).

- **`colegio_tests` no contenía datos del ensayo:** 1 institución (la sembrada por la migración), 0 usuarios, 76 migraciones, sin `migracion_legado_archivos`. La suite MariaDB interrumpida el 19-nov ya la había recreado (`migrate:fresh`). **No hizo falta ninguna limpieza destructiva.**
- **Suites:** SQLite 293/293. MariaDB con 54 fallos, explicados por la contaminación de `Etapa2ArchivoLegadoTest`; sin esa clase, 293 + 10 omitidas y 0 fallos; la clase sola, 4/4.
- **`colegio`:** 51/52 tablas iguales a la línea base del 19-nov 10:52. `usuarios` difiere solo en `sesion_valida_desde` (incidente, 10:04:55) y `ultimo_acceso` (14:12:06 del 19-nov). El checksum es igual al medido el 19-nov a las 17:34: **sin cambios desde entonces**. 71 migraciones; sin tablas de B10; `audit_logs` = 3.

### Bloqueos restantes
1. **`Etapa2ArchivoLegadoTest` contamina la suite MariaDB:** usa `migrate:fresh` sin `RefreshDatabase` y deja datos confirmados. Debe pasar a `RefreshDatabase` (o limpiar al terminar) antes de considerar fiable la suite MariaDB completa. **No corregido:** solo se autorizaron verificaciones.
2. **Dos cambios de código para la migración real**, no implementados por instrucción: una guardia propia para la migración de B10 en una base real, y un ámbito de aprobación «produccion» con su guardia para `--aplicar`.
3. **Decisión formal** de los 45 registros (correspondencia regenerada contra `colegio` y aprobada por escrito).
4. **Un solo agente** durante la Fase 2; el servidor `:8000` detenido.
5. **El slug `Privada`** y la normalización de slugs, si se desea, con autorización.

## 9. Pasos exactos para autorizar una migración real (B10 Fase 2)
Cada paso requiere autorización escrita. Solo un agente y con el servidor de desarrollo `:8000` detenido.
1. **Congelar:** detener `php artisan serve`, Copilot y cualquier otro agente; anunciar una ventana de mantenimiento.
2. **Respaldar `colegio`:** `mysqldump --single-transaction` + sha256 + restauración de prueba en una base nueva con comparación de checksums (52 tablas) y de esquema. Respaldar también `storage/app/public` (tar + sha256).
3. **Línea base:** registrar los checksums de `colegio` en `~/Respaldos` justo antes de empezar.
4. **Expansión** (con autorización): `ETAPA2_MIGRACIONES_AUTORIZADAS=colegio php artisan migrate --force` (M1–M4) y `ETAPA2_R2_AUTORIZADA=colegio …` (R2a). La migración de B10 está protegida por `GuardiaBaseDePruebas`: para la base real necesita una guardia equivalente con variable propia (**cambio de código pendiente de autorizar**).
5. **Simular** en `colegio` (solo lectura): la huella debe ser la misma que la del ensayo (`650368a9…`, si los datos no cambiaron).
6. **Regenerar la correspondencia** v4 contra `colegio`, completar las decisiones de §5 y **aprobarla por escrito**. Hoy `--aplicar` solo funciona en `colegio_tests`: habilitarlo para `colegio` exige un nuevo ámbito de aprobación («produccion») y una guardia con variable propia (**cambio de código pendiente de autorizar**).
7. **Aplicar** en una transacción. Verificar: 38 archivados con checksum, manifiesto sin diferencias, verificador y auditoría sin críticos.
8. **B8** (por `--path`, con su autorización y guardia propia).
9. **Verificar:** sitios vacíos, plantillas en solo lectura, suites y comprobaciones HTTP de humo.
10. **Plan de vuelta atrás ensayado:** B8 `down()` → `--revertir=<lote>` → comparar con la línea base; si algo falla, restaurar el respaldo del paso 2.

## 10. Archivos de esta preparación
- **Modificados:**
  - `app/Modules/Instituciones/Poblacion/ArchivoLegado.php`: manifiesto y `verificarArchivos`.
  - `database/migrations/etapa2/b10/2026_11_20_100000_etapa2_b10_archivo_legado.php`: columna `archivos`, también reanudable si la tabla ya existía.
  - `tests/Feature/Aislamiento/Etapa2ArchivoLegadoTest.php`: prueba del manifiesto.
  - `tests/Feature/Aislamiento/Etapa2SimulacionPoblacionTest.php`: formato v4.
- **Nuevo:** este documento.
- **Informes del ensayo** (`storage/app/auditoria/`, ignorado por git): `b10-ensayo-copia-real.json` y `b10-ensayo-copia-real-decidido.json`, ambos de la base de pruebas con decisiones de ensayo.
- **Respaldos:**
  - `~/Respaldos/sistema-escolar/b10-auditoria-20261119_173936/`: árbol completo antes de mis cambios, estado de git y lista de archivos borrados por el otro agente.
