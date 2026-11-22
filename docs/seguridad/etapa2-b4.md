# Etapa 2 — B4: preparación y ensayo controlado de la población

**Fechas:** 2026-11-18 (implementación) y 2026-11-19 (segunda autorización: aprobación explícita, bloqueos y validación dentro de la operación protegida) · **Rama:** `etapa0-contencion` (sin commit) · **Alcance autorizado:** preparación técnica y pruebas con datos ficticios en `colegio_tests`. **Base real `colegio`: sin cambios. Dump real: no transformado.**

## 1. Qué se implementó

| Pieza | Función |
|---|---|
| `institucion:poblar --aplicar=<archivo>` | Aplica un archivo de correspondencia aprobado. **Solo `colegio_tests`.** |
| `institucion:poblar --revertir=<lote>` | Deshace un lote aplicado. **Solo `colegio_tests`.** |
| `institucion:poblar --validar=<archivo>` | Revisa un archivo, decisión por decisión, sin escribir |
| `Correspondencia` (formato v3) | Decisiones individuales **y por grupo**; validación completa |
| `AplicadorPoblacion` | Algoritmo transaccional, trazabilidad y reversión |
| `VerificadorPertenencia` | 18 reglas de coherencia institucional basadas en las columnas de B2 |
| Migración **R2a** | R2 anticipado: únicos compuestos en las 6 tablas clonables (solo base de pruebas) |

### 1.1 Garantías de `--aplicar` / `--revertir`
Numeradas como en la autorización del 2026-11-19:

1. **Base:** se comprueba la conexión efectiva (`SELECT DATABASE()` = `colegio_tests` **y** `CURRENT_USER()` = `colegio_tests`) **antes de leer el archivo**. SQLite solo se acepta en memoria y dentro de la suite de pruebas. No hay variable que lo habilite para otra base.
2. **Esquema:** exige las 16 columnas de B2 y `migracion_pertenencia`.
3. **Aprobación explícita para pruebas:** el archivo debe incluir este bloque:
   ```json
   "aprobacion": {"ambito": "pruebas", "base": "colegio_tests", "huella_plan": "<la misma huella>", "aprobado_por": "…", "fecha": "<ISO-8601>"}
   ```
   La plantilla lo genera en `null`. Sin el bloque, o si el ámbito, la base o la huella no coinciden, se rechaza. En B4 solo existe el ámbito `pruebas`.
4. **Huella dentro de la operación protegida:** además de una validación previa (respuesta rápida), la simulación, la huella y **todo** el archivo se vuelven a validar **dentro de la transacción, después de bloquear las filas**, inmediatamente antes de escribir. Un cambio concurrente entre la validación previa y el bloqueo se detecta («Huella desactualizada») sin escribir nada.
5. **Condiciones de carrera:**
   - **Cerrojo exclusivo** `GET_LOCK('institucion_poblar:<base>', 0)` para `--aplicar` y `--revertir`. Si otro proceso lo tiene, se rechaza («en curso») sin tocar nada. Se libera siempre (`finally`) y, si el proceso muere, al caer su conexión.
   - **Bloqueo de filas** `SELECT COUNT(*) … FOR UPDATE` sobre las 32 tablas que la población lee o escribe, como **primera** operación de la transacción. En REPEATABLE READ esto bloquea también los huecos, así que nadie puede insertar, modificar ni borrar en ellas hasta el COMMIT. La simulación posterior lee exactamente los datos bloqueados.
