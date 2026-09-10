<?php

session_start();

require_once __DIR__ . '/config/database.php';


// =====================================================
// DEBE HABER SESIÓN Y TENER EL CAMBIO PENDIENTE
// =====================================================

if (!isset($_SESSION['id_usuario'])) {

    header('Location: /comercializadora/login.php');
    exit;

}

if (empty($_SESSION['cambiar_password'])) {

    header('Location: /comercializadora/index.php');
    exit;

}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: /comercializadora/cambiar_password.php');
    exit;

}


// =====================================================
// FUNCIÓN PARA VOLVER CON ERROR
// =====================================================

function volverConErrorCambio(string $mensaje): void
{
    $_SESSION['cambio_password_error'] = $mensaje;

    header('Location: /comercializadora/cambiar_password.php');
    exit;

}


// =====================================================
// RECOGER DATOS
// =====================================================

$passwordActual    = $_POST['password_actual'] ?? '';
$passwordNueva      = $_POST['password_nueva'] ?? '';
$passwordConfirmar  = $_POST['password_confirmar'] ?? '';


// =====================================================
// VALIDACIONES BÁSICAS
// =====================================================

if ($passwordActual === '' || $passwordNueva === '' || $passwordConfirmar === '') {
    volverConErrorCambio('Rellena los tres campos.');
}

if (strlen($passwordNueva) < 8) {
    volverConErrorCambio('La contraseña nueva debe tener al menos 8 caracteres.');
}

if ($passwordNueva !== $passwordConfirmar) {
    volverConErrorCambio('Las dos contraseñas nuevas no coinciden.');
}

if ($passwordNueva === $passwordActual) {
    volverConErrorCambio('La contraseña nueva debe ser distinta de la actual.');
}


// =====================================================
// COMPROBAR LA CONTRASEÑA ACTUAL
// =====================================================

$stmt = $pdo->prepare("
    SELECT password
    FROM usuarios
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$_SESSION['id_usuario']]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario || !password_verify($passwordActual, $usuario['password'])) {
    volverConErrorCambio('La contraseña actual no es correcta.');
}


// =====================================================
// GUARDAR LA NUEVA CONTRASEÑA
// =====================================================

$passwordHash = password_hash($passwordNueva, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("
    UPDATE usuarios
    SET
        password = :password,
        cambiar_password = 0
    WHERE id = :id
");

$stmt->execute([
    ':password' => $passwordHash,
    ':id'       => $_SESSION['id_usuario'],
]);


// =====================================================
// ACTUALIZAR LA SESIÓN Y CONTINUAR AL PANEL
// =====================================================

$_SESSION['cambiar_password'] = 0;

header('Location: /comercializadora/index.php');
exit;