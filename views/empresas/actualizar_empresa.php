<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('empresas');

require_once '../../config/database.php';
require_once '../../includes/logs.php';
require_once '../../includes/validaciones.php';
require_once '../../includes/form_flash.php';


// =====================================================
// COMPROBAR QUE EL FORMULARIO SE ENVÍA POR POST
// =====================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: empresas.php');
    exit;

}


// =====================================================
// RECOGER DATOS DEL FORMULARIO
// =====================================================

$id            = (int) ($_POST['id'] ?? 0);
$nombre        = trim($_POST['nombre'] ?? '');
$cif           = trim($_POST['cif'] ?? '');
$direccion     = trim($_POST['direccion'] ?? '');
$telefono      = trim($_POST['telefono'] ?? '');
$email         = trim($_POST['email'] ?? '');
$estado        = trim($_POST['estado'] ?? '');
$motivoInactivo = trim($_POST['motivo_inactivo'] ?? '');


// =====================================================
// VALIDACIONES
// =====================================================

if ($id <= 0) {

    die('
        <h2>Error</h2>

        <p>
            La empresa indicada no es válida.
        </p>

        <p>
            <a href="empresas.php">
                Volver a empresas
            </a>
        </p>
    ');

}

if (
    $nombre === '' ||
    $cif === ''
) {

    establecerErrorFormulario('Faltan datos obligatorios.', $_POST, 'editar_empresa.php?id=' . $id);

}

if (!validarNombreEmpresa($nombre)) {

    if (preg_match("/^[-'.]/", $nombre)) {
        establecerErrorFormulario('El nombre de la empresa debe empezar con una letra.', $_POST, 'editar_empresa.php?id=' . $id, 'nombre');
    }

    establecerErrorFormulario('El nombre de la empresa no es válido. Solo se permiten letras, números, espacios, guiones, apóstrofes y puntos.', $_POST, 'editar_empresa.php?id=' . $id, 'nombre');

}

if (!validarCIF($cif)) {

    establecerErrorFormulario('El CIF no es válido.', $_POST, 'editar_empresa.php?id=' . $id, 'cif');

}

if ($direccion !== '' && !validarDireccion($direccion)) {

    establecerErrorFormulario('La dirección no es válida.', $_POST, 'editar_empresa.php?id=' . $id, 'direccion');

}

if ($telefono !== '' && !validarTelefono($telefono)) {

    establecerErrorFormulario('El teléfono no es válido. Introduce un número nacional o internacional (7 a 15 dígitos).', $_POST, 'editar_empresa.php?id=' . $id, 'telefono');

}

if ($email !== '' && !validarEmail($email)) {

    establecerErrorFormulario('El email no es válido.', $_POST, 'editar_empresa.php?id=' . $id, 'email');

}

if (!in_array($estado, ['Activo', 'Inactivo'], true)) {

    establecerErrorFormulario('El estado seleccionado no es válido.', $_POST, 'editar_empresa.php?id=' . $id, 'estado');

}

if (mb_strlen($motivoInactivo, 'UTF-8') > 500) {

    establecerErrorFormulario('El motivo de inactividad no puede superar los 500 caracteres.', $_POST, 'editar_empresa.php?id=' . $id, 'motivo_inactivo');

}


// =====================================================
// COMPROBAR QUE LA EMPRESA EXISTE
// =====================================================

$stmt = $pdo->prepare("
    SELECT id, creado_por
    FROM empresas
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$empresaExistente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$empresaExistente) {

    die('
        <h2>Error</h2>

        <p>
            La empresa que intentas editar no existe.
        </p>

        <p>
            <a href="empresas.php">
                Volver a empresas
            </a>
        </p>
    ');

}


// =====================================================
// COMPROBAR QUE PUEDE EDITAR ESTA EMPRESA
// =====================================================

if (!puedeVerEmpresa(
    $empresaExistente['creado_por'] !== null ? (int) $empresaExistente['creado_por'] : null
)) {

    die('
        <h2>Error</h2>

        <p>
            No tienes permiso para editar esta empresa.
        </p>

        <p>
            <a href="empresas.php">
                Volver a empresas
            </a>
        </p>
    ');

}


// =====================================================
// COMPROBAR CIF DUPLICADO (EN OTRA EMPRESA)
// =====================================================

$stmt = $pdo->prepare("
    SELECT id
    FROM empresas
    WHERE cif = ?
        AND id != ?
    LIMIT 1
");

$stmt->execute([$cif, $id]);

if ($stmt->fetch()) {

    establecerErrorFormulario('Ya existe otra empresa con ese CIF.', $_POST, 'editar_empresa.php?id=' . $id, 'cif');

}


// =====================================================
// ACTUALIZAR EMPRESA
// =====================================================

$stmt = $pdo->prepare("
    UPDATE empresas
    SET
        nombre = :nombre,
        cif = :cif,
        direccion = :direccion,
        telefono = :telefono,
        email = :email,
        estado = :estado,
        motivo_inactivo = :motivo_inactivo
    WHERE id = :id
");

$stmt->execute([
    ':nombre'         => $nombre,
    ':cif'            => $cif,
    ':direccion'      => $direccion !== '' ? $direccion : null,
    ':telefono'       => $telefono !== '' ? $telefono : null,
    ':email'          => $email !== '' ? $email : null,
    ':estado'         => $estado,
    ':motivo_inactivo' => $estado === 'Inactivo' && $motivoInactivo !== '' ? $motivoInactivo : null,
    ':id'             => $id,
]);

$pdo->prepare("
    UPDATE usuarios u
    INNER JOIN roles r ON r.id = u.id_rol
    SET u.estado = ?, u.motivo_inactivo = ?
    WHERE u.id_empresa = ?
        AND r.nombre = ?
")->execute([
    $estado,
    $estado === 'Inactivo' && $motivoInactivo !== '' ? $motivoInactivo : null,
    $id,
    ROL_EMPRESA,
]);

$stmtDatos = $pdo->prepare("
    SELECT
        id,
        codigo_empresa,
        nombre,
        cif,
        direccion,
        telefono,
        email,
        estado,
        motivo_inactivo
    FROM empresas
    WHERE id = ?
    LIMIT 1
");

$stmtDatos->execute([$id]);

$empresa = $stmtDatos->fetch(PDO::FETCH_ASSOC);

if (!$empresa) {

    die('
        <h2>Error</h2>

        <p>
            La empresa se actualizó, pero no se pudieron recuperar sus datos.
        </p>

        <p>
            <a href="empresas.php">
                Volver a empresas
            </a>
        </p>
    ');

}

registrarLog(
    LOG_EXITO,
    'Empresa modificada',
    'Se ha modificado la empresa "' . $nombre . '".'
);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Empresa actualizada - Comparador Eléctrico</title>

    <link rel="stylesheet" href="../../css/style.css">

</head>

<body>

    <?php include '../../templates/header.php'; ?>

    <div class="app-container">

        <?php include '../../templates/sidebar.php'; ?>

        <main class="main-content">

            <div class="page-header">

                <div>
                    <h1>Empresas</h1>
                    <p>Empresa actualizada correctamente</p>
                </div>

                <div class="page-header-actions">

                    <a href="crear_empresa.php" class="config-save-button">
                        + Añadir empresa
                    </a>

                </div>

            </div>

            <div class="config-card confirmation-card">

                <div class="form-info registration-success">
                    <p>
                        La empresa
                        <strong><?= htmlspecialchars($empresa['nombre'], ENT_QUOTES, 'UTF-8') ?></strong>
                        se ha actualizado correctamente.
                    </p>
                </div>

                <div class="usuario-detalle">

                    <div class="usuario-detalle-grid">

                        <div class="usuario-detalle-item">
                            <span>ID de empresa</span>
                            <strong><?= htmlspecialchars($empresa['id'], ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Código de empresa</span>
                            <strong><?= htmlspecialchars($empresa['codigo_empresa'], ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Nombre</span>
                            <strong><?= htmlspecialchars($empresa['nombre'], ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>CIF</span>
                            <strong><?= htmlspecialchars($empresa['cif'], ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Dirección</span>
                            <strong><?= htmlspecialchars($empresa['direccion'] ?? 'No indicada', ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Teléfono</span>
                            <strong><?= htmlspecialchars($empresa['telefono'] ?? 'No indicado', ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Email</span>
                            <strong><?= htmlspecialchars($empresa['email'] ?? 'No indicado', ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>

                        <div class="usuario-detalle-item empresa-estado-item">
                            <span>Estado</span>
                            <strong class="<?= $empresa['estado'] === 'Activo' ? 'text-success' : 'text-danger' ?>">
                                <?= htmlspecialchars($empresa['estado'], ENT_QUOTES, 'UTF-8') ?>
                            </strong>
                        </div>

                        <?php if ($empresa['estado'] === 'Inactivo'): ?>

                            <div class="usuario-detalle-item empresa-motivo-item">
                                <span>Motivo</span>
                                <strong><?= htmlspecialchars($empresa['motivo_inactivo'] ?: '-', ENT_QUOTES, 'UTF-8') ?></strong>
                            </div>

                        <?php endif; ?>

                    </div>

                </div>

                <div class="form-actions">

                    <a href="editar_empresa.php?id=<?= (int) $empresa['id'] ?>" class="config-save-button">
                        Editar
                    </a>

                    <a href="empresas.php" class="config-cancel-button">
                        Volver a empresas
                    </a>

                </div>

            </div>

        </main>

    </div>

    <?php include '../../templates/footer.php'; ?>

</body>

</html>
