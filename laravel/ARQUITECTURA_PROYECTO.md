# Auditoría de Arquitectura — Sistema de Gestión Escolar (Laravel)

**Fecha:** 2026-07-18
**Alcance:** `laravel/` (aplicación activa). `legacy/` no se audita porque está siendo eliminada en esta misma rama (`feature/Yair`) — es el sistema que Laravel está reemplazando.
**Fase:** 1 (Análisis) + 2 (Diseño) + 3 (Propuesta). **No se ha modificado ningún archivo de código.** Fase 4 (tu aprobación) queda pendiente antes de tocar nada.

---

## 0. Resumen ejecutivo — lee esto primero

Antes de entrar al detalle, la conclusión más importante de la auditoría, porque cambia el resto del documento:

**Este NO es el monolito legacy gigante que la petición original asume.** Es una aplicación Laravel 12 relativamente joven y ya bien escrita:

| Métrica | Valor |
|---|---|
| Controllers | 19 (2 008 líneas totales, promedio 106 líneas) |
| Models | 23 (960 líneas totales, promedio 42 líneas) |
| Services | 3 (247 líneas totales) |
| Form Requests | 20, ya agrupados por dominio |
| Rutas | 172 líneas en un solo `web.php`, ya agrupadas por prefijo/dominio |
| Vistas Blade | 37, ya agrupadas por módulo (`panel/gestion-academica/`, `panel/calificaciones/`...) |
| JS | 30 archivos, ya organizados en `core/ components/ pages/` |
| CSS | 12 archivos, ya organizados en `base/ components/ layouts/ themes/` |
| Policies / Jobs / Events / Listeners | 0 / 0 / 0 / 0 |
| Migraciones propias del dominio | 3 de 7 (el resto son las de Laravel por defecto) |

No hay clases de 1 000 líneas, no hay controllers de 2 000 líneas, no hay código espagueti mezclando HTML/SQL/lógica. El equipo (o la IA que escribió esto antes) ya aplicó buenas prácticas básicas: Form Requests, mass-assignment controlado con `$fillable`, escape de HTML en el `SidebarBuilder`, transacciones DB en los procesos multi-tabla, separación JS/CSS por página.

**Por eso mi recomendación como arquitecto, y la razón por la que me pediste que te la diera si difiere de lo pedido:**

> No propongo la arquitectura `Modules/ Core/ Infrastructure/ Shared/` con DTOs, Repositories e interfaces por todas partes que pediste literalmente. Para 23 modelos y ~5 200 líneas de código de aplicación, ese nivel de abstracción es **sobre-ingeniería**: añade indirección (Controller → Action → Service → Repository → Interface → Eloquent) que ralentiza a un equipo pequeño sin aportar valor real, porque no hay múltiples implementaciones que intercambiar ni un dominio tan complejo que lo justifique.
>
> Propongo en su lugar una **arquitectura modular pragmática**: mismo principio de "todo lo de un módulo vive junto" que pediste, pero sin capas Core/Infrastructure separadas ni Repository/DTO obligatorios en todas partes — solo donde el problema real los necesita (el motor de boletines, por ejemplo, si lo justifica). Es el punto intermedio entre "Laravel por defecto" y "DDD hexagonal completo", y es el que usan la mayoría de equipos Laravel senior para monolitos de este tamaño (es básicamente el patrón que Spatie, Laravel Modules y Laravel Beyond CRUD documentan).

Si después de leer la sección 5 prefieres igual la versión completa con Core/Infrastructure/DTO explícitos, dímelo y ajustamos el plan — no es una decisión difícil de revertir porque no hemos migrado nada todavía.

---

## 1. Contexto del proyecto

- **Dominio:** gestión académica de un colegio (matrículas, cursos, materias, horarios, asistencia, calificaciones/boletines, comunicados, landing pública).
- **Migración en curso:** el repo está reemplazando un sistema legacy en PHP plano (`legacy/app/controllers`, `legacy/app/models`, sin framework) por esta app Laravel. El `git status` actual ya muestra `legacy/` completo en estado `D` (borrado) en esta rama.
- **Base de datos:** la app se conecta a una base de datos MySQL (`colegio`) que **ya existía antes de Laravel** (creada por el sistema legacy). Los nombres de tablas/columnas (`usuarios`, `id_usuario`, `estado_matricula`, etc.) y las claves primarias no estándar vienen heredados de ahí, no de convención Laravel. Esto es intencional y está documentado en comentarios del propio código (`Usuario.php:10-13`), pero tiene consecuencias que detallo en la sección 3.
- **Stack:** Laravel 12, PHP 8.2+, Vite + Sass, Bootstrap 5, SweetAlert2, sin frontend framework (JS vanilla organizado por módulo).

