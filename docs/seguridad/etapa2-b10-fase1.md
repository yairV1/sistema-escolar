# Etapa 2 — B10 Fase 1: validación integral y preparación

**Fecha:** 2026-11-19 · **Rama:** `etapa0-contencion` · **Estado:** Fase 1 documentada; Fase 2 no autorizada. Sin commit, push ni merge.

## Alcance y límites

Se validaron el código y los entornos aislados existentes. La conexión real confirmada fue MariaDB 10.4.32, con `datadir` `/opt/lampp/var/mysql/`; `colegio` tenía `@@read_only=0`. Las consultas autorizadas sobre `colegio` se ejecutaron con `SoloLectura` y se revirtieron. No se ejecutó DDL/DML, no se cambió ninguna credencial y no se movió ni eliminó ningún archivo real.

La comprobación histórica de checksums halló una diferencia en `usuarios`: **51 de 52 checksums coinciden** con la línea base. La comparación adicional se limitó a conteos de igualdad de `password`, `sesion_valida_desde` y `ultimo_acceso`, sin devolver ni imprimir valores, identificadores de usuario o hashes; las claves solo se usaron para emparejar las filas. Las dos filas tienen claves coincidentes y no hay filas faltantes o adicionales; una fila difiere en cada uno de esos tres campos. `colegio_verificacion` y `colegio_b10_restore_20261119_143439`, ambas restauradas del mismo respaldo, coinciden en los tres campos.

La atribución está documentada en `docs/seguridad/incidente-superadmin-20261119.md`: a las 10:04:53 del 19-nov se registró una escritura directa que cambió `password` y `sesion_valida_desde`; después, el propietario cambió la contraseña desde el perfil. Los inicios de sesión del propietario a las 10:50:38 y 10:57:19 explican el cambio de `ultimo_acceso`. La escritura directa se atribuye al agente en el historial del incidente. No se leyó el valor actual de la contraseña ni se comprobó su hash. MariaDB no tenía log binario ni general; el log de errores no estaba disponible para esta revisión, por lo que la cronología depende de las evidencias del incidente y no de un registro completo del motor. No se modificó ni reemplazó ninguna línea base.

## 1. Auditoría B0–B9 y pruebas

Los dos colegios de prueba se mantuvieron ficticios. Las suites existentes ejercitan autenticación y roles; administración central y alta transaccional; configuración por institución; sitios y admisiones por slug; aislamiento de registros académicos y personales; y entrega de archivos. Se ejecutaron los flujos HTTP de sitio/admisiones, administración institucional, configuración y archivos privados dentro de sus pruebas de integración. No se ejecutó una única macroprueba que recorra cada módulo académico de extremo a extremo; la cobertura de esos módulos corresponde a sus pruebas de regresión existentes.

| Ejecución | Resultado |
|---|---:|
| Suite completa SQLite, después del ajuste de slug | 289 pruebas, 2.034 aserciones, aprobadas |
| Suite completa MariaDB `colegio_tests`, después del ajuste de slug | 303 pruebas, 1.930 aserciones; 10 omitidas, sin fallos |
| HTTP sitio/admisiones (`Etapa2B7SitioPublicoTest`), SQLite / MariaDB | 9 pruebas, 113 aserciones por motor, aprobadas |
| HTTP archivos privados (`ArchivosPrivadosTest`), SQLite / MariaDB | 4 pruebas, 25 aserciones por motor, aprobadas |
| B8 restricciones, SQLite / MariaDB | 3 pruebas por motor; 55 / 59 aserciones, aprobadas |
| HTTP aprovisionamiento SuperAdmin, SQLite / MariaDB | 5 pruebas, 37 aserciones por motor, aprobadas |
| Pint sobre los dos archivos PHP del ajuste | Aprobado |

Antes de las pruebas MariaDB se respaldó `colegio_tests`; al terminar se restauró ese estado. La restauración dejó 56 tablas y pasó `mysqlcheck`.

El ajuste de slug es solo de código: `/sitio/Privada` acepta el valor legado exacto si no hay colisión normalizada; `/sitio/privada` no se trata como alias y el slug almacenado no se modifica. No se cambió el contenido de `colegio`.

## 2. Integridad MariaDB y FK de usuarios

La migración histórica `2026_07_25_090001_add_institucion_to_usuarios_table` retorna temprano si ya existe `usuarios.id_institucion`. En un esquema parcialmente aplicado, por tanto, la columna puede existir sin que se creen el índice ni la FK.

En la estructura examinada, `usuarios.id_institucion` y `instituciones.id_institucion` son `BIGINT(20) UNSIGNED`, por lo que sus tipos son compatibles. La primera columna permite `NULL`, no tenía índice y no tenía FK. Había un usuario sin institución —válido para el SuperAdmin— y cero referencias huérfanas. La nulabilidad no se debe eliminar.

