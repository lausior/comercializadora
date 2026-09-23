<?php

session_start();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/permisos.php';
require_once __DIR__ . '/../../includes/logs.php';
require_once __DIR__ . '/../../includes/recordarme.php';


// =====================================================
// SI YA HAY SESIÓN, DIRECTO AL PANEL (evita que un usuario ya logueado vuelva al panel de login)
// =====================================================

if (isset($_SESSION['id_usuario'])) { 

    if (!empty($_SESSION['cambiar_password'])) {
        header('Location: /comercializadora/views/login/cambiar_password.php');
        exit;
    }

    header('Location: /comercializadora/index.php');
    exit;

}


// =====================================================
// SOLO ACEPTAR POST
// =====================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: /comercializadora/views/login/login.php');
    exit;

}


// =====================================================
// RECOGER DATOS DEL FORMULARIO
// =====================================================

$usuarioCompuesto = trim($_POST['usuario'] ?? '');
$password         = $_POST['password'] ?? '';


// =====================================================
// FUNCIÓN PARA VOLVER AL LOGIN CON UN ERROR (redirige al login y muestra un mensaje de error)
// =====================================================

function volverConError(string $mensaje): void
{
    $_SESSION['login_error'] = $mensaje;

    header('Location: /comercializadora/views/login/login.php');
    exit;
}


// =====================================================
// SI ALGÚN CAMPO ESTÁ VACÍO MUESTRA MENSAJE DE ERROR
// =====================================================

if ($usuarioCompuesto === '' || $password === '') {
    volverConError('Introduce usuario y contraseña.');
}


// =====================================================
// SEPARAR EL USUARIO COMPUESTO
// Formato esperado: codigo_empresa-id-username
// Ejemplo: 1001-15-jperez
// =====================================================

$partes = explode('-', $usuarioCompuesto);  //separa por -

if (count($partes) !== 3) { //comprueba que haya 3 partes
    volverConError('El usuario introducido no tiene un formato válido.');
}

[$codigoEmpresa, $idUsuario, $username] = $partes; //guarda cada parte en su variable

// codigo_empresa se guarda como CHAR(6) y puede empezar por
// "0" (ej. "057620"): NO se castea a (int), porque eso le
// quitaría el cero inicial y la comparación con la BD
// dejaría de coincidir aunque el usuario y la contraseña
// sean correctos.
$codigoEmpresa = trim($codigoEmpresa);
$idUsuario     = (int) $idUsuario;
$username      = trim($username);

if (!ctype_digit($codigoEmpresa) || $idUsuario <= 0 || $username === '') { //comprueba el formato introducido
    volverConError('El usuario introducido no tiene un formato válido.');
}


// =====================================================
// BUSCAR AL USUARIO EN BASE DE DATOS
// =====================================================
//
// Se comprueban a la vez el id, el username y el código
// de empresa: si cualquiera de los tres no coincide con
// el mismo registro, el login se considera inválido.
//
// =====================================================

$stmt = $pdo->prepare("
    SELECT
        u.id,
        u.username,
        u.nombre,
        u.apellidos,
        u.password,
        u.cambiar_password,
        u.id_empresa,
        u.id_rol,
        u.estado,
        e.codigo_empresa,
        e.nombre AS empresa,
        r.nombre AS rol
    FROM usuarios u
    INNER JOIN empresas e ON e.id = u.id_empresa
    INNER JOIN roles r ON r.id = u.id_rol
    WHERE u.id = :id
        AND u.username = :username
        AND e.codigo_empresa = :codigo_empresa
    LIMIT 1
");

$stmt->execute([
    ':id'             => $idUsuario,
    ':username'       => $username,
    ':codigo_empresa' => $codigoEmpresa,
]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);


// =====================================================
// COMPROBAR USUARIO Y CONTRASEÑA
// =====================================================
//
// El mismo mensaje de error tanto si el usuario no
// existe como si la contraseña es incorrecta, para no
// dar pistas de cuál de los dos ha fallado.
//
// =====================================================

if (!$usuario || !password_verify($password, $usuario['password'])) {

    if ($usuario) {

        registrarLog(
            LOG_ERROR,
            'Acceso fallido',
            'Contraseña incorrecta.',
            (int) $usuario['id'],
            $usuario['username'],
            $usuario['rol']
        );

    } else {

        registrarLog(
            LOG_ERROR,
            'Acceso fallido',
            'El usuario introducido no existe.',
            null,
            $username,
            ROL_SRG
        );

    }

    volverConError('Usuario o contraseña incorrectos.');
}


// =====================================================
// COMPROBAR QUE EL USUARIO ESTÉ ACTIVO
// =====================================================

if ($usuario['estado'] !== 'Activo') {

    registrarLog(
        LOG_ADVERTENCIA,
        'Acceso denegado',
        'Intento de acceso con la cuenta inactiva.',
        (int) $usuario['id'],
        $usuario['username'],
        $usuario['rol']
    );

    volverConError('Tu cuenta está inactiva. Contacta con un administrador.');

}


// =====================================================
// INICIAR SESIÓN
// =====================================================

session_regenerate_id(true);

$_SESSION['id_usuario']       = $usuario['id'];
$_SESSION['username']         = $usuario['username'];
$_SESSION['nombre']           = $usuario['nombre'];
$_SESSION['apellidos']        = $usuario['apellidos'];
$_SESSION['id_empresa']       = $usuario['id_empresa'];
$_SESSION['empresa']          = $usuario['empresa'];
$_SESSION['codigo_empresa']   = $usuario['codigo_empresa'];
$_SESSION['id_rol']           = $usuario['id_rol'];
$_SESSION['rol']              = $usuario['rol'];
$_SESSION['cambiar_password'] = (int) $usuario['cambiar_password'];

// "Recordarme": crea un token persistente aparte de la sesión
// (ver includes/recordarme.php) para poder volver a entrar sin
// contraseña aunque se cierre el navegador.
if (!empty($_POST['recordarme'])) {
    activarRecordarme($pdo, (int) $usuario['id']);
}

registrarLog(
    LOG_EXITO,
    'Inicio de sesión',
    'Inicio de sesión realizado correctamente.'
);

comprobarYBorrarLogsAntiguos($pdo);


// =====================================================
// REDIRIGIR SEGÚN SI DEBE CAMBIAR LA CONTRASEÑA
// =====================================================

if ($_SESSION['cambiar_password'] === 1) {

    header('Location: /comercializadora/views/login/cambiar_password.php');
    exit;

}

header('Location: /comercializadora/index.php');
exit;