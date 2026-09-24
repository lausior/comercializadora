<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('empresas');

require_once '../../config/database.php';
require_once '../../includes/logs.php';
require_once '../../includes/validaciones.php';
require_once '../../includes/form_flash.php';
require_once '../../includes/empresas.php';


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

    if (!preg_match('/^[\p{L}\p{N}]/u', $nombre)) {
        establecerErrorFormulario('El nombre de la empresa debe empezar con una letra o un número.', $_POST, 'editar_empresa.php?id=' . $id, 'nombre');
    }

    establecerErrorFormulario('El nombre de la empresa no es válido. Solo se permiten letras, números, espacios y los símbolos . , \' - & ( ) /.', $_POST, 'editar_empresa.php?id=' . $id, 'nombre');

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

if ($estado === 'Inactivo' && !in_array($motivoInactivo, array_keys(MOTIVOS_INACTIVO_EMPRESA), true)) {

    establecerErrorFormulario('Debes seleccionar un motivo.', $_POST, 'editar_empresa.php?id=' . $id, 'motivo_inactivo');

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
// USERNAME DEL USUARIO DE ACCESO DE LA EMPRESA
// =====================================================

$usuarioAcceso = obtenerUsuarioAccesoEmpresa($pdo, $id);

$usuarioUsername = trim($_POST['usuario_username'] ?? '');

if ($usuarioAcceso !== null) {

    if (!validarUsername($usuarioUsername)) {

        establecerErrorFormulario('El username del usuario solo puede contener letras minúsculas (incluida la ñ), sin números, espacios ni otros caracteres especiales.', $_POST, 'editar_empresa.php?id=' . $id, 'usuario_username');

    }

    $stmtUsername = $pdo->prepare("
        SELECT id
        FROM usuarios
        WHERE username = ?
            AND id != ?
        LIMIT 1
    ");

    $stmtUsername->execute([$usuarioUsername, $usuarioAcceso['id']]);

    if ($stmtUsername->fetch()) {

        establecerErrorFormulario('El username ya existe.', $_POST, 'editar_empresa.php?id=' . $id, 'usuario_username');

    }

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
    ':motivo_inactivo' => $estado === 'Inactivo' ? $motivoInactivo : null,
    ':id'             => $id,
]);

// Su usuario de acceso toma el mismo estado y sus empleados
// se inactivan/reactivan en cascada (ver includes/empresas.php).
sincronizarUsuariosConEstadoEmpresa($pdo, $id, $estado, $motivoInactivo);

$usernameCambiado = $usuarioAcceso !== null && $usuarioUsername !== $usuarioAcceso['username'];

if ($usernameCambiado) {

    $pdo->prepare("
        UPDATE usuarios
        SET username = ?
        WHERE id = ?
    ")->execute([$usuarioUsername, $usuarioAcceso['id']]);

}

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

// Datos del login para la tarjeta (ver tarjeta_empresa.php):
// el login completo, ya con el username nuevo si se ha
// cambiado, y si la empresa aún tiene pendiente cambiar la
// contraseña inicial (recién creada o restablecida). La
// contraseña nunca se toca al editar la empresa.
$datosLogin = null;
$avisoLogin = null;

if ($usuarioAcceso !== null) {

    $passwordPendiente = (int) $usuarioAcceso['cambiar_password'] === 1;

    $datosLogin = [
        ['Username', loginAcceso($empresa['codigo_empresa'], (int) $usuarioAcceso['id'], $usuarioUsername)],
        ['Contraseña', $passwordPendiente ? 'Pendiente de cambio' : 'Sin cambios'],
    ];

    $avisoLogin = 'La contraseña no se ha modificado al guardar estos cambios.'
        . ($passwordPendiente ? ' La empresa todavía debe cambiar la contraseña inicial en su próximo acceso.' : '')
        . ($usernameCambiado ? ' El username ha cambiado: la empresa deberá entrar con el nuevo.' : '');

}

registrarLog(
    LOG_EXITO,
    'Empresa modificada',
    'Se ha modificado la empresa "' . $nombre . '".'
        . ($usernameCambiado
            ? ' Username de acceso: "' . $usuarioAcceso['username'] . '" -> "' . $usuarioUsername . '".'
            : '')
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

                <!-- Mismos datos y orden que la tarjeta de
                     guardar_empresa.php (empresa creada). -->

                <?php include 'tarjeta_empresa.php'; ?>

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
