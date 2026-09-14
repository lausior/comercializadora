<?php

session_start();

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

header('Location: /comercializadora/login.php');
exit;