6. **Decisiones incompletas, ambiguas o inconsistentes:** formato v3; `base` igual a la base efectiva; **todas** las decisiones completas y válidas; sin huérfanos, sin relaciones entre colegios y sin registros inconsistentes. Con un solo error no se aplica nada.
7. **Trazabilidad:** cada `asignar`, `clonar` (con id original y nuevo), `repuntar` (con valor anterior y nuevo), `plantilla`, `descartar` y el propio lote quedan en `migracion_pertenencia`.
8. **Sin asignación por defecto:** solo se asigna automáticamente lo `inequivoca` (todos sus usos son de una institución). Lo demás, únicamente por decisión explícita.
9. **Transacción:** todo el DML va en **una** transacción, sin DDL dentro, porque en MariaDB el DDL provoca un COMMIT implícito. El DDL (M1–M4, R2a) va en migraciones separadas.
10. **Integridad antes del COMMIT:** 18 reglas de coherencia, auditoría sin huérfanos ni cruces, cada operación con el valor esperado y conteos iguales a originales + copias. Si algo falla, ROLLBACK.

**Idempotencia:** el lote registra `huella:<plan>`. Una segunda ejecución devuelve «ya aplicado», comprobado antes y dentro de la operación protegida. Cada `UPDATE` exige el valor previo esperado (`id_institucion IS NULL`, `columna = valor_anterior`) y afecta exactamente 1 fila; si no, se aborta.

### 1.2 Decisiones por grupo (formato v3)
Grupos: `configuracion`, `imagenes`, `landing`, `galeria`, `noticias` y `categorias_calendario` (las categorías sin uso). Cada grupo lista sus registros y las opciones válidas para **todos** ellos.

Resolución: la decisión individual de un registro, si no es `null`, **prevalece**; si es `null`, se aplica la del grupo. `--validar` muestra el origen de cada decisión (`regla`, `archivo:individual`, `archivo:grupo:<g>`). Un grupo en `null` o `pendiente` deja sin decisión a sus registros, y el archivo se rechaza.

Acciones y validez:

| Acción | Válida si |
|---|---|
| `asignar` | `institucion_destino` existe y ninguna otra institución usa el registro (en un compartido se rechaza) |
| `clonar` | La tabla es clonable (catálogos, configuración, landing; **no** perfiles, eventos ni soportes); `instituciones_clon` tiene ≥2 slugs que cubren a todos los usuarios; `conserva_original` está entre ellos; y **no queda único global** en la tabla (requiere R2a) |
| `plantilla` | Solo `evento_categorias` y `escala_notas` |
| `descartar` | Solo registros sin uso (quedan sin institución y no se borran) |
| `seguir_configuracion` | Solo imágenes; su configuración debe asignarse o clonarse |

## 2. Algoritmo de transformación
```
guardia(base, usuario) → esquema M1–M4
validación previa: plan = simular() en SOLO LECTURA · ¿ya aplicado? · resolver(archivo, plan) + aprobación
GET_LOCK (exclusivo; si está tomado → rechazo)
BEGIN
  SELECT … FOR UPDATE en 32 tablas         -- desde aquí nadie más escribe en ellas
  plan = simular() otra vez                 -- sobre los datos bloqueados
  ¿ya aplicado? → ROLLBACK, «ya aplicado»
  ops = resolver(archivo, plan)             -- huella + aprobación + todas las decisiones; error → ROLLBACK, rechazo
  registrar lote (huella)
  para cada op, en orden de tablas (materias → áreas → … → configuración → imágenes → landing…):
    asignar:   UPDATE t SET id_institucion=X WHERE pk=id AND id_institucion IS NULL   (=1 fila)
    clonar:    asignar original a conserva_original
               por cada otra institución I: INSERT copia (id_institucion=I) → registrar (original, copia)
                 por cada referencia del plan que pertenece a I:
                   UPDATE ref SET col=copia WHERE pk=rid AND col=original            (=1 fila)
    seguir_configuracion: misma decisión que su configuración (asignar o clonar)
    plantilla / descartar: sin cambio (se verifica NULL) y se registra
  jerarquías: cada materia → área de SU institución; materia → padre de su institución;
              imagen → configuración de su institución (si no existe esa versión: error)
  verificar: 18 reglas de coherencia = 0 · auditoría: 0 huérfanos y 0 cruces ·
             cada op con el valor esperado · conteos = originales + copias
COMMIT  (o ROLLBACK ante cualquier excepción)
RELEASE_LOCK
```
- Las **referencias a repuntar** salen del plan: cada fila referenciante (asignación, `profesor_materia`, actividad, boletín, evento) con la institución que le corresponde (curso, profesor o creador).
- **Nunca se borran originales.** Al revertir solo se borran **copias**, y únicamente si se demuestra que no tienen referencias (las FK de la tabla se consultan una por una); si alguna tiene, se aborta.
- `ON DELETE CASCADE` no entra en juego porque la población no ejecuta ningún `DELETE`.

