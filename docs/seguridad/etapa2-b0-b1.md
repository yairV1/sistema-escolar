# Etapa 2 — B0 (entorno de pruebas) y B1 (auditoría institucional)

**Fecha:** 2026-11-18 · **Rama:** `etapa0-contencion` (sin commit) · **Migraciones sobre bases existentes:** ninguna.

## B0 — Entorno de pruebas

### Dos motores
| Suite | Comando | Base | Qué ejecuta |
|---|---|---|---|
| SQLite | `php artisan test` | `:memory:` | Todo excepto `#[Group('mariadb')]` |
| MariaDB | `php artisan test --configuration=phpunit.mariadb.xml` | `colegio_tests` | Todo |

Grupo `mariadb`: pruebas que tocan SQL propio de MySQL/MariaDB — `FIELD()` (Dashboard, horarios, detalle de curso), `DATE_FORMAT` (panel SuperAdmin) — o que necesitan desactivar FK dentro de una transacción.

### Base `colegio_tests`
- Creada tras verificar que no existía ni la base ni el usuario y que ningún `.env` de otros proyectos usa el nombre.
- Usuario `colegio_tests@localhost` y `@127.0.0.1`: `USAGE` global (ningún privilegio global) y todos los privilegios **solo** sobre `colegio_tests.*`. Verificado: `ERROR 1044 Access denied` al intentar abrir `colegio`.
- Contraseña aleatoria **solo** en `laravel/.env.testing` (permisos 600, ignorado por git; añadido a `laravel/.gitignore`). `phpunit.mariadb.xml` no contiene credenciales.
- `.env.testing` tiene una `APP_KEY` propia de pruebas (no la de la aplicación).
- Nota: XAMPP expone por defecto la base `test` a cualquier usuario; no se usa.

### Protección contra operaciones destructivas
`App\Core\Seguridad\GuardiaBaseDePruebas` verifica la **conexión efectiva**, no variables de entorno:
- SQLite: `PRAGMA database_list` → la base principal debe ser en memoria.
- MariaDB: `SELECT DATABASE()` = `colegio_tests` **y** `CURRENT_USER()` = `colegio_tests`.
- Si no puede verificarse: no autorizada (falla cerrado).

Se aplica en dos puntos:
1. `tests/TestCase.php::setUpTraits()` — antes de que `RefreshDatabase` / `DatabaseMigrations` / `DatabaseTruncation` actúen.
2. `AppServiceProvider` (consola) — prohíbe `migrate:fresh`, `migrate:refresh`, `migrate:reset` y `db:wipe` si la conexión no está autorizada. `migrate:rollback` queda permitido (vía de reversión de las migraciones de expansión).

Verificado sin ejecutar nada destructivo: con la conexión de la aplicación (`colegio`) los cuatro comandos quedan prohibidos; pruebas automáticas confirman el rechazo de SQLite en archivo, de conexiones no verificables y de una conexión a `colegio` aunque use el usuario de pruebas.

### Pruebas automatizadas
- 20 factories (`database/factories`) usadas con `XFactory::new()` (los modelos no se modificaron).
- `Tests\Support\EscenarioDosColegios`: dos colegios ficticios con datos en todos los módulos y marcadores únicos.
- Port completo de las matrices: Etapa 0 (43 casos) y Etapa 1 (142 casos: URL 48, escrituras 17, formularios/exportaciones 13, listados 54, registro/asignación masiva/SuperAdmin 10). Los 142 casos se cubren con 141 pruebas (las 3 pantallas del SuperAdmin van en una) + 1 prueba extra de asignación masiva a nivel de modelo.
- 3 pruebas preexistentes corregidas (desactualizadas: faltaba el rector, el perfil de profesor, y una instancia en memoria obsoleta).

| Suite | Resultado |
|---|---|
| SQLite | **196 / 196** |
| MariaDB `colegio_tests` | **208 / 208** |

## B1 — `php artisan institucion:auditar`

Solo lectura **impuesta por el motor** (`App\Core\Seguridad\SoloLectura`): `START TRANSACTION READ ONLY … ROLLBACK` en MariaDB, `PRAGMA query_only` en SQLite. Únicos archivos que escribe: los informes pedidos, en `storage/app/auditoria/` (ignorado por git).

Secciones: registros sin institución · relaciones entre colegios (11 cruces) · catálogos compartidos/resolubles/ambiguos/plantilla · asignaciones inconsistentes · referencias huérfanas (30 relaciones) · configuración duplicada o compartida · restricciones únicas incompatibles.

Opciones: `--json=<nombre>`, `--plantilla-correspondencia=<nombre>`, `--limite=N`, `--estricto` (código 1 si hay críticos).

### Archivo de correspondencia (`correspondencia-institucional/v1`)
```json
{
  "formato": "correspondencia-institucional/v1",
  "generado": "2026-11-18T15:00:00-05:00",
  "base": "colegio",
  "instrucciones": "…",
  "instituciones": [{ "id": 1, "slug": "colegio-san-cristobal", "nombre": "Colegio San Cristóbal" }],
  "decisiones": [
    {
      "tabla": "evento_categorias", "id": 5, "descripcion": "Reuniones",
      "motivo": "sin_uso_ambiguo",
      "instituciones_detectadas": [],
      "decision": null,
      "institucion_destino": null,
      "instituciones_clon": []
    }
  ]
}
```
`decision`: `asignar` (con `institucion_destino` = slug) · `clonar` (con `instituciones_clon` = [slugs]) · `plantilla` · `descartar`. **El comando genera todas las decisiones en `null`; ninguna se aplica mientras siga en `null`.** Lo aplicará `institucion:poblar` (B3/B4, no implementado).

### Hallazgos sobre la base real `colegio` (checksums idénticos antes/después en las 52 tablas)
- 2 instituciones: San Cristóbal (1) y Parroquila (2). Sin registros sin institución, sin cruces, sin huérfanos, sin asignaciones inconsistentes.
- **Crítico:** una sola `colegio_configuracion` y una sola landing (24 claves, galería, noticias) compartidas por las 2 instituciones.
- 7 categorías de calendario no sistema **sin uso y ambiguas** (hay 2 instituciones): requieren decisión.
- 6 plantillas (4 niveles de escala + 2 categorías de sistema).
- 9 restricciones únicas globales incompatibles; `usuarios(correo, numero_documento)` se conservan.
- Informativo: nombre/logo distintos entre `instituciones` y `colegio_configuracion`.

En la copia de prueba con datos de dos colegios, además: materia, periodo y tipo de actividad **compartidos** (requieren clonación) y 3 asignaciones cuyo profesor no tiene la materia registrada en `profesor_materia`.

## Reversión (B0 + B1)
Respaldo: `~/Respaldos/sistema-escolar/etapa2-b0b1-20261118_144942/` (árbol completo + `SHA256SUMS`). Para quitar el entorno de pruebas de MariaDB (operación manual, requiere autorización):
```sql
DROP DATABASE colegio_tests;
DROP USER 'colegio_tests'@'localhost'; DROP USER 'colegio_tests'@'127.0.0.1';
```
y borrar `laravel/.env.testing`.
