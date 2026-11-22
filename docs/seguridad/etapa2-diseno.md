# Etapa 2 — Diseño del aislamiento multiinstitución

**Estado:** diseño aprobado para planificación (2026-11-18). B0 y B1 implementados (ver `etapa2-b0-b1.md`); B2 ensayado solo en `colegio_tests` (ver `etapa2-b2.md`); B3 simulación implementada (ver `etapa2-b3.md`); B4 aplicación preparada y ensayada solo en `colegio_tests` (ver `etapa2-b4.md`); B5 código de catálogos institucional con transición de esquema (ver `etapa2-b5.md`); B6 configuración por colegio (ver `etapa2-b6.md`); B7 sitio público y admisiones por slug (ver `etapa2-b7.md`). **B8 en adelante y toda base real: pendientes de autorización.**

## 1. Inventario (55 tablas)

| Clasificación | Tablas | Estado actual |
|---|---|---|
| Global de plataforma (16) | `instituciones`, `planes`, `modulos`, `plan_modulo`, `roles`, `permissions`, `permission_role`, `plataforma_configuracion`, `migrations`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `notifications`, `audit_logs` | Correcto (audit_logs con institución opcional) |
| Identidad personal (3) | `usuarios`, `password_resets`, `sessions` | `usuarios.id_institucion` nullable (SuperAdmin = NULL) |
| Institucional con columna (2) | `cursos`, `solicitudes_admision` | nullable, FK `SET NULL` |
| Institucional SIN columna (17) | `estudiantes`, `profesores`, `acudientes`, `materias`, `areas`, `periodos_academicos`, `tipos_actividad`, `escala_notas`, `evento_categorias`, `colegio_configuracion` (singleton), `colegio_imagenes`, `landing_contenido`, `landing_galeria`, `landing_noticias`, `eventos`, `soportes`, `notificaciones` | Compartidas (V7) |
| Hija por FK (14) | `asignaciones_academicas`, `horarios`, `matriculas`, `actividades`, `detalle_notas`, `asistencia`, `observaciones`, `boletines`, `boletin_detalle`, `evento_adjuntos`, `evento_comentarios`, `evento_excepciones`, `evento_historial`, `evento_recordatorios_enviados` | Aisladas por la Etapa 1 |
| Relación (3) | `estudiante_acudiente`, `profesor_materia`, `evento_participantes` | Sin mezcla (control en aplicación) |

60 claves foráneas. Riesgos: `cursos/solicitudes.id_institucion ON DELETE SET NULL` (borrar un colegio deja huérfanos); cadena `CASCADE` curso→asignación→actividad→notas/asistencia; ninguna FK garantiza coherencia institucional entre padres de una misma fila. `escala_notas` y `areas` no tienen modelo ni se usan. Nombre y logo están duplicados en `instituciones` y `colegio_configuracion`.

## 2. Arquitectura

**Base compartida + `id_institucion` en tablas raíz; hijas por FK.** Extiende `PerteneceAInstitucion` (Etapa 1) sin cambiar consumidores.

| Grupo | Diseño |
|---|---|
| Perfiles (`estudiantes`, `profesores`, `acudientes`) | `id_institucion` denormalizada desde el usuario (códigos únicos por colegio) + verificación de coherencia |
| Catálogos propios (`materias`, `areas`, `periodos_academicos`, `tipos_actividad`) | `id_institucion NOT NULL` |
| Catálogos con plantilla (`escala_notas`, `evento_categorias`) | `NULL` = plantilla global de solo lectura; copia por colegio al darlo de alta |
| Configuración | `colegio_configuracion` una fila por colegio (`UNIQUE id_institucion`) + colores, contacto, calendario académico, legales. `instituciones` = registro de plataforma; fin de la duplicación nombre/logo |
| Landing | `id_institucion` en `landing_*` y `colegio_imagenes`; `UNIQUE (id_institucion, clave)` |
| Eventos, soportes | `id_institucion` denormalizada (creador/remitente) |
| Hijas | Sin columna; `institucion:auditar` verifica coherencia |
| FKs a `instituciones` | `ON DELETE RESTRICT`; los colegios se suspenden, no se borran |

**Resolución de institución:** autenticado → `TenantContext`. Público → `InstitucionPublica`: `/sitio/{slug}`; `/` solo si `PORTAL_PUBLICO_SLUG` está configurado (si no, 404). Futuro: `instituciones.dominio` + middleware host→colegio. Sin request (correos, ICS, tareas) → institución del propio registro; se elimina `ColegioConfiguracion::singleton()`.

**Admisiones:** `POST /sitio/{slug}/admision`; institución resuelta por el servidor (activa y con el módulo); nunca desde el cuerpo; sin resolución → 404 sin guardar.

**Usuarios en varios colegios:** A) mantener 1 usuario = 1 colegio (Etapa 2); B) membresías `usuario_institucion` + colegio activo en sesión (etapa futura, recomendada); C) unicidad por colegio (descartada).

