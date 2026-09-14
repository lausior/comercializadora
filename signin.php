<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Registrarse - Comparador Eléctrico</title>

    <link rel="stylesheet" href="css/forms.css">
    <link rel="stylesheet" href="css/signin.css">

</head>

<body>

    <main class="signin-page">

        <section class="signin-card">

            <!-- =================================================
                 CABECERA
            ================================================== -->

            <div class="signin-header">

                <div class="signin-logo">
                    ⚡
                </div>

                <h1>Crear cuenta</h1>

                <p>
                    Registra un nuevo usuario en el sistema
                </p>

            </div>


            <!-- =================================================
                 MENSAJE DE ERROR
            ================================================== -->

            <div id="signinError" class="signin-error" style="display: none;"></div>


            <!-- =================================================
                 FORMULARIO DE REGISTRO
            ================================================== -->

            <form action="index.php" method="POST" class="signin-form" id="signinForm" novalidate>


                <!-- NOMBRE -->

                <div class="form-group">

                    <label for="nombre">
                        Nombre
                    </label>

                    <input type="text" id="nombre" name="nombre" placeholder="Introduce tu nombre"
                        autocomplete="given-name" maxlength="50" required>

                </div>


                <!-- APELLIDOS -->

                <div class="form-group">

                    <label for="apellidos">
                        Apellidos
                    </label>

                    <input type="text" id="apellidos" name="apellidos" placeholder="Introduce tus apellidos"
                        autocomplete="family-name" maxlength="100" required>

                </div>


                <!-- USUARIO -->

                <div class="form-group">

                    <label for="usuario">
                        Usuario
                    </label>

                    <input type="text" id="usuario" name="usuario" placeholder="Elige un nombre de usuario"
                        autocomplete="username" maxlength="30" required>

                </div>


                <!-- CORREO -->

                <div class="form-group">

                    <label for="email">
                        Correo electrónico
                    </label>

                    <input type="email" id="email" name="email" placeholder="correo@ejemplo.com" autocomplete="email"
                        maxlength="100" required>

                </div>


                <!-- CONTRASEÑA -->

                <div class="form-group">

                    <label for="password">
                        Contraseña
                    </label>

                    <div class="password-wrapper">

                        <input type="password" id="password" name="password" placeholder="Introduce una contraseña"
                            autocomplete="new-password" minlength="8" maxlength="128" required>

                        <button type="button" class="password-toggle" id="togglePassword"
                            aria-label="Mostrar contraseña">
                            👁
                        </button>

                    </div>

                    <small class="password-help">
                        Mínimo 8 caracteres, incluyendo letras, números y un carácter especial.
                    </small>

                </div>


                <!-- REPETIR CONTRASEÑA -->

                <div class="form-group">

                    <label for="password_confirm">
                        Repetir contraseña
                    </label>

                    <div class="password-wrapper">

                        <input type="password" id="password_confirm" name="password_confirm"
                            placeholder="Repite la contraseña" autocomplete="new-password" minlength="8" maxlength="128"
                            required>

                        <button type="button" class="password-toggle" id="togglePasswordConfirm"
                            aria-label="Mostrar contraseña">
                            👁
                        </button>

                    </div>

                </div>


                <!-- TÉRMINOS -->

                <label class="terms">

                    <input type="checkbox" id="terminos" name="terminos" required>

                    <span>
                        Acepto los
                        <a href="#">
                            términos y condiciones
                        </a>
                    </span>

                </label>


                <!-- BOTÓN -->

                <button type="submit" class="signin-button">
                    Crear cuenta
                </button>

            </form>


            <!-- =================================================
                 ENLACE AL LOGIN
            ================================================== -->

            <div class="signin-login">

                <span>
                    ¿Ya tienes una cuenta?
                </span>

                <a href="login.php">
                    Iniciar sesión
                </a>

            </div>


            <!-- =================================================
                 FOOTER
            ================================================== -->

            <div class="signin-footer">

                <span>
                    Comparador Eléctrico
                </span>

                <span>
                    © 2026
                </span>

            </div>

        </section>

    </main>


    <script src="js/signin.js"></script>

</body>

</html>