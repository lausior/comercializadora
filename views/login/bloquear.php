<?php

session_start();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/recordarme.php';


// =====================================================
// RECORDAR QUIÉN ERA ANTES DE DESTRUIR LA SESIÓN
// =====================================================
// Mismo formato que el login: codigo_empresa-id-username.
// Así, al volver a login.php, el campo "Usuario" ya viene relleno y solo hace falta escribir la contraseña.
// =====================================================

$usuarioBloqueo = '';

if ( //comprueba que la sesión tenga codigo_empresa, id_usuario_username
    isset($_SESSION['codigo_empresa'], $_SESSION['id_usuario'], $_SESSION['username'])
) {
    
    //si existen, construye el usuario completo
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
// =====================================================

$_SESSION = []; //elimina todos los datos que había almacenados en la sesión

// Olvida también el "Recordarme" de este navegador: bloquear
// exige volver a escribir la contraseña de verdad, así que no
// puede quedar una cookie que reabra sesión sin pedirla.
olvidarRecordarme($pdo);

//Elimina la cookie de sesión
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

session_destroy(); //destruye la sesión


// =====================================================
// VOLVER AL LOGIN CON EL USUARIO YA RELLENO
// =====================================================

$destino = '/comercializadora/views/login/login.php';

if ($usuarioBloqueo !== '') { //si el username no está vacío
    $destino .= '?usuario=' . urlencode($usuarioBloqueo); //añade el usuario a la url
}

header('Location: ' . $destino); //redirige a login
exit;