Se añadió una reparación reanudable a la migración B8 para crear el índice y una FK `ON DELETE RESTRICT` si faltan, conservando `NULL`. En MariaDB se comprobó la FK consultando el esquema y verificando que una referencia institucional inexistente es rechazada por el motor. SQLite omite la reconstrucción de `usuarios`: hacerlo puede romper las muchas FK dependientes; sus pruebas no sustituyen la comprobación MariaDB. No se reportó otra tabla afectada por esta omisión específica.

La reparación solo se probó en `colegio_tests`; no se aplicó a `colegio`.

## 3. Archivos públicos y privados

Clasificación funcional:

| Tipo | Clasificación / control |
|---|---|
| Logos institucionales y medios destinados a landings/galerías | Públicos únicamente cuando su contenido fue aprobado para publicación. Cinco SVG genéricos inventariados en `storage/app/public/colegio`; no se hallaron archivos personales en esa carpeta. |
| Fotografías de perfil | Privadas; cargas nuevas fuera del disco público y entrega autenticada con comprobación institucional. |
| Capturas de soporte | Privadas; acceso restringido al administrador de la institución correspondiente o SuperAdmin. |
| Adjuntos de calendario | Privados; entrega sujeta a la autorización del evento y a aislamiento institucional. |
| Documentos académicos, como PDF de boletines | Se generan y entregan por rutas autenticadas y con aislamiento institucional. |
| Documentos personales de estudiantes/familias | No se identificó otro flujo de carga activo en el inventario revisado; requiere inventario adicional antes de producción. |

Los registros heredados de perfiles, soporte y eventos no se movieron ni borraron. Sus prefijos `/storage/perfiles`, `/storage/soporte` y `/storage/eventos` quedan bloqueados para acceso estático en Apache; una prueba HTTP con fixtures obtuvo 403 para un archivo heredado sensible y 200 para un medio institucional público. Las rutas privadas nuevas usan controladores autenticados. Filtrar la fila de base de datos no protege por sí solo una URL directa.

**Bloqueo de despliegue:** Nginx no interpreta `.htaccess`. Antes de producción hay que denegar esos prefijos en la configuración Nginx antes del servicio estático, o completar una transición controlada a almacenamiento privado. No se verificó una regla Nginx en este entorno.

## 4. Auditoría de datos reales y decisiones humanas

La base real registra 71 migraciones y no tiene aplicadas las migraciones B2–B9 de Etapa 2. El preflight de pertenencia no encontró cruces ni huérfanos reportados, pero quedan **45 decisiones humanas sin resolver**, todas con `decision: null`:

| Grupo | Pendientes |
|---|---:|
| `evento_categorias` sin uso | 7 |
| `colegio_configuracion` compartida | 1 |
| `colegio_imagenes` | 4 |
| `landing_contenido` | 24 |
| `landing_galeria` | 6 |
| `landing_noticias` | 3 |
| **Total** | **45** |

El inventario también reportó 6 plantillas globales y 2 hallazgos críticos de configuración. No se asignó ningún propietario ni se modificó el archivo de correspondencia.

El slug real `Privada` normaliza a `privada`; la auditoría no encontró colisión con otro slug normalizado. Se conservó sin cambios. El código ahora admite el enlace legado exacto `/sitio/Privada` cuando la comprobación de colisión sigue limpia. No se renombró el registro ni se decidió normalizarlo en la base.

Los datos de landing/configuración no se atribuyen automáticamente. El preflight detectó las decisiones pendientes antes de cualquier DDL; por ello, no se ejecutó B2–B8 sobre la base real ni sobre la restauración.

## 5. Respaldo y restauración

- Respaldo de origen: `/home/yair/Respaldos/sistema-escolar/colegio_xampp_20261118_100239.sql`, acompañado de `.sha256`. Se verificó el checksum antes de restaurar.
- Destino nuevo, sin sobrescribir otra base: `colegio_b10_restore_20261119_143439`.
- Resultado: 55 tablas, 471 filas, 60 FK, cero referencias huérfanas; `CHECK TABLE` no reportó errores. Los conteos coinciden con `colegio_verificacion`.
- La restauración y sus verificaciones se realizaron sobre la base desechable. La base restaurada se conserva; no se borraron bases de trabajo ni respaldos.

### Recuperación preparada