## 3. Migraciones (expandir → poblar → validar → restringir)

| # | Contenido |
|---|---|
| M1 | `id_institucion` nullable + índice: catálogos |
| M2 | ídem: perfiles, eventos, soportes |
| M3 | ídem: configuración, imágenes, landing; columnas de perfil institucional |
| M4 | `migracion_pertenencia` (trazabilidad para revertir) |
| P | `institucion:poblar --simular` / `--aplicar` (transacción, idempotente, usa el archivo de correspondencia) |
| V | `institucion:auditar` sin hallazgos críticos |
| R1 | `NOT NULL` + FK `RESTRICT` |
| R2 | Únicos compuestos (§4) |
| R3 | `cursos/solicitudes.id_institucion`: `SET NULL` → `RESTRICT` |

Transición: cada modelo pasa `RUTA_INSTITUCION` a `''` solo tras poblarse.

## 4. Restricciones únicas

| Actual | Propuesta |
|---|---|
| `cursos(nombre_curso, anio_lectivo, jornada)` | `+ id_institucion` (retira el bloqueo de `Curso::buscarOCrear`) |
| `materias(nombre_materia)`, `areas(nombre_area)`, `tipos_actividad(nombre_tipo)` | `(id_institucion, …)` |
| `periodos_academicos(nombre_periodo, anio_lectivo)` | `(id_institucion, …)` |
| `evento_categorias(slug)` | `(id_institucion, slug)` + regla de aplicación frente a plantillas (`NULL`) |
| `landing_contenido(clave)` | `(id_institucion, clave)` |
| `estudiantes(codigo_estudiante)`, `profesores(codigo_profesor)` | `(id_institucion, codigo_…)` + consecutivo por colegio |
| `usuarios(correo / numero_documento / ics_token)` | **Sin cambios** |

## 5. Transformación de datos (sin colegio por defecto)

Reglas: materias ← cursos de sus asignaciones ∪ profesores (`profesor_materia`), hijas siguen al padre; áreas ← sus materias; periodos ← actividades y boletines; tipos ← actividades; categorías de sistema → plantilla; resto ← creadores de eventos; escala → plantilla; perfiles/eventos/soportes ← su usuario; configuración y landing ← **solo** por archivo de correspondencia firmado.

| Resultado | Acción |
|---|---|
| 1 institución | asignar |
| Varias | clonar por institución + repuntar referencias (registrado en `migracion_pertenencia`) |
| Sin uso y una institución en la base | asignar a esa (sin ambigüedad) |
| Sin uso y varias instituciones | **ambiguo**: requiere decisión en el archivo de correspondencia |

Cada bloque: respaldo verificado → ensayo en copia → comparación de conteos/checksums → base real solo con autorización.

## 6. Archivos afectados
Modelos de catálogos, configuración, landing, perfiles, eventos y soportes (+ modelos nuevos `Area`, `EscalaNota`); controladores GestionAcademica, Calificaciones, Boletines, EventoCategoria, ConfiguracionColegio, EditarLanding, Website, SolicitudAdmision; Requests de catálogos; `CodigoUnicoService`; servicios nuevos `InstitucionPublica` y alta de colegio (copia de plantillas); `AppServiceProvider`, 3 mailables, `IcsExportService`; rutas públicas `/sitio/{slug}`; vistas de landing y configuración.

## 7. Riesgos
Población errónea (mitigado con simulación, correspondencia, trazabilidad, respaldos); repunte de referencias al clonar; cambio de URL pública; correos sin institución; `NULL` múltiples en únicos; `down()` no revierte clonaciones ni únicos con duplicados (aborta; recuperación = respaldo); rendimiento (índices compuestos).

## 8. Pruebas
Dos colegios ficticios con nombres idénticos ("7A", "Matemáticas", "Primer Trimestre 2026"); creación/edición/borrado aislados; validación de catálogos ajenos (422); `institucion:auditar` limpio; landing y admisiones por slug; alta de colegio con plantillas; conservación de datos (checksums antes/después); `up`/`down`/`up` en copia; regresión de las suites existentes en SQLite y MariaDB.

## 9. Reversión
Código: scripts `revertir-*.sh`. Expansión: `down()` sin pérdida. Población: `migracion_pertenencia` (o respaldo si ya hubo escrituras nuevas). Restricción: `down()` verifica conflictos y aborta. Base real: respaldo verificado antes de cada bloque y checksums después.

## 10. Orden por bloques (cada uno con autorización)
B0 entorno de pruebas ✅ · B1 auditoría (solo lectura) ✅ · B2 M1–M4 ✅ (solo `colegio_tests`) · B3 poblar `--simular` ✅ · B4 `--aplicar` en `colegio_tests` ✅ · B5 código de catálogos con columna propia ✅ · B6 configuración por colegio ✅ · B7 landing por slug + admisiones ✅ · B8 restricciones · B9 alta de colegio con plantillas · B10 base real, bloque a bloque · Futuro: dominios, membresías.
