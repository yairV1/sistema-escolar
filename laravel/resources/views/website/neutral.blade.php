<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{ $colegioConfiguracion->nombre_colegio }}</title>
  <link rel="icon" href="{{ asset('favicon.ico') }}">
  <style>
    :root { --fondo: #f6f7f9; --texto: #1f2933; --suave: #52606d; --acento: #2563eb; }
    @media (prefers-color-scheme: dark) { :root { --fondo: #12161c; --texto: #e4e7eb; --suave: #9aa5b1; --acento: #60a5fa; } }
    body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: var(--fondo); color: var(--texto); font-family: system-ui, sans-serif; }
    main { max-width: 32rem; padding: 2rem 1rem; text-align: center; }
    h1 { font-size: 1.5rem; margin: 0 0 .75rem; }
    p { color: var(--suave); line-height: 1.5; margin: 0 0 1.5rem; }
    a { display: inline-block; padding: .6rem 1.2rem; border-radius: .5rem; background: var(--acento); color: #fff; text-decoration: none; }
  </style>
</head>
<body>
  {{-- Etapa 2 · B7: «/» sin PORTAL_PUBLICO_SLUG. Página neutral: no muestra ningún colegio. --}}
  <main>
    <h1>{{ $colegioConfiguracion->nombre_colegio }}</h1>
    <p>Plataforma de gestión escolar. Cada colegio tiene su propio sitio público; pide a tu colegio su dirección.</p>
    <a href="{{ route('login') }}">Ingresar</a>
  </main>
</body>
</html>
