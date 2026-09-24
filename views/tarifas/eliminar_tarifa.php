<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('tarifas');

require_once '../../config/database.php';
require_once '../../includes/logs.php';
require_once '../../includes/tarifas.php';


// =====================================================
// RESPUESTA JSON
// =====================================================
//
// Solo se llama por AJAX desde el modal de tarifas.php
// (ver js/tarifas.js), que espera JSON tanto si sale bien
// como si no.
//
// =====================================================

header('Content-Type: application/json; charset=UTF-8');

function responderErrorEliminarTarifa(string $mensaje): void
{
    echo json_encode([
        'ok' => false,
        'error' => $mensaje,
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// =====================================================
// COMPROBAR QUE LA TARIFA EXISTE Y SE PUEDE ELIMINAR
// =====================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    responderErrorEliminarTarifa('Petición no válida.');

}

$tarifa = obtenerTarifaVisible($pdo, (int) ($_POST['id'] ?? 0));

if (!$tarifa) {

    responderErrorEliminarTarifa('La tarifa que intentas eliminar no existe o no tienes permiso para eliminarla.');

}


// =====================================================
// ELIMINAR TARIFA
// =====================================================

$stmtEliminar = $pdo->prepare("
    DELETE FROM tarifas
    WHERE id = ?
");

$stmtEliminar->execute([(int) $tarifa['id']]);

if ($stmtEliminar->rowCount() !== 1) {

    responderErrorEliminarTarifa('No se ha podido eliminar la tarifa.');

}

registrarLog(
    LOG_ADVERTENCIA,
    'Tarifa eliminada',
    'Se ha eliminado la tarifa ' . $tarifa['tipo_suministro'] . ' ' . $tarifa['peaje'] . ' "' . $tarifa['nombre'] . '" de "' . $tarifa['nombre_titular'] . '".'
);

echo json_encode([
    'ok' => true,
    'tipo' => 'Tarifa',
    'nombre' => $tarifa['nombre'],
    'secciones' => [
        [
            'titulo' => 'Datos de la tarifa',
            'campos' => [
                [$tarifa['id_cliente'] !== null ? 'Cliente' : 'Comercializadora', $tarifa['nombre_titular']],
                ['Nombre', $tarifa['nombre']],
                ['Tipo de tarifa', $tarifa['peaje']],
            ],
        ],
    ],
], JSON_UNESCAPED_UNICODE);
