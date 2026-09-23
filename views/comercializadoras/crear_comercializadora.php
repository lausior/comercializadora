<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('comercializadoras');

require_once '../../config/database.php';
require_once '../../includes/form_flash.php';


// =====================================================
// ERROR PENDIENTE (SI VENIMOS DE guardar_comercializadora.php)
// =====================================================

$errorFormulario = obtenerErrorFormulario();
$datosPrevios = $errorFormulario['datos'] ?? [];

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Crear comercializadora - Comparador Eléctrico</title>

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

                    <h1>Comercializadoras</h1>

                    <p>
                        Crear nueva comercializadora
                    </p>

                </div>


                <div class="page-header-actions">

                    <a href="comercializadoras.php" class="config-save-button">
                        ← Volver
                    </a>

                </div>

            </div>


            <!-- =================================================
                 FORMULARIO
            ================================================== -->

            <div class="config-card">

                <h2>Datos de la comercializadora</h2>

                <form action="guardar_comercializadora.php" method="POST" novalidate>


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

                            <span class="field-error"
                                id="error-nombre"><?= mensajeErrorCampo($errorFormulario, 'nombre') ?></span>

                        </div>


                        <!-- =========================
                             CIF
                        ========================== -->

                        <div class="form-group">

                            <label for="cif">
                                CIF
                            </label>

                            <input type="text" id="cif" name="cif"
                                class="<?= claseErrorCampo($errorFormulario, 'cif') ?>"
                                value="<?= valorFormulario($datosPrevios, 'cif') ?>" required>

                            <span class="field-error"
                                id="error-cif"><?= mensajeErrorCampo($errorFormulario, 'cif') ?></span>

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

                            <span class="field-error"
                                id="error-direccion"><?= mensajeErrorCampo($errorFormulario, 'direccion') ?></span>

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

                            <span class="field-error"
                                id="error-telefono"><?= mensajeErrorCampo($errorFormulario, 'telefono') ?></span>

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
                                value="<?= valorFormulario($datosPrevios, 'email') ?>">

                            <span class="field-error"
                                id="error-email"><?= mensajeErrorCampo($errorFormulario, 'email') ?></span>

                        </div>


                        <!-- =========================
                             SERVICIOS (LUZ / GAS)
                        ========================== -->

                        <div class="form-group">

                            <label>
                                Servicios que suministra
                            </label>

                            <div style="display:flex; gap:20px; align-items:center; flex-wrap: wrap; padding-top: 6px;">

                                <label style="display:flex; align-items:center; gap:8px; font-weight: normal;">
                                    <input type="checkbox" id="suministra_luz" name="suministra_luz" value="1"
                                        <?= valorFormulario($datosPrevios, 'suministra_luz') === '1' ? 'checked' : '' ?>>
                                    <span>⚡ Luz</span>
                                </label>

                                <label style="display:flex; align-items:center; gap:8px; font-weight: normal;">
                                    <input type="checkbox" id="suministra_gas" name="suministra_gas" value="1"
                                        <?= valorFormulario($datosPrevios, 'suministra_gas') === '1' ? 'checked' : '' ?>>
                                    <span>🔥 Gas</span>
                                </label>

                            </div>

                            <span class="field-error"
                                id="error-suministra_luz"><?= mensajeErrorCampo($errorFormulario, 'suministra_luz') ?></span>

                        </div>


                    </div>


                    <!-- =================================================
                         BOTÓN
                    ================================================== -->

                    <div class="form-actions">

                        <button type="submit" class="config-save-button">
                            Crear comercializadora
                        </button>

                        <a href="comercializadoras.php" class="config-cancel-button">
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


    <script src="../../js/comercializadoras.js"></script>

</body>

</html>
