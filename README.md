# 🏫 Sistema de Gestión Escolar — Colegio San Cristóbal

> Plataforma web académica para la administración completa del colegio: matrículas, usuarios, gestión académica, evaluaciones, comunicados y más.

---

## 📌 Estado actual del proyecto

Este repositorio contiene **dos aplicaciones que conviven durante una migración incremental**, ambas conectadas a la misma base de datos MySQL (`colegio`):

| | `legacy/` | `laravel/` |
|---|---|---|
| Tecnología | PHP puro, MVC casero, sin framework | Laravel 12 |
| Rol | Sistema original, en retirada progresiva | Sistema destino de la migración |
| Estado | Los módulos ya migrados redirigen automáticamente a Laravel | Recibe cada módulo migrado, uno a la vez |

La migración es tipo **"strangler fig"**: cada módulo se reconstruye en Laravel, se verifica que funcione igual o mejor que el original, y recién ahí la ruta correspondiente en `legacy/index.php` pasa a redirigir hacia Laravel. Nunca se apaga el legacy de golpe.

### Módulos ya migrados a Laravel
Login/autenticación, Dashboard, Listados (estudiantes/docentes/administrativos), Matrículas, Registro (estudiantes/docentes/administrativos), Gestión Académica (materias/cursos/asignaciones).

### Módulos pendientes (todavía en `legacy/`)
Calificaciones/Evaluaciones, Observaciones, Boletines, Estadísticas, Comunicados, Editar Landing Page, Perfil, Portal de estudiante/acudiente.

---

## 🗂️ Estructura del repositorio

```
colegio/
├── legacy/          → Sistema PHP puro (en retirada)
│   ├── app/           (controllers/, helpers/, models/, views/)
│   ├── config/         (config.php, database.php, mail.php, menu.php)
│   ├── public/          (assets estáticos: CSS, JS, imágenes)
│   ├── vendor/
│   ├── composer.json
│   ├── index.php        (router / punto de entrada)
│   └── .htaccess
│
├── laravel/         → Sistema destino de la migración (Laravel 12)
│   ├── app/            (Controllers, Models, Services, Requests, Middleware)
│   ├── resources/       (views, js, css)
│   ├── routes/
│   ├── config/
│   ├── database/        (migrations, seeders)
│   └── ...               (estructura estándar de Laravel, sin modificar)
│
├── scripts/          → Herramientas de desarrollo (composer.phar)
│
└── docs/              → Documentación técnica del proyecto
```

---

## ⚙️ Requisitos

| Tecnología | Versión |
|---|---|
| PHP | 8.2+ |
| MySQL | 5.7+ / 8.0 |
| Composer | 2.x |
| Node.js | 18+ (para compilar assets de Laravel con Vite) |
| XAMPP (desarrollo local) | 8.2.x |

---

## 🚀 Puesta en marcha (desarrollo local)

### Base de datos
1. Iniciar Apache y MySQL desde XAMPP.
2. Crear/importar la base de datos `colegio` en phpMyAdmin.

### Legacy (`legacy/`)
```bash
cd legacy
php ../scripts/composer.phar install
```
Se accede vía `http://localhost/colegio/legacy/`.

### Laravel (`laravel/`)
```bash
cd laravel
composer install
npm install
php artisan migrate
npm run build      # o `npm run dev` en desarrollo
```
Se accede vía `http://localhost/colegio/laravel/public/`.

> Ambas apps comparten la misma base de datos (`colegio`), pero tienen **sesiones de autenticación independientes** (una usa sesiones nativas de PHP, la otra el driver `database` de Laravel) — es normal tener que iniciar sesión por separado en cada una mientras dure la migración.

---

## 👥 Roles del sistema

| Rol | Descripción |
|---|---|
| 🔑 Administrador | Acceso total al sistema y configuración general |
| 🏛️ Directivo | Visibilidad institucional y toma de decisiones |
| 📋 Coordinador | Gestión académica y de convivencia |
| 📁 Secretario/a | Manejo de matrículas, documentos y datos |
| 👨‍🏫 Profesor | Registro de notas, asistencia y observaciones |
| 🎒 Estudiante | Consulta de notas, horarios y boletines |
| 👨‍👩‍👧 Acudiente | Seguimiento del proceso académico de sus acudidos |

---

## 📄 Licencia

MIT.
