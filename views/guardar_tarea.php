<?php

session_start();

require_once __DIR__ . '/../config/permisos.php';
requerirPermiso('planificador');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/logs.php';
require_once __DIR__ . '/../includes/fechas.php';


// =====================================================
// COMPROBAR QUE EL FORMULARIO SE ENVÍA POR POST
// =====================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: planificador.php');
    exit;

}


// =====================================================
// RECOGER DATOS DEL FORMULARIO
// =====================================================

$titulo      = trim($_POST['titulo'] ?? '');
$area        = trim($_POST['area'] ?? '');
$fecha       = trim($_POST['fecha'] ?? '');
$hora        = trim($_POST['hora'] ?? '');
$responsable = trim($_POST['responsable'] ?? '');
$estado      = trim($_POST['estado'] ?? '');


// =====================================================
// VALIDACIONES
// =====================================================

$areasValidas  = ['Tarifas', 'Clientes', 'Incidencias', 'Comparador', 'Sistema'];
$estadosValidos = ['Pendiente', 'En curso', 'Completada'];

if (
    $titulo === '' ||
    !in_array($area, $areasValidas, true) ||
    !DateTime::createFromFormat('Y-m-d', $fecha) ||
    !in_array($estado, $estadosValidos, true)
) {

    die('
        <h2>Error</h2>

        <p>
            Faltan datos obligatorios o alguno de los valores enviados no es válido.
        </p>

        <p>
            <a href="crear_tarea.php">
                Volver al formulario
            </a>
        </p>
    ');

}


// =====================================================
// INSERTAR TAREA
// =====================================================

$stmt = $pdo->prepare("
    INSERT INTO tareas (
        titulo,
        area,
        fecha,
        hora,
        responsable,
        estado,
        creado_por
    )
    VALUES (
        :titulo,
        :area,
        :fecha,
        :hora,
        :responsable,
        :estado,
        :creado_por
    )
");

$stmt->execute([
    ':titulo'      => $titulo,
    ':area'        => $area,
    ':fecha'       => $fecha,
    ':hora'        => $hora !== '' ? $hora : null,
    ':responsable' => $responsable !== '' ? $responsable : null,
    ':estado'      => $estado,
    ':creado_por'  => $_SESSION['id_usuario'],
]);

$idTarea = $pdo->lastInsertId();

registrarLog(
    LOG_EXITO,
    'Tarea creada',
    'Se ha creado la tarea "' . $titulo . '".'
);


// =====================================================
// RECUPERAR LA TAREA CREADA
// =====================================================

$stmtDatos = $pdo->prepare("
    SELECT id, titulo, area, fecha, hora, responsable, estado
    FROM tareas
    WHERE id = ?
    LIMIT 1
");

$stmtDatos->execute([$idTarea]);

$tarea = $stmtDatos->fetch(PDO::FETCH_ASSOC);

$mesTarea = substr($tarea['fecha'], 0, 7);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tarea creada - Comparador Eléctrico</title>

    <link rel="stylesheet" href="../css/style.css">

</head>

<body>


    <?php include '../templates/header.php'; ?>


    <div class="app-container">


        <?php include '../templates/sidebar.php'; ?>


        <main class="main-content">


            <div class="page-header">

                <div>

                    <h1>Planificador</h1>

                    <p>
                        Tarea creada correctamente
                    </p>

                </div>

            </div>


            <div class="config-card">

                <h2>Tarea creada correctamente</h2>

                <div class="form-info">

                    <p>

                        La tarea

                        <strong>
                            <?= htmlspecialchars($tarea['titulo']) ?>
                        </strong>

                        se ha creado correctamente.

                    </p>

                </div>


                <div class="usuario-detalle">

                    <div class="usuario-detalle-grid">

                        <div class="usuario-detalle-item">
                            <span>Área</span>
                            <strong><?= htmlspecialchars($tarea['area']) ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Fecha</span>
                            <strong><?= date('d/m/Y', strtotime($tarea['fecha'])) ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Hora</span>
                            <strong><?= $tarea['hora'] ? substr($tarea['hora'], 0, 5) : 'Sin hora' ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Responsable</span>
                            <strong><?= htmlspecialchars($tarea['responsable'] ?? 'Sin asignar') ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Estado</span>
                            <strong><?= htmlspecialchars($tarea['estado']) ?></strong>
                        </div>

                    </div>

                </div>


                <div class="form-actions">

                    <a href="crear_tarea.php" class="config-save-button">
                        + Nueva tarea
                    </a>

                    <a href="planificador.php?mes=<?= htmlspecialchars($mesTarea) ?>" class="config-cancel-button">
                        Volver al planificador
                    </a>

                </div>

            </div>


        </main>


    </div>


    <?php include '../templates/footer.php'; ?>


</body>

</html>
