# Etapa 2 — B2: migraciones de expansión M1–M4

**Fecha:** 2026-11-18 · **Rama:** `etapa0-contencion` (sin commit) · **Ejecutadas sobre:** solo `colegio_tests` (usuario limitado `colegio_tests`). **Base real `colegio`: sin cambios** (52/52 checksums idénticos; las 4 migraciones siguen *pendientes* allí).

## Qué hace B2 (y qué no)
Agrega `id_institucion BIGINT UNSIGNED NULL` + índice + FK a `instituciones(id_institucion)` `ON DELETE RESTRICT` en 16 tablas, y crea la tabla de trazabilidad `migracion_pertenencia`.

**No** puebla columnas, **no** cambia únicos, **no** convierte nada a `NOT NULL`, **no** toca las FK existentes (`cursos`/`solicitudes_admision` siguen con `SET NULL`), **no** cambia el singleton de configuración, rutas públicas ni código de la aplicación. Tampoco se agregaron campos de colores/contacto/legales a `colegio_configuracion` (requieren propuesta de esquema aparte).

| Migración | Tablas |
|---|---|
| M1 `2026_11_18_100000_etapa2_m1_institucion_en_catalogos` | `materias`, `areas`, `periodos_academicos`, `tipos_actividad`, `escala_notas`, `evento_categorias` |
| M2 `2026_11_18_100001_etapa2_m2_institucion_en_perfiles_y_recursos` | `estudiantes`, `profesores`, `acudientes`, `eventos`, `soportes` |
| M3 `2026_11_18_100002_etapa2_m3_institucion_en_configuracion_y_landing` | `colegio_configuracion`, `colegio_imagenes`, `landing_contenido`, `landing_galeria`, `landing_noticias` |
| M4 `2026_11_18_100003_etapa2_m4_create_migracion_pertenencia_table` | crea `migracion_pertenencia` |

`colegio_configuracion.id_institucion` lleva índice simple; el `UNIQUE` (una configuración por colegio) se aplica en B8, tras poblar.

### Decisiones de diseño
- **RESTRICT** en las FK nuevas: hoy todas las filas son `NULL`, así que no bloquea nada; poblado, impide borrar un colegio con datos (en vez de dejar huérfanos).
- **Reanudable** (el DDL de MariaDB no es transaccional): `up()` y `down()` verifican cada pieza —columna, índice, FK— por separado; una ejecución interrumpida entre dos `ALTER` se completa al volver a ejecutar. No depende de rollback transaccional.
- **`down()` protegido**: M1–M3 se niegan a eliminar la columna si alguna fila ya tiene `id_institucion` (población posterior); M4 se niega si `migracion_pertenencia` tiene registros. En ambos casos lanzan excepción **antes** de ejecutar DDL.
- **`migracion_pertenencia` sin FK** y sin datos personales: solo identificadores, tabla/columna, valor anterior/nuevo, regla, quién y cuándo; debe sobrevivir a cambios en los registros que describe. Índices: `lote`, `(tabla, id_registro)`, `id_institucion`.