**Reversión** (`--revertir`): toma el mismo cerrojo y bloquea las mismas filas; luego recorre el lote en orden inverso dentro de una transacción. Deshace los repuntes (exige el valor nuevo), borra las copias sin referencias, devuelve a `NULL` lo asignado (exige el valor asignado) y marca `revertido_at`. El historial se conserva.

## 3. Restricciones únicas — dependencia R2a
Con los únicos globales actuales, clonar viola `nombre_materia`, `slug`, `clave`, etc. Análisis:

| Alternativa | Decisión |
|---|---|
| Renombrar las copias («Matemáticas (Zeta)») | Descartada: altera datos visibles y la unicidad seguiría siendo global |
| Desactivar índices durante la población | Descartada: DDL dentro del proceso, sin garantía si se interrumpe |
| **Anticipar R2 solo en las tablas clonables** | **Elegida** |

**R2a** (`2026_11_18_100004_etapa2_r2a_unicos_compuestos_para_clonar`): en `materias`, `areas`, `tipos_actividad`, `periodos_academicos`, `evento_categorias` y `landing_contenido`, primero crea el único compuesto `(id_institucion, …)` y **después** retira el global, de modo que la tabla nunca queda sin unicidad. No toca `codigo_estudiante`, `codigo_profesor` ni `usuarios`. Es reanudable pieza a pieza.
- **Guardia propia, más estricta:** base de pruebas, o `ETAPA2_R2_AUTORIZADA=<base exacta>`; la variable de M1–M4 **no** sirve.
- **`down()`** se niega antes de ejecutar DDL si hay valores repetidos (por ejemplo, tras clonar): primero hay que revertir la población.
- **Limitación:** en un único compuesto, las filas con `id_institucion NULL` (plantillas o datos sin poblar) no chocan entre sí.
  - `EventoCategoriaSeeder` dependía del índice (`insertOrIgnore`) y ahora comprueba el `slug` antes de insertar.
  - `EditarLandingController::updateOrCreate(['clave' => 'hero_imagen'])` actualizaría una copia arbitraria si la landing se clona: debe resolverse en **B7** antes de clonar la landing en una base en uso.
- **Base real:** no se aplicó. Allí es parte de B8 y requiere su propia autorización. En `colegio` las 5 migraciones de la Etapa 2 siguen pendientes y bloqueadas.

Nombres: MariaDB limita los identificadores a 64 caracteres, por eso los compuestos usan la forma `<tabla>_inst_<cols>_uq`. Una primera versión con nombres largos falló en MariaDB, sin efecto en SQLite; corregido y reensayado.

## 4. Defectos encontrados y corregidos durante B4
1. **`SoloLectura` confirmaba transacciones ajenas en MariaDB** (latente desde B1). Dentro de una transacción ya abierta, `START TRANSACTION READ ONLY` hace un COMMIT implícito de la exterior. En la práctica afectaba a la suite MariaDB (los datos de algunas pruebas quedaban confirmados en `colegio_tests`); el comando nunca lo sufrió, porque corre sin transacción previa.
   - **Corrección:** con una transacción abierta se usa `SAVEPOINT … ROLLBACK TO SAVEPOINT`, más un bloqueo previo a la ejecución (`beforeExecuting`) de toda sentencia de escritura.
   - **Prueba de regresión:** `test_solo_lectura_no_confirma_una_transaccion_exterior`.
   - **Efecto lateral:** la suite MariaDB bajó de 92 s a ~50 s.
