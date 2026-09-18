<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('usuarios');

require_once '../../config/database.php';
require_once '../../includes/logs.php';


// =====================================================
// COMPROBAR ID DEL USUARIO
// =====================================================
//
// Activar llega por GET (enlace directo, sin motivo).
// Desactivar llega por POST (el modal de usuarios.js envía
// el id junto con el motivo), así que hay que aceptar el id
// en cualquiera de los dos.
//
// =====================================================

$id = isset($_POST['id'])
    ? (int) $_POST['id']
    : (isset($_GET['id']) ? (int) $_GET['id'] : 0);

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
    SELECT
        u.id,
        u.username,
        u.estado,
        u.id_empresa,
        u.creado_por,
        r.nombre AS rol
    FROM usuarios u
    INNER JOIN roles r
        ON r.id = u.id_rol
    WHERE u.id = ?
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


// =====================================================
// SI PASA A INACTIVO, EXIGIR MOTIVO
// =====================================================
//
// Mismo requisito que en crear_usuario.php/editar_usuario.php:
// desactivar a alguien exige indicar el motivo. El modal del
// listado (ver usuarios.js) lo envía por POST; si se llega
// aquí sin él (por ejemplo, manipulando la URL a mano), se
// corta en vez de desactivar sin motivo.
//
// =====================================================

$motivo = trim($_POST['motivo'] ?? '');

if ($nuevoEstado === 'Inactivo') {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $motivo === '') {

        die('
            <h2>Error</h2>

            <p>
                Indica el motivo por el que el usuario pasa a inactivo.
            </p>

            <p>
                <a href="usuarios.php">
                    Volver a usuarios
                </a>
            </p>
        ');

    }

    if (mb_strlen($motivo, 'UTF-8') > 500) {

        die('
            <h2>Error</h2>

            <p>
                El motivo de inactividad no puede superar los 500 caracteres.
            </p>

            <p>
                <a href="usuarios.php">
                    Volver a usuarios
                </a>
            </p>
        ');

    }

}

$stmtActualizar = $pdo->prepare("
    UPDATE usuarios
    SET estado = ?, motivo_inactivo = ?
    WHERE id = ?
");

$stmtActualizar->execute([
    $nuevoEstado,
    $nuevoEstado === 'Inactivo' ? $motivo : null,
    $id,
]);

// El usuario con rol EMPRESA representa el acceso de su empresa.
// Su estado debe mantenerse sincronizado con el estado mostrado
// en el listado de empresas; los usuarios normales no cambian
// el estado global de la empresa.
if ($usuario['rol'] === ROL_EMPRESA) {

    $stmtEmpresa = $pdo->prepare("
        UPDATE empresas
        SET estado = ?
        WHERE id = ?
    ");

    $stmtEmpresa->execute([$nuevoEstado, $usuario['id_empresa']]);

}

// El evento es "Usuario activado"/"Usuario desactivado" (en vez
// de un único "Estado modificado") para que se pueda filtrar en
// Logs por activaciones/desactivaciones sin tener que leer la
// descripción.
registrarLog(
    LOG_INFORMACION,
    $nuevoEstado === 'Activo' ? 'Usuario activado' : 'Usuario desactivado',
    'El usuario "' . $usuario['username'] . '" ha pasado a estado ' . $nuevoEstado
        . ($nuevoEstado === 'Inactivo' ? '. Motivo: ' . $motivo : '') . '.'
);

header('Location: usuarios.php');
exit;
