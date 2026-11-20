# Etapa 2 — B9: administración central de colegios

**Fecha:** 2026-11-19 · **Rama:** `etapa0-contencion` · **Estado:** implementado y probado en SQLite en memoria y MariaDB `colegio_tests`. Sin commit, push ni merge. No se inició B10.

## Alcance y aislamiento

- Se conservó el árbol sin commit existente de B0–B8.
- Antes de editar B9 se respaldaron el diff rastreado, 88 archivos no rastreados y las 56 tablas de `colegio_tests`. El dump de prueba tiene SHA-256 `100b3cc796d04b85d66676b44cf1f6897fe46daebc0005b36f84e6a0b5fb749a`; se verificó con `gzip -t` y se restauró después de las suites MariaDB.
- La suite MariaDB apunta explícitamente a `colegio_tests`. No se consultó ni modificó `colegio`; no se ejecutaron migraciones de B2–B8 ni DDL en esa base.
- No se movieron ni eliminaron archivos públicos existentes.

## Funcionalidades implementadas

- El panel lista, crea y edita colegios; permite activarlos y suspenderlos de forma controlada. No ofrece eliminación, para evitar borrar colegios con datos.
- El alta crea en una única transacción la institución, una fila propia de `colegio_configuracion`, un usuario inicial con rol Administrador (`id_rol=1`) y las entradas de auditoría. Un fallo en cualquiera de esos pasos revierte todas las escrituras.
- La configuración nueva se inicializa con los datos básicos del colegio. No se copian ni asignan landing, galerías, noticias, categorías u otros contenidos compartidos.
- El administrador inicial recibe un hash de contraseña aleatoria que no se muestra, registra ni devuelve. No se envía una contraseña temporal; debe establecer la suya usando el flujo existente de recuperación de acceso.
- La asignación usa únicamente el ID de la institución recién creada; `id_institucion` no es un campo aceptado para asignar el usuario desde el formulario.
- Crear, asignar administrador, editar y cambiar estado generan auditoría dentro de la transacción correspondiente. La entrada de asignación registra IDs y rol, no nombres, correo, documento ni contraseña.
- La ficha central muestra datos institucionales y métricas agregadas; ya no consulta ni lista usuarios, correos, roles o datos académicos. El formulario de edición tampoco lee o actualiza la cuenta del administrador.
- La ficha ofrece `/sitio/{slug}` y `/sitio/{slug}/ingresar` como enlaces solo cuando el colegio está activo y su slug es canónico. Para slugs legados no canónicos, no fabrica enlaces que puedan romperse.

## Controles de seguridad y compatibilidad

- Las rutas permanecen en el grupo `web`, con `auth`, middleware `superadmin`, limitación de tasa y permisos puntuales. La `InstitucionPolicy` vuelve a exigir identidad SuperAdmin y permiso. El grupo web conserva la protección CSRF estándar.
- La política B8 se reutiliza: solo slugs ASCII minúsculos, números y guiones simples; se rechazan duplicados y colisiones por normalización. Los slugs existentes no se normalizan ni modifican. `Privada` no fue alterado.
- Antes de ofrecer o ejecutar un alta, `RequisitosProvisionamientoInstitucion` comprueba estructuralmente B2–B8: columnas M1–M4, FK institucionales, `NOT NULL` B8 (respetando los catálogos globales `escala_notas` y `evento_categorias`), índices únicos R2a/B8 y unicidad global de correo y documento. También bloquea hallazgos críticos de pertenencia. En esquema incompleto, la pantalla informa los requisitos y el POST devuelve HTTP 409.
- Este control es de lectura: no invoca Artisan ni aplica migraciones. El aprovisionamiento requiere que las migraciones B2–B8 se apliquen y verifiquen previamente mediante el procedimiento autorizado.
- En las pruebas de MariaDB, `usuarios.id_institucion` existía pero no tenía una FK en el esquema de prueba. B9 no intenta reparar ese esquema ni ejecutar DDL; la ruta de alta asigna el ID del nuevo colegio y no acepta uno arbitrario. Antes de producción debe revisarse explícitamente la FK histórica y definir su regla de borrado seguro.
- El listado conserva solo el conteo agregado de usuarios, sin exponer registros personales. El correo y teléfono institucionales siguen siendo editables como datos básicos del colegio.

