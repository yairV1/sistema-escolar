# Etapa 2 — B3: simulación de pertenencia institucional

**Fecha:** 2026-11-18 · **Rama:** `etapa0-contencion` (sin commit) · **Escrituras en bases existentes:** ninguna. **`--aplicar` (B4): no implementado.**

## Qué se implementó

### `php artisan institucion:poblar --simular`
- **Solo lectura impuesta por el motor:** corre dentro de `SoloLectura` (`START TRANSACTION READ ONLY … ROLLBACK` en MariaDB, `PRAGMA query_only` en SQLite). Sin `--simular` termina con error y no hace nada. No existe opción `--aplicar`.
- **No necesita B2:** deduce la institución de cada registro a partir de sus usos. Si la tabla ya tiene `id_institucion` (B2), compara el valor actual con lo inferido. El plan es idéntico con y sin las columnas; probado en `colegio_tests` antes y después de aplicar M1–M4, y en la base real sin B2.
- **Determinista:** el plan lleva una `huella` (SHA-256). Dos ejecuciones sobre los mismos datos dan la misma huella; el archivo de correspondencia la incluye (`huella_plan`) para detectar que los datos cambiaron desde la simulación.
- Opciones: `--json=<nombre>` (plan completo), `--correspondencia=<nombre>` (decisiones pendientes), `--limite=N`. Los únicos archivos que escribe van a `storage/app/auditoria/`, que git ignora.

### Clasificación por registro (16 tablas de B2)
| Situación | Significado | Acción propuesta |
|---|---|---|
| `inequivoca` | Todos sus usos son de una institución (o la base tiene una sola) | `asignar` (automática en B4) |
| `ya_asignado` | Con B2: ya tiene la institución inferida | ninguna |
| `plantilla` | Categoría `es_sistema` o escala de notas | ninguna (queda `NULL`, no se modifica) |
| `compartido` | Usado por varias instituciones | `clonar` (requiere aprobación) |
| `sin_uso_sin_propietario` | Sin uso y varias instituciones | `pendiente` (decisión humana) |
| `decision_manual` | Configuración/landing, o dueño sin institución (p. ej. SuperAdmin) | `decidir` |
| `huerfano` | Referencia a un registro inexistente | `revisar` |
| `relacion_inconsistente` | Evidencia de otra institución, padre en otra institución, o valor B2 distinto | `revisar` |

Reglas de pertenencia:
- **materias:** cursos de sus asignaciones ∪ profesores en `profesor_materia`.
- **áreas:** las de sus materias.
- **periodos:** actividades y boletines.
- **tipos:** actividades.
- **categorías:** creadores de eventos.
- **escala:** plantilla.
- **perfiles, eventos y soportes:** el usuario dueño, verificado contra matrículas, asignaciones, dirección de grupo, acudidos y el curso o asignación del evento.
- **configuración y landing:** **nunca** se asignan automáticamente.

En ningún caso se asigna una institución por defecto.

### Simulación de clonaciones (no crea nada)
Para cada registro compartido el plan calcula:
- el original;
- las instituciones que lo usan;
- las copias necesarias (n − 1: el original se queda con **una** institución, que decide el propietario en `conserva_original`);
- las filas a repuntar por institución, con tabla, columna e id;
- el mínimo y el máximo de repuntes, según qué institución conserve el original;
- las referencias sin institución determinable;
- las relaciones padre-hijo (`id_materia_padre`, `id_area`, configuración → imágenes);
- los conflictos con únicos globales;
- los riesgos por `ON DELETE CASCADE`.

### Archivo de correspondencia (`correspondencia-institucional/v2`)
```json
{
  "formato": "correspondencia-institucional/v2",
  "base": "colegio",
  "huella_plan": "37145e7e…",
  "estado": "PENDIENTE DE APROBACIÓN",
  "instituciones": [{"id": 1, "slug": "colegio-san-cristobal", "nombre": "Colegio San Cristóbal"}, …],
  "asignaciones_automaticas": 0,
  "decisiones": [{
    "tabla": "evento_categorias", "id": 5, "descripcion": "Reuniones",
    "situacion": "sin_uso_sin_propietario", "regla": "sin_uso_varias_instituciones",
    "instituciones_candidatas": [], "accion_propuesta": "pendiente",
    "opciones": ["asignar", "plantilla", "clonar", "pendiente", "descartar"],
    "decision": null, "institucion_destino": null, "instituciones_clon": [], "conserva_original": null,
    "justificacion": "Sin uso y hay varias instituciones: …",
    "dependencias": [], "observaciones": [], "clonacion": null
  }]
}
```
Contiene **solo** lo que requiere decisión humana; todas las decisiones se generan en `null`. Las asignaciones automáticas quedan en el plan JSON (y su conteo en el archivo). `seguir_configuracion` existe solo para imágenes: van con su configuración.

