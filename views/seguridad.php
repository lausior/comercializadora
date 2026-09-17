<?php

session_start();

require_once __DIR__ . '/../config/permisos.php';
requerirPermiso('seguridad');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/seguridad.php';

$intentosLoginMax        = obtenerIntentosLoginMax($pdo);
$minutosBloqueoIntentos  = obtenerMinutosBloqueoIntentos($pdo);

$passwordError = $_SESSION['password_error'] ?? '';
unset($_SESSION['password_error']);

$passwordSuccess = $_SESSION['password_success'] ?? '';
unset($_SESSION['password_success']);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguridad - Comparador Eléctrico</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include '../templates/header.php'; ?>

<div class="app-container">

    <?php include '../templates/sidebar.php'; ?>

    <!-- ========================================
         CONTENIDO PRINCIPAL
    ========================================= -->

    <main class="main-content">

        <!-- CABECERA -->

        <div class="page-header">

            <div>

                <h1>Seguridad</h1>

                <p>
                    Estado y configuración de seguridad del sistema
                </p>

            </div>

        </div>


        <!-- ========================================
             CAMBIO DE CONTRASEÑA / CONTROL DE ACCESOS
        ========================================= -->

        <section class="security-grid">


            <!-- CAMBIAR CONTRASEÑA -->

            <div class="panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Cambiar contraseña
                        </h2>

                        <p>
                            Actualiza la contraseña de tu cuenta
                        </p>

                    </div>

                </div>


                <div class="security-password-form">


                    <div class="form-error-general" id="form-error-general" role="alert"
                        style="<?= $passwordError !== '' ? 'display: block;' : 'display: none;' ?>">
                        <?= htmlspecialchars($passwordError) ?>
                    </div>

                    <div class="form-success-general" id="form-success-general" role="status"
                        style="<?= $passwordSuccess !== '' ? 'display: block;' : 'display: none;' ?>">
                        <?= htmlspecialchars($passwordSuccess) ?>
                    </div>


                    <form action="actualizar_password.php" method="POST" novalidate>


                        <!-- CONTRASEÑA NUEVA -->

                        <div class="form-group">

                            <label for="password_nueva">
                                Contraseña nueva
                            </label>

                            <div class="password-wrapper">

                                <input type="password" id="password_nueva" name="password_nueva"
                                    placeholder="Escribe tu nueva contraseña" autocomplete="new-password"
                                    required minlength="8">

                                <button type="button" class="password-toggle" data-target="password_nueva"
                                    aria-label="Mostrar contraseña">
                                    <i class="bi bi-eye-slash"></i>
                                </button>

                            </div>

                            <ul class="password-requisitos" id="passwordRequisitos">

                                <li data-req="longitud">Mínimo 8 caracteres</li>
                                <li data-req="mayuscula">Mayúsculas</li>
                                <li data-req="minuscula">Minúsculas</li>
                                <li data-req="numero">Números</li>
                                <li data-req="especial">Caracteres especiales</li>

                            </ul>

                        </div>


                        <!-- REPETIR CONTRASEÑA -->

                        <div class="form-group">

                            <label for="password_confirmar">
                                Repite la contraseña nueva
                            </label>

                            <div class="password-wrapper">

                                <input type="password" id="password_confirmar" name="password_confirmar"
                                    placeholder="Repite la nueva contraseña" autocomplete="new-password"
                                    required minlength="8">

                                <button type="button" class="password-toggle" data-target="password_confirmar"
                                    aria-label="Mostrar contraseña">
                                    <i class="bi bi-eye-slash"></i>
                                </button>

                            </div>

                        </div>


                        <div class="form-actions">

                            <button type="submit" class="config-save-button">
                                Guardar nueva contraseña
                            </button>

                        </div>


                    </form>


                </div>

            </div>


            <!-- CONTROL DE ACCESOS -->

            <div class="panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Control de accesos
                        </h2>

                        <p>
                            Bloqueo por intentos fallidos
                        </p>

                    </div>

                </div>


                <form action="guardar_intentos_login.php" method="POST" class="security-settings">


                    <div class="security-setting">

                        <div>

                            <strong>
                                Intentos de login antes de bloqueo
                            </strong>

                            <span>
                                Número de intentos fallidos permitidos
                            </span>

                        </div>

                        <input type="number" name="intentos_login_max" class="security-select"
                            min="1" max="20" value="<?= $intentosLoginMax ?>" required>

                    </div>


                    <div class="security-setting">

                        <div>

                            <strong>
                                Minutos de bloqueo tras intentos fallidos
                            </strong>

                            <span>
                                Tiempo que permanece bloqueado el acceso
                            </span>

                        </div>

                        <input type="number" name="minutos_bloqueo_intentos" class="security-select"
                            min="1" max="1440" value="<?= $minutosBloqueoIntentos ?>" required>

                    </div>


                    <div class="form-actions">

                        <button type="submit" class="config-save-button">
                            Guardar cambios
                        </button>

                    </div>


                </form>

            </div>


        </section>


    </main>

</div>


<?php include '../templates/footer.php'; ?>

<script src="../js/seguridad.js"></script>

</body>
</html>