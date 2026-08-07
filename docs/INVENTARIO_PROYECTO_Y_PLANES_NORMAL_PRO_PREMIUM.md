# Inventario del proyecto y propuesta de planes Normal, Pro y Premium

Fecha de elaboración: 2026-07-29

## 1. Resumen ejecutivo

Este proyecto corresponde a un sistema escolar orientado a la gestión institucional, con una arquitectura basada en Laravel 12 y un componente legado en proceso de migración. El repositorio ya contiene una base funcional amplia: autenticación, usuarios, matrícula, gestión académica, calificaciones, observaciones, reportes, calendario, landing, soporte, auditoría y módulos de administración institucional.

La propuesta de este documento es permitir entender qué existe hoy en el proyecto y si es viable organizarlo en tres niveles de implementación:

- Plan Normal: versión base y operativa.
- Plan Pro: versión completa para operación académica diaria.
- Plan Premium: versión institucional, escalable y con capacidades avanzadas.

La idea es que cada plan sea un subconjunto claro del sistema, eliminando o posponiendo funciones que no son esenciales para el objetivo inicial.

---

## 2. Qué existe hoy en el proyecto

### 2.1 Estructura general del repositorio

El repositorio está organizado de forma modular y está dividido en dos capas principales:

- Raíz del proyecto: contiene documentación, auditorías, scripts y la carpeta principal del sistema.
- Carpeta Laravel: contiene la aplicación activa desarrollada con Laravel 12.

### 2.2 Componentes principales

#### Aplicación Laravel
- Framework: Laravel 12
- Lenguaje: PHP 8.2+
- Base de datos: MySQL
- Frontend: Blade, Vite, CSS/JS
- Autenticación: sistema propio con roles y middleware
- Arquitectura: módulos por dominio

#### Sistema legado
- Existe una versión previa en PHP puro que sigue operando parcialmente.
- Está en transición hacia Laravel.
- El enfoque actual es una migración incremental tipo “strangler fig”.

### 2.3 Carpetas clave

- [README.md](../README.md): visión general del proyecto.
- [docs/ARQUITECTURA_SISTEMA_COLEGIO.md](ARQUITECTURA_SISTEMA_COLEGIO.md): arquitectura técnica general.
- [laravel/routes/web.php](../laravel/routes/web.php): definición de rutas del sistema.
- [laravel/app/Modules](../laravel/app/Modules): módulos funcionales del sistema.
- [laravel/resources/views](../laravel/resources/views): vistas Blade.
- [laravel/database](../laravel/database): migraciones y seeders.
- [laravel/tests](../laravel/tests): pruebas del sistema.

---

## 3. Inventario funcional del proyecto

### 3.1 Módulos de autenticación y acceso

Incluyen:
- Login
- Recuperación de contraseña
- Cierre de sesión
- Autenticación por roles
- Protección de rutas por middleware
- Soporte para autenticación avanzada como 2FA

Archivos relevantes:
- [laravel/app/Modules/Auth](../laravel/app/Modules/Auth)
- [laravel/routes/web.php](../laravel/routes/web.php)

### 3.2 Módulo de dashboard

Funciones:
- Panel principal del administrador
- Resúmenes y KPIs
- Vista de inicio institucional

Archivos relevantes:
- [laravel/app/Modules/Dashboard](../laravel/app/Modules/Dashboard)

### 3.3 Módulo de usuarios

Funciones:
- Listados de estudiantes, docentes y administrativos
- Registro y edición de usuarios
- Activación/desactivación de usuarios
- Gestión de perfiles básicos

Archivos relevantes:
- [laravel/app/Modules/Usuarios](../laravel/app/Modules/Usuarios)

### 3.4 Módulo de matrículas

Funciones:
- Gestión de solicitudes y estados de matrícula
- Seguimiento del proceso de admisión
- Control del estado de los estudiantes durante el ingreso

Archivos relevantes:
- [laravel/app/Modules/Matriculas](../laravel/app/Modules/Matriculas)

### 3.5 Módulo de gestión académica

Funciones:
- Materias
- Cursos
- Asignaciones académicas
- Horarios
- Organización del componente académico institucional

Archivos relevantes:
- [laravel/app/Modules/GestionAcademica](../laravel/app/Modules/GestionAcademica)

### 3.6 Módulo de calificaciones

