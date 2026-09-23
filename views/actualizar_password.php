<?php

session_start();

require_once __DIR__ . '/../config/permisos.php';
requerirPermiso('seguridad');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/logs.php';
require_once __DIR__ . '/../includes/recordarme.php';


// =====================================================
// A QUÉ PÁGINA VOLVER
// =====================================================
//
// El formulario ahora vive en un modal disponible desde
// cualquier página (ver templates/sidebar.php), así que al
// terminar hay que volver adonde estaba el usuario, no
// siempre al mismo sitio. La propia página manda su URL en
// "volver_a" (campo oculto); solo se acepta si es una ruta
// propia de la app, para no convertir esto en un redirector
// abierto si alguien manipula el campo a mano.
//
// =====================================================

function destinoVolverPassword(): string
{
    $volverA = $_POST['volver_a'] ?? '';

    if (is_string($volverA) && str_starts_with($volverA, '/comercializadora/')) {
        return $volverA;
    }

    return '/comercializadora/index.php';
}


// =====================================================
// COMPROBAR QUE EL FORMULARIO SE ENVÍA POR POST
// =====================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . destinoVolverPassword());
    exit;
}


// =====================================================
// FUNCIÓN PARA DEVOLVER ERRORES
// =====================================================

function volverConErrorPassword(string $mensaje): void
{
    $_SESSION['password_error'] = $mensaje;

    header('Location: ' . destinoVolverPassword());
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

// Con la contraseña ya cambiada, cualquier "Recordarme" creado
// con la anterior deja de valer, en todos los dispositivos.
olvidarTodosLosTokensDeUsuario($pdo, (int) $_SESSION['id_usuario']);

registrarLog(
    LOG_EXITO,
    'Cambio de contraseña',
    'La contraseña se ha cambiado correctamente desde el modal de Seguridad.'
);

$_SESSION['password_success'] = 'Contraseña actualizada correctamente.';

header('Location: ' . destinoVolverPassword());
exit;
