<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('empresas');

require_once '../../config/database.php';
require_once '../../includes/logs.php';


// =====================================================
// COMPROBAR ID DE LA EMPRESA
// =====================================================

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {

    header('Location: empresas.php');
    exit;

}


// =====================================================
// NO PERMITIR DESACTIVAR LA PROPIA EMPRESA
// =====================================================
//
// El listado ya oculta la propia empresa, así que esto
// solo entra en juego si alguien manipula la URL a mano.
//
// =====================================================

if ($id === (int) ($_SESSION['id_empresa'] ?? 0)) {

    header('Location: empresas.php?error=no_autodesactivar');
    exit;

}


// =====================================================
// OBTENER EMPRESA
// =====================================================

$stmt = $pdo->prepare("
    SELECT id, nombre, estado, creado_por
    FROM empresas
    WHERE id = ?
");

$stmt->execute([$id]);

$empresa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$empresa) {

    header('Location: empresas.php');
    exit;

}


// =====================================================
// COMPROBAR QUE PUEDE GESTIONAR ESTA EMPRESA
// =====================================================

if (!puedeVerEmpresa($empresa['creado_por'] !== null ? (int) $empresa['creado_por'] : null)) {

    header('Location: empresas.php?error=sin_permiso');
    exit;

}


// =====================================================
// CAMBIAR ESTADO
// =====================================================

$nuevoEstado = $empresa['estado'] === 'Activo' ? 'Inactivo' : 'Activo';

$stmtActualizar = $pdo->prepare("
    UPDATE empresas
    SET estado = ?
    WHERE id = ?
");

$stmtActualizar->execute([$nuevoEstado, $id]);

registrarLog(
    LOG_INFO,
    'Estado modificado',
    'La empresa "' . $empresa['nombre'] . '" ha pasado a estado ' . $nuevoEstado . '.'
);

header('Location: empresas.php');
exit;
