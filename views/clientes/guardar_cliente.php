<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('clientes');

require_once '../../config/database.php';
require_once '../../includes/logs.php';
require_once '../../includes/validaciones.php';
require_once '../../includes/form_flash.php';


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

$nombre    = trim($_POST['nombre'] ?? '');
$apellidos = trim($_POST['apellidos'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');
$telefono  = trim($_POST['telefono'] ?? '');
$email     = trim($_POST['email'] ?? '');
$nif       = trim($_POST['nif'] ?? '');


// =====================================================
// VALIDACIONES
// =====================================================

if (
    $nombre === '' ||
    $apellidos === '' ||
    $email === '' ||
    $nif === ''
) {

    establecerErrorFormulario('Faltan datos obligatorios.', $_POST, 'crear_cliente.php');

}


if (!validarNombre($nombre)) {

    if (preg_match("/^[-']/", $nombre)) {
        establecerErrorFormulario('El nombre debe empezar con una letra.', $_POST, 'crear_cliente.php', 'nombre');
    }

    establecerErrorFormulario('El nombre no es válido. Solo se permiten letras, espacios, guiones y apóstrofes.', $_POST, 'crear_cliente.php', 'nombre');

}

if (!validarApellidos($apellidos)) {

    if (preg_match("/^[-']/", $apellidos)) {
        establecerErrorFormulario('Los apellidos deben empezar con una letra.', $_POST, 'crear_cliente.php', 'apellidos');
    }

    establecerErrorFormulario('Los apellidos no son válidos. Solo se permiten letras, espacios, guiones y apóstrofes.', $_POST, 'crear_cliente.php', 'apellidos');

}

if (!validarDniNie($nif)) {

    establecerErrorFormulario('El DNI/NIE no es válido.', $_POST, 'crear_cliente.php', 'nif');

}

if ($direccion !== '' && !validarDireccion($direccion)) {

    establecerErrorFormulario('La dirección no es válida.', $_POST, 'crear_cliente.php', 'direccion');

}

if ($telefono !== '' && !validarTelefono($telefono)) {

    establecerErrorFormulario('El teléfono no es válido. Introduce un número nacional o internacional (7 a 15 dígitos).', $_POST, 'crear_cliente.php', 'telefono');

}

if (!validarEmail($email)) {

    establecerErrorFormulario('El email no es válido.', $_POST, 'crear_cliente.php', 'email');

}


// =====================================================
// COMPROBAR QUE EL NIF NO EXISTE
// =====================================================

$stmt = $pdo->prepare("
    SELECT id
    FROM clientes
    WHERE nif = ?
    LIMIT 1
");

$stmt->execute([$nif]);

if ($stmt->fetch()) {

    establecerErrorFormulario('Ya existe un cliente con ese DNI/NIE.', $_POST, 'crear_cliente.php', 'nif');

}


// =====================================================
// INSERTAR CLIENTE
// =====================================================

$stmt = $pdo->prepare("
    INSERT INTO clientes (
        nombre,
        apellidos,
        direccion,
        telefono,
        email,
        nif,
        creado_por
    )
    VALUES (
        :nombre,
        :apellidos,
        :direccion,
        :telefono,
        :email,
        :nif,
        :creado_por
    )
");

$stmt->execute([
    ':nombre'     => $nombre,
    ':apellidos'  => $apellidos,
    ':direccion'  => $direccion !== '' ? $direccion : null,
    ':telefono'   => $telefono !== '' ? $telefono : null,
    ':email'      => $email,
    ':nif'        => $nif,
    ':creado_por' => $_SESSION['id_usuario'],
]);

$idCliente = $pdo->lastInsertId();


// =====================================================
// RECUPERAR EL CLIENTE CREADO
// =====================================================

$stmtDatos = $pdo->prepare("
    SELECT
        id,
        nombre,
        apellidos,
        direccion,
        telefono,
        email,
        nif
    FROM clientes
    WHERE id = ?
    LIMIT 1
");

$stmtDatos->execute([$idCliente]);

$cliente = $stmtDatos->fetch(PDO::FETCH_ASSOC);

registrarLog(
    LOG_EXITO,
    'Cliente creado',
    'Se ha creado el cliente "' . $cliente['nombre'] . ' ' . $cliente['apellidos'] . '".'
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


            <div class="config-card confirmation-card">

                <h2>Cliente creado correctamente</h2>

                <div class="form-info">

                    <p>

                        El cliente

                        <strong>
                            <?= htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellidos']) ?>
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
                            <span>Apellidos</span>
                            <strong><?= htmlspecialchars($cliente['apellidos']) ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Dirección</span>
                            <strong><?= htmlspecialchars($cliente['direccion'] ?? 'No indicada') ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Teléfono</span>
                            <strong><?= htmlspecialchars($cliente['telefono'] ?? 'No indicado') ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Email</span>
                            <strong><?= htmlspecialchars($cliente['email']) ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>DNI/NIE</span>
                            <strong><?= htmlspecialchars($cliente['nif']) ?></strong>
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
