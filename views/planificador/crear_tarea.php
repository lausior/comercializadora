<?php

session_start();

require_once __DIR__ . '/../../config/permisos.php';
requerirPermiso('planificador');

require_once __DIR__ . '/../../includes/fechas.php';
require_once __DIR__ . '/../../includes/form_flash.php';


// =====================================================
// FECHA PRERRELLENADA
// =====================================================
//
// Si se llega aquí desde el "+" de un día del calendario,
// esa fecha viene en ?fecha=YYYY-MM-DD. Si no es válida,
// se usa la fecha de hoy.
// =====================================================

$fechaParam = $_GET['fecha'] ?? '';

$fechaPrellenada = DateTime::createFromFormat('Y-m-d', $fechaParam)
    ? $fechaParam
    : date('Y-m-d');


// =====================================================
// ERROR PENDIENTE (SI VENIMOS DE guardar_tarea.php)
// =====================================================

$errorFormulario = obtenerErrorFormulario();
$datosPrevios = $errorFormulario['datos'] ?? [];


// =====================================================
// ÁREAS Y ESTADOS VÁLIDOS
// =====================================================

$areasValidas   = ['Tarifas', 'Clientes', 'Incidencias', 'Comparador', 'Sistema'];
$estadosValidos = ['Pendiente', 'En curso', 'Completada'];

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Nueva tarea - Comparador Eléctrico</title>

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
                        Nueva tarea
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

                <form action="guardar_tarea.php" method="POST" novalidate>


                    <div class="form-error-general" id="form-error-general" role="alert"
                        style="display: <?= $errorFormulario ? 'block' : 'none' ?>;">
                        <?= $errorFormulario ? htmlspecialchars($errorFormulario['mensaje'], ENT_QUOTES, 'UTF-8') : '' ?>
                    </div>


                    <div class="form-grid">


                        <div class="form-group">

                            <label for="titulo">
                                Título
                            </label>

                            <input type="text" id="titulo" name="titulo"
                                class="<?= claseErrorCampo($errorFormulario, 'titulo') ?>"
                                value="<?= valorFormulario($datosPrevios, 'titulo') ?>" required>

                            <span class="field-error" id="error-titulo"><?= mensajeErrorCampo($errorFormulario, 'titulo') ?></span>

                        </div>


                        <div class="form-group">

                            <label for="area">
                                Área
                            </label>

                            <?php $areaPrevia = $datosPrevios['area'] ?? ''; ?>

                            <select id="area" name="area"
                                class="<?= claseErrorCampo($errorFormulario, 'area') ?>" required>

                                <option value="">Seleccionar área</option>

                                <?php foreach ($areasValidas as $areaOpcion): ?>

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
                                value="<?= valorFormulario($datosPrevios, 'fecha', $fechaPrellenada) ?>" required>

                            <span class="field-error" id="error-fecha"><?= mensajeErrorCampo($errorFormulario, 'fecha') ?></span>

                        </div>


                        <div class="form-group">

                            <label for="hora">
                                Hora (opcional)
                            </label>

                            <input type="time" id="hora" name="hora"
                                class="<?= claseErrorCampo($errorFormulario, 'hora') ?>"
                                value="<?= valorFormulario($datosPrevios, 'hora') ?>">

                            <span class="field-error" id="error-hora"><?= mensajeErrorCampo($errorFormulario, 'hora') ?></span>

                        </div>


                        <div class="form-group">

                            <label for="responsable">
                                Responsable (opcional)
                            </label>

                            <input type="text" id="responsable" name="responsable"
                                class="<?= claseErrorCampo($errorFormulario, 'responsable') ?>"
                                value="<?= valorFormulario($datosPrevios, 'responsable') ?>" placeholder="Nombre de la persona responsable">

                            <span class="field-error" id="error-responsable"><?= mensajeErrorCampo($errorFormulario, 'responsable') ?></span>

                        </div>


                        <div class="form-group">

                            <label for="estado">
                                Estado
                            </label>

                            <?php $estadoPrevio = $datosPrevios['estado'] ?? 'Pendiente'; ?>

                            <select id="estado" name="estado"
                                class="<?= claseErrorCampo($errorFormulario, 'estado') ?>" required>

                                <?php foreach ($estadosValidos as $estadoOpcion): ?>

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
                            Crear tarea
                        </button>

                    </div>


                </form>

            </div>


        </main>


    </div>


    <?php include '../../templates/footer.php'; ?>


</body>

</html>
