<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Acceso denegado | Colegio San Cristóbal</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;700;800&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', system-ui, sans-serif;
            background: #fbfdfb;
            color: #1f2d24;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 2rem 1rem;
        }
        .box {
            text-align: center;
            max-width: 480px;
        }
        .box i {
            font-size: 3.4rem;
            color: #e53e3e;
            margin-bottom: 1rem;
        }
        h1 {
            font-family: 'Baloo 2', system-ui, sans-serif;
            font-weight: 800;
            font-size: 1.8rem;
            margin-bottom: .6rem;
        }
        p {
            color: #5b6b60;
            font-size: 1.02rem;
            line-height: 1.6;
            margin-bottom: 1.8rem;
        }
        .btn-volver {
            text-decoration: none;
            background: #0a932c;
            color: #fff;
            font-weight: 700;
            padding: .8rem 1.6rem;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
        }
        .btn-volver:hover { background: #076e21; color: #fff; }
    </style>
</head>
<body>
    <div class="box">
        <i class="fa-solid fa-lock"></i>
        <h1>Acceso denegado</h1>
        <p>No tienes permisos para ver esta página. Si crees que esto es un error, contacta al administrador del sistema.</p>
        <a href="<?= BASE_URL ?>login" class="btn-volver"><i class="fa-solid fa-right-to-bracket"></i> Ir al inicio de sesión</a>
    </div>
</body>
</html>