2. **La huella cubría código y esquema.** Incluía la lista de archivos con `singleton()` y los únicos, así que un cambio de código o la aplicación de R2a invalidaban una aprobación sin que cambiaran los datos. Ahora cubre solo lo derivado de los datos: instituciones, clasificación, referencias, cruces y huérfanos. Las huellas de B3 cambian y el archivo real se regeneró.
3. **Simulación tras aplicar:** marcaba como inconsistente lo asignado por decisión humana (configuración, landing) y no distinguía plantilla/descartado de «sin decidir». Ahora consulta los lotes vigentes de `migracion_pertenencia`.
4. **`institucion:auditar`** (pendiente desde B3) ya reconoce las columnas:
   - catálogos `asignado` / `asignado_incoherente`;
   - plantillas por decisión;
   - configuración y landing «sin institución» en lugar de «compartida»;
   - instituciones sin configuración propia;
   - nueva sección **8. Coherencia institucional por columnas** (`VerificadorPertenencia`).

## 5. Pruebas

### Automatizadas
Nueva: `tests/Feature/Aislamiento/Etapa2AplicacionPoblacionTest.php` (18 pruebas, una solo MariaDB y una solo SQLite). Escenario: dos colegios ficticios que comparten materia (con área), periodo, tipo de actividad y categoría. Además, en la prueba de rechazo, un **curso compartido** (estudiante de un colegio matriculado en un curso del otro).

| # | Requisito | Prueba |
|---|---|---|
| 1 | Aplicación correcta | Original → A y copia → B en materia, área, periodo, tipo y categoría; asignación, `profesor_materia`, actividades, boletines y evento de B → copias de B; la copia de la materia → copia del área; grupos aplicados; 0 violaciones |
| 2 | Decisiones incompletas o inválidas | 8 casos (sin decisión, `pendiente`, asignar un compartido, sin `conserva_original`, clonación sin cubrir a un usuario, plantilla en landing, slug inexistente, otra base) → rechazo, **0 cambios**, 0 filas de traza |
| 3 | Huella desactualizada | Cambio de datos tras aprobar → rechazo «Huella desactualizada», 0 cambios |
| 4, 9 | Sin pérdida de referencias, integridad FK | Mismos conteos en 7 tablas referenciantes; 0 huérfanos y 0 cruces; `PRAGMA foreign_key_check` vacío (SQLite) |
| 5 | Aislamiento | Ningún curso usa materia, periodo o tipo de otro colegio; cada colegio tiene su propio «Matemáticas» |
| 6 | Conservación de originales | Ningún original borrado; columnas originales idénticas (salvo `id_institucion`) |
| 7 | Transacción fallida | Interrupción en el primer repunte y en la primera plantilla → estado idéntico y 0 trazas; luego una aplicación completa funciona |
| 8 | Idempotencia | Segunda aplicación «ya aplicado» sin cambios; la nueva simulación tiene 0 pendientes y 0 inconsistentes |
| 10 | Bloqueo fuera de la base de pruebas | Conexión SQLite en archivo → `--aplicar` y `--revertir` BLOQUEADOS sin crear nada |
| — | Reversión | Restaura el estado exacto y la misma huella; se niega si una copia recibió referencias nuevas; permite volver a aplicar |
| — | Grupos | Excepción individual (`hero_imagen` descartada) dentro de un grupo clonado |
| — | Curso compartido | Relación entre colegios → rechazo sin cambios |
| — | R2a requerido | Con el único global presente, clonar se rechaza (solo SQLite, porque requiere DDL en la prueba) |
| — | Auditoría tras aplicar | 0 críticos; solo la advertencia «institución sin configuración propia» |
| — | Regresión `SoloLectura` | No confirma una transacción exterior |
| 3 (aprobación) | Aprobación explícita | Sin `aprobacion`, con otro ámbito o para otro plan → rechazo, 0 cambios (en la prueba 2) |
| 4 (huella dentro) | Cambio concurrente | Datos modificados **entre** la validación previa y el bloqueo → rechazo «Huella desactualizada», 0 asignaciones, 0 copias, 0 trazas |
| 5 (carrera) | Cerrojo | Otra conexión retiene `GET_LOCK` → rechazo «en curso», 0 cambios; liberado, aplica (solo MariaDB) |

