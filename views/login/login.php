<?php

session_start();

$usuarioBloqueado = $_GET['usuario'] ?? '';
$estaBloqueado = $usuarioBloqueado !== '';

// =====================================================
// "RECORDARME": REANUDAR SESIÓN SI HAY UNA COOKIE VÁLIDA
// =====================================================
//
// Salvo en la pantalla de "bloqueado" (llega con ?usuario=):
// bloquear la pantalla es una acción deliberada para exigir
// la contraseña de nuevo, y bloquear.php ya borra el
// "Recordarme" de este navegador (ver includes/recordarme.php),
// así que aquí nunca debería quedar cookie que reanudar. Se
// evita igualmente, por si acaso, para no poder saltarse el
// bloqueo escribiendo login.php sin el "?usuario=".
//
// =====================================================

if (!$estaBloqueado && !isset($_SESSION['id_usuario'])) {

    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../includes/recordarme.php';

    reanudarSesionRecordarme($pdo);

}

if (isset($_SESSION['id_usuario'])) {

    if (!empty($_SESSION['cambiar_password'])) {
        header('Location: /comercializadora/views/login/cambiar_password.php');
        exit;
    }

    header('Location: /comercializadora/index.php');
    exit;

}

$errorLogin = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Iniciar sesión - Comparador Eléctrico</title>

    <link rel="stylesheet" href="../../css/forms.css">
    <link rel="stylesheet" href="../../css/login.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
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
                    <?= $estaBloqueado
                        ? 'Sesión bloqueada. Introduce tu contraseña para continuar.'
                        : 'Accede al panel de gestión' ?>
                </p>

            </div>


            <div class="form-error-general" id="form-error-general" role="alert"
                style="display: <?= $errorLogin !== '' ? 'block' : 'none' ?>;">
                <?= htmlspecialchars($errorLogin) ?>
            </div>


            <form action="procesar_login.php" method="POST" class="login-form" novalidate
                data-bloqueado="<?= $estaBloqueado ? '1' : '0' ?>">

                <div class="form-group">

                    <label for="usuario">
                        Usuario
                    </label>

                    <input type="text" id="usuario" name="usuario" placeholder="Código empresa-ID-usuario"
                        autocomplete="username" value="<?= htmlspecialchars($usuarioBloqueado) ?>"
                        <?= $estaBloqueado ? 'readonly' : '' ?> required>

                    <?php if ($estaBloqueado): ?>
                        <a href="/comercializadora/views/login/login.php" class="login-cambiar-usuario">
                            ¿No eres tú? Usa otro usuario
                        </a>
                    <?php endif; ?>

                </div>


                <div class="form-group">

                    <label for="password">
                        Contraseña
                    </label>

                    <div class="password-wrapper">

                        <input type="password" id="password" name="password" placeholder="Introduce tu contraseña"
                            autocomplete="<?= $estaBloqueado ? 'new-password' : 'current-password' ?>"
                            <?= $estaBloqueado ? 'autofocus' : '' ?> required>

                        <button type="button" class="password-toggle" id="togglePassword"
                            aria-label="Mostrar contraseña">
                            <i class="bi bi-eye-slash"></i>
                        </button>

                    </div>

                </div>


                <div class="login-options">

                    <label class="remember-me">

                        <input type="checkbox" name="recordarme">

                        <span>Recordarme</span>

                    </label>

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


    <script src="../../js/login.js"></script>

</body>

</html>