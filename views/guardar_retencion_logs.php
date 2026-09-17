<?php

session_start();

require_once __DIR__ . '/../config/permisos.php';
requerirPermiso('configuracion');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/logs.php';


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
// =====================================================

$valoresPermitidos = [0, 30, 60, 90, 365];
$dias = (int) ($_POST['dias'] ?? -1);

if (in_array($dias, $valoresPermitidos, true)) {

    guardarRetencionLogsDias($pdo, $dias);

    registrarLog(
        LOG_EXITO,
        'Configuración modificada',
        'Retención de logs configurada a ' .
            ($dias === 0 ? 'Nunca' : $dias . ' días') . '.'
    );

}

header('Location: /comercializadora/views/configuracion.php');
exit;