Actualizadas por el cambio deliberado de únicos (R2a): `Etapa2MigracionesExpansionTest` y `AuditoriaPertenenciaTest` (los catálogos ya no reportan único global; los de identidad sí). También `Etapa2SimulacionPoblacionTest`: formato v3 y conflictos de único ausentes con R2a.

| Suite | Antes de B4 | Después |
|---|---|---|
| SQLite | 214 | **230 / 230** |
| MariaDB `colegio_tests` | 227 | **243 superadas + 1 omitida** (la prueba de R2a que requiere DDL dentro de la transacción, solo SQLite) |

La suite MariaDB pasó de ~55 s a ~155 s. Es el coste de los `FOR UPDATE` sobre 32 tablas en cada aplicación de prueba.

### Ensayo manual 2 — 2026-11-19 (MariaDB, escenario ficticio Alfa/Zeta)
El dataset del primer ensayo se perdió con el reinicio (estaba en la carpeta temporal). Se reconstruyó en `colegio_tests`:
- `migrate:fresh` (permitido por la guardia solo en esa base);
- el escenario ficticio de la suite (`EscenarioDosColegios`), con materia y área, periodo, tipo, categoría y `profesor_materia` compartidos;
- configuración, imágenes, galería y noticias.

| Paso | Resultado |
|---|---|
| `--validar` en blanco / decidido sin aprobación / decidido y aprobado | 50 errores (incluye «Falta aprobacion») / 1 error (aprobación) / válido |
| `--aplicar` | asignar 52 · clonar 29 (materia, área, periodo, tipo, categoría + 24 claves) · repuntar 7 · plantilla 12 · descartar 4 |
| Copias y repuntes | Las 5 copias son de Zeta. Los 7 repuntes son filas de Zeta: asignación, `profesor_materia`, actividad ×2, boletín, evento y **la copia de la materia → copia del área** (jerarquía) |
| Verificador / auditoría / nueva simulación | 0 violaciones · 0 críticos · nada pendiente (12 plantillas) |
| Segunda `--aplicar` | «ya aplicado», 0 diferencias |
| `--revertir` | Idéntico al estado previo, comparando **todas** las columnas (incluida `id_institucion`); solo cambia el historial |
| Otra sesión retiene `GET_LOCK` | `--aplicar` rechazado («en curso»), código 1, 0 diferencias |
| Aplicación pausada con la transacción abierta | Otra sesión: `INSERT` en `materias` y `UPDATE` de una asignación → **ERROR 1205** (bloqueo); las lecturas siguen viendo el estado previo; la fila intrusa no existe; la aplicación termina bien |
| `kill -9` con la transacción abierta | 0 diferencias; el cerrojo queda libre al caer la conexión; la aplicación siguiente funciona (0 violaciones) |

### Ensayo manual 1 — 2026-11-18 (MariaDB, datos de ensayo con 3 instituciones)
Partida: estado previo a B2, idéntico a la instantánea de B2. Todas las órdenes se ejecutaron tras verificar `DATABASE()`/`CURRENT_USER()` = `colegio_tests`.