1. Antes de cualquier operación futura, crear un respaldo nuevo con nombre único; no sobrescribir el respaldo original. Verificar el checksum y comprobar que el dump puede restaurarse en una base desechable.
2. Restaurar siempre a un nombre nuevo. Comparar tablas, conteos, FK, huérfanos y `CHECK TABLE` antes de considerarlo utilizable.
3. En una aplicación futura, separar DDL de DML. MariaDB puede confirmar DDL implícitamente: ante error, detenerse, conservar logs y respaldo, inspeccionar el estado real de cada columna/índice/FK y usar la reanudación explícita de la migración; no asumir rollback automático.
4. Si el resultado no es confiable, no sobrescribir `colegio` con una restauración improvisada. Recuperar primero en otra base nueva y exigir autorización y procedimiento operativo específico para cualquier sustitución.

## 6. Simulación de migración

Se inició el preflight sobre datos aislados y se aplicó la condición de parada: 45 decisiones de pertenencia permanecían nulas. **No se ejecutó DDL ni DML de B2–B8 sobre la restauración**, ni se habilitó el aprovisionamiento B9 allí. Por lo tanto, esta Fase 1 no certifica el esquema posterior a migraciones sobre una copia completa de los datos reales; certifica que el proceso se detiene antes de transformar datos ambiguos.

Las migraciones y restricciones se probaron por separado en SQLite y en `colegio_tests`, incluidos los FK reales de MariaDB. Esas pruebas con fixtures ficticios no sustituyen una simulación secuencial sobre una copia real después de aprobar las 45 decisiones.

## 7. Archivos creados o modificados en B10

**Nuevos:** `laravel/app/Core/Archivos/ArchivoPrivado.php`, `laravel/tests/Feature/Aislamiento/ArchivosPrivadosTest.php`.

**Modificados:** `laravel/config/filesystems.php`; `laravel/app/Modules/Auth/Models/Usuario.php`; `laravel/app/Modules/Perfil/Controllers/PerfilController.php`; `laravel/app/Modules/Soporte/Models/Soporte.php`; `laravel/app/Modules/Soporte/Controllers/SoporteController.php`; `laravel/app/Modules/Calendario/Services/EventoService.php`; `laravel/app/Modules/Calendario/Controllers/EventoController.php`; `laravel/app/Modules/Calendario/Controllers/EventoAdjuntoController.php`; `laravel/routes/web.php`; `laravel/public/.htaccess`; `laravel/database/migrations/etapa2/b8/2026_11_19_120000_etapa2_b8_restricciones_institucionales.php`; `laravel/tests/Feature/Aislamiento/Etapa2B8RestriccionesTest.php`; `laravel/app/Modules/Landing/Support/SitioInstitucional.php`; `laravel/tests/Feature/Aislamiento/Etapa2B7SitioPublicoTest.php`.

El último par preserva el enlace legado de `Privada` sin normalizar datos reales. Se conservaron los cambios B0–B9 existentes. No se movieron ni eliminaron archivos públicos reales.

## 8. Bloqueos y requisitos exactos para autorizar Fase 2

Fase 2 no debe comenzar hasta que se cumpla y se autorice explícitamente todo lo siguiente:

1. Resolver y aprobar individualmente las 45 decisiones de pertenencia en un archivo de correspondencia actualizado; generar y verificar una nueva huella del plan. No inferir propietarios automáticamente.
2. Dejar constancia de la atribución documentada de la discrepancia de `usuarios` y obtener del responsable confirmación del estado esperado de la cuenta por un canal autorizado. No leer ni cambiar contraseñas para hacer coincidir checksums, y no reemplazar la línea base histórica.
3. Decidir si se conserva `Privada` o se autoriza su normalización, y revisar enlaces existentes. El código preserva el enlace exacto hoy; no autoriza una modificación del dato.
4. Aplicar primero la regla Nginx para impedir acceso estático directo a prefijos privados, o aprobar una transición equivalente; inventariar archivos históricos y huérfanos sin borrarlos.
5. Hacer una restauración nueva y ejecutar el ensayo secuencial autorizado de B2–B8 y del gate B9, con las decisiones ya completas. Verificar columnas, tipos, índices, FK mediante intentos inválidos, unicidad, conteos, huérfanos, relaciones cruzadas, dos colegios y rollback/recuperación.
6. Confirmar el respaldo, checksum, espacio, ventana de mantenimiento, plan de recuperación y responsable operativo de cualquier despliegue. Los DDL MariaDB no son reversibles automáticamente en todos los casos.
7. Recibir autorización separada y explícita que identifique la base objetivo, huella aprobada, operaciones permitidas y límites. La autorización de esta Fase 1 no habilita migraciones reales.

**Estado final:** Fase 1 terminada; `colegio` no recibió escrituras ni migraciones durante esta revisión. La diferencia respecto de la línea base histórica está explicada por los cambios documentados, pero la contraseña actual no se inspeccionó y requiere confirmación del responsable. No se inició Fase 2.
