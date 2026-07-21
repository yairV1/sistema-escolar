    <!-- title: Rutas del panel del rector -->

    # Rutas del panel del rector

    Este documento resume las rutas que forman parte del panel administrativo del sistema, considerando que actualmente el único rol operativo para ese panel es el rol `rector`.

    ## Resumen general

    En la implementación actual, no existe un panel exclusivo para `rector` con permisos distintos a los de `admin`. Ambos comparten el mismo acceso porque las rutas del panel están protegidas con el middleware `role:admin,rector`.

    Eso significa que:

    - `rector` puede entrar al panel administrativo.
    - `admin` puede entrar al mismo panel.
    - No hay rutas que sean exclusivas de `rector` ni vistas distintas para ese rol.

    ## Rutas del panel

    ### Inicio
    - `GET /inicio` → vista de inicio del panel

    ### Listados
    - `GET /listados`
    - `POST /listados/estudiantes/{estudiante}/desactivar`
    - `POST /listados/estudiantes/{estudiante}/activar`
    - `POST /listados/docentes/{profesor}/desactivar`
    - `POST /listados/docentes/{profesor}/activar`
    - `POST /listados/administrativos/{usuario}/desactivar`
    - `POST /listados/administrativos/{usuario}/activar`

    ### Matrículas
    - `GET /matriculas`
    - `POST /matriculas/{matricula}/estado`

    ### Gestión académica
    - `GET /gestion-academica`
    - `GET /gestion-academica/cursos/{curso}`
    - `POST /gestion-academica/materias`
    - `POST /gestion-academica/materias/{materia}`
    - `POST /gestion-academica/materias/{materia}/desactivar`
    - `POST /gestion-academica/materias/{materia}/activar`
    - `POST /gestion-academica/cursos`
    - `POST /gestion-academica/cursos/{curso}`
    - `POST /gestion-academica/cursos/{curso}/desactivar`
    - `POST /gestion-academica/cursos/{curso}/activar`
    - `POST /gestion-academica/asignaciones`
    - `POST /gestion-academica/asignaciones/{asignacion}/desactivar`
    - `POST /gestion-academica/asignaciones/{asignacion}/activar`
    - `POST /gestion-academica/horarios`
    - `POST /gestion-academica/horarios/{horario}`
    - `POST /gestion-academica/horarios/{horario}/desactivar`
    - `POST /gestion-academica/horarios/{horario}/activar`

    ### Registro de usuarios
    - `GET /registro/estudiantes`
    - `POST /registro/estudiantes`
    - `GET /registro/estudiantes/{estudiante}/editar`
    - `POST /registro/estudiantes/{estudiante}`
    - `GET /registro/docentes`
    - `POST /registro/docentes`
    - `GET /registro/docentes/{profesor}/editar`
    - `POST /registro/docentes/{profesor}`
    - `GET /registro/administrativos`
    - `POST /registro/administrativos`
    - `GET /registro/administrativos/{usuario}/editar`
    - `POST /registro/administrativos/{usuario}`

    ### Calificaciones
    - `GET /calificaciones`
    - `POST /calificaciones/periodos`
    - `POST /calificaciones/periodos/{periodo}`
    - `POST /calificaciones/periodos/{periodo}/desactivar`
    - `POST /calificaciones/periodos/{periodo}/activar`
    - `POST /calificaciones/tipos-actividad`
    - `POST /calificaciones/tipos-actividad/{tipoActividad}`
    - `POST /calificaciones/tipos-actividad/{tipoActividad}/desactivar`
    - `POST /calificaciones/tipos-actividad/{tipoActividad}/activar`
    - `GET /calificaciones/asignaciones/{asignacion}`
    - `POST /calificaciones/asignaciones/{asignacion}/actividades`
    - `POST /calificaciones/actividades/{actividad}`
    - `POST /calificaciones/actividades/{actividad}/desactivar`
    - `POST /calificaciones/actividades/{actividad}/activar`
    - `GET /calificaciones/actividades/{actividad}/notas`
    - `POST /calificaciones/actividades/{actividad}/notas`

    ### Observaciones
    - `GET /observaciones`
    - `POST /observaciones`
    - `POST /observaciones/{observacion}`
    - `POST /observaciones/{observacion}/desactivar`
    - `POST /observaciones/{observacion}/activar`

    ### Boletines
    - `GET /boletines`
    - `POST /boletines/generar`
    - `GET /boletines/{boletin}`
    - `POST /boletines/{boletin}/publicar`
    - `POST /boletines/{boletin}/anular`
    - `POST /boletines/{boletin}/borrador`

    ### Asistencia
    - `GET /asistencia/asignaciones/{asignacion}`
    - `POST /asistencia/asignaciones/{asignacion}`
    - `GET /asistencia/asignaciones/{asignacion}/historial`

    ### Reportes y comunicación
    - `GET /estadisticas`
    - `GET /comunicados`
    - `POST /comunicados`
    - `GET /editar-landing`
    - `POST /editar-landing/contenido`
    - `POST /editar-landing/noticias`
    - `POST /editar-landing/noticias/{noticia}`
    - `POST /editar-landing/noticias/{noticia}/desactivar`
    - `POST /editar-landing/noticias/{noticia}/activar`
    - `POST /editar-landing/galeria`
    - `POST /editar-landing/galeria/{galeria}`
    - `POST /editar-landing/galeria/{galeria}/desactivar`
    - `POST /editar-landing/galeria/{galeria}/activar`

    ## Rutas de perfil y autenticación
    - `GET /perfil` y `POST /perfil`
    - `POST /perfil/password`
    - `POST /logout`

    ## Fuente de definición
    - Archivo de rutas: `laravel/routes/web.php`
    - Middleware de permisos: `role:admin,rector`
    - Modelo de roles: `laravel/app/Modules/Auth/Models/Usuario.php`

    ## Conclusión

    Actualmente el sistema no tiene un rol distinto para el rector en el panel; el acceso está consolidado en un único conjunto de rutas compartido entre `admin` y `rector`.
