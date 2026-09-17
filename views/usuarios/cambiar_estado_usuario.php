<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('usuarios');

require_once '../../config/database.php';
require_once '../../includes/logs.php';


// =====================================================
// COMPROBAR ID DEL USUARIO
// =====================================================

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {

    header('Location: usuarios.php');
    exit;

}


// =====================================================
// NO PERMITIR DESACTIVARSE A UNO MISMO
// =====================================================
//
// El listado ya oculta la propia fila, así que esto solo
// entra en juego si alguien manipula la URL a mano.
//
// =====================================================

if ($id === (int) ($_SESSION['id_usuario'] ?? 0)) {

    header('Location: usuarios.php?error=no_autodesactivar');
    exit;

}


// =====================================================
// OBTENER USUARIO
// =====================================================

$stmt = $pdo->prepare("
    SELECT id, username, estado, creado_por
    FROM usuarios
    WHERE id = ?
");

$stmt->execute([$id]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {

    header('Location: usuarios.php');
    exit;

}


// =====================================================
// COMPROBAR QUE PUEDE GESTIONAR ESTE USUARIO
// =====================================================

if (!puedeVerUsuario($usuario['creado_por'] !== null ? (int) $usuario['creado_por'] : null)) {

    header('Location: usuarios.php?error=sin_permiso');
    exit;

}


// =====================================================
// CAMBIAR ESTADO
// =====================================================

$nuevoEstado = $usuario['estado'] === 'Activo' ? 'Inactivo' : 'Activo';

$stmtActualizar = $pdo->prepare("
    UPDATE usuarios
    SET estado = ?
    WHERE id = ?
");

$stmtActualizar->execute([$nuevoEstado, $id]);

registrarLog(
    LOG_INFO,
    'Estado modificado',
    'El usuario "' . $usuario['username'] . '" ha pasado a estado ' . $nuevoEstado . '.'
);

header('Location: usuarios.php');
exit;
