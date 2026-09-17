<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('empresas');

require_once '../../config/database.php';
require_once '../../includes/logs.php';
require_once '../../includes/validaciones.php';


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

if (!validarNombre($nombre)) {

    if (preg_match("/^[-']/", $nombre)) {
        die('El nombre de la empresa debe empezar con una letra.');
    }

    die('El nombre de la empresa no es válido. Solo se permiten letras, espacios, guiones y apóstrofes.');

}

if (!validarCIF($cif)) {

    die('El CIF no es válido.');

}

if ($direccion !== '' && !validarDireccion($direccion)) {

    die('La dirección no es válida.');

}

if ($telefono !== '' && !validarTelefono($telefono)) {

    die('El teléfono no es válido. Debe tener 9 dígitos y comenzar por 6, 7, 8 o 9.');

}

if ($email !== '' && !validarEmail($email)) {

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
        nombre = :nombre,
        cif = :cif,
        direccion = :direccion,
        telefono = :telefono,
        email = :email
    WHERE id = :id
");

$stmt->execute([
    ':nombre'         => $nombre,
    ':cif'            => $cif,
    ':direccion'      => $direccion !== '' ? $direccion : null,
    ':telefono'       => $telefono !== '' ? $telefono : null,
    ':email'          => $email !== '' ? $email : null,
    ':id'             => $id,
]);

registrarLog(
    LOG_EXITO,
    'Empresa modificada',
    'Se ha modificado la empresa "' . $nombre . '".'
);


// =====================================================
// VOLVER AL LISTADO
// =====================================================

header('Location: empresas.php');
exit;
