<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('tarifas');

require_once '../../config/database.php';
require_once '../../includes/logs.php';
require_once '../../includes/tarifas.php';


// =====================================================
// COMPROBAR QUE EL FORMULARIO SE ENVÍA POR POST
// =====================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: tarifas.php');
    exit;

}


// =====================================================
// QUÉ REJILLA SE GUARDA (VISTA / SERVICIO / PEAJE)
// =====================================================

$ambito = ($_POST['vista'] ?? '') === 'clientes' ? 'clientes' : 'comercializadoras';
$servicio = $_POST['servicio'] ?? '';
$peaje = $_POST['peaje'] ?? '';

if (!in_array($servicio, SERVICIOS_TARIFA, true) || !in_array($peaje, peajesServicio($servicio), true)) {

    header('Location: tarifas.php');
    exit;

}

$esClientes = $ambito === 'clientes';
// Al volver, la tabla del cliente sigue abierta si se ha
// guardado ella o si estaba desplegada al guardar la de
// comercializadoras (campo cliente_abierto, ver js/tarifas.js).
$clienteAbierto = $esClientes || ($_POST['cliente_abierto'] ?? '') === '1';
$urlVolver = urlRejillaTarifas($servicio, $peaje, $clienteAbierto);
$claveRejilla = $ambito . '|' . $servicio . '|' . $peaje;


// =====================================================
// FILAS DE LA REJILLA
// =====================================================
//
// js/tarifas.js manda toda la rejilla en un único campo JSON
// (filas_json): con muchas filas se superaría el límite de
// campos por petición de PHP (max_input_vars, 1000 por
// defecto) y se perderían filas sin avisar. Sin JS llegan
// como filas[clave][campo].
//
// =====================================================

$filas = isset($_POST['filas_json'])
    ? json_decode((string) $_POST['filas_json'], true)
    : ($_POST['filas'] ?? []);

if (!is_array($filas)) {
    $filas = [];
}

$titulares = obtenerTitularesTarifas($pdo, $ambito, $servicio);
$existentes = obtenerTarifasRejilla($pdo, $ambito, $servicio, $peaje, array_keys($titulares));


// =====================================================
// VALIDAR TODAS LAS FILAS
// =====================================================
//
// Se valida la rejilla entera antes de guardar nada: si
// alguna celda está mal, no se guarda ninguna fila y se
// vuelve con lo escrito y las celdas marcadas en rojo, para
// no dejar a medias un cambio de precios.
//
// =====================================================

$aInsertar = [];
$aActualizar = [];
$errores = [];
$detalle = [];
$nombresVistos = [];

foreach ($filas as $clave => $fila) {

    if (!is_array($fila)) {
        continue;
    }

    $fila = array_map(fn($valor) => is_scalar($valor) ? (string) $valor : '', $fila);

    $clave = (string) $clave;
    $idTitular = (int) ($fila['id_titular'] ?? 0);
    $idTarifa = (int) ($fila['id'] ?? 0);

    // Titular que este rol no puede ver (o petición
    // manipulada): la fila se ignora.
    if (!isset($titulares[$idTitular])) {
        continue;
    }

    // Tarifa existente que ya no está (borrada desde otra
    // pestaña) o que no es de este titular/servicio/peaje.
    if (
        $idTarifa > 0
        && (!isset($existentes[$idTarifa]) || (int) $existentes[$idTarifa]['id_titular'] !== $idTitular)
    ) {
        continue;
    }

    if ($idTarifa === 0 && esFilaTarifaVacia($fila)) {
        continue;
    }

    $datos = recogerDatosTarifa($fila, $servicio, $peaje, $ambito);

    // La tarifa de un cliente no tiene "activa": siempre
    // cuenta para comparar.
    if ($esClientes) {
        $datos['activa'] = 1;
    }

    $erroresFila = validarDatosTarifa($datos, $servicio, $peaje, $ambito);

    $claveNombre = $idTitular . '|' . mb_strtolower($datos['nombre'], 'UTF-8');

    if ($datos['nombre'] !== '' && isset($nombresVistos[$claveNombre])) {

        $erroresFila[] = [
            'mensaje' => 'Ya hay otra tarifa ' . $peaje . ' con ese nombre en ' . ($esClientes ? 'este cliente.' : 'esta comercializadora.'),
            'campo'   => 'nombre',
        ];

    }

    $nombresVistos[$claveNombre] = true;

    if (!empty($erroresFila)) {

        $etiquetaFila = $titulares[$idTitular]['nombre']
            . ($datos['nombre'] !== '' ? ' — ' . $datos['nombre'] : '');

        foreach ($erroresFila as $error) {

            $errores[$clave][$error['campo']] ??= $error['mensaje'];
            $detalle[] = $etiquetaFila . ': ' . $error['mensaje'];

        }

        continue;

    }

    $datos['id_titular'] = $idTitular;

    if ($idTarifa > 0) {
        $aActualizar[$idTarifa] = $datos;
    } else {
        $aInsertar[] = $datos;
    }

}

if (!empty($errores)) {

    establecerResultadoTarifas([
        'rejilla' => $claveRejilla,
        'mensaje' => 'No se ha guardado nada: corrige las celdas marcadas en rojo y vuelve a guardar.',
        'detalle' => $detalle,
        'filas'   => $filas,
        'errores' => $errores,
    ]);

    header('Location: ' . $urlVolver);
    exit;

}


