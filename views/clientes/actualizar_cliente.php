<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('clientes');

require_once '../../config/database.php';
require_once '../../includes/logs.php';


// =====================================================
// COMPROBAR QUE LA PETICIÓN SEA POST
// =====================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: clientes.php');
    exit;

}


// =====================================================
// RECIBIR DATOS DEL FORMULARIO
// =====================================================

$id = isset($_POST['id'])
    ? (int) $_POST['id']
    : 0;

$nombre           = trim($_POST['nombre'] ?? '');
$tipo             = trim($_POST['tipo'] ?? '');
$identificacion   = trim($_POST['identificacion'] ?? '');
$correo           = trim($_POST['correo'] ?? '');
$comercializadora = trim($_POST['comercializadora'] ?? '');
$tarifa           = trim($_POST['tarifa'] ?? '');
$estado           = trim($_POST['estado'] ?? '');


// =====================================================
// COMPROBAR CAMPOS OBLIGATORIOS
// =====================================================

$tiposValidos             = ['Particular', 'Empresa'];
$comercializadorasValidas = ['Endesa', 'Iberdrola', 'Naturgy', 'Repsol', 'TotalEnergies'];
$tarifasValidas           = ['PVPC', 'Mercado libre', 'Tarifa fija'];
$estadosValidos           = ['Activo', 'Pendiente', 'Inactivo'];

if (
    $id <= 0 ||
    $nombre === '' ||
    !in_array($tipo, $tiposValidos, true) ||
    $identificacion === '' ||
    $correo === '' ||
    !in_array($comercializadora, $comercializadorasValidas, true) ||
    !in_array($tarifa, $tarifasValidas, true) ||
    !in_array($estado, $estadosValidos, true)
) {

    die('
        <h2>Error</h2>

        <p>
            Todos los campos obligatorios deben estar completos y ser válidos.
        </p>

        <p>
            <a href="clientes.php">
                Volver a clientes
            </a>
        </p>
    ');

}


// =====================================================
// COMPROBAR FORMATO DEL CORREO
// =====================================================

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {

    die('
        <h2>Error</h2>

        <p>
            El correo electrónico no tiene un formato válido.
        </p>

        <p>
            <a href="clientes.php">
                Volver a clientes
            </a>
        </p>
    ');

}


// =====================================================
// COMPROBAR QUE EL CLIENTE EXISTA
// =====================================================

$stmtCliente = $pdo->prepare("
    SELECT id, creado_por
    FROM clientes
    WHERE id = ?
");

$stmtCliente->execute([$id]);

$clienteExiste = $stmtCliente->fetch(PDO::FETCH_ASSOC);


if (!$clienteExiste) {

    die('
        <h2>Error</h2>

        <p>
            El cliente que intentas modificar no existe.
        </p>

        <p>
            <a href="clientes.php">
                Volver a clientes
            </a>
        </p>
    ');

}


// =====================================================
// COMPROBAR QUE PUEDE EDITAR ESTE CLIENTE
// =====================================================

if (!puedeVerCliente($clienteExiste['creado_por'] !== null ? (int) $clienteExiste['creado_por'] : null)) {

    die('
        <h2>Error</h2>

        <p>
            No tienes permiso para editar este cliente.
        </p>

        <p>
            <a href="clientes.php">
                Volver a clientes
            </a>
        </p>
    ');

}


// =====================================================
// COMPROBAR IDENTIFICACIÓN DUPLICADA
// =====================================================

$stmtIdentificacion = $pdo->prepare("
    SELECT id
    FROM clientes
    WHERE identificacion = ?
    AND id != ?
");

$stmtIdentificacion->execute([
    $identificacion,
    $id
]);

if ($stmtIdentificacion->fetch()) {

    die('
        <h2>Error</h2>

        <p>
            Ya existe otro cliente con esa identificación.
        </p>

        <p>
            <a href="javascript:history.back()">
                Volver al formulario
            </a>
        </p>
    ');

}


// =====================================================
// ACTUALIZAR CLIENTE
// =====================================================

$stmtActualizar = $pdo->prepare("
    UPDATE clientes
    SET
        nombre = ?,
        tipo = ?,
        identificacion = ?,
        correo = ?,
        comercializadora = ?,
        tarifa = ?,
        estado = ?
    WHERE id = ?
");

$stmtActualizar->execute([
    $nombre,
    $tipo,
    $identificacion,
    $correo,
    $comercializadora,
    $tarifa,
    $estado,
    $id
]);


// =====================================================
// RECUPERAR LOS DATOS ACTUALIZADOS
// =====================================================

$stmtDatos = $pdo->prepare("
    SELECT
        id,
        nombre,
        tipo,
        identificacion,
        correo,
        comercializadora,
        tarifa,
        estado
    FROM clientes
    WHERE id = ?
");

$stmtDatos->execute([$id]);

$cliente = $stmtDatos->fetch(PDO::FETCH_ASSOC);

registrarLog(
    LOG_EXITO,
    'Cliente modificado',
    'Se ha modificado el cliente "' . $cliente['nombre'] . '".'
);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cliente actualizado - Comparador Eléctrico</title>

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
             CONTENIDO PRINCIPAL
        ================================================== -->

        <main class="main-content">


            <!-- =================================================
                 CABECERA
            ================================================== -->

            <div class="page-header">

                <div>

                    <h1>Clientes</h1>

                    <p>
                        Cliente actualizado correctamente
                    </p>

                </div>


                <div class="page-header-actions">

                    <div class="page-date">
                        15 septiembre 2026
                    </div>

                </div>

            </div>


            <!-- =================================================
                 MENSAJE DE CONFIRMACIÓN
            ================================================== -->

            <div class="config-card">


                <h2>
                    Cliente actualizado
                </h2>


                <div class="form-info">

                    <p>

                        El cliente

                        <strong>
                            <?= htmlspecialchars($cliente['nombre']) ?>
                        </strong>

                        se ha actualizado correctamente.

                    </p>

                </div>


                <!-- =================================================
                     INFORMACIÓN DEL CLIENTE
                ================================================== -->

                <div class="usuario-detalle">

                    <div class="usuario-detalle-grid">

                        <div class="usuario-detalle-item">
                            <span>ID de cliente</span>
                            <strong><?= htmlspecialchars($cliente['id']) ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Nombre</span>
                            <strong><?= htmlspecialchars($cliente['nombre']) ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Tipo</span>
                            <strong><?= htmlspecialchars($cliente['tipo']) ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Identificación</span>
                            <strong><?= htmlspecialchars($cliente['identificacion']) ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Correo</span>
                            <strong><?= htmlspecialchars($cliente['correo']) ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Comercializadora</span>
                            <strong><?= htmlspecialchars($cliente['comercializadora']) ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Tarifa</span>
                            <strong><?= htmlspecialchars($cliente['tarifa']) ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Estado</span>
                            <strong><?= htmlspecialchars($cliente['estado']) ?></strong>
                        </div>

                    </div>

                </div>


                <!-- =================================================
                     BOTONES
                ================================================== -->

                <div class="form-actions">


                    <a href="crear_cliente.php" class="config-save-button">
                        + Nuevo cliente
                    </a>


                    <a href="clientes.php" class="config-cancel-button">
                        Volver a clientes
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