## Archivos creados y modificados

**Creados**

- `laravel/app/Modules/Instituciones/Support/RequisitosProvisionamientoInstitucion.php`
- `laravel/resources/views/SuperAdmin/instituciones/partials/_campos-administrador.blade.php`
- `laravel/tests/Feature/SuperAdmin/InstitucionProvisionamientoTest.php`
- `docs/seguridad/etapa2-b9.md`

**Modificados**

- `laravel/app/Modules/SuperAdmin/Controllers/InstitucionController.php`
- `laravel/app/Modules/Instituciones/Requests/InstitucionStoreRequest.php`
- `laravel/app/Modules/Instituciones/Requests/InstitucionUpdateRequest.php`
- `laravel/resources/views/SuperAdmin/instituciones/create.blade.php`
- `laravel/resources/views/SuperAdmin/instituciones/edit.blade.php`
- `laravel/resources/views/SuperAdmin/instituciones/show.blade.php`
- `laravel/resources/js/pages/superadmin/instituciones.js`
- `laravel/tests/Feature/SuperAdmin/AutorizacionTest.php`
- `laravel/tests/Feature/SuperAdmin/InstitucionAuditoriaTest.php`

El parcial `_campos-rector.blade.php` fue sustituido por `_campos-administrador.blade.php`; no quedan referencias al formulario anterior.

## Pruebas

| Suite | Resultado |
|---|---:|
| Pruebas B9/Autorización focalizadas en SQLite | 30 aprobadas, 325 aserciones |
| Suite completa SQLite | 284 aprobadas, 2.003 aserciones |
| Pruebas B9 de aprovisionamiento en MariaDB | 5 aprobadas, 37 aserciones |
| Suite completa MariaDB `colegio_tests` | 298, 1.895 aserciones, 10 omitidas, sin fallos |
| `pint --test` y `git diff --check` | aprobados |
| `npm run build` | aprobado; muestra avisos de deprecación Sass preexistentes |

Las pruebas HTTP verifican alta, rol y pertenencia del administrador, rechazo de `id_institucion` del cliente, rollback tras un fallo de auditoría, unicidad y normalización de slugs, estados, auditoría, bloqueo sin B8 y no exposición de datos personales en la ficha. Las pruebas MariaDB usan aislamiento sin transacción externa alrededor del DDL y limpian las filas de fixture.

`colegio_tests` fue restaurada al dump previo a B9. La suma SHA-256 del respaldo permanece `100b3cc796d04b85d66676b44cf1f6897fe46daebc0005b36f84e6a0b5fb749a`.

## Archivos públicos

B9 no cambió rutas ni almacenamiento de archivos. Se mantiene el riesgo registrado en B8: `laravel/public/storage` sirve directamente archivos del disco `public`, por lo que filtrar filas en la base no protege una URL conocida.

- Público únicamente tras aprobación: logos y medios destinados al sitio/landing.
- Requiere clasificación y, normalmente, almacenamiento privado con entrega autorizada: fotos de perfil, capturas de soporte y adjuntos de eventos.
- Antes de producción se necesita inventariar rutas antiguas y huérfanas, migrar adjuntos privados con verificación de checksums y conservar originales hasta validar la transición. No se borraron ni movieron archivos durante B9.

## Riesgos pendientes y requisitos para B10

- Revisar y resolver explícitamente la FK de `usuarios.id_institucion` en el esquema de despliegue; el esquema de prueba usado no la tenía. Verificar también los índices y reglas B2–B8 en la base objetivo antes de habilitar altas.
- Confirmar la entrega de correo y el procedimiento de recuperación para el administrador inicial; B9 no envía invitaciones.
- Clasificar archivos públicos y privados y diseñar el almacenamiento privado antes de abrir acceso a más colegios.
- B10 requiere autorización separada, inventario de datos y archivos, auditoría sin hallazgos críticos, decisiones de pertenencia explícitas, respaldo verificable y ventana de despliegue. B9 no autoriza ni inicia esos trabajos.

**B9 termina aquí.** No se ejecutó B10, no se crearon commits y `colegio` permanece sin modificaciones por esta tarea.