### Protección adicional: M1–M4 no se aplican por accidente
`App\Core\Seguridad\GuardiaMigracionesEtapa2`, invocada al inicio de `up()` en las 4 migraciones de B2. Permite aplicarlas en dos casos:
- la conexión efectiva es la base de pruebas autorizada;
- la variable de entorno `ETAPA2_MIGRACIONES_AUTORIZADAS` coincide **exactamente** con `SELECT DATABASE()`, por ejemplo `ETAPA2_MIGRACIONES_AUTORIZADAS=colegio php artisan migrate`, y solo con autorización de B10.

`--pretend` sigue permitido, porque no ejecuta DDL. La variable no debe dejarse en `.env`, porque convertiría la autorización en permanente.

Probada en `colegio_tests` conectando como `root` (conexión no autorizada), con M1–M4 pendientes:

| Caso | Resultado |
|---|---|
| Sin la variable | **BLOQUEADO**, 0 cambios |
| Variable con otro nombre (`colegio`) | **BLOQUEADO** |
| Variable con el nombre exacto | Aplicada |
| `colegio_tests` con su usuario | Funciona igual que antes |

## Resultados

### Base de pruebas `colegio_tests` (dos colegios ficticios + catálogos compartidos)
Instituciones 1, 2 y 1003 (colegio de prueba B); 67 registros analizados.

| Tabla | Total | Asignar | Plantilla | Compartido (copias) | Sin dueño | Manual |
|---|---|---|---|---|---|---|
| materias | 2 | 1 | 0 | 1 (1) | 0 | 0 |
| periodos_academicos | 1 | 0 | 0 | 1 (1) | 0 | 0 |
| tipos_actividad | 1 | 0 | 0 | 1 (1) | 0 | 0 |
| escala_notas | 4 | 0 | 4 | 0 | 0 | 0 |
| evento_categorias | 9 | 0 | 2 | 1 (1) | 6 | 0 |
| estudiantes / profesores / acudientes | 4 / 3 / 1 | 4 / 3 / 1 | 0 | 0 | 0 | 0 |
| eventos / soportes | 2 / 2 | 2 / 2 | 0 | 0 | 0 | 0 |
| configuración + imágenes + landing | 38 | 0 | 0 | 0 | 0 | 38 |

**Totales:** 13 asignables automáticamente · 4 a clonar (4 copias) · 48 decisiones (4 clonaciones + 6 categorías + 38 de configuración y landing) · 0 huérfanos · 0 inconsistentes.

Ejemplo de clonación del periodo compartido:
- Institución 1: 3 referencias (actividades 1001 y 1002, boletín 1001).
- Institución 1003: 2 referencias (actividad 1003, boletín 1002).
- Repuntes: 2 si conserva el original la institución 1, 3 si lo conserva la 1003.
- Riesgos: el único global `(nombre_periodo, anio_lectivo)` choca con la copia, y `actividades`/`boletines` tienen `ON DELETE CASCADE`.

### Base real `colegio` (solo lectura; B2 **no** aplicada)
Instituciones: 1 `colegio-san-cristobal`, 2 `Privada` (Parroquila). Huella del plan: `37145e7ed80597d802544e8f83248b5781648ce1d43cc49ccf32fce739a1c92c`.

| Tabla | Registros | Resultado |
|---|---|---|
| materias, áreas, periodos, tipos, estudiantes, profesores, acudientes, eventos, soportes | 0 | — (la base real no tiene datos académicos) |
| escala_notas | 4 | plantilla (Bajo, Básico, Alto, Superior) |
| evento_categorias | 9 | 2 plantillas de sistema (Clases, Actividades) · **7 sin uso y sin dueño** |
| colegio_configuracion | 1 | decisión manual (compartida por 2) |
| colegio_imagenes | 4 | decisión manual (siguen a la configuración 1) |
| landing_contenido | 24 | decisión manual |
| landing_galeria | 6 | decisión manual |
| landing_noticias | 3 | decisión manual |

