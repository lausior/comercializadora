<?php

session_start();

require_once __DIR__ . '/../../config/permisos.php';
requerirPermiso('planificador');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/form_flash.php';


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
    SELECT id, titulo, area, fecha, hora, responsable, estado, creado_por
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
            La tarea que intentas editar no existe.
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

if (!puedeVerTarea($tarea['creado_por'] !== null ? (int) $tarea['creado_por'] : null)) {

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
// ERROR PENDIENTE (SI VENIMOS DE actualizar_tarea.php)
// =====================================================

$errorFormulario = obtenerErrorFormulario();
$datosPrevios = $errorFormulario['datos'] ?? [];

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Editar tarea - Comparador Eléctrico</title>

    <link rel="stylesheet" href="../../css/style.css">

</head>

<body>


    <?php include '../../templates/header.php'; ?>


    <div class="app-container">


        <?php include '../../templates/sidebar.php'; ?>


        <main class="main-content">


            <div class="page-header">

                <div>

                    <h1>Planificador</h1>

                    <p>
                        Editar tarea
                    </p>

                </div>


                <div class="page-header-actions">

                    <a href="planificador.php" class="config-save-button">
                        ← Volver
                    </a>

                </div>

            </div>


            <div class="config-card">

                <h2>Datos de la tarea</h2>

                <form action="actualizar_tarea.php" method="POST" novalidate>


                    <div class="form-error-general" id="form-error-general" role="alert"
                        style="display: <?= $errorFormulario ? 'block' : 'none' ?>;">
                        <?= $errorFormulario ? htmlspecialchars($errorFormulario['mensaje'], ENT_QUOTES, 'UTF-8') : '' ?>
                    </div>

                    <input type="hidden" name="id" value="<?= (int) $tarea['id'] ?>">


                    <div class="form-grid">


                        <div class="form-group">

                            <label for="titulo">
                                Título
                            </label>

                            <input type="text" id="titulo" name="titulo"
                                class="<?= claseErrorCampo($errorFormulario, 'titulo') ?>"
                                value="<?= valorFormulario($datosPrevios, 'titulo', $tarea['titulo']) ?>" required>

                            <span class="field-error" id="error-titulo"><?= mensajeErrorCampo($errorFormulario, 'titulo') ?></span>

                        </div>


                        <div class="form-group">

                            <label for="area">
                                Área
                            </label>

                            <?php $areaPrevia = $datosPrevios['area'] ?? $tarea['area']; ?>

                            <select id="area" name="area"
                                class="<?= claseErrorCampo($errorFormulario, 'area') ?>" required>

                                <?php foreach (['Tarifas', 'Clientes', 'Incidencias', 'Comparador', 'Sistema'] as $areaOpcion): ?>

                                    <option value="<?= $areaOpcion ?>" <?= $areaPrevia === $areaOpcion ? 'selected' : '' ?>>
                                        <?= $areaOpcion ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                            <span class="field-error" id="error-area"><?= mensajeErrorCampo($errorFormulario, 'area') ?></span>

                        </div>


                        <div class="form-group">

                            <label for="fecha">
                                Fecha
                            </label>

                            <input type="date" id="fecha" name="fecha"
                                class="<?= claseErrorCampo($errorFormulario, 'fecha') ?>"
                                value="<?= valorFormulario($datosPrevios, 'fecha', $tarea['fecha']) ?>" required>

                            <span class="field-error" id="error-fecha"><?= mensajeErrorCampo($errorFormulario, 'fecha') ?></span>

                        </div>


                        <div class="form-group">

                            <label for="hora">
                                Hora (opcional)
                            </label>

                            <input type="time" id="hora" name="hora"
                                class="<?= claseErrorCampo($errorFormulario, 'hora') ?>"
                                value="<?= valorFormulario($datosPrevios, 'hora', $tarea['hora'] ? substr($tarea['hora'], 0, 5) : '') ?>">

                            <span class="field-error" id="error-hora"><?= mensajeErrorCampo($errorFormulario, 'hora') ?></span>

                        </div>


                        <div class="form-group">

                            <label for="responsable">
                                Responsable (opcional)
                            </label>

                            <input type="text" id="responsable" name="responsable"
                                class="<?= claseErrorCampo($errorFormulario, 'responsable') ?>"
                                value="<?= valorFormulario($datosPrevios, 'responsable', $tarea['responsable'] ?? '') ?>" placeholder="Nombre de la persona responsable">

                            <span class="field-error" id="error-responsable"><?= mensajeErrorCampo($errorFormulario, 'responsable') ?></span>

                        </div>


                        <div class="form-group">

                            <label for="estado">
                                Estado
                            </label>

                            <?php $estadoPrevio = $datosPrevios['estado'] ?? $tarea['estado']; ?>

                            <select id="estado" name="estado"
                                class="<?= claseErrorCampo($errorFormulario, 'estado') ?>" required>

                                <?php foreach (['Pendiente', 'En curso', 'Completada'] as $estadoOpcion): ?>

                                    <option value="<?= $estadoOpcion ?>" <?= $estadoPrevio === $estadoOpcion ? 'selected' : '' ?>>
                                        <?= $estadoOpcion ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                            <span class="field-error" id="error-estado"><?= mensajeErrorCampo($errorFormulario, 'estado') ?></span>

                        </div>


                    </div>


                    <div class="form-actions">

                        <a href="planificador.php" class="config-cancel-button">
                            Cancelar
                        </a>

                        <button type="submit" class="config-save-button">
                            Guardar cambios
                        </button>

                    </div>


                </form>

            </div>


        </main>


    </div>


    <?php include '../../templates/footer.php'; ?>


</body>

</html>
