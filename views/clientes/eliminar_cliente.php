<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('clientes');

require_once '../../config/database.php';
require_once '../../includes/logs.php';


// =====================================================
// COMPROBAR QUE SE HA RECIBIDO UN ID
// =====================================================

$id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;


if ($id <= 0) {

    die('
        <h2>Error</h2>

        <p>
            El cliente seleccionado no es válido.
        </p>

        <p>
            <a href="clientes.php">
                Volver a clientes
            </a>
        </p>
    ');

}


// =====================================================
// BUSCAR EL CLIENTE
// =====================================================

$stmtCliente = $pdo->prepare("
    SELECT
        id,
        nombre,
        identificacion,
        correo,
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

    die('
        <h2>Error</h2>

        <p>
            El cliente que intentas eliminar no existe.
        </p>

        <p>
            <a href="clientes.php">
                Volver a clientes
            </a>
        </p>
    ');

}


// =====================================================
// COMPROBAR QUE PUEDE ELIMINAR ESTE CLIENTE
// =====================================================

if (!puedeVerCliente($cliente['creado_por'] !== null ? (int) $cliente['creado_por'] : null)) {

    die('
        <h2>Error</h2>

        <p>
            No tienes permiso para eliminar este cliente.
        </p>

        <p>
            <a href="clientes.php">
                Volver a clientes
            </a>
        </p>
    ');

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

    die('
        <h2>Error</h2>

        <p>
            No se ha podido eliminar el cliente.
        </p>

        <p>
            <a href="clientes.php">
                Volver a clientes
            </a>
        </p>
    ');

}


// =====================================================
// COMPROBAR QUE REALMENTE SE HA ELIMINADO
// =====================================================

if ($stmtEliminar->rowCount() !== 1) {

    die('
        <h2>Error</h2>

        <p>
            No se ha podido eliminar el cliente.
        </p>

        <p>
            <a href="clientes.php">
                Volver a clientes
            </a>
        </p>
    ');

}

registrarLog(
    LOG_ADVERTENCIA,
    'Cliente eliminado',
    'Se ha eliminado el cliente "' . $cliente['nombre'] . '".'
);

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


                <div class="form-info">


                    <p>

                        El cliente

                        <strong>
                            <?= htmlspecialchars($cliente['nombre']) ?>
                        </strong>

                        ha sido eliminado correctamente.

                    </p>


                    <p>

                        ID de cliente:

                        <strong>
                            <?= htmlspecialchars($cliente['id']) ?>
                        </strong>

                    </p>


                    <p>

                        Identificación:

                        <strong>
                            <?= htmlspecialchars($cliente['identificacion']) ?>
                        </strong>

                    </p>


                    <p>

                        Correo:

                        <strong>
                            <?= htmlspecialchars($cliente['correo']) ?>
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
