<?php

session_start();

require_once __DIR__ . '/config/database.php';


// =====================================================
// SI YA HAY SESIÓN, DIRECTO AL PANEL
// =====================================================

if (isset($_SESSION['id_usuario'])) {

    header('Location: /comercializadora/index.php');
    exit;

}


// =====================================================
// SOLO ACEPTAR POST
// =====================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: /comercializadora/login.php');
    exit;

}


// =====================================================
// RECOGER DATOS DEL FORMULARIO
// =====================================================

$usuarioCompuesto = trim($_POST['usuario'] ?? '');
$password         = $_POST['password'] ?? '';


// =====================================================
// FUNCIÓN PARA VOLVER AL LOGIN CON UN ERROR
// =====================================================

function volverConError(string $mensaje): void
{
    $_SESSION['login_error'] = $mensaje;

    header('Location: /comercializadora/login.php');
    exit;
}


// =====================================================
// VALIDAR CAMPOS BÁSICOS
// =====================================================

if ($usuarioCompuesto === '' || $password === '') {
    volverConError('Introduce usuario y contraseña.');
}


// =====================================================
// SEPARAR EL USUARIO COMPUESTO
// Formato esperado: codigo_empresa-id-username
// Ejemplo: 1001-15-jperez
// =====================================================

$partes = explode('-', $usuarioCompuesto);

if (count($partes) !== 3) {
    volverConError('El usuario introducido no tiene un formato válido.');
}

[$codigoEmpresa, $idUsuario, $username] = $partes;

$codigoEmpresa = (int) $codigoEmpresa;
$idUsuario     = (int) $idUsuario;
$username      = trim($username);

if ($codigoEmpresa <= 0 || $idUsuario <= 0 || $username === '') {
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
    volverConError('Usuario o contraseña incorrectos.');
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
$_SESSION['id_rol']           = $usuario['id_rol'];
$_SESSION['rol']              = $usuario['rol'];
$_SESSION['cambiar_password'] = (int) $usuario['cambiar_password'];


// =====================================================
// REDIRIGIR SEGÚN SI DEBE CAMBIAR LA CONTRASEÑA
// =====================================================

if ($_SESSION['cambiar_password'] === 1) {

    header('Location: /comercializadora/cambiar_password.php');
    exit;

}

header('Location: /comercializadora/index.php');
exit;