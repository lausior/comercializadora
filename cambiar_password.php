<?php

session_start(); //Inicia o recupera la sesión PHP del usuario

require_once __DIR__ . '/config/permisos.php';


// =====================================================
// DEBE HABER SESIÓN INICIADA
// =====================================================
//
// Ojo: aquí NO se usa requerirPermiso(), porque esa
// función redirige precisamente A esta página cuando
// cambiar_password = 1. Usar requerirPermiso() aquí
// crearía un bucle infinito de redirecciones.
//
// =====================================================

if (!isset($_SESSION['id_usuario'])) { //comprueba si existe el id en la sesión, si no existe lo envía a login

    header('Location: /comercializadora/login.php');
    exit;

}


// =====================================================
// SI NO TIENE PENDIENTE EL CAMBIO, NO NECESITA ESTAR AQUÍ
// =====================================================

if (empty($_SESSION['cambiar_password'])) { //si cambiar_contraseña está vacío, es 0 o false, envia a index.php

    header('Location: /comercializadora/index.php');
    exit;

}


$errorCambio = $_SESSION['cambio_password_error'] ?? ''; //si existe cambio_password_error en la sesión, guarda su valor en $errorCambio
unset($_SESSION['cambio_password_error']); //elimina el mensaje de error de la sesión una vez guardado


// Si todo es correcto se muestra el formulario para cambiar la contraseña
?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cambiar contraseña - Comparador Eléctrico</title>

    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="css/style.css">

</head>

<body>

    <main class="login-page">

        <section class="login-card">

            <div class="login-header">

                <div class="login-logo">
                    ⚡
                </div>

                <h1>Cambio de contraseña obligatorio</h1>

                <p>
                    Es tu primer acceso (o tu contraseña ha sido
                    restablecida). Antes de continuar, crea una
                    contraseña nueva.
                </p>

            </div>


            <div class="form-error-general" id="form-error-general" role="alert"
                style="<?= $errorCambio !== '' ? 'display: block;' : 'display: none;' ?>">
                <?= htmlspecialchars($errorCambio) ?></div>


            <form action="procesar_cambio_password.php" method="POST" class="login-form" novalidate>

                <div class="form-group">

                    <label for="password_nueva">
                        Contraseña nueva
                    </label>

                    <div class="password-wrapper">

                        <input type="password" id="password_nueva" name="password_nueva"
                            placeholder="Crea tu contraseña" autocomplete="new-password" required minlength="8">

                        <button type="button" class="password-toggle" data-target="password_nueva"
                            aria-label="Mostrar contraseña">
                            👁
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


                <div class="form-group">

                    <label for="password_confirmar">
                        Repite la contraseña nueva
                    </label>

                    <div class="password-wrapper">

                        <input type="password" id="password_confirmar" name="password_confirmar"
                            placeholder="Repite la contraseña nueva" autocomplete="new-password" required minlength="8">

                        <button type="button" class="password-toggle" data-target="password_confirmar"
                            aria-label="Mostrar contraseña">
                            👁
                        </button>

                    </div>

                </div>


                <button type="submit" class="login-button">
                    Guardar nueva contraseña
                </button>

            </form>

        </section>

    </main>


    <script src="js/cambiar_password.js"></script>

</body>

</html>