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
