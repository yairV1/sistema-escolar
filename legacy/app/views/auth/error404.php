<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Página no encontrada | Colegio San Cristóbal</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;600;700;800&family=Nunito:ital,wght@0,400;0,600;0,700;0,800;1,600&display=swap" rel="stylesheet">

    <style>
        :root {
            --verde-principal: #0a932c;
            --verde-oscuro: #076e21;
            --verde-suave: #e7f7ec;
            --amarillo: #ffb020;
            --amarillo-suave: #fff3dd;
            --morado: #7c6ff0;
            --morado-suave: #eeecfe;
            --azul-cielo: #eaf5ff;
            --texto-oscuro: #1f2d24;
            --texto-suave: #5b6b60;
            --blanco: #ffffff;
            --borde-suave: #e3ede6;
            --sombra-suave: 0 10px 30px -12px rgba(10, 147, 44, .18);
            --sombra-boton: 0 8px 20px -6px rgba(10, 147, 44, .45);
            --radio-xl: 28px;
            --radio-lg: 20px;
            --radio-md: 14px;
            --fuente-display: 'Baloo 2', system-ui, sans-serif;
            --fuente-cuerpo: 'Nunito', system-ui, sans-serif;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: var(--fuente-cuerpo);
            color: var(--texto-oscuro);
            background: #fbfdfb;
            overflow-x: hidden;
            margin: 0;
        }

        /* ---------- Fondo decorativo ---------- */
        .fondo-manchas {
            position: fixed;
            inset: 0;
            z-index: -2;
            overflow: hidden;
            pointer-events: none;
        }

        .mancha {
            position: absolute;
            border-radius: 50%;
            filter: blur(2px);
            opacity: .5;
        }

        .mancha-1 {
            width: 520px;
            height: 520px;
            background: radial-gradient(circle at 30% 30%, var(--morado-suave), transparent 70%);
            top: -160px;
            left: -140px;
        }

        .mancha-2 {
            width: 420px;
            height: 420px;
            background: radial-gradient(circle at 60% 40%, var(--azul-cielo), transparent 70%);
            top: 10%;
            right: -120px;
        }

        .mancha-3 {
            width: 600px;
            height: 600px;
            background: radial-gradient(circle at 40% 60%, var(--verde-suave), transparent 70%);
            bottom: -220px;
            left: 35%;
        }

        /* ---------- Navbar ---------- */
        .navbar-escolar {
            background: rgba(255, 255, 255, .85);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--borde-suave);
            padding: .9rem 0;
        }

        .marca-colegio {
            display: flex;
            align-items: center;
            gap: .7rem;
        }

        .icono-colegio {
            width: 46px;
            height: 46px;
            background: linear-gradient(145deg, var(--verde-principal), var(--verde-oscuro));
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.3rem;
            box-shadow: 0 6px 14px -4px rgba(10, 147, 44, .5);
            flex-shrink: 0;
        }

        .marca-texto h1 {
            font-family: var(--fuente-display);
            font-size: 1.15rem;
            font-weight: 700;
            margin: 0;
            line-height: 1.15;
            color: var(--texto-oscuro);
        }

        .marca-texto p {
            margin: 0;
            font-size: .72rem;
            color: var(--texto-suave);
            font-weight: 600;
            letter-spacing: .2px;
        }

        .enlace-nav {
            color: var(--texto-suave) !important;
            font-weight: 700;
            font-size: .92rem;
            display: flex;
            align-items: center;
            gap: .4rem;
            padding: .5rem .9rem !important;
            border-radius: 10px;
            transition: .2s ease;
        }

        .enlace-nav:hover {
            color: var(--verde-oscuro) !important;
            background: var(--verde-suave);
        }

        .btn-dashboard-nav {
            background: var(--verde-principal);
            color: #fff !important;
            font-weight: 700;
            border-radius: 12px;
            padding: .6rem 1.3rem !important;
            box-shadow: var(--sombra-boton);
            transition: .2s ease;
            text-decoration: none;
        }

        .btn-dashboard-nav:hover {
            background: var(--verde-oscuro);
            color: #fff !important;
            transform: translateY(-2px);
        }

        /* ---------- Hero 404 ---------- */
        .hero-404 {
            position: relative;
            padding: 3.2rem 0 4rem;
            min-height: 72vh;
            display: flex;
            align-items: center;
        }

        .numero-404 {
            font-family: var(--fuente-display);
            font-weight: 800;
            font-size: clamp(4.5rem, 13vw, 9.5rem);
            line-height: .9;
            display: flex;
            gap: .15em;
            letter-spacing: -.02em;
        }

        .digito {
            display: inline-block;
            position: relative;
            animation: flotar 3.4s ease-in-out infinite;
            filter: drop-shadow(0 14px 18px rgba(0, 0, 0, .12));
        }

        .digito-1 {
            color: var(--amarillo);
            animation-delay: 0s;
        }

        .digito-2 {
            color: var(--morado);
            animation-delay: .25s;
        }

        .digito-3 {
            color: var(--verde-principal);
            animation-delay: .5s;
        }

        @keyframes flotar {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-16px) rotate(-2deg);
            }
        }

        .digito-2 {
            animation-name: flotar-centro;
        }

        @keyframes flotar-centro {

            0%,
            100% {
                transform: translateY(0) scale(1);
            }

            50% {
                transform: translateY(-20px) scale(1.03);
            }
        }

        .chispa {
            position: absolute;
            color: var(--amarillo);
            animation: brillar 2.2s ease-in-out infinite;
        }

        @keyframes brillar {

            0%,
            100% {
                opacity: .35;
                transform: scale(.85) rotate(0deg);
            }

            50% {
                opacity: 1;
                transform: scale(1.15) rotate(15deg);
            }
        }

        .signo-interrogacion {
            position: absolute;
            font-family: var(--fuente-display);
            font-weight: 800;
            color: var(--morado);
            animation: rebote-suave 2.6s ease-in-out infinite;
            filter: drop-shadow(0 8px 10px rgba(124, 111, 240, .3));
        }

        @keyframes rebote-suave {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-14px);
            }
        }

        .avion-papel {
            position: absolute;
            color: #9fc9ee;
            animation: volar 5s ease-in-out infinite;
        }

        @keyframes volar {
            0% {
                transform: translate(0, 0) rotate(8deg);
            }

            50% {
                transform: translate(-14px, -22px) rotate(-4deg);
            }

            100% {
                transform: translate(0, 0) rotate(8deg);
            }
        }

        .titulo-404 {
            font-family: var(--fuente-display);
            font-weight: 700;
            font-size: clamp(1.7rem, 3.4vw, 2.5rem);
            color: var(--texto-oscuro);
            margin-bottom: .75rem;
        }

        .texto-404 {
            color: var(--texto-suave);
            font-size: 1.05rem;
            line-height: 1.6;
            max-width: 520px;
            margin-bottom: 1.8rem;
        }

        .texto-404 strong {
            color: var(--verde-oscuro);
        }

        .acciones-404 {
            display: flex;
            gap: .85rem;
            flex-wrap: wrap;
        }

        .btn-primario-404 {
            text-decoration: none;
            background: var(--verde-principal);
            border: none;
            color: #fff;
            font-weight: 700;
            padding: .85rem 1.7rem;
            border-radius: 14px;
            box-shadow: var(--sombra-boton);
            display: flex;
            align-items: center;
            gap: .55rem;
            transition: transform .2s ease, background .2s ease, box-shadow .2s ease;
        }

        .btn-primario-404:hover {
            background: var(--verde-oscuro);
            color: #fff;
            transform: translateY(-3px);
            box-shadow: 0 14px 24px -6px rgba(10, 147, 44, .55);
        }

        .btn-secundario-404 {
            background: #fff;
            border: 2px solid var(--borde-suave);
            color: var(--texto-oscuro);
            font-weight: 700;
            padding: .8rem 1.6rem;
            border-radius: 14px;
            display: flex;
            align-items: center;
            gap: .55rem;
            transition: .2s ease;
        }

        .btn-secundario-404:hover {
            border-color: var(--verde-principal);
            color: var(--verde-oscuro);
            background: var(--verde-suave);
            transform: translateY(-3px);
        }

        /* ---------- Ilustraciones laterales ---------- */
        .escena-ilustrada {
            position: relative;
            height: 100%;
            min-height: 420px;
            padding-bottom: 20px;
        }

        /* Mochila */
        .mochila-wrap {
            position: absolute;
            left: 2%;
            bottom: 0;
            width: 230px;
            animation: flotar-lento 4.5s ease-in-out infinite;
        }

        @keyframes flotar-lento {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        /* Búho leyendo */
        .buho-wrap {
            position: absolute;
            right: 0;
            bottom: 0;
            width: 230px;
            animation: balanceo 5s ease-in-out infinite;
            transform-origin: bottom center;
        }

        @keyframes balanceo {

            0%,
            100% {
                transform: rotate(0deg);
            }

            50% {
                transform: rotate(1.5deg);
            }
        }

        .nube-decorativa {
            position: absolute;
            color: #fff;
            filter: drop-shadow(0 6px 10px rgba(0, 0, 0, .06));
            animation: derivar 8s ease-in-out infinite;
        }

        @keyframes derivar {

            0%,
            100% {
                transform: translateX(0);
            }

            50% {
                transform: translateX(18px);
            }
        }

        .estrella-decor {
            position: absolute;
            color: var(--amarillo);
            animation: girar-brillo 3.5s ease-in-out infinite;
        }

        @keyframes girar-brillo {

            0%,
            100% {
                opacity: .5;
                transform: rotate(0deg) scale(.9);
            }

            50% {
                opacity: 1;
                transform: rotate(20deg) scale(1.1);
            }
        }

        /* ---------- Sección "Qué puedes hacer" ---------- */
        .seccion-acciones {
            background: var(--blanco);
            border-top: 1px solid var(--borde-suave);
            padding: 3.2rem 0 3.6rem;
            position: relative;
        }

        .titulo-seccion {
            font-family: var(--fuente-display);
            font-weight: 700;
            font-size: 1.5rem;
            text-align: center;
            margin-bottom: 2rem;
            color: var(--texto-oscuro);
        }

        .tarjeta-accion {
            background: #fff;
            border: 1px solid var(--borde-suave);
            border-radius: var(--radio-lg);
            padding: 1.5rem 1.4rem;
            height: 100%;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
            text-decoration: none;
            cursor: pointer;
        }

        .tarjeta-accion:hover {
            transform: translateY(-6px);
            box-shadow: var(--sombra-suave);
            border-color: var(--verde-principal);
        }

        .icono-tarjeta {
            width: 50px;
            height: 50px;
            border-radius: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
            transition: transform .25s ease;
        }

        .tarjeta-accion:hover .icono-tarjeta {
            transform: scale(1.1) rotate(-4deg);
        }

        .icono-tarjeta.morado {
            background: var(--morado-suave);
            color: var(--morado);
        }

        .icono-tarjeta.amarillo {
            background: var(--amarillo-suave);
            color: #c77d00;
        }

        .icono-tarjeta.verde {
            background: var(--verde-suave);
            color: var(--verde-principal);
        }

        .texto-tarjeta h3 {
            font-family: var(--fuente-display);
            font-weight: 700;
            font-size: 1.02rem;
            margin: 0 0 .2rem;
            color: var(--texto-oscuro);
        }

        .texto-tarjeta p {
            margin: 0;
            font-size: .85rem;
            color: var(--texto-suave);
        }

        /* ---------- Footer con ola ---------- */
        .footer-ola {
            position: relative;
            padding-top: 70px;
        }

        .ola-svg {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            line-height: 0;
        }

        .contenido-footer {
            background: linear-gradient(160deg, var(--verde-principal), var(--verde-oscuro));
            padding: 1.6rem 0 1.8rem;
            text-align: center;
            color: rgba(255, 255, 255, .92);
            font-size: .88rem;
        }

        .contenido-footer i {
            color: #ffb3c1;
        }

        /* ---------- Accesibilidad: foco visible ---------- */
        a:focus-visible,
        button:focus-visible {
            outline: 3px solid var(--morado);
            outline-offset: 3px;
            border-radius: 8px;
        }

        /* ---------- Responsive ---------- */
        @media (max-width: 991.98px) {
            .escena-ilustrada {
                min-height: 260px;
                margin-top: 2rem;
            }

            .mochila-wrap,
            .buho-wrap {
                width: 170px;
            }

            .hero-404 {
                text-align: center;
                padding: 3rem 0 2rem;
            }

            .numero-404 {
                justify-content: center;
            }

            .acciones-404 {
                justify-content: center;
            }

            .texto-404 {
                margin-left: auto;
                margin-right: auto;
            }
        }

        @media (max-width: 575.98px) {
            .marca-texto p {
                display: none;
            }

            .numero-404 {
                font-size: clamp(3.6rem, 22vw, 5rem);
            }

            .titulo-404 {
                font-size: 1.4rem;
            }

            .mochila-wrap {
                width: 120px;
                left: 0;
            }

            .buho-wrap {
                width: 130px;
            }

            .btn-primario-404,
            .btn-secundario-404 {
                width: 100%;
                justify-content: center;
            }

            .acciones-404 {
                flex-direction: column;
                width: 100%;
            }
        }

        /* ---------- Reduced motion ---------- */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: .001ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: .001ms !important;
            }
        }
    </style>