Funciones:
- Periodos académicos
- Tipos de actividad
- Actividades de evaluación
- Registro de notas
- Gestión por asignación

Archivos relevantes:
- [laravel/app/Modules/Calificaciones](../laravel/app/Modules/Calificaciones)

### 3.7 Módulo de observaciones

Funciones:
- Registro de observaciones al estudiante
- Gestión de seguimiento disciplinario o formativo

Archivos relevantes:
- [laravel/app/Modules/Observaciones](../laravel/app/Modules/Observaciones)

### 3.8 Módulo de reportes y boletines

Funciones:
- Boletines
- Publicación y estado de boletines
- Estadísticas generales
- Reportes académicos

Archivos relevantes:
- [laravel/app/Modules/Reportes](../laravel/app/Modules/Reportes)

### 3.9 Módulo de asistencia

Funciones:
- Registro de asistencia por asignación
- Historial de asistencia
- Seguimiento docente o institucional

Archivos relevantes:
- [laravel/app/Modules/Asistencia](../laravel/app/Modules/Asistencia)

### 3.10 Módulo de comunicados

Funciones:
- Envío o administración de comunicados internos
- Mensajería institucional simple

Archivos relevantes:
- [laravel/app/Modules/Comunicados](../laravel/app/Modules/Comunicados)

### 3.11 Módulo de landing y sitio público

Funciones:
- Página pública del colegio
- Gestión de contenido de landing
- Noticias y galería
- Solicitudes de admisión

Archivos relevantes:
- [laravel/app/Modules/Landing](../laravel/app/Modules/Landing)

### 3.12 Módulo de perfil

Funciones:
- Perfil de usuario autenticado
- Cambio de contraseña
- Actualización de datos propios

Archivos relevantes:
- [laravel/app/Modules/Perfil](../laravel/app/Modules/Perfil)

### 3.13 Módulos de roles y administración institucional

Funciones:
- Gestión de roles
- Configuración del colegio
- Administración institucional
- Panel rector/dirigente

Archivos relevantes:
- [laravel/app/Modules/Rector](../laravel/app/Modules/Rector)
- [laravel/app/Modules/Colegio](../laravel/app/Modules/Colegio)
- [laravel/app/Modules/Docente](../laravel/app/Modules/Docente)
- [laravel/app/Modules/Estudiante](../laravel/app/Modules/Estudiante)
- [laravel/app/Modules/Acudiente](../laravel/app/Modules/Acudiente)

### 3.14 Módulos de soporte, auditoría y superadministración

Funciones:
- Soporte de usuarios
- Auditoría del sistema
- Gestión multi-tenant de la plataforma
- Control de configuración global
- Gestión de permisos a nivel plataforma

Archivos relevantes:
- [laravel/app/Modules/Soporte](../laravel/app/Modules/Soporte)
- [laravel/app/Modules/Auditoria](../laravel/app/Modules/Auditoria)
- [laravel/app/Modules/SuperAdmin](../laravel/app/Modules/SuperAdmin)

### 3.15 Módulo de calendario

Funciones:
- Eventos institucionales
- Categorías y permisos
- Exportación .ics
- Comentarios y adjuntos
- Notificaciones de panel

Archivos relevantes:
- [laravel/app/Modules/Calendario](../laravel/app/Modules/Calendario)

---

## 4. Estado actual del proyecto

### 4.1 Lo que ya está bastante maduro

- Autenticación y acceso
- Gestión de usuarios básicos
- Matrículas
- Gestión académica
- Panel administrativo
- Rutas y módulos organizados
- Estructura modular lista para crecer

### 4.2 Lo que está presente pero puede requerir más afinación

- Calificaciones
- Reportes y boletines
- Observaciones
- Asistencia
- Soporte y auditoría
- Landing y administración pública
- Calendario avanzado

### 4.3 Lo que parece más avanzado o más institucional

- Superadmin y control multi-tenant
- Gestión de plataforma global
- Configuración institucional y modularización por rol

---

## 5. Propuesta para dividir el proyecto en 3 planes

La mejor forma de aprovechar este proyecto es pensar en una implementación por capas, donde cada plan agrega funciones sin romper la base institucional.

### Estrategia recomendada

- Plan Normal: entregar la base operativa mínima.
- Plan Pro: agregar el núcleo académico y de seguimiento del estudiante.
- Plan Premium: ampliar a una plataforma institucional completa y escalable.

