<?php

session_start();

require_once __DIR__ . '/../config/permisos.php';
requerirPermiso('configuracion');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/logs.php';
require_once __DIR__ . '/../includes/empresas.php';


// =====================================================
// COMPROBAR QUE EL FORMULARIO SE ENVÍA POR POST
// =====================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /comercializadora/views/configuracion.php');
    exit;
}


// =====================================================
// FUNCIÓN PARA VOLVER CON UN ERROR
// =====================================================

function volverConErrorLogo(string $mensaje): void
{
    $_SESSION['logo_error'] = $mensaje;

    header('Location: /comercializadora/views/configuracion.php');
    exit;
}


// =====================================================
// COMPROBAR QUE SE HA SUBIDO UN ARCHIVO VÁLIDO
// =====================================================

if (!isset($_FILES['logo']) || $_FILES['logo']['error'] === UPLOAD_ERR_NO_FILE) {
    volverConErrorLogo('Selecciona una imagen para subir.');
}

if ($_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
    volverConErrorLogo('No se ha podido subir el archivo.');
}

$TAMANO_MAXIMO = 2 * 1024 * 1024; // 2 MB

if ($_FILES['logo']['size'] > $TAMANO_MAXIMO) {
    volverConErrorLogo('La imagen no puede superar los 2 MB.');
}

$EXTENSIONES_PERMITIDAS = [
    'image/png'  => 'png',
    'image/jpeg' => 'jpg',
    'image/webp' => 'webp',
];

$infoImagen = getimagesize($_FILES['logo']['tmp_name']);

if ($infoImagen === false || !isset($EXTENSIONES_PERMITIDAS[$infoImagen['mime']])) {
    volverConErrorLogo('El archivo debe ser una imagen PNG, JPG o WEBP.');
}

$extension = $EXTENSIONES_PERMITIDAS[$infoImagen['mime']];


// =====================================================
// GUARDAR EL ARCHIVO EN DISCO
// =====================================================

$idEmpresa = (int) $_SESSION['id_empresa'];

$directorioLogos = __DIR__ . '/../uploads/logos';

if (!is_dir($directorioLogos)) {
    mkdir($directorioLogos, 0755, true);
}

// Elimina cualquier logo anterior de esta empresa, aunque
// tuviera otra extensión.
foreach (glob($directorioLogos . '/empresa_' . $idEmpresa . '.*') as $archivoAnterior) {
    unlink($archivoAnterior);
}

$nombreArchivo = 'empresa_' . $idEmpresa . '.' . $extension;
$rutaDestino = $directorioLogos . '/' . $nombreArchivo;

if (!move_uploaded_file($_FILES['logo']['tmp_name'], $rutaDestino)) {
    volverConErrorLogo('No se ha podido guardar la imagen en el servidor.');
}


// =====================================================
// GUARDAR LA RUTA EN BASE DE DATOS
// =====================================================

$rutaRelativa = 'uploads/logos/' . $nombreArchivo;

guardarLogoEmpresa($pdo, $idEmpresa, $rutaRelativa);

registrarLog(
    LOG_EXITO,
    'Configuración modificada',
    'Se ha actualizado el logo de la empresa.'
);

header('Location: /comercializadora/views/configuracion.php');
exit;