| Paso | Resultado |
|---|---|
| `migrate` (M1–M4 + R2a) | 19 diferencias, todas de R2a (6 únicos sustituidos) y `migrations`; 0 cambios de datos |
| `--validar` del archivo en blanco | 48 errores, código 1 |
| Decisiones **ficticias** (`decidir_ensayo.php`, solo acepta `base = colegio_tests`) + `--validar` | Válido; origen de cada decisión visible (regla, individual, grupo) |
| `--aplicar` | asignar 52 · clonar 28 (materia, periodo, tipo, categoría + 24 claves de landing) · repuntar 5 · plantilla 12 · descartar 3 |
| Repuntes | Exactamente las 5 filas de la institución 1003 (asignación 1003, actividad 1003 ×2, boletín 1002, evento 1002) |
| Verificador / auditoría | 0 violaciones · 0 críticos · 0 huérfanos · 0 cruces · 76 FK |
| Nueva simulación | 0 asignables, 0 a clonar, 0 decisiones, 0 inconsistentes, 12 plantillas |
| Segunda `--aplicar` | «ya aplicado», 0 diferencias |
| `migrate:rollback` de R2a con copias | Se niega («valores repetidos»), 0 cambios |
| `--revertir` | Estado idéntico al previo; las 16 columnas en `NULL`; huella de la simulación idéntica (`688e4dc7…`); historial conservado (101 filas con `revertido_at`); segunda reversión: «nada que revertir» |
| Excepción en el paso 60 (transacción de nivel 0) | 0 cambios |
| **`kill -9` del proceso** con la transacción abierta (paso 60) | 0 cambios (InnoDB revierte lo no confirmado) |
| `migrate:rollback` de R2a / aplicar sin R2a | Esquema idéntico a B2 / rechazo «clonar exige R2 anticipado» (28 errores) |
| Re-migrar R2a y reaplicar | Mismas operaciones; resultado equivalente (ids nuevos de autoincremento); 0 violaciones |
| Guardias como `root` sobre `colegio_tests` | R2a sin variable: BLOQUEADO · con la variable de M1–M4: BLOQUEADO · con `ETAPA2_R2_AUTORIZADA`: aplicada · `--aplicar` como `root`: BLOQUEADO (usuario distinto) |

## 6. Resultados de auditoría (`colegio_tests`, tras aplicar)
| Sección | Hallazgos |
|---|---|
| Sin institución, relaciones entre colegios, huérfanos, coherencia por columnas, catálogos ambiguos | 0 |
| Catálogos | 9 `asignado`, 12 `plantilla` |
| Configuración | 2 advertencias (instituciones 2 y 1003 sin configuración propia: B6) y 3 informativos (nombre/logo duplicados) |
| Asignaciones inconsistentes | 3 informativos, preexistentes (profesor sin la materia en `profesor_materia`) |
| Únicos globales | 5 (cursos, códigos de estudiante y profesor, usuarios): no afectan a la clonación |

## 7. Base real `colegio`
- `--aplicar` y `--revertir` ejecutados contra `colegio` (app real): **BLOQUEADO**, código 1. La guardia actúa antes de leer el archivo; además, el archivo real tiene las 45 decisiones en `null` (`--validar`: 45 errores).
- CHECKSUM EXTENDED de 52 tablas antes y después de los intentos y de la simulación: **idénticos**.
- Sigue con 71 migraciones; las 5 de la Etapa 2 están *pendientes* y bloqueadas por sus guardias. No hay columnas de B2 ni `migracion_pertenencia`.
- Archivo real **pendiente de aprobación** (regenerado en v3, solo lectura): `laravel/storage/app/auditoria/b4-colegio-correspondencia.json`, con huella `650368a9…`, 45 entradas, 6 grupos y **0 decisiones completadas**. El de B3 (v2) queda obsoleto.

