# 🏫 Sistema de Gestión Escolar — EduGestión

> Plataforma web académica integral para la administración completa de colegios y escuelas.  
> Diseñada para ser escalable, multirol y lista para producción.

---

## 📌 ¿Qué es este proyecto?

**EduGestión** es una base de datos relacional profesional diseñada para soportar una plataforma web de gestión escolar completa. Su propósito es centralizar toda la información académica, administrativa y disciplinaria de una institución educativa en un solo sistema, permitiendo que cada actor del colegio (administradores, profesores, estudiantes y padres de familia) tenga acceso a la información que le corresponde según su rol.

El diseño está normalizado hasta la **Tercera Forma Normal (3FN)**, construido sobre **MySQL con InnoDB**, optimizado para ejecutarse en entornos **XAMPP / phpMyAdmin** y preparado para escalar hacia una aplicación web completa.

---

## 🎯 Objetivo del proyecto

Proveer una estructura de base de datos sólida, profesional y lista para construir encima de ella un sistema web que permita:

- Gestionar todos los actores de una institución educativa bajo un sistema de **roles y permisos**.
- Registrar y consultar **calificaciones, asistencia, horarios y boletines** de manera eficiente.
- Llevar el **observador del estudiante** (académico, disciplinario, convivencial y positivo).
- Administrar **matrículas, pagos y pensiones** escolares.
- Enviar **notificaciones internas** y preparar comunicaciones por correo o WhatsApp.
- Dar a los **padres de familia y acudientes** visibilidad sobre el proceso académico de sus hijos.

---

## 👥 Roles del sistema

| Rol | Descripción |
|-----|-------------|
| 🔑 **Administrador** | Acceso total al sistema y configuración general |
| 🏛️ **Directivo** | Visibilidad institucional y toma de decisiones |
| 📋 **Coordinador** | Gestión académica y de convivencia |
| 📁 **Secretario/a** | Manejo de matrículas, documentos y datos |
| 👨‍🏫 **Profesor** | Registro de notas, asistencia y observaciones |
| 🎒 **Estudiante** | Consulta de notas, horarios y boletines |
| 👨‍👩‍👧 **Acudiente** | Seguimiento del proceso académico de sus acudidos |

---

## 📚 Módulos cubiertos por la base de datos

1. ✅ Autenticación e inicio de sesión por roles
2. ✅ Gestión de usuarios y perfiles
3. ✅ Gestión de estudiantes
4. ✅ Gestión de profesores
5. ✅ Gestión de acudientes / padres de familia
6. ✅ Relación estudiante–acudiente con parentesco y jerarquía
7. ✅ Gestión de cursos / grados por año lectivo
8. ✅ Catálogo de materias
9. ✅ Asignaciones académicas (profesor + materia + curso)
10. ✅ Matrículas académicas por año lectivo
11. ✅ Periodos académicos
12. ✅ Tipos de actividades evaluativas
13. ✅ Registro de actividades y evaluaciones
14. ✅ Calificaciones por actividad (detalle de notas)
15. ✅ Generación y almacenamiento de boletines
16. ✅ Registro de asistencia diaria
17. ✅ Horarios académicos semanales
18. ✅ Observador del estudiante (disciplinario, académico, positivo)
19. ✅ Notificaciones internas del sistema
20. ✅ Preparación para envío por correo y WhatsApp
21. ✅ Módulo de pagos y conceptos de cobro

---

## 🗂️ Tablas de la base de datos

```
colegio_db
│
├── roles                      → Roles del sistema
├── usuarios                   → Tabla central de autenticación
├── estudiantes                → Datos extendidos del estudiante
├── profesores                 → Datos laborales del docente
├── acudientes                 → Padres / acudientes con acceso al sistema
├── estudiante_acudiente       → Relación N:M estudiante ↔ acudiente
│
├── cursos                     → Grados escolares por año y jornada
├── materias                   → Catálogo de asignaturas
├── asignaciones_academicas    → Profesor + materia + curso + año
├── matriculas                 → Matrícula del estudiante por año lectivo
│
├── periodos_academicos        → Periodos del año escolar
├── tipos_actividad            → Catálogo: tarea, quiz, examen, etc.
├── actividades                → Actividades evaluativas por asignación
├── detalle_notas              → Calificación individual por actividad
│
├── asistencia                 → Registro diario de asistencia
├── horarios                   → Horario semanal por asignación
├── observaciones              → Observador del estudiante
│
├── notificaciones             → Notificaciones internas y externas
├── boletines                  → Boletín por estudiante y periodo
├── boletin_detalle            → Nota definitiva por materia en el boletín
│
├── conceptos_pago             → Catálogo: matrícula, pensión, uniforme...
└── pagos                      → Registro de pagos por estudiante
```

