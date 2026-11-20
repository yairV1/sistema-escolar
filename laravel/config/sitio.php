<?php

/*
 * Etapa 2 · B7 — sitio público por institución.
 *
 * portal_publico_slug: si se define, la raíz «/» muestra el sitio de esa
 * institución (debe existir y estar activa; si no, 404). Si NO se define, «/»
 * muestra una página neutral de la plataforma: nunca se elige un colegio por
 * defecto. Cada colegio tiene además su sitio en /sitio/{slug}.
 */
return [
    'portal_publico_slug' => env('PORTAL_PUBLICO_SLUG'),
];
