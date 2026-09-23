<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('comercializadoras');

require_once '../../config/database.php';
require_once '../../includes/logs.php';
require_once '../../includes/validaciones.php';
require_once '../../includes/form_flash.php';


// =====================================================
// COMPROBAR QUE EL FORMULARIO SE ENVÍA POR POST
// =====================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: comercializadoras.php');
    exit;

}


// =====================================================
// RECOGER DATOS DEL FORMULARIO
// =====================================================

$nombre         = trim($_POST['nombre'] ?? '');
$cif            = trim($_POST['cif'] ?? '');
$direccion      = trim($_POST['direccion'] ?? '');
$telefono       = trim($_POST['telefono'] ?? '');
$email          = trim($_POST['email'] ?? '');
$suministraLuz  = !empty($_POST['suministra_luz']);
$suministraGas  = !empty($_POST['suministra_gas']);


// =====================================================
// VALIDACIONES
// =====================================================
//
// Se acumulan TODOS los errores encontrados (formato y
// duplicados) en vez de cortar en el primero: así, si el
// CIF no es válido Y ya existe otra comercializadora con
// ese nombre, se avisa de las dos cosas a la vez en el
// primer intento (mismo criterio que guardar_empresa.php).
//
// Dirección, teléfono y email son opcionales: solo se
// valida su formato si se han rellenado.
//
// =====================================================

$errores = [];

if (!validarNombreEmpresa($nombre)) {

    $errores[] = [
        'mensaje' => $nombre !== '' && !preg_match('/^[\p{L}\p{N}]/u', $nombre)
            ? 'El nombre debe empezar con una letra o un número.'
            : 'El nombre es obligatorio y debe tener entre 2 y 150 caracteres. Solo se permiten letras, números, espacios y los símbolos . , \' - & ( ) /.',
        'campo' => 'nombre',
    ];

}

if (!validarCIF($cif)) {

    $errores[] = ['mensaje' => 'El CIF no es válido.', 'campo' => 'cif'];

}

if ($direccion !== '' && !validarDireccion($direccion)) {

    $errores[] = ['mensaje' => 'La dirección no es válida.', 'campo' => 'direccion'];

}

if ($telefono !== '' && !validarTelefono($telefono)) {

    $errores[] = ['mensaje' => 'El teléfono no es válido. Introduce un número nacional o internacional (7 a 15 dígitos).', 'campo' => 'telefono'];

}

if ($email !== '' && !validarEmail($email)) {

    $errores[] = ['mensaje' => 'El email no es válido.', 'campo' => 'email'];

}

if (!$suministraLuz && !$suministraGas) {

    $errores[] = ['mensaje' => 'Debes marcar al menos un servicio (luz o gas).', 'campo' => 'suministra_luz'];

}


// =====================================================
// COMPROBAR QUE EL CIF NO EXISTE
// =====================================================

