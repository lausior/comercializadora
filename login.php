<?php

session_start();

$errorLogin = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Iniciar sesión - Comparador Eléctrico</title>

    <link rel="stylesheet" href="css/login.css">
</head>

<body>

    <main class="login-page">

        <section class="login-card">

            <div class="login-header">

                <div class="login-logo">
                    ⚡
                </div>

                <h1>Comparador Eléctrico</h1>

                <p>
                    Accede al panel de gestión
                </p>

            </div>


            <?php if ($errorLogin !== ''): ?>

                <div class="form-error-general" style="display: block;">
                    <?= htmlspecialchars($errorLogin) ?>
                </div>

            <?php endif; ?>


            <form action="procesar_login.php" method="POST" class="login-form" novalidate>

                <div class="form-group">

                    <label for="usuario">
                        Usuario
                    </label>

                    <input type="text" id="usuario" name="usuario" placeholder="Código empresa-ID-usuario"
                        autocomplete="username" required>

                </div>


                <div class="form-group">

                    <label for="password">
                        Contraseña
                    </label>

                    <div class="password-wrapper">

                        <input type="password" id="password" name="password" placeholder="Introduce tu contraseña"
                            autocomplete="current-password" required>

                        <button type="button" class="password-toggle" id="togglePassword"
                            aria-label="Mostrar contraseña">
                            👁
                        </button>

                    </div>

                </div>


                <div class="login-options">

                    <label class="remember-me">

                        <input type="checkbox" name="recordarme">

                        <span>Recordarme</span>

                    </label>

                    <a href="recuperar-password.php">
                        ¿Has olvidado tu contraseña?
                    </a>

                </div>


                <button type="submit" class="login-button">
                    Iniciar sesión
                </button>

            </form>


            <div class="login-footer">

                <span>
                    Comparador Eléctrico
                </span>

                <span>
                    © 2026
                </span>

            </div>

        </section>

    </main>


    <script src="js/login.js"></script>

</body>

</html>