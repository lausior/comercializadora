<?php

session_start();

require_once __DIR__ . '/../config/permisos.php';
requerirPermiso('configuracion');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/logs.php';
require_once __DIR__ . '/../includes/empresas.php';


// =====================================================
// COMPROBAR QUE EL FORMULARIO SE ENVÍA POR POST
// =====================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /comercializadora/views/configuracion.php');
    exit;
}

$idEmpresa = (int) $_SESSION['id_empresa'];

$logoActual = obtenerLogoEmpresa($pdo, $idEmpresa);

if ($logoActual !== null) {

    $rutaAbsoluta = __DIR__ . '/../' . $logoActual;

    if (is_file($rutaAbsoluta)) {
        unlink($rutaAbsoluta);
    }

    guardarLogoEmpresa($pdo, $idEmpresa, null);

    registrarLog(
        LOG_EXITO,
        'Configuración modificada',
        'Se ha eliminado el logo de la empresa.'
    );

}

header('Location: /comercializadora/views/configuracion.php');
exit;
