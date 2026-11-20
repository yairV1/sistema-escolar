# Etapa 2 — B8: integridad y restricciones multiinstitución

**Fecha:** 2026-11-19 · **Rama:** `etapa0-contencion` · **Estado:** implementado y probado solo en SQLite en memoria y `colegio_tests`. Sin commit, push ni merge. No se ejecutó B9 ni B10.

## 1. Verificaciones previas y respaldo

- Rama confirmada: `etapa0-contencion`.
- El árbol ya tenía cambios sin commit de B0–B7. Antes de editar se guardaron:
  - Diff de cambios rastreados: `files/b8-prechange-backup/tracked-changes.patch`, 227.338 bytes, SHA-256 `008be47e26deae961d5f8f86663bf6db1e21f2e7fd374321c3fb3886c835f203`.
  - Archivos nuevos preexistentes: `files/b8-prechange-backup/untracked-files.tar.gz`, 83 entradas, SHA-256 `f6ffada14c17c74c7a8ff5b0f264194dd19bba0d4c2cd4c3283215a5ead4d7d`.
  - Base aislada `colegio_tests`: `files/b8-prechange-backup/colegio_tests.sql.gz`, 56 tablas, `gzip -t` correcto, SHA-256 `95a0a89dd5e008319f1ff07808510b62afc1ce91ca9eb9d820d44c737f4ecba2`. Después de las pruebas MariaDB se restauró este respaldo; 56 tablas restauradas y `mysqlcheck` sin errores.
- El informe B7 registra que `colegio` tenía 71 migraciones y no tenía las columnas M1–M4. B8 no se conectó a `colegio` para repetir la consulta: la comprobación aquí se limita a ese registro previo para respetar el uso exclusivo de bases aisladas.
- Ninguna migración se ejecutó contra `colegio`. No se cambió, borró ni movió ningún archivo público existente. El archivo temporal empleado en la prueba HTTP se eliminó al finalizar.

## 2. Restricciones R1–R3

La migración está en `laravel/database/migrations/etapa2/b8/2026_11_19_120000_etapa2_b8_restricciones_institucionales.php`. Está fuera del directorio de migraciones automáticas y se ejecuta explícitamente con `--path=database/migrations/etapa2/b8`; esto evita cambiar el esquema legado durante las suites anteriores. La migración solo contiene DDL. Primero valida todas las tablas, pertenencias, referencias huérfanas, relaciones cruzadas y duplicados; cualquier hallazgo bloqueante aborta antes del primer DDL.

### R1 — pertenencia obligatoria y borrado seguro

`id_institucion` pasa a `NOT NULL` en:

`cursos`, `solicitudes_admision`, `materias`, `areas`, `periodos_academicos`, `tipos_actividad`, `estudiantes`, `profesores`, `acudientes`, `eventos`, `soportes`, `colegio_configuracion`, `colegio_imagenes`, `landing_contenido`, `landing_galeria` y `landing_noticias`.

La migración rechaza filas sin institución en cualquiera de esas tablas; no asigna una institución por defecto. `evento_categorias` y `escala_notas` conservan `NULL` para plantillas globales. Las FK institucionales existentes se conservan; las de `cursos` y `solicitudes_admision` dejan de permitir que un borrado desasigne filas.

SQL equivalente:

```sql
ALTER TABLE <tabla_institucional> MODIFY id_institucion BIGINT UNSIGNED NOT NULL;
ALTER TABLE <tabla_institucional>
  ADD CONSTRAINT <tabla>_id_institucion_foreign
  FOREIGN KEY (id_institucion) REFERENCES instituciones (id_institucion)
  ON DELETE RESTRICT;
```

### R2 — unicidad por colegio, identidad global

La migración añade:

```sql
CREATE UNIQUE INDEX cursos_inst_nombre_anio_jornada_uq
  ON cursos (id_institucion, nombre_curso, anio_lectivo, jornada);
CREATE UNIQUE INDEX estudiantes_inst_codigo_uq
  ON estudiantes (id_institucion, codigo_estudiante);
CREATE UNIQUE INDEX profesores_inst_codigo_uq
  ON profesores (id_institucion, codigo_profesor);
CREATE UNIQUE INDEX colegio_configuracion_inst_uq
  ON colegio_configuracion (id_institucion);
```

