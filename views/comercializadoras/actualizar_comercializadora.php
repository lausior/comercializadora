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

$id             = (int) ($_POST['id'] ?? 0);
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

if ($id <= 0) {

    die('
        <h2>Error</h2>

        <p>
            La comercializadora indicada no es válida.
        </p>

        <p>
            <a href="comercializadoras.php">
                Volver a comercializadoras
            </a>
        </p>
    ');

}

if (!validarNombreEmpresa($nombre)) {

    establecerErrorFormulario(
        $nombre !== '' && !preg_match('/^[\p{L}\p{N}]/u', $nombre)
            ? 'El nombre debe empezar con una letra o un número.'
            : 'El nombre es obligatorio y debe tener entre 2 y 150 caracteres. Solo se permiten letras, números, espacios y los símbolos . , \' - & ( ) /.',
        $_POST,
        'editar_comercializadora.php?id=' . $id,
        'nombre'
    );

}

if (!validarCIF($cif)) {

    establecerErrorFormulario('El CIF no es válido.', $_POST, 'editar_comercializadora.php?id=' . $id, 'cif');

}

if ($direccion !== '' && !validarDireccion($direccion)) {

    establecerErrorFormulario('La dirección no es válida.', $_POST, 'editar_comercializadora.php?id=' . $id, 'direccion');

}

if ($telefono !== '' && !validarTelefono($telefono)) {

    establecerErrorFormulario('El teléfono no es válido. Introduce un número nacional o internacional (7 a 15 dígitos).', $_POST, 'editar_comercializadora.php?id=' . $id, 'telefono');

}

if ($email !== '' && !validarEmail($email)) {

    establecerErrorFormulario('El email no es válido.', $_POST, 'editar_comercializadora.php?id=' . $id, 'email');

}

if (!$suministraLuz && !$suministraGas) {

    establecerErrorFormulario('Debes marcar al menos un servicio (luz o gas).', $_POST, 'editar_comercializadora.php?id=' . $id, 'suministra_luz');

}


// =====================================================
// COMPROBAR QUE LA COMERCIALIZADORA EXISTE
// =====================================================

$stmt = $pdo->prepare("
    SELECT id, creado_por
    FROM comercializadoras
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$comercializadoraExistente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$comercializadoraExistente) {

    die('
        <h2>Error</h2>

        <p>
            La comercializadora que intentas editar no existe.
        </p>

        <p>
            <a href="comercializadoras.php">
                Volver a comercializadoras
            </a>
        </p>
    ');

}


// =====================================================
// COMPROBAR QUE PUEDE EDITAR ESTA COMERCIALIZADORA
// =====================================================

if (!puedeVerComercializadora(
    $comercializadoraExistente['creado_por'] !== null ? (int) $comercializadoraExistente['creado_por'] : null
)) {

    die('
        <h2>Error</h2>

        <p>
            No tienes permiso para editar esta comercializadora.
        </p>

        <p>
            <a href="comercializadoras.php">
                Volver a comercializadoras
            </a>
        </p>
    ');

}


// =====================================================
// COMPROBAR CIF DUPLICADO (EN OTRA COMERCIALIZADORA)
// =====================================================

$stmt = $pdo->prepare("
    SELECT id
    FROM comercializadoras
    WHERE cif = ?
        AND id != ?
    LIMIT 1
");

$stmt->execute([$cif, $id]);

if ($stmt->fetch()) {

    establecerErrorFormulario('Ya existe otra comercializadora con ese CIF.', $_POST, 'editar_comercializadora.php?id=' . $id, 'cif');

}


// =====================================================
// ACTUALIZAR COMERCIALIZADORA
// =====================================================

$stmt = $pdo->prepare("
    UPDATE comercializadoras
    SET
        nombre = :nombre,
        cif = :cif,
        direccion = :direccion,
        telefono = :telefono,
        email = :email,
        suministra_luz = :suministra_luz,
        suministra_gas = :suministra_gas
    WHERE id = :id
");

$stmt->execute([
    ':nombre'          => $nombre,
    ':cif'             => $cif,
    ':direccion'       => $direccion !== '' ? $direccion : null,
    ':telefono'        => $telefono !== '' ? $telefono : null,
    ':email'           => $email !== '' ? $email : null,
    ':suministra_luz'  => $suministraLuz ? 1 : 0,
    ':suministra_gas'  => $suministraGas ? 1 : 0,
    ':id'              => $id,
]);

$stmtDatos = $pdo->prepare("
    SELECT id, nombre, cif, direccion, telefono, email, suministra_luz, suministra_gas
    FROM comercializadoras
    WHERE id = ?
    LIMIT 1
");

$stmtDatos->execute([$id]);

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
    'Comercializadora modificada',
    'Se ha modificado la comercializadora "' . $nombre . '".'
);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Comercializadora actualizada - Comparador Eléctrico</title>

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
                    <p>Comercializadora actualizada correctamente</p>
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
                        <strong><?= htmlspecialchars($comercializadora['nombre'], ENT_QUOTES, 'UTF-8') ?></strong>
                        se ha actualizado correctamente.
                    </p>
                </div>

                <div class="usuario-detalle">

                    <div class="usuario-detalle-grid">

                        <div class="usuario-detalle-item">
                            <span>Nombre</span>
                            <strong><?= htmlspecialchars($comercializadora['nombre'], ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>ID</span>
                            <strong><?= htmlspecialchars($comercializadora['id'], ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>CIF</span>
                            <strong><?= htmlspecialchars($comercializadora['cif'], ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Dirección</span>
                            <strong><?= htmlspecialchars($comercializadora['direccion'] ?? 'No indicada', ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Teléfono</span>
                            <strong><?= htmlspecialchars($comercializadora['telefono'] ?? 'No indicado', ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Email</span>
                            <strong><?= htmlspecialchars($comercializadora['email'] ?? 'No indicado', ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Servicios</span>
                            <strong><?= htmlspecialchars(implode(' y ', $serviciosComercializadora), ENT_QUOTES, 'UTF-8') ?></strong>
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