**Total: 22 tablas** | Motor: InnoDB | Charset: utf8mb4

---

## 🔗 Relaciones principales

```
roles ──────────────────── usuarios
                               │
             ┌─────────────────┼──────────────────┐
             │                 │                  │
        estudiantes        profesores          acudientes
             │                 │                  │
             └────── estudiante_acudiente ─────────┘
             │
        matriculas ──── cursos ──── asignaciones_academicas
                                            │
                          ┌─────────────────┼──────────────────┐
                          │                 │                  │
                       horarios         actividades        asistencia
                                            │
                                      detalle_notas
                                            │
                                         boletines
                                            │
                                      boletin_detalle
```

---

## ⚙️ Tecnologías requeridas

| Tecnología | Versión recomendada |
|-----------|-------------------|
| MySQL | 5.7+ / 8.0 |
| XAMPP | 3.3+ |
| phpMyAdmin | 5.x |
| PHP *(para el backend)* | 8.0+ |

---

## 🚀 Instalación rápida

### 1. Clonar o descargar el proyecto
```bash
git@github.com:yairV1/sistema-escolar.git
```

### 2. Iniciar XAMPP
Asegúrate de que los servicios **Apache** y **MySQL** estén activos.

### 3. Importar la base de datos en phpMyAdmin
1. Abre tu navegador en `http://localhost/phpmyadmin`
2. Ve a la pestaña **Importar**
3. Selecciona el archivo `colegio_db.sql`
4. Formato: **SQL**
5. Haz clic en **Continuar**

La base de datos `colegio_db` se creará automáticamente con todas sus tablas, relaciones e inserts iniciales.

### 4. Verificar la instalación
Deberías ver **22 tablas** creadas dentro de `colegio_db`, incluyendo los datos iniciales en:
- `roles` (7 registros)
- `tipos_actividad` (9 registros)
- `conceptos_pago` (6 registros)

---

## 📁 Estructura del repositorio

```
edugestion/
├── database/
│   └── colegio_db.sql       ← Script principal de la base de datos
├── README.md                ← Este archivo
└── (próximamente)
    ├── backend/             ← API en PHP / Laravel / Node.js
    └── frontend/            ← Interfaz web (HTML, CSS, JS o framework)
```

---

## 🧭 Hoja de ruta (Roadmap)

### ✅ Versión 1.0 — Base de datos
- [x] Diseño relacional completo normalizado (3FN)
- [x] 22 tablas con llaves foráneas, índices y restricciones
- [x] Inserts iniciales de catálogos
- [x] Compatible con XAMPP / phpMyAdmin

### 🔄 Versión 1.1 — Backend (próximamente)
- [ ] API REST para autenticación por roles
- [ ] Endpoints para notas, asistencia y boletines
- [ ] Sistema de notificaciones

### 🔄 Versión 2.0 — Mejoras de base de datos
- [ ] Tabla `log_accesos` para auditoría de sesiones
- [ ] Tabla `permisos` granular por módulo
- [ ] Tabla `anios_lectivos` como entidad propia
- [ ] Soporte para calificaciones por competencias (saber, saber hacer, ser)
- [ ] Tabla `recuperaciones` para habilitaciones

---

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Si deseas mejorar el diseño de la base de datos, agregar módulos o iniciar el desarrollo del frontend/backend, puedes:

1. Hacer un **fork** del repositorio
2. Crear una rama: `git checkout -b feature/mi-mejora`
3. Hacer commit: `git commit -m 'Agrego tabla de permisos granulares'`
4. Abrir un **Pull Request**

---

## 📄 Licencia

Este proyecto está bajo la licencia **MIT**. Puedes usarlo, modificarlo y distribuirlo libremente con atribución.

---

## 👨‍💻 Autor

Desarrollado con fines educativos y profesionales para instituciones de educación básica y media.  
Si tienes dudas o sugerencias, abre un **Issue** en el repositorio.

---

> *"Una buena base de datos es el cimiento de cualquier sistema robusto."*