Los índices globales anteriores de cursos, códigos de estudiante y códigos de profesor se eliminan únicamente después de verificar duplicados y de crear el índice compuesto correspondiente. Ya estaban compuestos por B4/R2a y no se cambian aquí: `(id_institucion, nombre_materia)`, `(id_institucion, nombre_area)`, `(id_institucion, nombre_tipo)`, `(id_institucion, nombre_periodo, anio_lectivo)`, `(id_institucion, slug)` y `(id_institucion, clave)`.

La unicidad de `usuarios.correo` y `usuarios.numero_documento` sigue siendo global, al igual que `id_usuario` en los perfiles. El índice de configuración garantiza **como máximo una** fila por institución; no exige que cada institución ya tenga una fila.

### R3 — FK de cursos y admisiones

```sql
ALTER TABLE cursos
  DROP FOREIGN KEY cursos_id_institucion_foreign,
  ADD CONSTRAINT cursos_id_institucion_foreign
  FOREIGN KEY (id_institucion) REFERENCES instituciones (id_institucion)
  ON DELETE RESTRICT;
ALTER TABLE solicitudes_admision
  DROP FOREIGN KEY solicitudes_admision_id_institucion_foreign,
  ADD CONSTRAINT solicitudes_admision_id_institucion_foreign
  FOREIGN KEY (id_institucion) REFERENCES instituciones (id_institucion)
  ON DELETE RESTRICT;
```

La migración reanuda operaciones verificando columnas, índices y FK existentes. En SQLite protege la relación `cursos.id_director_grupo` durante el rebuild de `profesores` para no convertir directores en `NULL`. MariaDB no envuelve DDL en una transacción; si `down()` detecta colisiones con los únicos globales originales, se detiene antes de alterar el esquema. La recuperación prevista ante una reversión bloqueada es conservar las restricciones y restaurar el respaldo, no borrar o fusionar filas.

La verificación de relaciones padre-hijo usa `AuditoriaPertenencia`/`VerificadorPertenencia` y rechaza incoherencias preexistentes antes del DDL. Los hijos sin columna institucional siguen el diseño de B1–B7: la aplicación valida sus relaciones y la auditoría detecta cruces. B8 no añade columnas redundantes ni triggers para que el motor valide cada combinación multi-padre; SQL manual que ignore la aplicación puede volver a introducir relaciones cruzadas (riesgo pendiente).

## 3. Slugs institucionales

- Política canónica: 1–120 caracteres ASCII en minúsculas, dígitos y guiones simples (`^[a-z0-9]+(?:-[a-z0-9]+)*$`). La normalización de referencia es `Str::slug($valor, '-')`; `Privada` normaliza a `privada`.
- Las solicitudes de creación/edición rechazan slugs nuevos no canónicos y detectan colisiones comparando la forma normalizada con los slugs existentes. Un slug legado puede conservarse sin cambios al editar el resto del registro.
- No se normalizan slugs ni se añaden alias/redirects. B7 registró la institución `Privada` y que `/sitio/Privada` respondía 404; cambiarla a `privada` afecta datos y enlaces y no se autorizó. Antes de cualquier normalización real debe detectarse la colisión `LOWER(slug)` y aprobarse el destino.
- El comportamiento público estricto de B7 se conserva: slug inválido o no canónico → 404.

Archivos de código: `laravel/app/Modules/Instituciones/Support/SlugInstitucion.php`, `laravel/app/Modules/Instituciones/Requests/InstitucionStoreRequest.php`, `laravel/app/Modules/Instituciones/Requests/InstitucionUpdateRequest.php` y `laravel/app/Modules/Landing/Support/SitioInstitucional.php`.

## 4. Archivos públicos

`laravel/public/storage` es un enlace a `laravel/storage/app/public`; el disco `public` genera URLs `/storage/...`. Se confirmó mediante un servidor HTTP local ligado solo a `127.0.0.1`: un archivo ficticio nuevo en ese disco respondió **200 sin autenticación** por URL directa. El archivo fue retirado tras la prueba. En el árbol local había cinco SVG genéricos bajo `colegio/`: `logo.svg` y `galeria/{aulas,deportes,fachada,patio}.svg`; no se inspeccionó ni modificó su contenido. B7 ya había advertido que archivos antiguos de `landing/` podían seguir disponibles por URL directa aunque la vista o la fila ya no se mostrara.

Clasificación por uso actual del disco:

- Público legítimo si el contenido fue aprobado como tal: logos institucionales y medios de `landing/{id}/hero`, `landing/{id}/noticias` y `landing/{id}/galeria`.
- Requiere decisión y, normalmente, autorización: fotos de perfil (`perfiles/`), capturas de soporte (`soporte/`) y adjuntos de eventos. Los controladores autorizan la operación/registro, pero el atributo de URL apunta al disco público; conocer la ruta evita esas comprobaciones.
- El prefijo por institución no es una barrera de autorización. Tampoco lo es que la fila de base de datos se filtre o deje de mostrarse.

Estrategia pendiente, sin mover archivos en B8: almacenar nuevos adjuntos privados fuera de `public/`; servirlos mediante controlador autenticado que compruebe usuario, institución y permisos del registro; dejar en el disco público solo material de landing deliberadamente público. Para archivos antiguos, inventariar rutas y huérfanos, copiar a almacenamiento privado con verificación de checksums y cambiar referencias en una operación aprobada; conservar el original hasta validar la transición y tener autorización explícita para retirarlo.

## 5. Pruebas y compatibilidad

Pruebas B8 nuevas (dos instituciones ficticias):

- Rechazo previo al DDL de filas sin institución y de una asignación curso–profesor cruzada.
- `NOT NULL` en las 16 tablas; excepciones `NULL` para plantillas.
- Unicidad por institución de curso, código de estudiante, código de profesor y configuración; repetición válida entre A y B.
- Persistencia de únicos globales de correo/documento.
- Restricciones de borrado `RESTRICT`; `down()` bloqueado si no puede reconstruir un único global; `up/down` sin pérdida de filas o valores.
- Normalización de `Privada`, rechazo de slugs no canónicos, colisión insensible a mayúsculas y conservación del valor legado.
- Prueba HTTP de archivo estático directo descrita arriba.

Resultados:

| Suite | Resultado |
|---|---:|
| B8 focalizada, SQLite en memoria | 6 pruebas, 64 aserciones, aprobadas |
| B8 focalizada, MariaDB `colegio_tests` | 6 pruebas, 64 aserciones, aprobadas |
| Suite completa SQLite | 278 pruebas, 1.957 aserciones, aprobadas |
| Suite completa MariaDB `colegio_tests` | 292 pruebas, 1.849 aserciones, 10 omitidas, sin fallos |
| HTTP archivo estático, loopback | GET directo sin autenticación: 200; fixture eliminado |

`colegio_tests` se restauró desde el dump previo y sus tablas pasaron `mysqlcheck`. El esquema legado sigue compatible porque B8 no se carga en las migraciones automáticas: requiere B2 completa, pertenencias decididas y una invocación explícita de su ruta de migración. No se aplicó B8 a `colegio`.

## 6. Archivos creados y modificados

**Nuevos**

- `laravel/database/migrations/etapa2/b8/2026_11_19_120000_etapa2_b8_restricciones_institucionales.php`
- `laravel/app/Modules/Instituciones/Support/SlugInstitucion.php`
- `laravel/tests/Feature/Aislamiento/Etapa2B8RestriccionesTest.php`
- `laravel/tests/Feature/Aislamiento/Etapa2B8SlugTest.php`
- `docs/seguridad/etapa2-b8.md`

**Modificados**

- `laravel/app/Modules/Landing/Support/SitioInstitucional.php`
- `laravel/app/Modules/Instituciones/Requests/InstitucionStoreRequest.php`
- `laravel/app/Modules/Instituciones/Requests/InstitucionUpdateRequest.php`

No se modificaron archivos existentes de `laravel/public/` ni se cambiaron datos reales.

## 7. Riesgos y dependencias

- B9 debe crear configuración propia de forma idempotente respetando `UNIQUE(id_institucion)`, preparar categorías/escala plantilla y exigir slugs canónicos sin adoptar automáticamente `Privada`.
- B10 requiere autorización separada, inventario/auditoría sin críticos, decisiones explícitas para landing/configuración/medios, respaldo real verificado y ventana de aplicación por bloques. B8 no concede esa autorización.
- La protección de archivos exige trabajo posterior de almacenamiento privado; el filtrado de registros no invalida URLs públicas antiguas.
- Las relaciones multi-padre sin clave institucional redundante siguen dependiendo de validación de aplicación/auditoría, como define el diseño actual.

**B8 termina aquí.** No se ejecutaron pasos de B9/B10 ni cambios en la base real.