// =====================================================
// GUARDAR (TODO O NADA)
// =====================================================

$columnaTitular = columnaTitularTarifa($ambito);
$columnasEditables = array_merge(['nombre'], todasColumnasPrecio(), ['activa']);
$columnasInsertar = array_merge([$columnaTitular, 'tipo_suministro', 'peaje', 'creado_por'], $columnasEditables);

$stmtInsertar = $pdo->prepare('
    INSERT INTO tarifas (' . implode(', ', $columnasInsertar) . ')
    VALUES (:' . implode(', :', $columnasInsertar) . ')
');

$stmtActualizar = $pdo->prepare('
    UPDATE tarifas
    SET ' . implode(', ', array_map(fn(string $columna): string => $columna . ' = :' . $columna, $columnasEditables)) . '
    WHERE id = :id
');

// Datos de factura (solo en las rejillas que los llevan, ver
// tieneDatosFactura()): una fila de `datos_factura` por
// tarifa, que se crea o se actualiza con la misma sentencia.
$conFactura = tieneDatosFactura($servicio, $ambito);
$columnasFactura = todasColumnasFactura();

$stmtFactura = $pdo->prepare('
    INSERT INTO datos_factura (id_tarifa, ' . implode(', ', $columnasFactura) . ')
    VALUES (:id_tarifa, :' . implode(', :', $columnasFactura) . ')
    ON DUPLICATE KEY UPDATE ' . implode(', ', array_map(fn(string $columna): string => $columna . ' = VALUES(' . $columna . ')', $columnasFactura)) . '
');

/**
 * Guarda los datos de factura de una tarifa. Devuelve si ha
 * cambiado algo (MySQL: 1 = insertada, 2 = actualizada,
 * 0 = ya estaba igual).
 */
$guardarFactura = function (int $idTarifa, array $datos) use ($stmtFactura, $columnasFactura): bool {

    $stmtFactura->execute(array_intersect_key($datos, array_flip($columnasFactura)) + ['id_tarifa' => $idTarifa]);

    return $stmtFactura->rowCount() > 0;

};

$creadas = [];
$modificadas = [];

$pdo->beginTransaction();

try {

    foreach ($aInsertar as $datos) {

        $stmtInsertar->execute(
            array_intersect_key($datos, array_flip($columnasEditables))
            + [
                $columnaTitular   => $datos['id_titular'],
                'tipo_suministro' => $servicio,
                'peaje'           => $peaje,
                'creado_por'      => $_SESSION['id_usuario'],
            ]
        );

        if ($conFactura) {
            $guardarFactura((int) $pdo->lastInsertId(), $datos);
        }

        $creadas[] = $titulares[$datos['id_titular']]['nombre'] . ' — ' . $datos['nombre'];

    }

    foreach ($aActualizar as $idTarifa => $datos) {

        $stmtActualizar->execute(array_intersect_key($datos, array_flip($columnasEditables)) + ['id' => $idTarifa]);

        // MySQL solo cuenta la fila si algún valor ha cambiado
        // de verdad: así el log recoge solo las modificadas.
        $haCambiado = $stmtActualizar->rowCount() > 0;

        if ($conFactura) {
            $haCambiado = $guardarFactura($idTarifa, $datos) || $haCambiado;
        }

        if ($haCambiado) {
            $modificadas[] = $titulares[$datos['id_titular']]['nombre'] . ' — ' . $datos['nombre'];
        }

    }

    $pdo->commit();

} catch (Throwable $e) {

    $pdo->rollBack();

    establecerResultadoTarifas([
        'rejilla' => $claveRejilla,
        'mensaje' => 'No se han podido guardar los cambios. Inténtalo de nuevo.',
        'filas'   => $filas,
        'errores' => [],
    ]);

    header('Location: ' . $urlVolver);
    exit;

}


// =====================================================
// LOG Y MENSAJE
// =====================================================

if (empty($creadas) && empty($modificadas)) {

    $mensaje = 'No había cambios que guardar.';

} else {

    $partes = [];

    if (!empty($creadas)) {
        $partes[] = count($creadas) . (count($creadas) === 1 ? ' tarifa nueva' : ' tarifas nuevas');
    }

    if (!empty($modificadas)) {
        $partes[] = count($modificadas) . (count($modificadas) === 1 ? ' modificada' : ' modificadas');
    }

    $mensaje = 'Cambios guardados: ' . implode(' y ', $partes) . '.';

    registrarLog(
        LOG_EXITO,
        ($esClientes ? 'Tarifas de clientes ' : 'Tarifas ') . $servicio . ' ' . $peaje . ' actualizadas',
        $mensaje
            . (!empty($creadas) ? ' Nuevas: ' . implode('; ', $creadas) . '.' : '')
            . (!empty($modificadas) ? ' Modificadas: ' . implode('; ', $modificadas) . '.' : '')
    );

}

establecerResultadoTarifas([
    'rejilla' => $claveRejilla,
    'mensaje' => $mensaje,
]);

header('Location: ' . $urlVolver);
exit;