</head>

<body>

    <!-- Fondo decorativo -->
    <div class="fondo-manchas">
        <div class="mancha mancha-1"></div>
        <div class="mancha mancha-2"></div>
        <div class="mancha mancha-3"></div>
    </div>

    <!-- NAVBAR -->
    <nav class="navbar navbar-escolar sticky-top">
        <div class="container d-flex justify-content-between align-items-center flex-wrap">
            <div class="marca-colegio">
                <div class="icono-colegio"><i class="fa-solid fa-graduation-cap"></i></div>
                <div class="marca-texto">
                    <h1>Colegio San Cristóbal</h1>
                    <p>Aprender hoy, liderar mañana</p>
                </div>
            </div>
            <div class="d-none d-md-flex align-items-center gap-1">
                <a href="/colegio" class="enlace-nav"><i class="fa-solid fa-house"></i> Inicio</a>
                <a href="#" class="enlace-nav"><i class="fa-regular fa-circle-question"></i> Ayuda</a>
                <a href="#" class="enlace-nav"><i class="fa-regular fa-envelope"></i> Contacto</a>
                <a href="<?= BASE_URL . 'inicio' ?>" class="btn-dashboard-nav ms-2">Ir al Dashboard</a>
            </div>
        </div>
    </nav>

    <!-- HERO 404 -->
    <section class="hero-404">
        <div class="container">
            <div class="row align-items-center g-4">

                <!-- Columna izquierda: mensaje -->
                <div class="col-lg-6 order-2 order-lg-1">

                    <div class="position-relative" style="display:inline-block;">
                        <i class="fa-solid fa-sparkle chispa" style="left:-30px; top:-10px; font-size:1.1rem;" aria-hidden="true"></i>
                        <i class="fa-solid fa-star chispa" style="left:-45px; top:60px; font-size:.9rem; animation-delay:.6s;" aria-hidden="true"></i>

                        <div class="numero-404" role="img" aria-label="Error 404">
                            <span class="digito digito-1">4</span>
                            <span class="digito digito-2">0</span>
                            <span class="digito digito-3">4</span>
                        </div>

                        <i class="fa-solid fa-paper-plane avion-papel" style="right:-40px; top:0; font-size:1.4rem;" aria-hidden="true"></i>
                        <i class="fa-solid fa-star chispa" style="right:-20px; top:75%; font-size:1rem; animation-delay:1.1s;" aria-hidden="true"></i>
                    </div>

                    <h2 class="titulo-404">¡Ups! Página no encontrada</h2>
                    <p class="texto-404">
                        Parece que esta página se perdió en el camino del conocimiento.
                        Pero no te preocupes, <strong>juntos la encontraremos</strong> <i class="fa-solid fa-heart" style="color:#ff6b81;"></i>
                    </p>

                    <div class="acciones-404">
                        <a href="#" class="btn-primario-404">
                            <i class="fa-solid fa-house"></i> Volver al inicio
                        </a>
                        <button type="button" onclick="history.back()" class="btn-secundario-404">
                            <i class="fa-solid fa-arrow-left"></i> Ir atrás
                        </button>
                    </div>
                </div>

                <!-- Columna derecha: ilustraciones -->
                <div class="col-lg-6 order-1 order-lg-2">
                    <div class="escena-ilustrada">

                        <!-- Nubes y estrellas de fondo -->
                        <i class="fa-solid fa-cloud nube-decorativa" style="top:0%; right:15%; font-size:2.4rem;" aria-hidden="true"></i>
                        <i class="fa-solid fa-cloud nube-decorativa" style="top:10%; left:5%; font-size:1.6rem; animation-delay:1s;" aria-hidden="true"></i>
                        <i class="fa-solid fa-star estrella-decor" style="top:5%; left:20%; font-size:1.1rem;" aria-hidden="true"></i>
                        <i class="fa-solid fa-star estrella-decor" style="bottom:8%; left:0%; font-size:1rem; animation-delay:.8s;" aria-hidden="true"></i>

                        <!-- Signo de interrogación flotante -->
                        <div class="signo-interrogacion" style="right:28%; top:2%; font-size:2.6rem;">?</div>

                        <!-- Mochila (SVG ilustrado) -->
                        <div class="mochila-wrap">
                            <svg viewBox="0 0 220 240" xmlns="http://www.w3.org/2000/svg">
                                <ellipse cx="110" cy="225" rx="70" ry="10" fill="#000" opacity="0.06" />
                                <path d="M60 90 Q60 40 110 40 Q160 40 160 90 L160 100 L60 100 Z" fill="#5b4fce" />
                                <rect x="45" y="95" width="130" height="120" rx="24" fill="#1fb85e" />
                                <rect x="45" y="95" width="130" height="120" rx="24" fill="url(#sombraMochila)" />
                                <rect x="70" y="120" width="80" height="55" rx="10" fill="#ffffff" opacity="0.15" />
                                <rect x="82" y="130" width="56" height="8" rx="4" fill="#ffffff" opacity="0.55" />
                                <rect x="65" y="150" width="35" height="45" rx="8" fill="#ffb020" />
                                <circle cx="82" cy="172" r="4" fill="#c77d00" />
                                <path d="M85 95 L85 60 Q110 45 135 60 L135 95" stroke="#0d8f45" stroke-width="10" fill="none" stroke-linecap="round" />
                                <rect x="95" y="60" width="30" height="20" rx="6" fill="#ffb020" />
                                <g transform="translate(150,50) rotate(18)">
                                    <rect x="0" y="0" width="10" height="90" rx="5" fill="#ff8fa3" />
                                    <rect x="0" y="0" width="10" height="14" rx="5" fill="#e85d75" />
                                </g>
                                <g transform="translate(30,45) rotate(-8)">
                                    <rect x="0" y="0" width="9" height="70" rx="4.5" fill="#7c6ff0" />
                                </g>
                                <g transform="translate(45,35) rotate(6)">
                                    <rect x="0" y="0" width="9" height="80" rx="4.5" fill="#ffd166" />
                                </g>
                                <defs>
                                    <linearGradient id="sombraMochila" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="#000000" stop-opacity="0" />
                                        <stop offset="100%" stop-color="#000000" stop-opacity="0.08" />
                                    </linearGradient>
                                </defs>
                            </svg>
                        </div>

                        <!-- Búho leyendo (SVG ilustrado) -->
                        <div class="buho-wrap">
                            <svg viewBox="0 0 220 230" xmlns="http://www.w3.org/2000/svg">
                                <ellipse cx="110" cy="218" rx="80" ry="10" fill="#000" opacity="0.06" />
                                <rect x="120" y="150" width="55" height="18" rx="4" fill="#7c6ff0" />
                                <rect x="128" y="132" width="55" height="18" rx="4" fill="#1fb85e" />
                                <rect x="112" y="114" width="55" height="18" rx="4" fill="#ffb020" />
                                <ellipse cx="95" cy="120" rx="55" ry="60" fill="#c98a4b" />
                                <ellipse cx="95" cy="128" rx="42" ry="46" fill="#e8ad6f" />
                                <path d="M55 75 Q65 45 90 60 Z" fill="#c98a4b" />
                                <path d="M135 75 Q125 45 100 60 Z" fill="#c98a4b" />
                                <circle cx="75" cy="112" r="20" fill="#ffffff" />
                                <circle cx="115" cy="112" r="20" fill="#ffffff" />
                                <circle cx="75" cy="112" r="19" fill="none" stroke="#6b4a2b" stroke-width="3" />
                                <circle cx="115" cy="112" r="19" fill="none" stroke="#6b4a2b" stroke-width="3" />
                                <line x1="95" y1="112" x2="95" y2="112" stroke="#6b4a2b" stroke-width="3" />
                                <path d="M75 112 L115 112" stroke="#6b4a2b" stroke-width="3" />
                                <circle cx="75" cy="114" r="9" fill="#3b2a1a" />
                                <circle cx="115" cy="114" r="9" fill="#3b2a1a" />
                                <circle cx="72" cy="111" r="2.5" fill="#fff" />
                                <circle cx="112" cy="111" r="2.5" fill="#fff" />
                                <path d="M90 128 L100 128 L95 138 Z" fill="#ffb020" />
                                <ellipse cx="60" cy="145" rx="10" ry="16" fill="#c98a4b" transform="rotate(-15 60 145)" />
                                <ellipse cx="130" cy="145" rx="10" ry="16" fill="#c98a4b" transform="rotate(15 130 145)" />
                                <path d="M55 155 Q95 172 135 155 L135 165 Q95 182 55 165 Z" fill="#a9773f" />
                            </svg>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECCIÓN: ¿QUÉ PUEDES HACER? -->
    <section class="seccion-acciones">
        <div class="container">
            <h3 class="titulo-seccion">¿Qué puedes hacer?</h3>
            <div class="row g-3 justify-content-center">
                <div class="col-md-4">
                    <a href="#" class="tarjeta-accion">
                        <div class="icono-tarjeta morado"><i class="fa-solid fa-chart-simple"></i></div>
                        <div class="texto-tarjeta">
                            <h3>Ir al Dashboard</h3>
                            <p>Accede al panel principal</p>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="#" class="tarjeta-accion">
                        <div class="icono-tarjeta amarillo"><i class="fa-solid fa-graduation-cap"></i></div>
                        <div class="texto-tarjeta">
                            <h3>Explorar módulos</h3>
                            <p>Descubre todas las opciones</p>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="#" class="tarjeta-accion">
                        <div class="icono-tarjeta verde"><i class="fa-solid fa-life-ring"></i></div>
                        <div class="texto-tarjeta">
                            <h3>Centro de ayuda</h3>
                            <p>Estamos para ayudarte</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER CON OLA -->
    <footer class="footer-ola">
        <div class="ola-svg">
            <svg viewBox="0 0 1440 90" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" style="width:100%; height:70px; display:block;">
                <path fill="#e7f7ec" d="M0,40 C240,90 480,0 720,20 C960,40 1200,90 1440,40 L1440,90 L0,90 Z"></path>
                <path fill="#0a932c" opacity="0.9" d="M0,55 C240,20 480,90 720,55 C960,20 1200,70 1440,50 L1440,90 L0,90 Z"></path>
            </svg>
        </div>
        <div class="contenido-footer">
            © 2024 Colegio San Cristóbal. Todos los derechos reservados. <i class="fa-solid fa-heart"></i>
        </div>
    </footer>

</body>

</html>