## SQL generado (`php artisan migrate --pretend`, MariaDB)
(Se omiten las consultas de lectura a `information_schema` que hace la verificación por pieza.)
```sql
alter table `materias` add `id_institucion` bigint unsigned null after `id_materia`;
alter table `materias` add index `materias_id_institucion_index`(`id_institucion`);
alter table `materias` add constraint `materias_id_institucion_foreign` foreign key (`id_institucion`) references `instituciones` (`id_institucion`) on delete restrict;
alter table `areas` add `id_institucion` bigint unsigned null after `id_area`;
alter table `areas` add index `areas_id_institucion_index`(`id_institucion`);
alter table `areas` add constraint `areas_id_institucion_foreign` foreign key (`id_institucion`) references `instituciones` (`id_institucion`) on delete restrict;
alter table `periodos_academicos` add `id_institucion` bigint unsigned null after `id_periodo`;
alter table `periodos_academicos` add index `periodos_academicos_id_institucion_index`(`id_institucion`);
alter table `periodos_academicos` add constraint `periodos_academicos_id_institucion_foreign` foreign key (`id_institucion`) references `instituciones` (`id_institucion`) on delete restrict;
alter table `tipos_actividad` add `id_institucion` bigint unsigned null after `id_tipo_actividad`;
alter table `tipos_actividad` add index `tipos_actividad_id_institucion_index`(`id_institucion`);
alter table `tipos_actividad` add constraint `tipos_actividad_id_institucion_foreign` foreign key (`id_institucion`) references `instituciones` (`id_institucion`) on delete restrict;
alter table `escala_notas` add `id_institucion` bigint unsigned null after `id_escala`;
alter table `escala_notas` add index `escala_notas_id_institucion_index`(`id_institucion`);
alter table `escala_notas` add constraint `escala_notas_id_institucion_foreign` foreign key (`id_institucion`) references `instituciones` (`id_institucion`) on delete restrict;
alter table `evento_categorias` add `id_institucion` bigint unsigned null after `id_categoria`;
alter table `evento_categorias` add index `evento_categorias_id_institucion_index`(`id_institucion`);
alter table `evento_categorias` add constraint `evento_categorias_id_institucion_foreign` foreign key (`id_institucion`) references `instituciones` (`id_institucion`) on delete restrict;
alter table `estudiantes` add `id_institucion` bigint unsigned null after `id_estudiante`;
alter table `estudiantes` add index `estudiantes_id_institucion_index`(`id_institucion`);
alter table `estudiantes` add constraint `estudiantes_id_institucion_foreign` foreign key (`id_institucion`) references `instituciones` (`id_institucion`) on delete restrict;
alter table `profesores` add `id_institucion` bigint unsigned null after `id_profesor`;
alter table `profesores` add index `profesores_id_institucion_index`(`id_institucion`);
alter table `profesores` add constraint `profesores_id_institucion_foreign` foreign key (`id_institucion`) references `instituciones` (`id_institucion`) on delete restrict;
alter table `acudientes` add `id_institucion` bigint unsigned null after `id_acudiente`;
alter table `acudientes` add index `acudientes_id_institucion_index`(`id_institucion`);
alter table `acudientes` add constraint `acudientes_id_institucion_foreign` foreign key (`id_institucion`) references `instituciones` (`id_institucion`) on delete restrict;
alter table `eventos` add `id_institucion` bigint unsigned null after `id_evento`;
alter table `eventos` add index `eventos_id_institucion_index`(`id_institucion`);
alter table `eventos` add constraint `eventos_id_institucion_foreign` foreign key (`id_institucion`) references `instituciones` (`id_institucion`) on delete restrict;
alter table `soportes` add `id_institucion` bigint unsigned null after `id_soporte`;
alter table `soportes` add index `soportes_id_institucion_index`(`id_institucion`);
alter table `soportes` add constraint `soportes_id_institucion_foreign` foreign key (`id_institucion`) references `instituciones` (`id_institucion`) on delete restrict;
alter table `colegio_configuracion` add `id_institucion` bigint unsigned null after `id_configuracion`;
alter table `colegio_configuracion` add index `colegio_configuracion_id_institucion_index`(`id_institucion`);
alter table `colegio_configuracion` add constraint `colegio_configuracion_id_institucion_foreign` foreign key (`id_institucion`) references `instituciones` (`id_institucion`) on delete restrict;
alter table `colegio_imagenes` add `id_institucion` bigint unsigned null after `id_imagen`;
alter table `colegio_imagenes` add index `colegio_imagenes_id_institucion_index`(`id_institucion`);
alter table `colegio_imagenes` add constraint `colegio_imagenes_id_institucion_foreign` foreign key (`id_institucion`) references `instituciones` (`id_institucion`) on delete restrict;
alter table `landing_contenido` add `id_institucion` bigint unsigned null after `id_contenido`;
alter table `landing_contenido` add index `landing_contenido_id_institucion_index`(`id_institucion`);
alter table `landing_contenido` add constraint `landing_contenido_id_institucion_foreign` foreign key (`id_institucion`) references `instituciones` (`id_institucion`) on delete restrict;
alter table `landing_galeria` add `id_institucion` bigint unsigned null after `id_foto`;
alter table `landing_galeria` add index `landing_galeria_id_institucion_index`(`id_institucion`);
alter table `landing_galeria` add constraint `landing_galeria_id_institucion_foreign` foreign key (`id_institucion`) references `instituciones` (`id_institucion`) on delete restrict;
alter table `landing_noticias` add `id_institucion` bigint unsigned null after `id_noticia`;
alter table `landing_noticias` add index `landing_noticias_id_institucion_index`(`id_institucion`);
alter table `landing_noticias` add constraint `landing_noticias_id_institucion_foreign` foreign key (`id_institucion`) references `instituciones` (`id_institucion`) on delete restrict;
create table `migracion_pertenencia` (`id` bigint unsigned not null auto_increment primary key, `lote` char(36) not null comment 'Agrupa todos los cambios de una ejecución', `accion` varchar(20) not null comment 'asignar | clonar | repuntar | plantilla | descartar', `tabla` varchar(64) not null, `id_registro` bigint unsigned not null, `columna` varchar(64) null comment 'Columna modificada (repuntar) o id_institucion', `valor_anterior` varchar(64) null, `valor_nuevo` varchar(64) null, `id_institucion` bigint unsigned null comment 'Institución resultante', `id_registro_nuevo` bigint unsigned null comment 'Id del clon creado (clonar)', `origen` varchar(20) not null comment 'regla | correspondencia', `regla` varchar(80) null comment 'Regla de pertenencia o entrada del archivo de correspondencia', `ejecutado_por` int unsigned null comment 'id_usuario que ejecutó (sin FK: historial)', `created_at` timestamp not null default CURRENT_TIMESTAMP, `revertido_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `migracion_pertenencia` add index `migracion_pertenencia_lote_index`(`lote`);
alter table `migracion_pertenencia` add index `migracion_pertenencia_tabla_id_registro_index`(`tabla`, `id_registro`);
alter table `migracion_pertenencia` add index `migracion_pertenencia_id_institucion_index`(`id_institucion`);
```

## Ensayo en `colegio_tests`
Datos de ensayo: copia de `colegio_restauracion_prueba` con los datos de dos colegios de la Etapa 1 (55 tablas, 15 usuarios, 3 instituciones, 510 filas en las 52 tablas no volátiles), cargada como usuario `colegio_tests`. Antes de cada paso se verificó la conexión efectiva (`DATABASE()` = `colegio_tests`, `CURRENT_USER()` = `colegio_tests@localhost`).

Instantáneas (esquema de `information_schema`: columnas con tipo/nulo/default, índices, FK con reglas; datos: filas + MD5 de las **columnas originales** ordenadas por PK). La instantánea es determinista (dos tomas seguidas: 0 diferencias).

| # | Paso | Resultado |
|---|---|---|
| 1 | `migrate` | 4 migraciones DONE (≈550 ms) |
| 2 | Comparación antes → después | **50 diferencias, todas esperadas**: +1 tabla, +16 columnas, +16 índices, +16 FK, +4 filas en `migrations`. 0 cambios en columnas/índices/FK existentes; MD5 de datos originales idéntico en las 52 tablas |
| 3 | Tipos | 16 × `bigint(20) unsigned`, `NULL`, default `NULL`, ubicada tras la PK; FK `delete_rule = RESTRICT`, `update_rule = RESTRICT` → `instituciones.id_institucion` |
| 4 | Población | 0 filas con `id_institucion` no nulo en las 16 tablas |
| 5 | FK del esquema | 60 → 76 (+16) |
| 6 | Relaciones | joins asignación→materia/profesor, matrícula→estudiante, actividad→tipo/periodo, evento→categoría, estudiante↔acudiente: mismos resultados |
| 7 | Segundo `migrate` | `Nothing to migrate`; instantánea idéntica (0 diferencias) |
| 8 | `migrate:rollback --step=4` con una fila en `migracion_pertenencia` | **Aborta** en M4 sin revertir nada (las 4 siguen *Ran*); se borró la fila de prueba |
| 9 | `migrate:rollback --step=4` | 4 DONE; instantánea **idéntica a la de antes** (0 diferencias en esquema y datos) |
| 10 | Interrupción simulada (`materias`: solo columna; `areas`: columna + índice, sin FK) y `migrate` | Completa lo faltante sin error |
| 11 | Comparación con el paso 2 | Idéntico salvo `migrations` (ids 76–79 en vez de 72–75: autoincremento tras el rollback) |
| 12 | Tercer `migrate` | `Nothing to migrate` |

## Pruebas automatizadas
Nueva: `tests/Feature/Aislamiento/Etapa2MigracionesExpansionTest.php` (6 pruebas, ambos motores): columnas/índices/FK de las 16 tablas, columnas en `NULL` con el escenario de dos colegios, esquema de `migracion_pertenencia` sin FK, únicos y `cursos.id_institucion SET NULL` intactos, `down()` de M1 y M4 se niegan con datos (sin DDL dentro de la prueba).

| Suite | Antes de B2 | Después |
|---|---|---|
| SQLite (`php artisan test`) | 196 | **202 / 202** |
| MariaDB `colegio_tests` (`--configuration=phpunit.mariadb.xml`) | 208 | **214 / 214** |

`RefreshDatabase`: `tests/TestCase::setUpTraits()` exige `GuardiaBaseDePruebas` antes de migrar (conexión efectiva SQLite en memoria, o `colegio_tests` **y** usuario `colegio_tests`, sin privilegios fuera de esa base). La suite MariaDB recrea `colegio_tests`; los datos de ensayo están en el respaldo de abajo y en el scratchpad de la sesión.

### Revisión de código frente a las columnas nuevas
- Ninguna consulta con `JOIN` usa `id_institucion` sin calificar (`Dashboard`, `Estadisticas`, `Listados`, `RegistroEstudiantes`): sin ambigüedad (error 1052) ni sobrescritura del atributo por `select *`.
- `whereHas(... where('id_institucion'))` es subconsulta correlacionada: resuelve a la tabla interna (`usuarios`); `scopeDeInstitucion` califica la columna.
- Los 14 modelos afectados usan `$fillable` sin `id_institucion`: no asignable desde formularios.
- `institucion:auditar` (B1) deriva pertenencia por relaciones y aún no lee las columnas nuevas (sus pruebas pasan).

## Base real `colegio`
CHECKSUM EXTENDED de las 52 tablas no volátiles antes y después de B2: **idénticos**. Sigue con 71 migraciones, 55 tablas, 5 columnas `id_institucion` (las previas) y sin `migracion_pertenencia`.

## Riesgos pendientes
1. **Las 4 migraciones están pendientes en `colegio`**: un `php artisan migrate` con el `.env` de la aplicación las aplicaría (los guardias solo bloquean fresh/refresh/reset/wipe). No ejecutar `migrate` contra `colegio` hasta autorizar B10; antes, respaldo verificado.
2. `institucion:auditar` debe incorporar las columnas nuevas (contar `NULL`, verificar coherencia columna ↔ relación) — B3.
3. Mientras estén en `NULL`, las columnas no aíslan nada: el aislamiento sigue dependiendo de la Etapa 1 (`RUTA_INSTITUCION`). Ningún modelo debe pasar a `RUTA_INSTITUCION = ''` antes de poblar (B4/B5).
4. Con datos ya poblados, `down()` aborta a propósito; la reversión pasa por `migracion_pertenencia` o el respaldo.
5. En la base real con más filas, cada `ALTER` reconstruye la tabla (bloqueo breve); con el tamaño actual es despreciable, pero conviene aplicar fuera de horario.
6. Ensayado en MariaDB 10.4.32 (XAMPP); el dump de Windows no se ha importado ni ensayado.

## Archivos
Nuevos: las 4 migraciones de arriba, `tests/Feature/Aislamiento/Etapa2MigracionesExpansionTest.php`, este documento. Modificado: solo la línea de estado y el orden de bloques de `docs/seguridad/etapa2-diseno.md`. Ningún archivo de código existente modificado.

## Reversión
- `colegio_tests`: `php artisan migrate:rollback --step=4` con la conexión de pruebas (ensayado).
- Código: borrar los 5 archivos nuevos. Respaldo previo del árbol: `~/Respaldos/sistema-escolar/etapa2-b2-20261118_151744/` (`arbol-antes.tgz`, `git-status-antes.txt`, `SHA256SUMS`).