---

## 2. Metodología

Revisión manual y con herramientas de: estructura de carpetas completa, todos los controllers, todos los models, los 3 services, las 20 form requests, `routes/web.php` completo, migraciones, seeders, config, middleware, providers, `SidebarBuilder`, muestras representativas de vistas Blade/JS/CSS, y búsqueda dirigida de patrones de riesgo (SQL crudo, duplicación de bloques `activar/desactivar`, mass assignment, N+1).

---

## 3. Hallazgos

Ordenados por severidad. Cada uno indica ubicación, impacto y solución propuesta (para aplicar en Fase 5, no ahora).

### 🔴 Crítico

**H1 — El esquema de base de datos no está versionado.**
- **Dónde:** `database/migrations/` solo tiene 7 archivos: 3 son el scaffold por defecto de Laravel (`users`, `cache`, `jobs` — y encima `users` no se usa, la auth real es `usuarios`), y 3 son de las tablas de landing (`landing_contenido`, `landing_galeria`, `landing_noticias`) añadidas ahora. **Ninguna** migración crea `usuarios`, `estudiantes`, `profesores`, `cursos`, `materias`, `matriculas`, `asignaciones_academicas`, `horarios`, `periodos_academicos`, `notas`, `boletines`, `boletin_detalle`, `actividades`, `observaciones`, `acudientes`, `roles`, `comunicados` — que son *todas* las tablas de negocio, heredadas del `colegio` de la app legacy.
- **Impacto:** no se puede levantar un entorno nuevo (dev, CI, staging) desde cero solo con `php artisan migrate`. No hay historial de cambios de esquema. No se puede usar `RefreshDatabase` en tests. Cualquier cambio de columna futuro no queda documentado ni es reproducible. Bloquea CI/CD real.
- **Solución:** generar migraciones "baseline" que reflejen el esquema actual (`php artisan schema:dump` o migraciones escritas a mano tabla por tabla) y a partir de ahí, todo cambio de esquema pasa por migración. No se toca la BD en producción, solo se documenta lo que ya existe.

