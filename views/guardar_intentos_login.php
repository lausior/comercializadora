<?php

session_start();

require_once __DIR__ . '/../config/permisos.php';
requerirPermiso('configuracion');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/logs.php';
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../includes/form_flash.php';


// =====================================================
// COMPROBAR QUE EL FORMULARIO SE ENVÍA POR POST
// =====================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /comercializadora/views/configuracion.php');
    exit;
}


// =====================================================
// VALIDAR LOS VALORES RECIBIDOS
// =====================================================
//
// Ambos son números enteros dentro de un rango razonable.
// Si alguno no lo es, no se guarda ninguno y se vuelve al
// formulario con los campos marcados.
// =====================================================

$errores = [];
$valores = [];

foreach ([
    'intentos_login_max'       => [1, 20],
    'minutos_bloqueo_intentos' => [1, 1440],
] as $campo => [$minimo, $maximo]) {

    $valor = trim((string) ($_POST[$campo] ?? ''));

    if ($valor === '') {
        $errores[] = ['mensaje' => 'Este campo es obligatorio.', 'campo' => $campo];
    } elseif (!preg_match('/^[0-9]+$/', $valor) || (int) $valor < $minimo || (int) $valor > $maximo) {
        $errores[] = ['mensaje' => "Introduce un número entero entre $minimo y $maximo.", 'campo' => $campo];
    }

    $valores[$campo] = (int) $valor;

}

if (!empty($errores)) {

    establecerErroresFormulario($errores, $_POST, '/comercializadora/views/configuracion.php#control-accesos');

}

$intentos = $valores['intentos_login_max'];
$minutos  = $valores['minutos_bloqueo_intentos'];

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

header('Location: /comercializadora/views/configuracion.php');
exit;