---

## 6. Plan Normal

### Objetivo

Entregar una solución funcional para la operación básica del colegio, con foco en administración, matrícula y gestión académica mínima.

### Módulos incluidos

- Autenticación y roles básicos
- Dashboard inicial
- Gestión de usuarios básicos
- Registro de estudiantes, docentes y administrativos
- Matrículas
- Gestión académica básica
- Perfil de usuario
- Panel administrativo simple

### Funciones que se omiten o posponen

- Calificaciones detalladas
- Observaciones avanzadas
- Boletines completos
- Reportes complejos
- Asistencia detallada
- Comunicados institucionales
- Calendario avanzado
- Landing administrable
- Soporte avanzado
- Superadmin y gestión multi-tenant
- Auditoría profunda

### Resultado esperado

Un sistema usable para iniciar operaciones básicas de colegio, ideal para una primera etapa de implementación.

---

## 7. Plan Pro

### Objetivo

Ampliar el Plan Normal con el núcleo académico y de seguimiento del estudiante.

### Módulos incluidos

Todo lo del Plan Normal, más:
- Calificaciones
- Actividades y tipos de actividad
- Gestión de notas
- Observaciones
- Asistencia
- Boletines básicos
- Reportes académicos
- Comunicados
- Vistas para estudiantes y acudientes

### Funciones que se omiten o posponen

- Landing avanzada
- Calendario con permisos complejos
- Soporte institucional robusto
- Superadmin multi-tenant
- Auditoría global
- Configuraciones de plataforma muy elaboradas

### Resultado esperado

Un sistema más completo para la operación diaria académica y administrativa del colegio.

---

## 8. Plan Premium

### Objetivo

Construir la versión institucional completa y escalable del sistema, con capacidades de administración global y alta cobertura funcional.

### Módulos incluidos

Todo lo del Plan Pro, más:
- Landing completa y administrable
- Calendario institucional completo
- Notificaciones y comentarios
- Soporte al usuario
- Auditoría
- Configuración institucional avanzada
- Gestión de roles y permisos más elaborada
- Superadmin plataforma
- Módulos multi-tenant y de control global
- Integraciones y capacidades de expansión

### Resultado esperado

Una plataforma robusta, completa y preparada para crecer, administrar varias instituciones o escenarios más complejos.

---

## 9. Matriz resumida de alcance por plan

| Área | Plan Normal | Plan Pro | Plan Premium |
|---|---|---|---|
| Autenticación | Sí | Sí | Sí |
| Usuarios | Sí | Sí | Sí |
| Matrículas | Sí | Sí | Sí |
| Gestión académica básica | Sí | Sí | Sí |
| Dashboard | Sí | Sí | Sí |
| Calificaciones | No | Sí | Sí |
| Observaciones | No | Sí | Sí |
| Asistencia | No | Sí | Sí |
| Boletines y reportes | No | Sí | Sí |
| Comunicados | No | Sí | Sí |
| Portal estudiante/acudiente | No | Sí | Sí |
| Landing administrativa | No | No | Sí |
| Calendario avanzado | No | No | Sí |
| Soporte | No | No | Sí |
| Auditoría | No | No | Sí |
| Superadmin / multi-tenant | No | No | Sí |

---

## 10. Recomendación práctica

Si se desea implementar este proyecto en etapas reales, lo más sano es hacerlo así:

1. Implementar primero el Plan Normal como MVP institucional.
2. Agregar el Plan Pro cuando la operación básica ya esté estable.
3. Desarrollar el Plan Premium solo cuando el colegio requiera mayor cobertura, escalabilidad o gestión institucional avanzada.

### Recomendación de prioridad

- Prioridad 1: usuarios, matrícula, gestión académica, panel administrativo.
- Prioridad 2: calificaciones, observaciones, asistencia y reportes.
- Prioridad 3: landing, calendario, soporte, auditoría y superadmin.

---

## 11. Conclusión

Sí es viable dividir este proyecto en tres planes de implementación: Normal, Pro y Premium. La arquitectura actual ya está preparada para soportar esa estrategia, porque el sistema está bien modularizado y muchos módulos ya existen o están bien estructurados en Laravel.

La clave no es eliminar todo lo que existe, sino decidir qué debe entregarse primero para que el sistema sea útil, y qué se puede dejar para fases posteriores sin perder consistencia técnica.