**H2 — El motor de cálculo de boletines vive en el controller, sin tests, con riesgo de N+1 severo.**
- **Dónde:** [`BoletinesController::generar()`](app/Http/Controllers/Panel/BoletinesController.php#L41-L110).
- **Impacto:** es la lógica de negocio más importante y más delicada de todo el sistema (calcula notas definitivas ponderadas y puestos por curso) y está: (a) sin ningún test, (b) haciendo una consulta `Nota::where(...)->value('nota')` **dentro de un triple bucle** `estudiantes × asignaciones × actividades` — para un curso de 30 estudiantes con 8 materias y 5 actividades cada una, son hasta 1 200 queries en una sola request síncrona, (c) sin manejo de fallos parciales (si se cae a mitad, quedan boletines a medio generar sin transacción), (d) ejecutándose de forma síncrona en el request HTTP, con riesgo de timeout en cursos grandes.
- **Solución:** extraer a una clase `GenerarBoletinesAction` (o Service) testeable de forma aislada, resolver las notas con una sola consulta agregada (`whereIn` + agrupación en PHP, o `SUM/GROUP BY` en SQL) en vez de N consultas, envolver todo el proceso en una transacción, y evaluar moverlo a un Job en cola si los cursos son grandes.

### 🟠 Alto

**H3 — Controllers con múltiples responsabilidades (God Controllers).**
- **Dónde:** `GestionAcademicaController` (337 líneas) maneja 4 recursos distintos sin relación de composición real (Materias, Cursos, Asignaciones, Horarios) con su propio CRUD cada uno. `CalificacionesController` (265 líneas) maneja igual 4 recursos (Periodos, TiposActividad, Actividades, Notas).
- **Impacto:** viola SRP — un cambio en la validación de horarios obliga a tocar el mismo archivo que maneja materias. Dificulta encontrar código, aumenta el riesgo de conflictos de merge, dificulta testear una sola responsabilidad de forma aislada.
- **Solución:** dividir en un controller por recurso, siguiendo el mismo patrón que ya usan `RegistroEstudiantesController` / `RegistroDocentesController` / `RegistroAdministrativosController` (que sí están bien separados). P. ej. `MateriasController`, `CursosController`, `AsignacionesController`, `HorariosController` dentro del módulo `GestionAcademica`.

**H4 — No existe capa de autorización granular (Policies = 0).**
- **Dónde:** toda la autorización pasa por `EnsureRole` (middleware de ruta: `role:admin,rector`) y `authorize(): true` fijo en las 20 Form Requests.
- **Impacto:** hoy funciona porque solo `admin`/`rector` tocan el panel administrativo. Pero en cuanto un `docente` necesite, por ejemplo, registrar solo *sus propias* asistencias/notas (que es un caso de uso natural de un sistema escolar), no hay forma de expresar "puede editar esta asignación solo si es el profesor asignado" sin Policies — habría que empezar a meter `if` de autorización dentro de cada controller.
- **Solución:** introducir Policies cuando se abra el panel a roles no administrativos (no es urgente hoy, pero si está en el roadmap hay que diseñarlo ahora para no parchear después).

**H5 — Contraseña por defecto predecible para estudiantes.**
- **Dónde:** [`EstudianteRegistroService::crear()`](app/Services/EstudianteRegistroService.php#L38) — `'password' => Hash::make($d['numero_documento'])`.
- **Impacto:** el número de documento de un estudiante suele ser conocido por administrativos, compañeros o incluso estar en listados impresos. Si no se fuerza cambio de contraseña en el primer login, cualquiera que conozca el documento de un estudiante puede entrar a su cuenta.
- **Solución:** verificar si existe (o añadir) un flag `debe_cambiar_password` que fuerce el cambio en el primer login, o generar una contraseña temporal aleatoria y comunicarla por un canal separado. Esto es una decisión de producto además de técnica — lo marco para que la valides, no lo voy a cambiar sin confirmar contigo.

### 🟡 Medio

**H6 — Duplicación del patrón activar/desactivar (32 ocurrencias en 7 controllers).**
- **Dónde:** `CalificacionesController` (×6), `GestionAcademicaController` (×8), `ListadosController` (×8), `EditarLandingController` (×4), `BoletinesController`, `ObservacionesController`, `MatriculasController`.
- **Impacto:** el mismo par de métodos (`update(['estado' => 'activo'|'inactivo'])` + respuesta JSON) se repite casi textual en 7 archivos distintos. No es un bug hoy, pero cualquier cambio en el formato de respuesta (por ejemplo, añadir un log de auditoría de quién desactivó qué) requiere tocar 16 métodos.
- **Solución:** un trait `TieneEstadoActivable` en el modelo (con métodos `activar()`/`desactivar()`) + un método protegido reutilizable en un controller base para la respuesta JSON estándar. Sin necesidad de un framework de "soft toggling" genérico — solo eliminar la repetición literal.

**H7 — Services estáticos sin contrato/interfaz.**
- **Dónde:** los 3 Services (`CodigoUnicoService`, `DocenteRegistroService`, `EstudianteRegistroService`) exponen solo métodos `static`.
- **Impacto:** no es incorrecto para su tamaño actual, pero impide inyectar un mock en tests unitarios sin usar `Mockery::mock('alias:...')` (más frágil), y si mañana `EstudianteRegistroService` necesita colaboradores inyectados (p. ej. un servicio de notificaciones), el patrón estático no escala bien.
- **Solución:** al dividir Services más grandes en Fase 5, usar instancias con constructor + registrar en el Service Container, no métodos estáticos nuevos. Los 3 existentes se pueden dejar como están si no dan problemas — no hay que migrarlos solo por consistencia.

**H8 — Sin Jobs/Events pese a haber un proceso pesado real (H2).**
- **Impacto:** ya cubierto en H2. Lo separo aquí porque es también un hallazgo estructural: la carpeta `Jobs/` no existe, así que cuando se necesite (boletines, envío masivo de comunicados) no hay convención ya establecida.
- **Solución:** crear `app/Jobs/` cuando se implemente la solución de H2.

**H9 — Acoplamiento directo a Eloquent en controllers (sin Repository).**
- **Dónde:** generalizado — `Materia::count()`, `Curso::where(...)`, etc. directamente en controllers.
- **Impacto:** para el tamaño actual **no es un problema real** — es el patrón estándar y recomendado en Laravel para dominios de esta complejidad. Lo listo como hallazgo solo para justificar por qué **no** voy a introducir una capa Repository/Interface genérica en la propuesta (sección 5): añadiría una interfaz por modelo sin ningún beneficio medible, porque nunca se va a intercambiar Eloquent por otra fuente de datos.

### 🟢 Bajo

**H10 — `routes/web.php` es un único archivo (172 líneas).** Manejable hoy, agrupado por prefijo, pero crecerá con cada módulo nuevo. Solución: dividir por módulo en Fase 5 (sección 8).

**H11 — Tests de ejemplo sin borrar.** `tests/Feature/ExampleTest.php` y `tests/Unit/ExampleTest.php` son el scaffold por defecto de Laravel, no hay tests reales del dominio todavía. No es una "violación" pero sí una oportunidad — ligado a H1 (sin migraciones no se puede usar `RefreshDatabase` en tests de feature).

**H12 — `AppServiceProvider` vacío.** No hay bindings, no hay `Model::shouldBeStrict()` / `preventLazyLoading()` activado en local, que ayudaría a detectar N+1 como el de H2 automáticamente en desarrollo.

---

## 4. Lo que NO se debe tocar (ya está bien)

Para que quede explícito qué se conserva en el plan de migración:

- Organización de **Form Requests** por dominio (`Requests/Calificaciones/`, `Requests/GestionAcademica/`...) — es exactamente el patrón que se va a extender al resto.
- Organización de **JS** (`core/`, `components/`, `pages/`) y **CSS** (`base/`, `components/`, `layouts/`, `themes/`) — ya siguen el espíritu de la petición, solo hay que alinear el nombrado de `pages/` 1:1 con los módulos de backend.
- Vistas Blade ya agrupadas por feature en `panel/<modulo>/`.
- Uso de `$fillable` explícito en todos los modelos revisados (sin `$guarded = []` peligroso).
- Transacciones DB en procesos multi-tabla (`EstudianteRegistroService`, `DocenteRegistroService`).
- Escape de HTML consistente en `SidebarBuilder::esc()`.
- Eager loading ya usado correctamente en la mayoría de listados (`with('estudiante.usuario')`, etc.) — el N+1 real detectado (H2) es puntual, no generalizado.

---

## 5. Arquitectura propuesta

### 5.1 Principio

Un **módulo de dominio** agrupa todo lo que le pertenece. Sin capas `Core/Infrastructure` separadas del dominio (no aportan valor a este tamaño), sin Repository/DTO obligatorios (se añaden solo si un caso concreto los necesita, como el motor de boletines).

```
app/
├── Modules/
│   ├── Auth/
│   │   ├── Http/Controllers/          (LoginController, PasswordResetController)
│   │   ├── Http/Requests/
│   │   └── routes.php
│   │
│   ├── GestionAcademica/
│   │   ├── Http/Controllers/          (MateriasController, CursosController, AsignacionesController, HorariosController)
│   │   ├── Http/Requests/
│   │   ├── Models/                    (Materia, Curso, AsignacionAcademica, Horario)
│   │   └── routes.php
│   │
│   ├── Matriculas/
│   │   ├── Http/Controllers/
│   │   ├── Services/                  (EstudianteRegistroService, DocenteRegistroService)
│   │   ├── Models/                    (Matricula, Acudiente)
│   │   └── routes.php
│   │
│   ├── Calificaciones/
│   │   ├── Http/Controllers/          (PeriodosController, TiposActividadController, ActividadesController, NotasController)
│   │   ├── Actions/                   (GenerarBoletinesAction  ← H2)
│   │   ├── Models/                    (Periodo, TipoActividad, Actividad, Nota, Boletin, BoletinDetalle)
│   │   ├── Jobs/                      (si se decide encolar la generación)
│   │   └── routes.php
│   │
│   ├── Asistencia/
│   ├── Observaciones/
│   ├── Comunicados/
│   ├── Landing/                       (EditarLandingController + LandingContenido/Galeria/Noticia)
│   └── Panel/                         (Dashboard, Estadisticas, Perfil, Listados — vistas transversales, no dueñas de modelos)
│
├── Shared/
│   ├── Http/Controllers/Controller.php   (base abstracta)
│   ├── Http/Middleware/EnsureRole.php
│   ├── Models/Usuario.php                (usada por casi todos los módulos)
│   ├── Models/Rol.php
│   ├── Support/SidebarBuilder.php
│   ├── Support/CodigoUnicoService.php
│   └── Traits/TieneEstadoActivable.php   (← soluciona H6)
│
└── Providers/
```

**Por qué `Usuario` y `Rol` van en `Shared` y no en un módulo "Auth" propio:** son referenciados por *todos* los módulos (`estudiante->usuario`, `profesor->usuario`, `Usuario::ROLES_PANEL_ADMIN`...). Meterlos dentro de `Modules/Auth` obligaría a que todos los demás módulos dependan de `Auth`, rompiendo el aislamiento que se busca. Es la única excepción deliberada al principio "todo el módulo junto", y es estándar en cualquier arquitectura modular: el modelo de usuario/identidad casi siempre es compartido.

**Resources (vistas/JS/CSS):** se quedan en `resources/` (Laravel no permite fácilmente moverlas sin reconfigurar Vite/Blade namespaces, y el beneficio no compensa el riesgo) pero se **renombran para alinear 1:1** con los módulos de `app/Modules`:

```
resources/
├── views/panel/gestion-academica/...     (ya alineado)
├── js/pages/gestion-academica/...        (ya alineado)
└── css/... (ya organizado por tipo, no por módulo — se mantiene así: es una convención SCSS distinta y válida)
```

### 5.2 Alternativa que consideré y descarté

Evalué proponer la estructura completa `Modules/<Modulo>/{Domain,Application,Infrastructure}` (hexagonal/DDD táctico completo, con interfaces de Repository y DTOs en cada capa). La descarté porque:

- Ningún módulo tiene reglas de negocio lo bastante complejas para justificar separar `Domain` de `Application` (el único candidato real es el motor de boletines, H2, y ahí sí propongo una `Action` dedicada).
- No hay ni un caso de "necesito poder cambiar la fuente de datos" que justifique interfaces de Repository.
- El costo de mantenimiento (más archivos, más indirección, más tiempo para ubicar dónde vive algo) es real y se paga todos los días; el beneficio (testabilidad sin BD, intercambiar infraestructura) es hipotético para este proyecto.

Si el proyecto crece mucho (múltiples colegios/tenants, integraciones externas, equipo de 5+ devs), esta decisión se puede revisar — la estructura modular propuesta no lo impide, de hecho lo facilita: cada módulo podría evolucionar a hexagonal internamente sin afectar a los demás.

### 5.3 Ventajas / Desventajas

**Ventajas:**
- Todo lo de un módulo (controller, request, modelo, vista, JS) se encuentra en un lugar predecible.
- Onboarding más rápido: un dev nuevo entiende "Calificaciones" mirando una sola carpeta.
- Migración incremental real: cada módulo se mueve y se verifica de forma aislada (Fase 5), sin big-bang.
- No añade capas que el equipo tendría que aprender a usar correctamente (Repository/DTO mal usados son peores que no usarlos).

**Desventajas / trade-offs:**
- Laravel no tiene soporte nativo para "módulos" — hay que registrar rutas/vistas/factories de cada módulo manualmente en un ServiceProvider (o usar el paquete `nwidart/laravel-modules`, que evalué pero descarté para no añadir una dependencia de terceros a algo que se puede resolver con autoload PSR-4 estándar + un `RouteServiceProvider` por módulo).
- `Usuario`/`Rol` compartidos en `Shared` significan que un cambio ahí impacta a todos los módulos — es inherente a tener un modelo de identidad único, no una debilidad de esta propuesta en particular.
- Requiere tocar el `composer.json` (autoload PSR-4 de `Modules\` y `Shared\`) y recalcular el autoloader.

---

## 6. Base de datos

- **Prioridad 1 (H1):** migraciones baseline para las 16 tablas de dominio que hoy no tienen migración. Se documenta el esquema existente, **no se altera ninguna tabla**.
- **Índices:** revisar `estudiantes.numero_documento`, `usuarios.correo`, `notas.(id_actividad,id_estudiante)` — probablemente ya tienen índices/unique del sistema legacy, pero al no haber migraciones no hay forma de confirmarlo sin inspeccionar la BD directamente (lo haré en Fase 5 con `SHOW CREATE TABLE` antes de escribir cada migración baseline).
- **Seeders/Factories:** no existen (`database/factories/` vacío) — necesarios para poder tener tests de feature con `RefreshDatabase` (bloqueado hoy por H1).

## 7. Seguridad — resumen (detalle en H4/H5)

- CSRF: cubierto por defecto de Laravel (`@csrf` presente donde se revisó).
- Mass assignment: correcto, `$fillable` explícito en los modelos revisados.
- Autorización: funcional pero sin granularidad (H4).
- Contraseña por defecto predecible (H5) — el hallazgo de seguridad más importante, requiere tu validación de producto antes de tocarlo.

## 8. Rendimiento — resumen (detalle en H2)

- El único N+1 real detectado es H2 (generación de boletines).
- Candidatos a cache: `EstadisticasController` y `DashboardController` recalculan agregados (`selectRaw` de asistencia/promedios) en cada request — candidatos a `Cache::remember()` con TTL corto si el panel se vuelve lento, no es urgente hoy.

---

## 9. Plan de migración (Fase 5 — no empieza hasta tu aprobación)

Orden por **riesgo ascendente**, para validar el patrón en módulos pequeños antes de tocar el crítico:

1. **Landing** (`EditarLandingController` + 3 modelos) — módulo más aislado, sin dependencias de otros módulos, bueno para probar la estructura `Modules/` sin riesgo.
2. **Comunicados** — igual de aislado, controller pequeño (76 líneas).
3. **Observaciones** — aislado, controller pequeño (83 líneas).
4. **Auth + Perfil** (`LoginController`, `PasswordResetController`, `PerfilController`).
5. **GestionAcademica** — aquí se aplica H3 (split en 4 controllers) y H6 (trait de activar/desactivar).
6. **Matriculas + Registro** (estudiantes/docentes/administrativos) — usa los Services existentes, se valida que sigan funcionando igual.
7. **Asistencia + Listados + Estadisticas + Dashboard.**
8. **Calificaciones + Boletines (último, el más crítico)** — aquí se resuelve H2 (extraer `GenerarBoletinesAction`, arreglar N+1, añadir tests). Se deja para el final porque es la lógica de negocio más sensible y para entonces el patrón de migración ya estará probado en 7 módulos anteriores.

Cada módulo, al migrarse: se mueven sus archivos, se actualizan namespaces e imports, se corre la suite de tests (una vez exista, ligado a H1/H11), y se verifica manualmente el flujo principal en navegador antes de pasar al siguiente. Un módulo por PR, nunca varios a la vez.

## 10. Riesgos

- **Sin tests de regresión hoy** (H11): cada módulo migrado se debe probar manualmente end-to-end porque no hay red de seguridad automática todavía. Mitigación: escribir al menos un test de feature por módulo *antes* de moverlo, usando el módulo tal como está hoy (esto también obliga a resolver H1 pronto).
- **Rutas con nombre (`route('gestion-academica.cursos.show')`, etc.) usadas en Blade y `SidebarBuilder`:** si se dividen controllers (H3), hay que mantener los mismos `name()` de ruta o actualizar todas las referencias — riesgo de romper links si se olvida alguna.
- **Cambiar el namespace de un modelo usado por Eloquent relationships** (`belongsTo(Rol::class)`, etc.) requiere actualizar todas las referencias — alto riesgo de error humano si se hace a mano en vez of con find-and-replace verificado archivo por archivo.
- **H5 (password por defecto)** no se toca sin tu confirmación explícita porque cambia comportamiento visible para usuarios finales (estudiantes/acudientes), no es un refactor interno.

---

## 11. Qué necesito de ti para pasar a Fase 5

1. ¿Apruebas la arquitectura modular pragmática de la sección 5, o prefieres la versión completa con Core/Infrastructure/DTO que pediste originalmente?
2. ¿Confirmas el orden de migración de la sección 9, o hay un módulo que necesites priorizar por otra razón (ej. una feature en desarrollo activo ahora mismo)?
3. Sobre H5 (password predecible): ¿lo resolvemos ahora como parte de esta migración, o lo dejas para después porque tiene implicación de producto (comunicación a usuarios)?

No se toca ningún archivo de código hasta que respondas esto.
