<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('empresas');

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Crear empresa - Comparador Eléctrico</title>

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

                    <h1>Empresas</h1>

                    <p>
                        Crear nueva empresa
                    </p>

                </div>


                <div class="page-header-actions">

                    <div class="page-date">
                        11 septiembre 2026
                    </div>

                    <a href="empresas.php" class="config-save-button">
                        ← Volver
                    </a>

                </div>

            </div>


            <!-- =================================================
                 FORMULARIO
            ================================================== -->

            <div class="config-card">

                <h2>Datos de la empresa</h2>

                <form action="guardar_empresa.php" method="POST" novalidate>


                    <!-- =========================
                         MENSAJE DE ERROR GENERAL
                    ========================== -->

                    <div class="form-error-general" id="form-error-general" role="alert" style="display: none;"></div>


                    <div class="form-info">

                        <p>
                            El código de empresa se generará automáticamente
                            y se mostrará al terminar de crear la empresa.
                        </p>

                    </div>


                    <!-- =========================
                         NOMBRE
                    ========================== -->

                    <div class="form-group">

                        <label for="nombre">
                            Nombre
                        </label>

                        <input type="text" id="nombre" name="nombre" required>

                        <span class="field-error" id="error-nombre"></span>

                    </div>


                    <!-- =========================
                         CIF
                    ========================== -->

                    <div class="form-group">

                        <label for="cif">
                            CIF
                        </label>

                        <input type="text" id="cif" name="cif" required>

                        <span class="field-error" id="error-cif"></span>

                    </div>


                    <!-- =========================
                         DIRECCIÓN
                    ========================== -->

                    <div class="form-group">

                        <label for="direccion">
                            Dirección
                        </label>

                        <input type="text" id="direccion" name="direccion">

                        <span class="field-error" id="error-direccion"></span>

                    </div>


                    <!-- =========================
                         TELÉFONO
                    ========================== -->

                    <div class="form-group">

                        <label for="telefono">
                            Teléfono
                        </label>

                        <input type="tel" id="telefono" name="telefono">

                        <span class="field-error" id="error-telefono"></span>

                    </div>


                    <!-- =========================
                         EMAIL
                    ========================== -->

                    <div class="form-group">

                        <label for="email">
                            Email
                        </label>

                        <input type="email" id="email" name="email">

                        <span class="field-error" id="error-email"></span>

                    </div>


                    <!-- =================================================
                         BOTÓN
                    ================================================== -->

                    <div class="form-actions">

                        <a href="empresas.php" class="config-cancel-button">
                            Cancelar
                        </a>

                        <button type="submit" class="config-save-button">
                            Crear empresa
                        </button>

                    </div>


                </form>

            </div>


        </main>


    </div>


    <!-- =================================================
         FOOTER
    ================================================== -->

    <?php include '../../templates/footer.php'; ?>


    <script src="../../js/empresas.js"></script>

</body>

</html>
