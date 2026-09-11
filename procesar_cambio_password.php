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

$passwordNueva      = $_POST['password_nueva'] ?? '';
$passwordConfirmar  = $_POST['password_confirmar'] ?? '';


// =====================================================
// VALIDACIONES BÁSICAS
// =====================================================

if ($passwordNueva === '' || $passwordConfirmar === '') {
    volverConErrorCambio('Rellena los dos campos.');
}

if (strlen($passwordNueva) < 8) {
    volverConErrorCambio('La contraseña nueva debe tener al menos 8 caracteres.');
}

if (!preg_match('/[A-Z]/', $passwordNueva)) {
    volverConErrorCambio('La contraseña nueva debe incluir al menos una mayúscula.');
}

if (!preg_match('/[a-z]/', $passwordNueva)) {
    volverConErrorCambio('La contraseña nueva debe incluir al menos una minúscula.');
}

if (!preg_match('/[0-9]/', $passwordNueva)) {
    volverConErrorCambio('La contraseña nueva debe incluir al menos un número.');
}

if (!preg_match('/[^A-Za-z0-9]/', $passwordNueva)) {
    volverConErrorCambio('La contraseña nueva debe incluir al menos un carácter especial (ej. # ! % *).');
}

if ($passwordNueva !== $passwordConfirmar) {
    volverConErrorCambio('Las dos contraseñas nuevas no coinciden.');
}


// =====================================================
// COMPROBAR QUE SEA DISTINTA DE LA ACTUAL
// =====================================================
//
// No se le pide al usuario que escriba la contraseña
// actual: se compara la nueva directamente contra el
// hash que ya hay guardado en la base de datos.
//
// =====================================================

$stmt = $pdo->prepare("
    SELECT password
    FROM usuarios
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$_SESSION['id_usuario']]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    volverConErrorCambio('No se ha podido verificar tu usuario.');
}

if (password_verify($passwordNueva, $usuario['password'])) {
    volverConErrorCambio('La contraseña nueva debe ser distinta de la actual.');
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