**Verificación tras el reinicio (2026-11-19).** El MySQL del sistema había ocupado el 3306 y el usuario restableció el MariaDB de XAMPP. Comprobaciones:
- **Servidor:** por socket y por TCP responde MariaDB 10.4.32 con `datadir /opt/lampp/var/mysql/`, y el MySQL de Ubuntu está inactivo.
- **Referencia perdida:** los checksums de B3 y B4 se habían guardado en la carpeta temporal y se perdieron con el reinicio.
- **`colegio_restauracion_prueba` no sirve de referencia:** contiene los datos de la Etapa 1 (3 instituciones, 12 usuarios). Se había informado erróneamente que estaba en su estado base.
- **Verificación con el respaldo:** con autorización, se restauró el respaldo verificado `colegio_xampp_20261118_100239.sql` (sha256 OK; sin `USE` ni `CREATE DATABASE`) en una base nueva, `colegio_verificacion`. Frente a él, `colegio` tiene **datos idénticos salvo `password_resets`** (la fila del 18-nov 15:30, con el mismo checksum registrado en B3) y **estructura idéntica** (columnas, índices, FK). La carga no alteró `colegio`.
- **Nueva línea base persistente:** `~/Respaldos/sistema-escolar/etapa2-b4b-20261119_084144/colegio_checksums_linea_base.txt` (+ `colegio_linea_base.md`, en `SHA256SUMS`).
- **Al cierre de B4:** `--aplicar` y `--revertir` contra `colegio` → BLOQUEADO (código 1); checksums **iguales a la línea base** antes y después de los intentos.
- `colegio_verificacion` sigue existiendo; su eliminación queda pendiente de autorización.

## 8. Reversión
| Qué | Cómo |
|---|---|
| Una población (`colegio_tests`) | `php artisan institucion:poblar --revertir=<lote>` (ensayado) |
| R2a (`colegio_tests`) | Revertir antes la población; luego `migrate:rollback --step=1` (ensayado) |
| Código (2.ª ronda) | `~/Respaldos/sistema-escolar/etapa2-b4b-20261119_084144/` (tomado antes de modificar archivos; `SHA256SUMS` verificado) |
| Código (1.ª ronda) | Respaldo previo a B4: `~/Respaldos/sistema-escolar/etapa2-b4-20261118_154625/` (`arbol-antes.tgz`, `git-status-antes.txt`, `informes-auditoria-antes.tgz`, `SHA256SUMS` verificados), tomado **antes** de modificar archivos |
| Si una copia ya tiene referencias nuevas | `--revertir` se niega; recuperación por respaldo de la base |
| Interrupción (excepción, `kill -9`, corte de conexión) | Nada que hacer: la transacción no confirmada se revierte y el cerrojo se libera; volver a ejecutar `--aplicar` (ensayado) |

## 9. Riesgos pendientes (antes de cualquier base real)
1. **Orden en la base real:** respaldo verificado → M1–M4 → R2a (B8 anticipado; requiere autorización) → simulación → aprobación del archivo → aplicación. Detener antes el servidor de desarrollo del puerto 8000, que escribe en `colegio`.
2. **El código aún no usa las columnas** (B5–B7): mientras siga `ColegioConfiguracion::singleton()`, asignar o clonar la configuración no cambia lo que se ve. Con la landing clonada, `LandingContenido` por `clave` devolvería una copia arbitraria. **No clonar landing ni configuración en una base en uso antes de B6/B7.**
3. `NULL` en únicos compuestos: duplicados entre plantillas o filas sin poblar no los impide el motor (sí el seeder y la auditoría). Se cerraría en R1 (`NOT NULL` donde corresponda) o con una columna generada.
4. La creación de catálogos nuevos (materias, periodos…) sigue sin asignar `id_institucion` hasta B5; las filas nuevas nacerían en `NULL`.
5. El dump real (Windows) no se ha simulado: requiere su propia simulación, aprobación y ensayo en copia.
6. `--revertir` se niega si se crearon referencias a copias después de aplicar. En producción, entre aplicar y revertir puede haber actividad; la vía de retorno sería el respaldo.
7. Escala: la aplicación es una sola transacción que **bloquea la escritura en 32 tablas** (incluida `usuarios`) mientras dura. En la base real, la app no podría escribir en ellas durante ese tiempo: ventana de mantenimiento obligatoria, y medir la duración en una copia antes de B10.
8. MariaDB de XAMPP: escucha en todas las interfaces (`*:3306`) y `root` no tiene contraseña. Las cuentas `root` solo admiten conexiones locales, pero conviene `bind-address=127.0.0.1` y contraseña. Tras un reinicio, el MySQL del sistema puede volver a ocupar el puerto (servicio habilitado).
9. Los checksums de referencia deben guardarse siempre en `~/Respaldos`, nunca en la carpeta temporal.

