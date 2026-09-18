<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('clientes');

require_once '../../includes/form_flash.php';


// =====================================================
// ERROR PENDIENTE (SI VENIMOS DE guardar_cliente.php)
// =====================================================

$errorFormulario = obtenerErrorFormulario();
$datosPrevios = $errorFormulario['datos'] ?? [];

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Crear cliente - Comparador Eléctrico</title>

    <link rel="stylesheet" href="../../css/style.css">

</head>

<body>


    <!-- =================================================
         HEADER
    ================================================== -->

    <?php include '../../templates/header.php'; ?>


    <div class="app-container">


        <!-- =================================================
             SIDEBAR
        ================================================== -->

        <?php include '../../templates/sidebar.php'; ?>


        <!-- =================================================
             CONTENIDO PRINCIPAL
        ================================================== -->

        <main class="main-content">


            <!-- =================================================
                 CABECERA
            ================================================== -->

            <div class="page-header">

                <div>

                    <h1>Clientes</h1>

                    <p>
                        Crear nuevo cliente
                    </p>

                </div>


                <div class="page-header-actions">

                    <a href="clientes.php" class="config-save-button">
                        ← Volver
                    </a>

                </div>

            </div>


            <!-- =================================================
                 FORMULARIO
            ================================================== -->

            <div class="config-card">

                <h2>Datos del cliente</h2>

                <form action="guardar_cliente.php" method="POST" novalidate>


                    <!-- =========================
                         MENSAJE DE ERROR GENERAL
                    ========================== -->

                    <div class="form-error-general" id="form-error-general" role="alert"
                        style="display: <?= $errorFormulario ? 'block' : 'none' ?>;">
                        <?= $errorFormulario ? htmlspecialchars($errorFormulario['mensaje'], ENT_QUOTES, 'UTF-8') : '' ?>
                    </div>


                    <div class="form-grid">


                        <!-- =========================
                             NOMBRE
                        ========================== -->

                        <div class="form-group">

                            <label for="nombre">
                                Nombre
                            </label>

                            <input type="text" id="nombre" name="nombre"
                                class="<?= claseErrorCampo($errorFormulario, 'nombre') ?>"
                                value="<?= valorFormulario($datosPrevios, 'nombre') ?>" required>

                            <span class="field-error" id="error-nombre"><?= mensajeErrorCampo($errorFormulario, 'nombre') ?></span>

                        </div>


                        <!-- =========================
                             APELLIDOS
                        ========================== -->

                        <div class="form-group">

                            <label for="apellidos">
                                Apellidos
                            </label>

                            <input type="text" id="apellidos" name="apellidos"
                                class="<?= claseErrorCampo($errorFormulario, 'apellidos') ?>"
                                value="<?= valorFormulario($datosPrevios, 'apellidos') ?>" required>

                            <span class="field-error" id="error-apellidos"><?= mensajeErrorCampo($errorFormulario, 'apellidos') ?></span>

                        </div>


                        <!-- =========================
                             DNI/NIE
                        ========================== -->

                        <div class="form-group">

                            <label for="nif">
                                DNI/NIE
                            </label>

                            <input type="text" id="nif" name="nif"
                                class="<?= claseErrorCampo($errorFormulario, 'nif') ?>"
                                value="<?= valorFormulario($datosPrevios, 'nif') ?>" required>

                            <span class="field-error" id="error-nif"><?= mensajeErrorCampo($errorFormulario, 'nif') ?></span>

                        </div>


                        <!-- =========================
                             DIRECCIÓN
                        ========================== -->

                        <div class="form-group">

                            <label for="direccion">
                                Dirección
                            </label>

                            <input type="text" id="direccion" name="direccion"
                                class="<?= claseErrorCampo($errorFormulario, 'direccion') ?>"
                                value="<?= valorFormulario($datosPrevios, 'direccion') ?>">

                            <span class="field-error" id="error-direccion"><?= mensajeErrorCampo($errorFormulario, 'direccion') ?></span>

                        </div>


                        <!-- =========================
                             TELÉFONO
                        ========================== -->

                        <div class="form-group">

                            <label for="telefono">
                                Teléfono
                            </label>

                            <input type="tel" id="telefono" name="telefono"
                                class="<?= claseErrorCampo($errorFormulario, 'telefono') ?>"
                                value="<?= valorFormulario($datosPrevios, 'telefono') ?>">

                            <span class="field-error" id="error-telefono"><?= mensajeErrorCampo($errorFormulario, 'telefono') ?></span>

                        </div>


                        <!-- =========================
                             EMAIL
                        ========================== -->

                        <div class="form-group">

                            <label for="email">
                                Email
                            </label>

                            <input type="email" id="email" name="email"
                                class="<?= claseErrorCampo($errorFormulario, 'email') ?>"
                                value="<?= valorFormulario($datosPrevios, 'email') ?>" required>

                            <span class="field-error" id="error-email"><?= mensajeErrorCampo($errorFormulario, 'email') ?></span>

                        </div>


                    </div>


                    <!-- =================================================
                         BOTÓN
                    ================================================== -->

                    <div class="form-actions">

                        <button type="submit" class="config-save-button">
                            Crear cliente
                        </button>

                        <a href="clientes.php" class="config-cancel-button">
                            Cancelar
                        </a>

                    </div>


                </form>

            </div>


        </main>


    </div>


    <!-- =================================================
         FOOTER
    ================================================== -->

    <?php include '../../templates/footer.php'; ?>


</body>

</html>
