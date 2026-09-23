<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('comercializadoras');

require_once '../../config/database.php';
require_once '../../includes/form_flash.php';


// =====================================================
// COMPROBAR ID DE LA COMERCIALIZADORA
// =====================================================

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    header('Location: comercializadoras.php');
    exit;

}

$idComercializadora = (int) $_GET['id'];


// =====================================================
// OBTENER COMERCIALIZADORA
// =====================================================

$stmtComercializadora = $pdo->prepare("
    SELECT
        id,
        nombre,
        cif,
        direccion,
        telefono,
        email,
        suministra_luz,
        suministra_gas,
        creado_por
    FROM comercializadoras
    WHERE id = ?
");

$stmtComercializadora->execute([$idComercializadora]);

$comercializadora = $stmtComercializadora->fetch(PDO::FETCH_ASSOC);


// =====================================================
// COMPROBAR QUE EXISTE
// =====================================================

if (!$comercializadora) {

    header('Location: comercializadoras.php');
    exit;

}


// =====================================================
// COMPROBAR QUE PUEDE VER/EDITAR ESTA COMERCIALIZADORA
// =====================================================
//
// Oculto en el listado no es suficiente: sin esto, NG
// podría editar la comercializadora de otro NG tecleando
// su id en la URL directamente.
//
// =====================================================

if (
    !puedeVerComercializadora(
        $comercializadora['creado_por'] !== null ? (int) $comercializadora['creado_por'] : null
    )
) {

    header('Location: comercializadoras.php?error=sin_permiso');
    exit;

}


// =====================================================
// ERROR PENDIENTE (SI VENIMOS DE actualizar_comercializadora.php)
// =====================================================

$errorFormulario = obtenerErrorFormulario();
$datosPrevios = $errorFormulario['datos'] ?? [];

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Editar comercializadora - Comparador Eléctrico</title>

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
                        Editar comercializadora
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

                <form action="actualizar_comercializadora.php" method="POST" novalidate>


                    <!-- =================================================
                         ID DE LA COMERCIALIZADORA
                    ================================================== -->

                    <input type="hidden" name="id" value="<?= (int) $comercializadora['id'] ?>">


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
                                value="<?= valorFormulario($datosPrevios, 'nombre', $comercializadora['nombre']) ?>" required>

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
                                value="<?= valorFormulario($datosPrevios, 'cif', $comercializadora['cif']) ?>" required>

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
                                value="<?= valorFormulario($datosPrevios, 'direccion', $comercializadora['direccion'] ?? '') ?>">

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
                                value="<?= valorFormulario($datosPrevios, 'telefono', $comercializadora['telefono'] ?? '') ?>">

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
                                value="<?= valorFormulario($datosPrevios, 'email', $comercializadora['email'] ?? '') ?>">

                            <span class="field-error"
                                id="error-email"><?= mensajeErrorCampo($errorFormulario, 'email') ?></span>

                        </div>


                        <!-- =========================
                             SERVICIOS (LUZ / GAS)
                        ========================== -->

                        <?php
                        $prevLuz = array_key_exists('suministra_luz', $datosPrevios)
                            ? $datosPrevios['suministra_luz'] === '1'
                            : (bool) $comercializadora['suministra_luz'];

                        $prevGas = array_key_exists('suministra_gas', $datosPrevios)
                            ? $datosPrevios['suministra_gas'] === '1'
                            : (bool) $comercializadora['suministra_gas'];
                        ?>

                        <div class="form-group">

                            <label>
                                Servicios que suministra
                            </label>

                            <div style="display:flex; gap:20px; align-items:center; flex-wrap: wrap; padding-top: 6px;">

                                <label style="display:flex; align-items:center; gap:8px; font-weight: normal;">
                                    <input type="checkbox" id="suministra_luz" name="suministra_luz" value="1"
                                        <?= $prevLuz ? 'checked' : '' ?>>
                                    <span>⚡ Luz</span>
                                </label>

                                <label style="display:flex; align-items:center; gap:8px; font-weight: normal;">
                                    <input type="checkbox" id="suministra_gas" name="suministra_gas" value="1"
                                        <?= $prevGas ? 'checked' : '' ?>>
                                    <span>🔥 Gas</span>
                                </label>

                            </div>

                            <span class="field-error"
                                id="error-suministra_luz"><?= mensajeErrorCampo($errorFormulario, 'suministra_luz') ?></span>

                        </div>


                    </div>


                    <!-- =================================================
                         BOTONES
                    ================================================== -->

                    <div class="form-actions">

                        <button type="submit" class="config-save-button">
                            Guardar cambios
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
