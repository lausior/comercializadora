<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('clientes');

require_once '../../config/database.php';
require_once '../../includes/logs.php';


// =====================================================
// COMPROBAR QUE EL FORMULARIO SE ENVÍA POR POST
// =====================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: clientes.php');
    exit;

}


// =====================================================
// RECOGER DATOS DEL FORMULARIO
// =====================================================

$nombre           = trim($_POST['nombre'] ?? '');
$tipo             = trim($_POST['tipo'] ?? '');
$identificacion   = trim($_POST['identificacion'] ?? '');
$correo           = trim($_POST['correo'] ?? '');
$comercializadora = trim($_POST['comercializadora'] ?? '');
$tarifa           = trim($_POST['tarifa'] ?? '');
$estado           = trim($_POST['estado'] ?? '');


// =====================================================
// VALIDACIONES
// =====================================================

$tiposValidos             = ['Particular', 'Empresa'];
$comercializadorasValidas = ['Endesa', 'Iberdrola', 'Naturgy', 'Repsol', 'TotalEnergies'];
$tarifasValidas           = ['PVPC', 'Mercado libre', 'Tarifa fija'];
$estadosValidos           = ['Activo', 'Pendiente', 'Inactivo'];

if (
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
            Faltan datos obligatorios o alguno de los valores enviados no es válido.
        </p>

        <p>
            <a href="crear_cliente.php">
                Volver al formulario
            </a>
        </p>
    ');

}


// =====================================================
// VALIDAR CORREO
// =====================================================

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {

    die('
        <h2>Error</h2>

        <p>
            El correo no es válido.
        </p>

        <p>
            <a href="crear_cliente.php">
                Volver al formulario
            </a>
        </p>
    ');

}


// =====================================================
// COMPROBAR QUE LA IDENTIFICACIÓN NO EXISTE
// =====================================================

$stmt = $pdo->prepare("
    SELECT id
    FROM clientes
    WHERE identificacion = ?
    LIMIT 1
");

$stmt->execute([$identificacion]);

if ($stmt->fetch()) {

    die('
        <h2>Error</h2>

        <p>
            Ya existe un cliente con esa identificación.
        </p>

        <p>
            <a href="crear_cliente.php">
                Volver al formulario
            </a>
        </p>
    ');

}


// =====================================================
// INSERTAR CLIENTE
// =====================================================

$stmt = $pdo->prepare("
    INSERT INTO clientes (
        nombre,
        tipo,
        identificacion,
        correo,
        comercializadora,
        tarifa,
        estado,
        creado_por
    )
    VALUES (
        :nombre,
        :tipo,
        :identificacion,
        :correo,
        :comercializadora,
        :tarifa,
        :estado,
        :creado_por
    )
");

$stmt->execute([
    ':nombre'           => $nombre,
    ':tipo'             => $tipo,
    ':identificacion'   => $identificacion,
    ':correo'           => $correo,
    ':comercializadora' => $comercializadora,
    ':tarifa'           => $tarifa,
    ':estado'           => $estado,
    ':creado_por'       => $_SESSION['id_usuario'],
]);

$idCliente = $pdo->lastInsertId();


// =====================================================
// RECUPERAR EL CLIENTE CREADO
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
    LIMIT 1
");

$stmtDatos->execute([$idCliente]);

$cliente = $stmtDatos->fetch(PDO::FETCH_ASSOC);

registrarLog(
    LOG_EXITO,
    'Cliente creado',
    'Se ha creado el cliente "' . $cliente['nombre'] . '".'
);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cliente creado - Comparador Eléctrico</title>

    <link rel="stylesheet" href="../../css/style.css">

</head>

<body>


    <?php include '../../templates/header.php'; ?>


    <div class="app-container">


        <?php include '../../templates/sidebar.php'; ?>


        <main class="main-content">


            <div class="page-header">

                <div>

                    <h1>Clientes</h1>

                    <p>
                        Cliente creado correctamente
                    </p>

                </div>

                <div class="page-header-actions">

                    <div class="page-date">
                        15 septiembre 2026
                    </div>

                </div>

            </div>


            <div class="config-card">

                <h2>Cliente creado correctamente</h2>

                <div class="form-info">

                    <p>

                        El cliente

                        <strong>
                            <?= htmlspecialchars($cliente['nombre']) ?>
                        </strong>

                        se ha creado correctamente.

                    </p>

                </div>


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


    <?php include '../../templates/footer.php'; ?>


</body>

</html>