- **Asignables automáticamente:** 0.
- **Clonaciones necesarias hoy:** 0.
- **Decisiones manuales:** 45 (7 categorías + 38 de configuración y landing).
- **Huérfanos y relaciones cruzadas:** 0.

**Categorías del calendario (las 7 de B1):** Comunicados (2), Personal (4), Reuniones (5), Eventos Institucionales (6), Evaluaciones (7), Recordatorios (8), Festivos (9). Ninguna tiene uso. Opciones para cada una:
- `asignar` a una institución;
- `plantilla`: global, de solo lectura; el diseño lo permite para `evento_categorias`;
- `clonar` para ambas;
- `pendiente`;
- `descartar`: se excluye de la migración, sin borrarla.

Las categorías de sistema (Clases, Actividades) no se tocan.

**Configuración y landing:**
- Hay una sola configuración, compartida por 2 instituciones (crítico).
- Hay una sola landing de 24 claves, con 6 fotos de galería, 3 noticias y 4 imágenes, sin institución (crítico).
- El nombre y el logo de `instituciones` difieren de los de `colegio_configuracion` en ambas instituciones, así que hay dos fuentes de verdad.
- No se decide a quién pertenece la configuración actual.

**Dependencias de `ColegioConfiguracion::singleton()`:**
- 7 directas: `AppServiceProvider` (la comparte con todas las vistas), los mailables `RecuperarPasswordMail`, `RecordatorioEventoMail` y `ComunicadoMail`, `IcsExportService`, `ConfiguracionColegioController` y `errors/404`.
- 7 vistas indirectas (usan `$colegioConfiguracion`): los layouts `auth`, `panel`, `rector` y `docente`, `website/index`, el dashboard del rector y `errors/404`.

**Conflictos de integridad posibles:** 8 únicos globales impiden clonar o duplicar por institución:
- `areas(nombre_area)`, `materias(nombre_materia)` y `tipos_actividad(nombre_tipo)`;
- `periodos_academicos(nombre_periodo, anio_lectivo)`;
- `evento_categorias(slug)` y `landing_contenido(clave)`;
- `estudiantes(codigo_estudiante)` y `profesores(codigo_profesor)`.

Clonar categorías o la landing para las dos instituciones viola `slug` y `clave` mientras no se aplique R2.

**Archivo de correspondencia pendiente de aprobación:** `laravel/storage/app/auditoria/b3-colegio-correspondencia.json` (45 entradas, todas con `decision: null`). Plan completo: `b3-colegio-plan.json`.

## Pruebas
Nueva: `tests/Feature/Aislamiento/Etapa2SimulacionPoblacionTest.php` (13 pruebas; una del grupo `mariadb`).

| # | Requisito | Prueba |
|---|---|---|
| 1, 2 | Ninguna escritura ni cambio de relaciones | Huellas de 17 tablas antes y después, más el registro de todas las consultas: 0 `INSERT/UPDATE/DELETE/ALTER/CREATE/DROP` |
| 3 | Compartidos | materia, periodo y tipo → `compartido` con ambas instituciones, sin institución propuesta |
| 4 | Ambiguos | categorías sin uso → `sin_uso_sin_propietario`; de sistema → `plantilla`; configuración y landing → manual; evento del SuperAdmin → manual, sin asignar |
| 5 | Relaciones cruzadas | matrícula de un estudiante de B en un curso de A → estudiante `relacion_inconsistente` |
| 6 | Referencias a repuntar | ids exactos de actividades y boletines por institución, mínimo de repuntes y conflicto de único |
| 7 | Determinismo | misma huella |
| 8 | Con B2 | valor que coincide → `ya_asignado`; valor contradictorio → `relacion_inconsistente` |
| 9 | Sin B2 | plan idéntico salvo `esquema` |
| — | Correspondencia | campos requeridos y todas las decisiones en `null` |
| — | Sin `--simular` | código 1, sin cambios |
| — | Dependencias del singleton | sin comentarios ni archivos propios |
| — | Huérfano (MariaDB) | perfil cuyo usuario no existe → `huerfano` |
| — | Guardia de M1–M4 | conexión SQLite en archivo → BLOQUEADO |

| Suite | Antes de B3 | Después |
|---|---|---|
| SQLite | 202 | **214 / 214** |
| MariaDB `colegio_tests` | 214 | **227 / 227** |

Ensayos manuales en `colegio_tests` (datos de dos colegios, estado previo a B2 idéntico a la instantánea de B2):
- Simulación sin B2.
- `migrate` (B2) y nueva simulación: plan y correspondencia idénticos.
- Instantánea de esquema y datos antes y después de dos simulaciones: **0 diferencias**, misma huella.