## 11. Recomendación técnica para B5
B5 = que el código use la columna propia de los catálogos. Orden sugerido, cada paso con pruebas en ambos motores:
1. **Escritura antes que lectura:** que todo alta de catálogo (materias, áreas, periodos, tipos, categorías no plantilla) asigne `id_institucion = TenantContext::idActual()` en el servidor, nunca desde la petición. Así ningún registro nuevo nace en `NULL` (riesgo 4).
2. **Lectura con conmutación por modelo:** pasar `RUTA_INSTITUCION` a `''` (columna propia) en un modelo cada vez, con una prueba de equivalencia: el mismo listado por relación y por columna debe dar el mismo resultado sobre el escenario poblado.
3. **Validación de unicidad por colegio:** cambiar `Rule::unique` de los Requests de catálogos a `(id_institucion, nombre)` (coherente con R2a) y mostrar 422 ante duplicados del propio colegio.
4. **Plantillas:** las categorías con `id_institucion NULL` deben listarse como globales de solo lectura; ningún colegio puede editarlas ni borrarlas.
5. **Precondición:** B5 solo tiene sentido sobre datos poblados. Probar sobre `colegio_tests` poblada con este mecanismo; no tocar `colegio` hasta B10.
6. **Fuera de B5:** configuración y landing (B6/B7), únicos de códigos (B8), `NOT NULL` (R1).

## 10. Archivos
- **Nuevos:**
  - `app/Modules/Instituciones/Poblacion/{Correspondencia,AplicadorPoblacion,VerificadorPertenencia,RechazoPoblacion}.php`
  - `database/migrations/2026_11_18_100004_etapa2_r2a_unicos_compuestos_para_clonar.php`
  - `tests/Feature/Aislamiento/Etapa2AplicacionPoblacionTest.php`
  - este documento
- **Modificados:**
  - `app/Core/Seguridad/SoloLectura.php` (corrección del COMMIT implícito)
  - `app/Modules/Instituciones/Poblacion/SimuladorPoblacion.php` (huella de datos; estado tras aplicar)
  - `app/Modules/Instituciones/Console/Commands/PoblarPertenenciaCommand.php` (modos `--validar`, `--aplicar`, `--revertir`; grupos)
  - `app/Modules/Instituciones/Auditoria/AuditoriaPertenencia.php` y `AuditarPertenenciaCommand.php` (columnas de B2)
  - `database/seeders/EventoCategoriaSeeder.php` (idempotencia explícita por slug)
  - `tests/Feature/Aislamiento/{Etapa2MigracionesExpansionTest,Etapa2SimulacionPoblacionTest,AuditoriaPertenenciaTest}.php`
  - `docs/seguridad/etapa2-diseno.md` (estado)
- **Informes** (`storage/app/auditoria/`, ignorado por git):
  - `b4-colegio-plan.json` y `b4-colegio-correspondencia.json` (base real, pendiente)
  - `b4-ensayo-*` (ensayo 1) y `b4b-ensayo-*` (ensayo 2: plan, correspondencia, archivo decidido y aprobado ficticio, auditoría)
