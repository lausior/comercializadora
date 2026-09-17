<?php

session_start();

require_once __DIR__ . '/../config/permisos.php';
requerirPermiso('configuracion');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/logs.php';
require_once __DIR__ . '/../includes/seguridad.php';


// =====================================================
// COMPROBAR QUE EL FORMULARIO SE ENVÍA POR POST
// =====================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /comercializadora/views/configuracion.php');
    exit;
}


// =====================================================
// VALIDAR EL VALOR RECIBIDO
// =====================================================
//
// Solo se aceptan los valores del desplegable (0 = Nunca).
// Cualquier otra cosa se ignora y se deja el valor anterior.
// =====================================================

$valoresPermitidos = [5, 10, 15, 30, 0];
$minutos = (int) ($_POST['minutos'] ?? -1);

if (in_array($minutos, $valoresPermitidos, true)) {

    guardarBloqueoAutomaticoMinutos($pdo, $minutos);

    registrarLog(
        LOG_EXITO,
        'Configuración modificada',
        'Bloqueo automático configurado a ' .
            ($minutos === 0 ? 'Nunca' : $minutos . ' minutos') . '.'
    );

}

header('Location: /comercializadora/views/configuracion.php');
exit;
