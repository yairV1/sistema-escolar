<?php
$errores = $_SESSION['errores'] ?? [];
$datosPrevios = $_SESSION['datos_previos'] ?? [];
unset($_SESSION['errores'], $_SESSION['datos_previos']);

function val(string $campo, array $datos): string
{
    return htmlspecialchars($datos[$campo] ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Administrativo — COLEGIO</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/css/registro_administrativo.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/layouts/admin/css/Sidebar.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/css/admin.css" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    <?php include_once __DIR__ . '/../../layouts/administrativo/Sidebar.php'; ?>

    <!-- Overlay móvil -->
    <div class="admin-overlay" id="adminOverlay"></div>

    <main class="admin-main" id="adminMain">
      <div class="admin-container">
        <div class="admin-registro">

        <div class="admin-registro__header">
            <i class="fa-solid fa-user-tie admin-registro__header-icon"></i>
            <div>
                <h1 class="admin-registro__titulo">Registro de Administrativo</h1>
                <p class="admin-registro__subtitulo">Completa los datos para registrar un nuevo miembro del personal administrativo.</p>
            </div>
        </div>

        <!-- STEPPER -->
        <div class="stepper">
            <div class="stepper__linea"></div>

            <div class="stepper__paso stepper__paso--activo" data-paso="1">
                <div class="stepper__circulo"><i class="fa-solid fa-user"></i></div>
                <span class="stepper__label">Paso 1</span>
                <span class="stepper__nombre">Datos personales</span>
            </div>

            <div class="stepper__paso" data-paso="2">
                <div class="stepper__circulo"><i class="fa-solid fa-id-card"></i></div>
                <span class="stepper__label">Paso 2</span>
                <span class="stepper__nombre">Cargo e identificación</span>
            </div>

            <div class="stepper__paso" data-paso="3">
                <div class="stepper__circulo"><i class="fa-solid fa-envelope"></i></div>
                <span class="stepper__label">Paso 3</span>
                <span class="stepper__nombre">Contacto</span>
            </div>
        </div>

        <form action="/administrativos/guardar" method="POST" id="formAdministrativo" novalidate>
            <input type="hidden" name="id" value="<?= val('id', $datosPrevios) ?>">

            <!-- ============ PASO 1: DATOS PERSONALES ============ -->
            <section class="form-card" data-seccion="1">
                <div class="form-card__header">
                    <div class="form-card__icon-badge"><i class="fa-solid fa-user"></i></div>
                    <div>
                        <h2 class="form-card__titulo">Datos personales</h2>
                        <p class="form-card__subtitulo">Información básica de identificación.</p>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-field">
                        <label class="form-field__label" for="nombre">Nombre <span class="form-field__required">*</span></label>
                        <div class="form-field__input-wrapper">
                            <i class="fa-solid fa-user form-field__icon"></i>
                            <input
                                type="text"
                                id="nombre"
                                name="nombre"
                                class="form-field__input <?= isset($errores['nombre']) ? 'form-field__input--error' : '' ?>"
                                placeholder="Ej. Mariana"
                                value="<?= val('nombre', $datosPrevios) ?>"
                                required>
                        </div>
                        <?php if (isset($errores['nombre'])): ?>
                            <span class="form-field__error-msg"><?= htmlspecialchars($errores['nombre']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-field">
                        <label class="form-field__label" for="apellido">Apellido <span class="form-field__required">*</span></label>
                        <div class="form-field__input-wrapper">
                            <i class="fa-solid fa-user form-field__icon"></i>
                            <input
                                type="text"
                                id="apellido"
                                name="apellido"
                                class="form-field__input <?= isset($errores['apellido']) ? 'form-field__input--error' : '' ?>"
                                placeholder="Ej. Gómez Rodríguez"
                                value="<?= val('apellido', $datosPrevios) ?>"
                                required>
                        </div>
                        <?php if (isset($errores['apellido'])): ?>
                            <span class="form-field__error-msg"><?= htmlspecialchars($errores['apellido']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-actions">
                    <span></span>
                    <button type="button" class="btn btn--primary btn-siguiente" data-siguiente="2">
                        Siguiente <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </section>

            <!-- ============ PASO 2: CARGO E IDENTIFICACIÓN ============ -->
            <section class="form-card" data-seccion="2" style="display:none;">
                <div class="form-card__header">
                    <div class="form-card__icon-badge"><i class="fa-solid fa-id-card"></i></div>
                    <div>
                        <h2 class="form-card__titulo">Cargo e identificación</h2>
                        <p class="form-card__subtitulo">Rol dentro del colegio y documento de identidad.</p>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-field">
                        <label class="form-field__label" for="cedula">Cédula <span class="form-field__required">*</span></label>
                        <div class="form-field__input-wrapper">
                            <i class="fa-solid fa-address-card form-field__icon"></i>
                            <input
                                type="text"
                                id="cedula"
                                name="cedula"
                                class="form-field__input <?= isset($errores['cedula']) ? 'form-field__input--error' : '' ?>"
                                placeholder="Ej. 1032456789"
                                value="<?= val('cedula', $datosPrevios) ?>"
                                inputmode="numeric"
                                maxlength="10"
                                required>
                        </div>
                        <?php if (isset($errores['cedula'])): ?>
                            <span class="form-field__error-msg"><?= htmlspecialchars($errores['cedula']) ?></span>
                        <?php else: ?>
                            <span class="form-field__hint">Entre 6 y 10 dígitos, sin puntos ni espacios.</span>
                        <?php endif; ?>
                    </div>

                    <div class="form-field">
                        <label class="form-field__label" for="cargo">Cargo <span class="form-field__required">*</span></label>
                        <div class="form-field__input-wrapper">
                            <i class="fa-solid fa-briefcase form-field__icon"></i>
                            <select
                                id="cargo"
                                name="cargo"
                                class="form-field__select <?= isset($errores['cargo']) ? 'form-field__input--error' : '' ?>"
                                required>
                                <option value="" disabled <?= val('cargo', $datosPrevios) === '' ? 'selected' : '' ?>>Selecciona un cargo</option>
                                <?php
                                $cargos = ['Secretaría Académica', 'Coordinación', 'Tesorería', 'Recursos Humanos', 'Servicios Generales', 'Biblioteca', 'Sistemas', 'Otro'];
                                foreach ($cargos as $cargo):
                                ?>
                                    <option value="<?= htmlspecialchars($cargo) ?>" <?= val('cargo', $datosPrevios) === $cargo ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cargo) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php if (isset($errores['cargo'])): ?>
                            <span class="form-field__error-msg"><?= htmlspecialchars($errores['cargo']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn--secondary btn-anterior" data-anterior="1">
                        <i class="fa-solid fa-arrow-left"></i> Anterior
                    </button>
                    <button type="button" class="btn btn--primary btn-siguiente" data-siguiente="3">
                        Siguiente <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </section>

            <!-- ============ PASO 3: CONTACTO ============ -->
            <section class="form-card" data-seccion="3" style="display:none;">
                <div class="form-card__header">
                    <div class="form-card__icon-badge"><i class="fa-solid fa-envelope"></i></div>
                    <div>
                        <h2 class="form-card__titulo">Contacto</h2>
                        <p class="form-card__subtitulo">Datos para comunicarnos con este miembro del personal.</p>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-field form-grid--full">
                        <label class="form-field__label" for="email">Correo electrónico <span class="form-field__required">*</span></label>
                        <div class="form-field__input-wrapper">
                            <i class="fa-solid fa-envelope form-field__icon"></i>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-field__input <?= isset($errores['email']) ? 'form-field__input--error' : '' ?>"
                                placeholder="Ej. mariana.gomez@colegio.edu.co"
                                value="<?= val('email', $datosPrevios) ?>"
                                required>
                        </div>
                        <?php if (isset($errores['email'])): ?>
                            <span class="form-field__error-msg"><?= htmlspecialchars($errores['email']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-field">
                        <label class="form-field__label" for="telefono">Teléfono <span class="form-field__required">*</span></label>
                        <div class="form-field__input-wrapper">
                            <i class="fa-solid fa-phone form-field__icon"></i>
                            <input
                                type="tel"
                                id="telefono"
                                name="telefono"
                                class="form-field__input <?= isset($errores['telefono']) ? 'form-field__input--error' : '' ?>"
                                placeholder="Ej. 3001234567"
                                value="<?= val('telefono', $datosPrevios) ?>"
                                inputmode="numeric"
                                maxlength="10"
                                required>
                        </div>
                        <?php if (isset($errores['telefono'])): ?>
                            <span class="form-field__error-msg"><?= htmlspecialchars($errores['telefono']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn--secondary btn-anterior" data-anterior="2">
                        <i class="fa-solid fa-arrow-left"></i> Anterior
                    </button>
                    <button type="submit" class="btn btn--primary">
                        <i class="fa-solid fa-check"></i> Registrar administrativo
                    </button>
                </div>
            </section>

        </form>
        </div>
      </div>
    </main>
    <script src="<?= BASE_URL ?>/public/assets/layouts/admin/js/Sidebar.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/js/admin.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/js/registro_administrador.js"></script>
</body>

</html>