## Base real `colegio`
- Antes y después de cada simulación (CHECKSUM EXTENDED de 52 tablas): **idénticos**.
- Sigue con 71 migraciones, sin columnas de B2 y sin `migracion_pertenencia`.
- Respecto del inicio de B3 cambió **una** tabla, `password_resets`, con una fila. No la causó B3: proviene de una solicitud `POST /forgot-password` al servidor de desarrollo del puerto 8000 a las 15:30:54. La fila expira a las 16:00:54, es decir, se creó en ese instante, después del checksum inicial (15:29:06) y antes de la simulación. La simulación, enmarcada con checksums inmediatamente antes y después, no cambió nada.

## Riesgos para B4
1. **Orden obligatorio:**
   - **R2 (únicos compuestos) antes de cualquier clonación.** Con los únicos globales actuales, clonar materias, periodos, tipos, categorías o claves de la landing falla (o exigiría renombrar).
   - Asignar sin clonar no tiene ese problema.
2. **Configuración y landing:** hay 38 decisiones; clonarlas para dos colegios choca con `landing_contenido(clave)`. Antes de B4 conviene decidir **por grupo** (toda la landing → una institución) y diseñar B6/B7. B4 debería aceptar decisiones por grupo para no exigir 38 entradas iguales.
3. **Mientras siga `singleton()`**, asignar la configuración a una institución no cambia lo que ven las demás: las 14 dependencias deben migrarse en B6 o B7 antes de que la asignación tenga efecto.
4. **`ON DELETE CASCADE`** en asignaciones, actividades, boletines, `profesor_materia` y `colegio_imagenes`: B4 nunca debe borrar un original sin repuntar antes todas sus referencias, y debe hacerlo en una sola transacción. Las DML de MariaDB sí son transaccionales; el DDL no.
5. **La huella del plan debe coincidir** al aplicar: si los datos cambian entre la aprobación y B4 (por ejemplo, alguien usa la app, como ocurrió con `password_resets`), el plan aprobado ya no es válido. B4 debe negarse si la huella difiere.
6. Los datos de la base real son mínimos; el dump de Windows (con datos académicos reales) no se ha simulado. B4 sobre ese dump requeriría su propia simulación y aprobación.
7. `institucion:auditar` (B1) todavía no lee las columnas de B2; el simulador sí lo hace.
8. Servidor de desarrollo activo contra `colegio` en el puerto 8000: escribe en la base real (sesiones, `password_resets`). Conviene detenerlo durante B4 y B10.

## Archivos
- **Nuevos:**
  - `app/Modules/Instituciones/Poblacion/SimuladorPoblacion.php`
  - `app/Modules/Instituciones/Console/Commands/PoblarPertenenciaCommand.php`
  - `app/Core/Seguridad/GuardiaMigracionesEtapa2.php`
  - `tests/Feature/Aislamiento/Etapa2SimulacionPoblacionTest.php`
  - este documento
- **Modificados:**
  - `bootstrap/app.php`: registro del comando, 1 línea.
  - Las 4 migraciones de B2: llamada a la guardia al inicio de `up()` e `import DB`. Su DDL no cambió.
  - `docs/seguridad/etapa2-diseno.md`: estado.
- **Informes** (`storage/app/auditoria/`, ignorado por git): `b3-colegio-plan.json`, `b3-colegio-correspondencia.json`, `b3-tests-{sin,con}-b2.json`, `b3-tests-correspondencia-{sin,con}-b2.json`.

## Reversión
Borrar los archivos nuevos, quitar la línea de `bootstrap/app.php` y la llamada a la guardia en las 4 migraciones. B3 no escribió en ninguna base existente, así que no hay nada que revertir en datos.

Respaldo: `~/Respaldos/sistema-escolar/etapa2-b3-20261118_153346/` (`arbol.tgz`, `git-status.txt`, `bootstrap-app.php.antes-b3`, `SHA256SUMS`). Se tomó **durante** B3, no antes: ya contenía las primeras versiones de `SimuladorPoblacion.php` y `PoblarPertenenciaCommand.php` y la línea nueva de `bootstrap/app.php`, pero aún no la guardia ni su llamada en las migraciones. Para reconstruir el estado previo a B3: extraer `arbol.tgz`, sustituir `laravel/bootstrap/app.php` por `bootstrap-app.php.antes-b3` y eliminar esos dos archivos.
