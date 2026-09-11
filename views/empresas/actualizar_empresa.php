<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('empresas');

require_once '../../config/database.php';


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
$codigoEmpresa = trim($_POST['codigo_empresa'] ?? '');
$nombre        = trim($_POST['nombre'] ?? '');
$cif           = trim($_POST['cif'] ?? '');
$direccion     = trim($_POST['direccion'] ?? '');
$telefono      = trim($_POST['telefono'] ?? '');
$email         = trim($_POST['email'] ?? '');


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
    $codigoEmpresa === '' ||
    $nombre === '' ||
    $cif === ''
) {

    die('
        <h2>Error</h2>

        <p>
            Faltan datos obligatorios.
        </p>

        <p>
            <a href="editar_empresa.php?id=' . (int) $id . '">
                Volver al formulario
            </a>
        </p>
    ');

}

if (!preg_match('/^[0-9]{6}$/', $codigoEmpresa)) {

    die('
        <h2>Error</h2>

        <p>
            El código de empresa debe tener exactamente 6 dígitos.
        </p>

        <p>
            <a href="editar_empresa.php?id=' . (int) $id . '">
                Volver al formulario
            </a>
        </p>
    ');

}

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {

    die('
        <h2>Error</h2>

        <p>
            El email no es válido.
        </p>

        <p>
            <a href="editar_empresa.php?id=' . (int) $id . '">
                Volver al formulario
            </a>
        </p>
    ');

}


// =====================================================
// COMPROBAR QUE LA EMPRESA EXISTE
// =====================================================

$stmt = $pdo->prepare("
    SELECT id
    FROM empresas
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

if (!$stmt->fetch()) {

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
// COMPROBAR CÓDIGO DUPLICADO (EN OTRA EMPRESA)
// =====================================================

$stmt = $pdo->prepare("
    SELECT id
    FROM empresas
    WHERE codigo_empresa = ?
        AND id != ?
    LIMIT 1
");

$stmt->execute([$codigoEmpresa, $id]);

if ($stmt->fetch()) {

    die('
        <h2>Error</h2>

        <p>
            Ya existe otra empresa con ese código.
        </p>

        <p>
            <a href="editar_empresa.php?id=' . (int) $id . '">
                Volver al formulario
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

    die('
        <h2>Error</h2>

        <p>
            Ya existe otra empresa con ese CIF.
        </p>

        <p>
            <a href="editar_empresa.php?id=' . (int) $id . '">
                Volver al formulario
            </a>
        </p>
    ');

}


// =====================================================
// ACTUALIZAR EMPRESA
// =====================================================

$stmt = $pdo->prepare("
    UPDATE empresas
    SET
        codigo_empresa = :codigo_empresa,
        nombre = :nombre,
        cif = :cif,
        direccion = :direccion,
        telefono = :telefono,
        email = :email
    WHERE id = :id
");

$stmt->execute([
    ':codigo_empresa' => $codigoEmpresa,
    ':nombre'         => $nombre,
    ':cif'            => $cif,
    ':direccion'      => $direccion !== '' ? $direccion : null,
    ':telefono'       => $telefono !== '' ? $telefono : null,
    ':email'          => $email !== '' ? $email : null,
    ':id'             => $id,
]);


// =====================================================
// VOLVER AL LISTADO
// =====================================================

header('Location: empresas.php');
exit;
