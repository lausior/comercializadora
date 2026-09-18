<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('empresas');

require_once '../../config/database.php';
require_once '../../includes/form_flash.php';


// =====================================================
// ERROR PENDIENTE (SI VENIMOS DE guardar_empresa.php)
// =====================================================

$errorFormulario = obtenerErrorFormulario();
$datosPrevios = $errorFormulario['datos'] ?? [];


// =====================================================
// GENERAR CÓDIGO DE EMPRESA ALEATORIO
// =====================================================
//
// 6 dígitos, comprobando que no coincida con uno ya
// existente antes de darlo por válido. Se muestra ya
// relleno (y no editable) en el formulario; se vuelve a
// comprobar que sigue libre al guardar (guardar_empresa.php).
//
// =====================================================

do {

    $codigoEmpresa = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    $stmtCodigo = $pdo->prepare("
        SELECT id
        FROM empresas
        WHERE codigo_empresa = ?
        LIMIT 1
    ");

    $stmtCodigo->execute([$codigoEmpresa]);

} while ($stmtCodigo->fetch());

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

                    <div class="form-error-general" id="form-error-general" role="alert"
                        style="display: <?= $errorFormulario ? 'block' : 'none' ?>;">
                        <?= $errorFormulario ? htmlspecialchars($errorFormulario['mensaje'], ENT_QUOTES, 'UTF-8') : '' ?>
                    </div>


                    <div class="form-info">

                        <p>
                            El código de empresa se genera automáticamente
                            y no se puede modificar.
                        </p>

                    </div>


                    <div class="form-grid">


                        <!-- =========================
                             CÓDIGO DE EMPRESA
                        ========================== -->

                        <div class="form-group">

                            <label for="codigo_empresa">
                                Código de empresa
                            </label>

                            <input type="text" id="codigo_empresa" name="codigo_empresa"
                                value="<?= htmlspecialchars($codigoEmpresa) ?>" readonly>

                        </div>


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
                             CIF
                        ========================== -->

                        <div class="form-group">

                            <label for="cif">
                                CIF
                            </label>

                            <input type="text" id="cif" name="cif"
                                class="<?= claseErrorCampo($errorFormulario, 'cif') ?>"
                                value="<?= valorFormulario($datosPrevios, 'cif') ?>" required>

                            <span class="field-error" id="error-cif"><?= mensajeErrorCampo($errorFormulario, 'cif') ?></span>

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
                             (obligatorio: se reutiliza como
                             email del primer usuario)
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
                         ACCESO DE LA EMPRESA
                         =================================================
                         No es "un usuario que pertenece a la empresa":
                         es el acceso de la propia empresa (rol EMPRESA),
                         para no tener que crear la empresa y su login por
                         separado en dos formularios. Por eso reutiliza el
                         nombre, el email y el teléfono ya escritos arriba
                         como datos de la empresa, y aquí solo hace falta
                         el username — luego, desde ese acceso, la empresa
                         podrá dar de alta a su equipo (rol Usuario).
                    ================================================== -->

                    <h2>Acceso de la empresa</h2>

                    <div class="form-info">

                        <p>
                            Con esto la empresa ya puede acceder: usa su
                            propio nombre, email y teléfono (los de arriba)
                            como datos de acceso, con rol Empresa. Desde
                            ahí podrá dar de alta a su equipo. La
                            contraseña inicial se genera automáticamente y
                            deberá cambiarla en su primer acceso.
                        </p>

                    </div>

                    <div class="form-grid">


                        <!-- =========================
                             USERNAME
                        ========================== -->

                        <div class="form-group">

                            <label for="usuario_username">
                                Username
                            </label>

                            <input type="text" id="usuario_username" name="usuario_username"
                                class="<?= claseErrorCampo($errorFormulario, 'usuario_username') ?>"
                                value="<?= valorFormulario($datosPrevios, 'usuario_username') ?>" required>

                            <span class="field-error" id="error-usuario_username"><?= mensajeErrorCampo($errorFormulario, 'usuario_username') ?></span>

                        </div>


                    </div>


                    <!-- =================================================
                         BOTÓN
                    ================================================== -->

                    <div class="form-actions">

                        <button type="submit" class="config-save-button">
                            Crear empresa y su acceso
                        </button>

                        <a href="empresas.php" class="config-cancel-button">
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


    <script src="../../js/empresas.js"></script>

</body>

</html>
