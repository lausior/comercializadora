<?php

session_start();


// =====================================================
// RECORDAR QUIÉN ERA ANTES DE DESTRUIR LA SESIÓN
// =====================================================
//
// Mismo formato que el login: codigo_empresa-id-username.
// Así, al volver a login.php, el campo "Usuario" ya viene
// relleno y solo hace falta escribir la contraseña.
//
// =====================================================

$usuarioBloqueo = '';

if (
    isset($_SESSION['codigo_empresa'], $_SESSION['id_usuario'], $_SESSION['username'])
) {

    $usuarioBloqueo =
        $_SESSION['codigo_empresa'] . '-' .
        $_SESSION['id_usuario'] . '-' .
        $_SESSION['username'];

}


// =====================================================
// DESTRUIR LA SESIÓN
// =====================================================
//
// Bloquear no es un simple "volver al login": la sesión
// se destruye de verdad, igual que en logout.php, para
// que quien vuelva a esta pantalla tenga que escribir la
// contraseña real y no le sirva la sesión ya abierta.
//
// =====================================================

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $parametrosCookie = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $parametrosCookie['path'],
        $parametrosCookie['domain'],
        $parametrosCookie['secure'],
        $parametrosCookie['httponly']
    );
}

session_destroy();


// =====================================================
// VOLVER AL LOGIN CON EL USUARIO YA RELLENO
// =====================================================

$destino = '/comercializadora/login.php';

if ($usuarioBloqueo !== '') {
    $destino .= '?usuario=' . urlencode($usuarioBloqueo);
}

header('Location: ' . $destino);
exit;
