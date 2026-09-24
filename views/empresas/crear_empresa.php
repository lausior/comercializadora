<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('empresas');

require_once '../../config/database.php';
require_once '../../includes/form_flash.php';
require_once '../../includes/empresas.php';


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


// Datos iniciales para formulario_empresa.php: una empresa
// nueva solo trae el código generado; todavía no tiene
// usuario de acceso (se crea al guardar).
$empresa = ['codigo_empresa' => $codigoEmpresa];
$usuarioAcceso = null;
$esEdicion = false;

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

                    <?php include 'formulario_empresa.php'; ?>


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