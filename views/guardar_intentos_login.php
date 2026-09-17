<?php

session_start();

require_once __DIR__ . '/../config/permisos.php';
requerirPermiso('seguridad');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/logs.php';
require_once __DIR__ . '/../includes/seguridad.php';


// =====================================================
// COMPROBAR QUE EL FORMULARIO SE ENVÍA POR POST
// =====================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /comercializadora/views/seguridad.php');
    exit;
}


// =====================================================
// VALIDAR LOS VALORES RECIBIDOS
// =====================================================
//
// Ambos son números enteros dentro de un rango razonable.
// Cualquier valor fuera de rango se ignora y se deja el
// valor anterior.
// =====================================================

$intentos = (int) ($_POST['intentos_login_max'] ?? -1);
$minutos  = (int) ($_POST['minutos_bloqueo_intentos'] ?? -1);

if ($intentos >= 1 && $intentos <= 20) {

    guardarIntentosLoginMax($pdo, $intentos);

    registrarLog(
        LOG_EXITO,
        'Configuración modificada',
        'Intentos de login antes de bloqueo configurados a ' . $intentos . '.'
    );

}

if ($minutos >= 1 && $minutos <= 1440) {

    guardarMinutosBloqueoIntentos($pdo, $minutos);

    registrarLog(
        LOG_EXITO,
        'Configuración modificada',
        'Minutos de bloqueo tras intentos fallidos configurados a ' . $minutos . '.'
    );

}

header('Location: /comercializadora/views/seguridad.php');
exit;
