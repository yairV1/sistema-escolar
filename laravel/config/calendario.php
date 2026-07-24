<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Adjuntos de eventos
    |--------------------------------------------------------------------------
    |
    | Límites de subida para evento_adjuntos. 10 MB (mayor que los 2 MB de
    | logos/galería del colegio) porque acá se esperan circulares/agendas en
    | PDF de varias páginas, no solo fotos.
    |
    */

    'max_adjunto_kb' => 10240,

    'mimes_permitidos' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'],

    /*
    |--------------------------------------------------------------------------
    | Recordatorios
    |--------------------------------------------------------------------------
    |
    | Presets fijos de "avisarme N minutos antes" que ofrece el checkbox del
    | modal de eventos — sin input numérico libre, mismo criterio que el
    | <select> de colores de categoría (Fase 2). La clave más alta acota la
    | ventana de búsqueda del comando calendario:enviar-recordatorios.
    |
    */

    'recordatorio_opciones_minutos' => [
        10 => '10 minutos antes',
        30 => '30 minutos antes',
        60 => '1 hora antes',
        1440 => '1 día antes',
        10080 => '1 semana antes',
    ],

];
