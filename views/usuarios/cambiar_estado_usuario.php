<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('usuarios');

require_once '../../config/database.php';
require_once '../../includes/logs.php';
require_once '../../includes/empresas.php';


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
        r.nombre AS rol,
        e.estado AS empresa_estado
    FROM usuarios u
    INNER JOIN roles r
        ON r.id = u.id_rol
    INNER JOIN empresas e
        ON e.id = u.id_empresa
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
// NO ACTIVAR USUARIOS SUELTOS DE UNA EMPRESA INACTIVA
// =====================================================
//
// Un usuario normal (rol USUARIO) no se puede activar a mano
// mientras su empresa esté Inactiva: si se le dejara, quedaría
// Activo con la empresa Inactiva, un estado contradictorio. El
// listado ya oculta este botón en ese caso (ver usuarios.php),
// así que esto solo entra en juego si se manipula la URL a
// mano. El usuario con rol EMPRESA no entra aquí: activarlo A
// ÉL es precisamente cómo se reactiva la empresa (ver más abajo).
//
// =====================================================

if (
    $usuario['estado'] === 'Inactivo' &&
    $usuario['rol'] === ROL_USUARIO &&
    $usuario['empresa_estado'] === 'Inactivo'
) {

    header('Location: usuarios.php?error=empresa_inactiva');
    exit;

}


// =====================================================
// CAMBIAR ESTADO
// =====================================================

$nuevoEstado = $usuario['estado'] === 'Activo' ? 'Inactivo' : 'Activo';


// =====================================================
// SI PASA A INACTIVO, EL MOTIVO ES OBLIGATORIO
// =====================================================
//
// El modal del listado (ver usuarios.js) siempre envía por
// POST el id junto con el motivo (select cuyas opciones
// dependen del rol del usuario, igual que en
// crear_usuario.php/editar_usuario.php); si se llega aquí sin
// pasar por POST (por ejemplo, manipulando la URL a mano) o
// sin un motivo válido para ese rol, se corta igualmente, para
// que desactivar siga exigiendo el modal de confirmación.
//
// =====================================================

$motivo = trim($_POST['motivo'] ?? '');

// "empresa_inactiva" no está aquí a propósito: ese motivo lo
// pone solo la cascada de la empresa (ver includes/empresas.php),
// nunca se elige a mano al desactivar un usuario desde aquí.
$motivosValidos = $usuario['rol'] === ROL_EMPRESA
    ? ['impago', 'fin_contrato']
    : ['vacaciones', 'baja_laboral', 'baja_empresa'];

if ($nuevoEstado === 'Inactivo') {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

        die('
            <h2>Error</h2>

            <p>
                Solicitud no válida.
            </p>

            <p>
                <a href="usuarios.php">
                    Volver a usuarios
                </a>
            </p>
        ');

    }

    if (!in_array($motivo, $motivosValidos, true)) {

        die('
            <h2>Error</h2>

            <p>
                Debes indicar un motivo para desactivar al usuario.
            </p>

            <p>
                <a href="usuarios.php">
                    Volver a usuarios
                </a>
            </p>
        ');

    }

}

// inactivo_por_empresa se pone a 0: este cambio es una acción
// manual sobre ESTE usuario, así que deja de estar "marcado"
// por la cascada de la empresa (ver includes/empresas.php); si
// se le vuelve a inactivar aquí, es por su propio motivo, no
// porque la empresa se haya inactivado.
$stmtActualizar = $pdo->prepare("
    UPDATE usuarios
    SET estado = ?, motivo_inactivo = ?, inactivo_por_empresa = 0
    WHERE id = ?
");

$motivoGuardado = $nuevoEstado === 'Inactivo' ? $motivo : null;

$stmtActualizar->execute([
    $nuevoEstado,
    $motivoGuardado,
    $id,
]);

// El usuario con rol EMPRESA representa el acceso de su empresa.
// Su estado (y motivo) debe mantenerse sincronizado con lo que
// se muestra en el listado de empresas; los usuarios normales
// no cambian el estado global de la empresa. Cambiar este
// usuario equivale a cambiar la empresa, así que también se
// inactivan/reactivan en cascada sus empleados (rol USUARIO),
// igual que desde cambiar_estado_empresa.php.
if ($usuario['rol'] === ROL_EMPRESA) {

    $stmtEmpresa = $pdo->prepare("
        UPDATE empresas
        SET estado = ?, motivo_inactivo = ?
        WHERE id = ?
    ");

    $stmtEmpresa->execute([$nuevoEstado, $motivoGuardado, $usuario['id_empresa']]);

    if ($nuevoEstado === 'Inactivo') {
        inactivarUsuariosPorEmpresa($pdo, $usuario['id_empresa']);
    } else {
        reactivarUsuariosPorEmpresa($pdo, $usuario['id_empresa']);
    }

}

// El evento es "Usuario activado"/"Usuario desactivado" (en vez
// de un único "Estado modificado") para que se pueda filtrar en
// Logs por activaciones/desactivaciones sin tener que leer la
// descripción.
registrarLog(
    LOG_INFORMACION,
    $nuevoEstado === 'Activo' ? 'Usuario activado' : 'Usuario desactivado',
    'El usuario "' . $usuario['username'] . '" ha pasado a estado ' . $nuevoEstado
        . ($motivoGuardado !== null ? '. Motivo: ' . etiquetaMotivoInactivo($motivoGuardado) : '') . '.'
);

header('Location: usuarios.php');
exit;
