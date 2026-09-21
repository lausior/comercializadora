<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('clientes');

require_once '../../config/database.php';
require_once '../../includes/logs.php';


// =====================================================
// RESPUESTA DE ERROR (HTML O JSON SEGÚN LA PETICIÓN)
// =====================================================
//
// Si la petición viene por AJAX (ajax=1, ver clientes.js), un
// error a mitad de proceso no puede devolver HTML: el fetch()
// del listado espera JSON y su .json() rompería con
// "unexpected token '<'" al recibirlo (por ejemplo, al hacer
// doble clic y reenviar el borrado del mismo cliente).
//
// =====================================================

function responderErrorEliminarCliente(string $mensaje): void
{
    if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {

        header('Content-Type: application/json; charset=UTF-8');

        echo json_encode([
            'ok' => false,
            'error' => $mensaje,
        ], JSON_UNESCAPED_UNICODE);

        exit;

    }

    die('
        <h2>Error</h2>

        <p>' . htmlspecialchars($mensaje) . '</p>

        <p>
            <a href="clientes.php">
                Volver a clientes
            </a>
        </p>
    ');
}


// =====================================================
// COMPROBAR QUE SE HA RECIBIDO UN ID
// =====================================================

$id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;


if ($id <= 0) {

    responderErrorEliminarCliente('El cliente seleccionado no es válido.');

}


// =====================================================
// BUSCAR EL CLIENTE
// =====================================================

$stmtCliente = $pdo->prepare("
    SELECT
        id,
        nombre,
        apellidos,
        nif,
        email,
        creado_por
    FROM clientes
    WHERE id = ?
");

$stmtCliente->execute([$id]);

$cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);


// =====================================================
// COMPROBAR QUE EL CLIENTE EXISTA
// =====================================================

if (!$cliente) {

    responderErrorEliminarCliente('El cliente que intentas eliminar no existe.');

}


// =====================================================
// COMPROBAR QUE PUEDE ELIMINAR ESTE CLIENTE
// =====================================================

if (!puedeVerCliente($cliente['creado_por'] !== null ? (int) $cliente['creado_por'] : null)) {

    responderErrorEliminarCliente('No tienes permiso para eliminar este cliente.');

}


// =====================================================
// ELIMINAR CLIENTE
// =====================================================

try {

    $stmtEliminar = $pdo->prepare("
        DELETE FROM clientes
        WHERE id = ?
    ");

    $stmtEliminar->execute([$id]);


} catch (PDOException $e) {

    responderErrorEliminarCliente('No se ha podido eliminar el cliente.');

}


// =====================================================
// COMPROBAR QUE REALMENTE SE HA ELIMINADO
// =====================================================

if ($stmtEliminar->rowCount() !== 1) {

    responderErrorEliminarCliente('No se ha podido eliminar el cliente.');

}

registrarLog(
    LOG_ADVERTENCIA,
    'Cliente eliminado',
    'Se ha eliminado el cliente "' . $cliente['nombre'] . ' ' . $cliente['apellidos'] . '".'
);

if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {

    header('Content-Type: application/json; charset=UTF-8');

    echo json_encode([
        'ok' => true,
        'tipo' => 'Cliente',
        'nombre' => $cliente['nombre'] . ' ' . $cliente['apellidos'],
        'campos' => [
            'ID' => $cliente['id'],
            'DNI/NIE' => $cliente['nif'],
            'Email' => $cliente['email'],
        ],
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cliente eliminado - Comparador Eléctrico</title>

    <link rel="stylesheet" href="../../css/style.css">

</head>


<body>


    <!-- =================================================
         HEADER
    ================================================== -->

    <?php include '../../templates/header.php'; ?>


    <div class="app-container">


        <!-- =================================================
             SIDEBAR
        ================================================== -->

        <?php include '../../templates/sidebar.php'; ?>


        <!-- =================================================
             CONTENIDO
        ================================================== -->

        <main class="main-content">


            <!-- =================================================
                 CABECERA
            ================================================== -->

            <div class="page-header">

                <div>

                    <h1>
                        Clientes
                    </h1>

                    <p>
                        Cliente eliminado correctamente
                    </p>

                </div>


                <div class="page-header-actions">

                    <div class="page-date">
                        15 septiembre 2026
                    </div>

                </div>

            </div>


            <!-- =================================================
                 CONFIRMACIÓN
            ================================================== -->

            <div class="config-card">


                <h2>
                    Cliente eliminado
                </h2>


                <div class="form-info registration-success">

                    <p>

                        El cliente

                        <strong>
                            <?= htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellidos']) ?>
                        </strong>

                        ha sido eliminado correctamente.

                    </p>

                </div>


                <div class="form-info">


                    <p>

                        ID de cliente:

                        <strong>
                            <?= htmlspecialchars($cliente['id']) ?>
                        </strong>

                    </p>


                    <p>

                        DNI/NIE:

                        <strong>
                            <?= htmlspecialchars($cliente['nif']) ?>
                        </strong>

                    </p>


                    <p>

                        Email:

                        <strong>
                            <?= htmlspecialchars($cliente['email']) ?>
                        </strong>

                    </p>


                </div>


                <!-- =================================================
                     BOTONES
                ================================================== -->

                <div class="form-actions">


                    <a href="crear_cliente.php" class="config-save-button">
                        + Nuevo cliente
                    </a>


                    <a href="clientes.php" class="config-cancel-button">
                        Volver
                    </a>


                </div>


            </div>


        </main>


    </div>


    <!-- =================================================
         FOOTER
    ================================================== -->

    <?php include '../../templates/footer.php'; ?>


</body>

</html>
