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
//
// Se acumulan TODOS los errores encontrados en vez de
// cortar en el primero: así se avisa de todo lo que falla
// en un único intento (mismo criterio que
// guardar_empresa.php/guardar_usuario.php).
//
// =====================================================

$errores = [];

if (
    $nombre === '' ||
    $apellidos === '' ||
    $email === '' ||
    $nif === ''
) {

    $errores[] = ['mensaje' => 'Faltan datos obligatorios.', 'campo' => null];

}

if (!validarNombre($nombre)) {

    $errores[] = [
        'mensaje' => preg_match("/^[-']/", $nombre)
            ? 'El nombre debe empezar con una letra.'
            : 'El nombre no es válido. Solo se permiten letras, espacios, guiones y apóstrofes.',
        'campo' => 'nombre',
    ];

}

if (!validarApellidos($apellidos)) {

    $errores[] = [
        'mensaje' => preg_match("/^[-']/", $apellidos)
            ? 'Los apellidos deben empezar con una letra.'
            : 'Los apellidos no son válidos. Solo se permiten letras, espacios, guiones y apóstrofes.',
        'campo' => 'apellidos',
    ];

}

if (!validarDniNie($nif)) {

    $errores[] = ['mensaje' => 'El DNI/NIE no es válido.', 'campo' => 'nif'];

}

if ($direccion !== '' && !validarDireccion($direccion)) {

    $errores[] = ['mensaje' => 'La dirección no es válida.', 'campo' => 'direccion'];

}

if ($telefono !== '' && !validarTelefono($telefono)) {

    $errores[] = ['mensaje' => 'El teléfono no es válido. Introduce un número nacional o internacional (7 a 15 dígitos).', 'campo' => 'telefono'];

}

if (!validarEmail($email)) {

    $errores[] = ['mensaje' => 'El email no es válido.', 'campo' => 'email'];

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

    $errores[] = ['mensaje' => 'Ya existe un cliente con ese DNI/NIE.', 'campo' => 'nif'];

}


// =====================================================
// SI HAY ALGÚN ERROR, VOLVER AL FORMULARIO CON TODOS
// =====================================================

if (!empty($errores)) {

    $mensajes = array_unique(array_column($errores, 'mensaje'));
    $primerCampo = array_values(array_filter(array_column($errores, 'campo')))[0] ?? null;

    establecerErrorFormulario(implode(' ', $mensajes), $_POST, 'crear_cliente.php', $primerCampo);

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

                    <a href="crear_cliente.php" class="config-save-button">
                        + Añadir cliente
                    </a>

                    <div class="page-date">
                        15 septiembre 2026
                    </div>

                </div>

            </div>


            <div class="config-card confirmation-card">

                <div class="form-info registration-success">

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
                            <span>Nombre</span>
                            <strong><?= htmlspecialchars($cliente['nombre']) ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Apellidos</span>
                            <strong><?= htmlspecialchars($cliente['apellidos']) ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>ID de cliente</span>
                            <strong><?= htmlspecialchars($cliente['id']) ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>DNI/NIE</span>
                            <strong><?= htmlspecialchars($cliente['nif']) ?></strong>
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

                    </div>

                </div>


                <div class="form-actions">

                    <a href="editar_cliente.php?id=<?= (int) $cliente['id'] ?>" class="config-save-button">
                        Editar
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
