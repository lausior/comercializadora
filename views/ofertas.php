<?php

session_start();

require_once __DIR__ . '/../config/permisos.php';
requerirPermiso('ofertas');

?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Clientes - Comparador Eléctrico</title>

    <link rel="stylesheet" href="../css/style.css">

</head>

<body>

    <?php include '../templates/header.php'; ?>

    <div class="app-container">

        <?php include '../templates/sidebar.php'; ?>

        <main class="main-content">

            <!-- =====================================================
             CABECERA
        ====================================================== -->

            <div class="page-header">

                <div>

                    <h1>Ofertas</h1>

                    <p>
                        Gestión y consulta de las ofertas
                    </p>

                </div>

                <div class="page-header-actions">

                    <button type="button" class="config-save-button">
                        + Nuevo cliente
                    </button>

                </div>

            </div>


            <!-- =====================================================
             TABLA DE CLIENTES
        ====================================================== -->

            

        </main>

    </div>


    <?php include '../templates/footer.php'; ?>


    <script src="../js/clientes.js"></script>

</body>

</html>