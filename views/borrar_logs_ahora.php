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
// BORRADO FORZADO
// =====================================================
//
// Respeta la retención configurada (borra lo más antiguo
// que ese número de días). Si la retención está en "Nunca"
// (0), al forzar el borrado se eliminan TODOS los logs, ya
// que no hay ningún criterio de antigüedad definido.
// =====================================================

$dias = obtenerRetencionLogsDias($pdo);

if ($dias > 0) {
    $filasBorradas = borrarLogsMasAntiguosQue($pdo, $dias);
} else {
    $filasBorradas = borrarTodosLosLogs($pdo);
}

marcarUltimoBorradoLogs($pdo);

registrarLog(
    LOG_EXITO,
    'Configuración modificada',
    'Borrado forzado de logs: ' . $filasBorradas . ' registro(s) eliminado(s).'
);

header('Location: /comercializadora/views/configuracion.php');
exit;