if (validarCIF($cif)) {

    $stmt = $pdo->prepare("
        SELECT id
        FROM comercializadoras
        WHERE cif = ?
        LIMIT 1
    ");

    $stmt->execute([$cif]);

    if ($stmt->fetch()) {

        $errores[] = ['mensaje' => 'Ya existe una comercializadora con ese CIF.', 'campo' => 'cif'];

    }

}


// =====================================================
// SI HAY ALGÚN ERROR, VOLVER AL FORMULARIO CON TODOS
// =====================================================

if (!empty($errores)) {

    $mensajes = array_unique(array_column($errores, 'mensaje'));
    $primerCampo = array_values(array_filter(array_column($errores, 'campo')))[0] ?? null;

    establecerErrorFormulario(implode(' ', $mensajes), $_POST, 'crear_comercializadora.php', $primerCampo);

}


// =====================================================
// INSERTAR COMERCIALIZADORA
// =====================================================

$stmt = $pdo->prepare("
    INSERT INTO comercializadoras (
        nombre,
        cif,
        direccion,
        telefono,
        email,
        suministra_luz,
        suministra_gas,
        creado_por
    )
    VALUES (
        :nombre,
        :cif,
        :direccion,
        :telefono,
        :email,
        :suministra_luz,
        :suministra_gas,
        :creado_por
    )
");

$stmt->execute([
    ':nombre'          => $nombre,
    ':cif'             => $cif,
    ':direccion'       => $direccion !== '' ? $direccion : null,
    ':telefono'        => $telefono !== '' ? $telefono : null,
    ':email'           => $email !== '' ? $email : null,
    ':suministra_luz'  => $suministraLuz ? 1 : 0,
    ':suministra_gas'  => $suministraGas ? 1 : 0,
    ':creado_por'      => $_SESSION['id_usuario'],
]);

$idComercializadora = $pdo->lastInsertId();


// =====================================================
// RECUPERAR LA COMERCIALIZADORA CREADA
// =====================================================

$stmtDatos = $pdo->prepare("
    SELECT id, nombre, cif, direccion, telefono, email, suministra_luz, suministra_gas
    FROM comercializadoras
    WHERE id = ?
    LIMIT 1
");

$stmtDatos->execute([$idComercializadora]);

$comercializadora = $stmtDatos->fetch(PDO::FETCH_ASSOC);

$serviciosComercializadora = [];
if ($comercializadora['suministra_luz']) {
    $serviciosComercializadora[] = 'Luz';
}
if ($comercializadora['suministra_gas']) {
    $serviciosComercializadora[] = 'Gas';
}

registrarLog(
    LOG_EXITO,
    'Comercializadora creada',
    'Se ha creado la comercializadora "' . $comercializadora['nombre'] . '".'
);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Comercializadora creada - Comparador Eléctrico</title>

    <link rel="stylesheet" href="../../css/style.css">

</head>

<body>


    <?php include '../../templates/header.php'; ?>


    <div class="app-container">


        <?php include '../../templates/sidebar.php'; ?>


        <main class="main-content">


            <div class="page-header">

                <div>

                    <h1>Comercializadoras</h1>

                    <p>
                        Comercializadora creada correctamente
                    </p>

                </div>

                <div class="page-header-actions">

                    <a href="crear_comercializadora.php" class="config-save-button">
                        + Añadir comercializadora
                    </a>

                </div>

            </div>


            <div class="config-card confirmation-card">

                <div class="form-info registration-success">

                    <p>

                        La comercializadora

                        <strong>
                            <?= htmlspecialchars($comercializadora['nombre']) ?>
                        </strong>

                        se ha creado correctamente.

                    </p>

                </div>


                <h2 class="confirmation-section-title">Datos de la comercializadora</h2>

                <div class="usuario-detalle">

                    <div class="usuario-detalle-grid">

                        <div class="usuario-detalle-item">
                            <span>Nombre</span>
                            <strong><?= htmlspecialchars($comercializadora['nombre']) ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>ID</span>
                            <strong><?= htmlspecialchars($comercializadora['id']) ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>CIF</span>
                            <strong><?= htmlspecialchars($comercializadora['cif']) ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Dirección</span>
                            <strong><?= htmlspecialchars($comercializadora['direccion'] ?? 'No indicada') ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Teléfono</span>
                            <strong><?= htmlspecialchars($comercializadora['telefono'] ?? 'No indicado') ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Email</span>
                            <strong><?= htmlspecialchars($comercializadora['email'] ?? 'No indicado') ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Servicios</span>
                            <strong><?= htmlspecialchars(implode(' y ', $serviciosComercializadora)) ?></strong>
                        </div>

                    </div>

                </div>


                <div class="form-actions">

                    <a href="editar_comercializadora.php?id=<?= (int) $comercializadora['id'] ?>" class="config-save-button">
                        Editar
                    </a>

                    <a href="comercializadoras.php" class="config-cancel-button">
                        Volver a comercializadoras
                    </a>

                </div>

            </div>


        </main>


    </div>


    <?php include '../../templates/footer.php'; ?>

</body>

</html>
