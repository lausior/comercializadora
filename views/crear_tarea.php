<?php

session_start();

require_once __DIR__ . '/../config/permisos.php';
requerirPermiso('planificador');

require_once __DIR__ . '/../includes/fechas.php';


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

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Nueva tarea - Comparador Eléctrico</title>

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


                    <div class="form-error-general" id="form-error-general" role="alert" style="display: none;"></div>


                    <div class="form-group">

                        <label for="titulo">
                            Título
                        </label>

                        <input type="text" id="titulo" name="titulo" required>

                        <span class="field-error" id="error-titulo"></span>

                    </div>


                    <div class="form-group">

                        <label for="area">
                            Área
                        </label>

                        <select id="area" name="area" required>

                            <option value="">Seleccionar área</option>
                            <option value="Tarifas">Tarifas</option>
                            <option value="Clientes">Clientes</option>
                            <option value="Incidencias">Incidencias</option>
                            <option value="Comparador">Comparador</option>
                            <option value="Sistema">Sistema</option>

                        </select>

                        <span class="field-error" id="error-area"></span>

                    </div>


                    <div class="form-group">

                        <label for="fecha">
                            Fecha
                        </label>

                        <input type="date" id="fecha" name="fecha" value="<?= htmlspecialchars($fechaPrellenada) ?>" required>

                        <span class="field-error" id="error-fecha"></span>

                    </div>


                    <div class="form-group">

                        <label for="hora">
                            Hora (opcional)
                        </label>

                        <input type="time" id="hora" name="hora">

                        <span class="field-error" id="error-hora"></span>

                    </div>


                    <div class="form-group">

                        <label for="responsable">
                            Responsable (opcional)
                        </label>

                        <input type="text" id="responsable" name="responsable" placeholder="Nombre de la persona responsable">

                        <span class="field-error" id="error-responsable"></span>

                    </div>


                    <div class="form-group">

                        <label for="estado">
                            Estado
                        </label>

                        <select id="estado" name="estado" required>

                            <option value="Pendiente" selected>Pendiente</option>
                            <option value="En curso">En curso</option>
                            <option value="Completada">Completada</option>

                        </select>

                        <span class="field-error" id="error-estado"></span>

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


    <?php include '../templates/footer.php'; ?>


</body>

</html>
