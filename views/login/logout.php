<?php

session_start();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/recordarme.php';

if (isset($_SESSION['id_usuario'])) {

    require_once __DIR__ . '/../../includes/logs.php';

    registrarLog(
        LOG_EXITO,
        'Cierre de sesión',
        'Sesión cerrada correctamente.'
    );

}

// Olvida el "Recordarme" de este navegador: sin esto, tras
// cerrar sesión "de verdad" se podría volver a entrar solo por
// tener la cookie, lo que no tendría sentido.
olvidarRecordarme($pdo);

$_SESSION = []; //Vacía todas las variables de sesión


if (ini_get('session.use_cookies')) { //comprueba si PHP está utilizando una cookie para identificar la sesión
    $parametrosCookie = session_get_cookie_params(); //obtiene los parámetros de la cookie
    setcookie( //hace que la cookie de sesión quede caducada, eliminándola del navegador
        session_name(),
        '',
        time() - 42000,
        $parametrosCookie['path'],
        $parametrosCookie['domain'],
        $parametrosCookie['secure'],
        $parametrosCookie['httponly']
    );
}

session_destroy(); //destruye la sesión

header('Location: /comercializadora/views/login/login.php');
exit;