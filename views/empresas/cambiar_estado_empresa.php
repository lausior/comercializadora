<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('empresas');

require_once '../../config/database.php';
require_once '../../includes/logs.php';
require_once '../../includes/empresas.php';


// =====================================================
// COMPROBAR ID DE LA EMPRESA
// =====================================================
//
// Activar llega por GET (enlace directo, sin motivo).
// Desactivar llega por POST (el modal de empresas.js envía
// el id junto con el motivo), así que hay que aceptar el id
// en cualquiera de los dos.
//
// =====================================================

$id = isset($_POST['id'])
    ? (int) $_POST['id']
    : (isset($_GET['id']) ? (int) $_GET['id'] : 0);

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


// =====================================================
// SI PASA A INACTIVO, EL MOTIVO ES OBLIGATORIO
// =====================================================
//
// El modal del listado (ver empresas.js) siempre envía por
// POST el id junto con el motivo (select con las mismas
// opciones que crear_empresa.php/editar_empresa.php); si se
// llega aquí sin pasar por POST (por ejemplo, manipulando la
// URL a mano) o sin un motivo válido, se corta igualmente,
// para que desactivar siga exigiendo el modal de confirmación.
//
// =====================================================

$motivo = trim($_POST['motivo'] ?? '');

$motivosValidos = array_keys(MOTIVOS_INACTIVO_EMPRESA);

if ($nuevoEstado === 'Inactivo') {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

        die('
            <h2>Error</h2>

            <p>
                Solicitud no válida.
            </p>

            <p>
                <a href="empresas.php">
                    Volver a empresas
                </a>
            </p>
        ');

    }

    if (!in_array($motivo, $motivosValidos, true)) {

        die('
            <h2>Error</h2>

            <p>
                Debes indicar un motivo para desactivar la empresa.
            </p>

            <p>
                <a href="empresas.php">
                    Volver a empresas
                </a>
            </p>
        ');

    }

}

$stmtActualizar = $pdo->prepare("
    UPDATE empresas
    SET estado = ?, motivo_inactivo = ?
    WHERE id = ?
");

$stmtActualizar->execute([
    $nuevoEstado,
    $nuevoEstado === 'Inactivo' ? $motivo : null,
    $id,
]);

// El usuario de acceso de la empresa (rol EMPRESA) representa
// su login: toma el mismo estado, igual que al revés (ver
// cambiar_estado_usuario.php). Sus empleados (rol USUARIO) se
// inactivan/reactivan en cascada (ver includes/empresas.php).
sincronizarUsuariosConEstadoEmpresa($pdo, $id, $nuevoEstado, $motivo);

// El evento es "Empresa activada"/"Empresa desactivada" (en vez
// de un único "Estado modificado") para que se pueda filtrar en
// Logs por activaciones/desactivaciones sin tener que leer la
// descripción.
registrarLog(
    LOG_INFORMACION,
    $nuevoEstado === 'Activo' ? 'Empresa activada' : 'Empresa desactivada',
    'La empresa "' . $empresa['nombre'] . '" ha pasado a estado ' . $nuevoEstado
        . ($nuevoEstado === 'Inactivo' ? '. Motivo: ' . etiquetaMotivoInactivo($motivo) : '') . '.'
);

header('Location: empresas.php');
exit;
