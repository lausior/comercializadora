<?php

session_start();

require_once __DIR__ . '/../config/permisos.php';
requerirPermiso('seguridad');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/logs.php';


// =====================================================
// COMPROBAR QUE EL FORMULARIO SE ENVÍA POR POST
// =====================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /comercializadora/views/seguridad.php');
    exit;
}


// =====================================================
// FUNCIÓN PARA DEVOLVER ERRORES
// =====================================================

function volverConErrorPassword(string $mensaje): void
{
    $_SESSION['password_error'] = $mensaje;

    header('Location: /comercializadora/views/seguridad.php');
    exit;
}


// =====================================================
// RECOGER DATOS DEL FORMULARIO
// =====================================================

$passwordNueva     = $_POST['password_nueva'] ?? '';
$passwordConfirmar = $_POST['password_confirmar'] ?? '';


// =====================================================
// VALIDACIONES BÁSICAS
// =====================================================

if ($passwordNueva === '' || $passwordConfirmar === '') {
    volverConErrorPassword('Rellena los dos campos.');
}

if (strlen($passwordNueva) < 8) {
    volverConErrorPassword('La contraseña nueva debe tener al menos 8 caracteres.');
}

if (!preg_match('/[A-Z]/', $passwordNueva)) {
    volverConErrorPassword('La contraseña nueva debe incluir al menos una mayúscula.');
}

if (!preg_match('/[a-z]/', $passwordNueva)) {
    volverConErrorPassword('La contraseña nueva debe incluir al menos una minúscula.');
}

if (!preg_match('/[0-9]/', $passwordNueva)) {
    volverConErrorPassword('La contraseña nueva debe incluir al menos un número.');
}

if (!preg_match('/[^A-Za-z0-9]/', $passwordNueva)) {
    volverConErrorPassword('La contraseña nueva debe incluir al menos un carácter especial (ej. # ! % *).');
}

if ($passwordNueva !== $passwordConfirmar) {
    volverConErrorPassword('Las dos contraseñas nuevas no coinciden.');
}


// =====================================================
// COMPROBAR QUE SEA DISTINTA DE LA ACTUAL
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
    volverConErrorPassword('No se ha podido verificar tu usuario.');
}

if (password_verify($passwordNueva, $usuario['password'])) {
    volverConErrorPassword('La contraseña nueva debe ser distinta de la actual.');
}


// =====================================================
// GUARDAR LA NUEVA CONTRASEÑA
// =====================================================

$passwordHash = password_hash($passwordNueva, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("
    UPDATE usuarios
    SET password = :password
    WHERE id = :id
");

$stmt->execute([
    ':password' => $passwordHash,
    ':id'       => $_SESSION['id_usuario'],
]);

registrarLog(
    LOG_EXITO,
    'Cambio de contraseña',
    'La contraseña se ha cambiado correctamente desde la sección de seguridad.'
);

$_SESSION['password_success'] = 'Contraseña actualizada correctamente.';

header('Location: /comercializadora/views/seguridad.php');
exit;
