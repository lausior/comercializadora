<?php

session_start();

require_once __DIR__ . '/../config/permisos.php';
requerirPermiso('planificador');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/logs.php';


// =====================================================
// COMPROBAR QUE SE HA RECIBIDO UN ID
// =====================================================

$id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($id <= 0) {

    die('
        <h2>Error</h2>

        <p>
            La tarea seleccionada no es válida.
        </p>

        <p>
            <a href="planificador.php">
                Volver al planificador
            </a>
        </p>
    ');

}


// =====================================================
// BUSCAR LA TAREA
// =====================================================

$stmtTarea = $pdo->prepare("
    SELECT id, titulo, fecha, creado_por
    FROM tareas
    WHERE id = ?
");

$stmtTarea->execute([$id]);

$tarea = $stmtTarea->fetch(PDO::FETCH_ASSOC);


// =====================================================
// COMPROBAR QUE LA TAREA EXISTA
// =====================================================

if (!$tarea) {

    die('
        <h2>Error</h2>

        <p>
            La tarea que intentas eliminar no existe.
        </p>

        <p>
            <a href="planificador.php">
                Volver al planificador
            </a>
        </p>
    ');

}


// =====================================================
// COMPROBAR QUE PUEDE ELIMINAR ESTA TAREA
// =====================================================

if (!puedeVerTarea($tarea['creado_por'] !== null ? (int) $tarea['creado_por'] : null)) {

    die('
        <h2>Error</h2>

        <p>
            No tienes permiso para eliminar esta tarea.
        </p>

        <p>
            <a href="planificador.php">
                Volver al planificador
            </a>
        </p>
    ');

}


// =====================================================
// ELIMINAR TAREA
// =====================================================

$stmtEliminar = $pdo->prepare("
    DELETE FROM tareas
    WHERE id = ?
");

$stmtEliminar->execute([$id]);

registrarLog(
    LOG_ADVERTENCIA,
    'Tarea eliminada',
    'Se ha eliminado la tarea "' . $tarea['titulo'] . '".'
);


// =====================================================
// VOLVER AL PLANIFICADOR, EN EL MES DE LA TAREA
// =====================================================

header('Location: planificador.php?mes=' . substr($tarea['fecha'], 0, 7));
exit;
