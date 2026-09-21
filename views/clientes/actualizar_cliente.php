<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('clientes');

require_once '../../config/database.php';
require_once '../../includes/logs.php';
require_once '../../includes/validaciones.php';
require_once '../../includes/form_flash.php';


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

$nombre    = trim($_POST['nombre'] ?? '');
$apellidos = trim($_POST['apellidos'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');
$telefono  = trim($_POST['telefono'] ?? '');
$email     = trim($_POST['email'] ?? '');
$nif       = trim($_POST['nif'] ?? '');


// =====================================================
// COMPROBAR CAMPOS OBLIGATORIOS
// =====================================================

if ($id <= 0) {

    header('Location: clientes.php');
    exit;

}

// Se acumulan TODOS los errores encontrados en vez de
// cortar en el primero: así se avisa de todo lo que falla
// en un único intento (mismo criterio que
// guardar_empresa.php/guardar_usuario.php).

$errores = [];

if (
    $nombre === '' ||
    $apellidos === '' ||
    $email === '' ||
    $nif === ''
) {

    $errores[] = ['mensaje' => 'Todos los campos obligatorios deben estar completos.', 'campo' => null];

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

    $errores[] = ['mensaje' => 'El correo electrónico no tiene un formato válido.', 'campo' => 'email'];

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
// COMPROBAR NIF DUPLICADO
// =====================================================

$stmtNif = $pdo->prepare("
    SELECT id
    FROM clientes
    WHERE nif = ?
    AND id != ?
");

$stmtNif->execute([
    $nif,
    $id
]);

if ($stmtNif->fetch()) {

    $errores[] = ['mensaje' => 'Ya existe otro cliente con ese DNI/NIE.', 'campo' => 'nif'];

}


// =====================================================
// SI HAY ALGÚN ERROR, VOLVER AL FORMULARIO CON TODOS
// =====================================================

if (!empty($errores)) {

    $mensajes = array_unique(array_column($errores, 'mensaje'));
    $primerCampo = array_values(array_filter(array_column($errores, 'campo')))[0] ?? null;

    establecerErrorFormulario(implode(' ', $mensajes), $_POST, 'editar_cliente.php?id=' . $id, $primerCampo);

}


// =====================================================
// ACTUALIZAR CLIENTE
// =====================================================

$stmtActualizar = $pdo->prepare("
    UPDATE clientes
    SET
        nombre = ?,
        apellidos = ?,
        direccion = ?,
        telefono = ?,
        email = ?,
        nif = ?
    WHERE id = ?
");

$stmtActualizar->execute([
    $nombre,
    $apellidos,
    $direccion !== '' ? $direccion : null,
    $telefono !== '' ? $telefono : null,
    $email,
    $nif,
    $id
]);


// =====================================================
// RECUPERAR LOS DATOS ACTUALIZADOS
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
");

$stmtDatos->execute([$id]);

$cliente = $stmtDatos->fetch(PDO::FETCH_ASSOC);

registrarLog(
    LOG_EXITO,
    'Cliente modificado',
    'Se ha modificado el cliente "' . $cliente['nombre'] . ' ' . $cliente['apellidos'] . '".'
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

                    <a href="crear_cliente.php" class="config-save-button">
                        + Añadir cliente
                    </a>

                </div>

            </div>


            <!-- =================================================
                 MENSAJE DE CONFIRMACIÓN
            ================================================== -->

            <div class="config-card confirmation-card">


                <div class="form-info registration-success">

                    <p>

                        El cliente

                        <strong>
                            <?= htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellidos']) ?>
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


                <!-- =================================================
                     BOTONES
                ================================================== -->

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


    <!-- =================================================
         FOOTER
    ================================================== -->

    <?php include '../../templates/footer.php'; ?>


</body>

</html>
