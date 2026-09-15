<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('clientes');

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

                    <div class="page-date">
                        15 septiembre 2026
                    </div>

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

                    <div class="form-error-general" id="form-error-general" role="alert" style="display: none;"></div>


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
                         TIPO
                    ========================== -->

                    <div class="form-group">

                        <label for="tipo">
                            Tipo
                        </label>

                        <select id="tipo" name="tipo" required>

                            <option value="">Seleccionar tipo</option>
                            <option value="Particular">Particular</option>
                            <option value="Empresa">Empresa</option>

                        </select>

                        <span class="field-error" id="error-tipo"></span>

                    </div>


                    <!-- =========================
                         IDENTIFICACIÓN
                    ========================== -->

                    <div class="form-group">

                        <label for="identificacion">
                            Identificación (DNI / CIF)
                        </label>

                        <input type="text" id="identificacion" name="identificacion" required>

                        <span class="field-error" id="error-identificacion"></span>

                    </div>


                    <!-- =========================
                         CORREO
                    ========================== -->

                    <div class="form-group">

                        <label for="correo">
                            Correo
                        </label>

                        <input type="email" id="correo" name="correo" required>

                        <span class="field-error" id="error-correo"></span>

                    </div>


                    <!-- =========================
                         COMERCIALIZADORA
                    ========================== -->

                    <div class="form-group">

                        <label for="comercializadora">
                            Comercializadora
                        </label>

                        <select id="comercializadora" name="comercializadora" required>

                            <option value="">Seleccionar comercializadora</option>
                            <option value="Endesa">Endesa</option>
                            <option value="Iberdrola">Iberdrola</option>
                            <option value="Naturgy">Naturgy</option>
                            <option value="Repsol">Repsol</option>
                            <option value="TotalEnergies">TotalEnergies</option>

                        </select>

                        <span class="field-error" id="error-comercializadora"></span>

                    </div>


                    <!-- =========================
                         TARIFA
                    ========================== -->

                    <div class="form-group">

                        <label for="tarifa">
                            Tarifa
                        </label>

                        <select id="tarifa" name="tarifa" required>

                            <option value="">Seleccionar tarifa</option>
                            <option value="PVPC">PVPC</option>
                            <option value="Mercado libre">Mercado libre</option>
                            <option value="Tarifa fija">Tarifa fija</option>

                        </select>

                        <span class="field-error" id="error-tarifa"></span>

                    </div>


                    <!-- =========================
                         ESTADO
                    ========================== -->

                    <div class="form-group">

                        <label for="estado">
                            Estado
                        </label>

                        <select id="estado" name="estado" required>

                            <option value="Activo" selected>Activo</option>
                            <option value="Pendiente">Pendiente</option>
                            <option value="Inactivo">Inactivo</option>

                        </select>

                        <span class="field-error" id="error-estado"></span>

                    </div>


                    <!-- =================================================
                         BOTÓN
                    ================================================== -->

                    <div class="form-actions">

                        <a href="clientes.php" class="config-cancel-button">
                            Cancelar
                        </a>

                        <button type="submit" class="config-save-button">
                            Crear cliente
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


</body>

</html>
