<?php

session_start();

require_once __DIR__ . '/../../config/permisos.php';
requerirPermiso('planificador');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/logs.php';
require_once __DIR__ . '/../../includes/form_flash.php';


// =====================================================
// COMPROBAR QUE LA PETICIÓN SEA POST
// =====================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: planificador.php');
    exit;

}


// =====================================================
// RECIBIR DATOS DEL FORMULARIO
// =====================================================

$id          = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$titulo      = trim($_POST['titulo'] ?? '');
$area        = trim($_POST['area'] ?? '');
$fecha       = trim($_POST['fecha'] ?? '');
$hora        = trim($_POST['hora'] ?? '');
$responsable = trim($_POST['responsable'] ?? '');
$estado      = trim($_POST['estado'] ?? '');


// =====================================================
// VALIDACIONES
// =====================================================

$areasValidas   = ['Tarifas', 'Clientes', 'Incidencias', 'Comparador', 'Sistema'];
$estadosValidos = ['Pendiente', 'En curso', 'Completada'];

if ($id <= 0) {

    header('Location: planificador.php');
    exit;

}

if ($titulo === '') {

    establecerErrorFormulario('El título es obligatorio.', $_POST, 'editar_tarea.php?id=' . $id, 'titulo');

}

if (!in_array($area, $areasValidas, true)) {

    establecerErrorFormulario('Selecciona un área válida.', $_POST, 'editar_tarea.php?id=' . $id, 'area');

}

if (!DateTime::createFromFormat('Y-m-d', $fecha)) {

    establecerErrorFormulario('Selecciona una fecha válida.', $_POST, 'editar_tarea.php?id=' . $id, 'fecha');

}

if (!in_array($estado, $estadosValidos, true)) {

    establecerErrorFormulario('Selecciona un estado válido.', $_POST, 'editar_tarea.php?id=' . $id, 'estado');

}


// =====================================================
// COMPROBAR QUE LA TAREA EXISTA
// =====================================================

$stmtTarea = $pdo->prepare("
    SELECT id, creado_por
    FROM tareas
    WHERE id = ?
");

$stmtTarea->execute([$id]);

$tareaExiste = $stmtTarea->fetch(PDO::FETCH_ASSOC);

if (!$tareaExiste) {

    die('
        <h2>Error</h2>

        <p>
            La tarea que intentas modificar no existe.
        </p>

        <p>
            <a href="planificador.php">
                Volver al planificador
            </a>
        </p>
    ');

}


// =====================================================
// COMPROBAR QUE PUEDE EDITAR ESTA TAREA
// =====================================================

if (!puedeVerTarea($tareaExiste['creado_por'] !== null ? (int) $tareaExiste['creado_por'] : null)) {

    die('
        <h2>Error</h2>

        <p>
            No tienes permiso para editar esta tarea.
        </p>

        <p>
            <a href="planificador.php">
                Volver al planificador
            </a>
        </p>
    ');

}


// =====================================================
// ACTUALIZAR TAREA
// =====================================================

$stmtActualizar = $pdo->prepare("
    UPDATE tareas
    SET
        titulo = ?,
        area = ?,
        fecha = ?,
        hora = ?,
        responsable = ?,
        estado = ?
    WHERE id = ?
");

$stmtActualizar->execute([
    $titulo,
    $area,
    $fecha,
    $hora !== '' ? $hora : null,
    $responsable !== '' ? $responsable : null,
    $estado,
    $id,
]);

registrarLog(
    LOG_EXITO,
    'Tarea modificada',
    'Se ha modificado la tarea "' . $titulo . '".'
);


// =====================================================
// VOLVER AL PLANIFICADOR, EN EL MES DE LA TAREA
// =====================================================

header('Location: planificador.php?mes=' . substr($fecha, 0, 7));
exit;
