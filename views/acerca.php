<?php

session_start();

require_once __DIR__ . '/../config/permisos.php';
requerirPermiso('ayuda');

?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Acerca de - Comparador Eléctrico</title>

    <link rel="stylesheet" href="../css/style.css">

</head>

<body>


<?php include '../templates/header.php'; ?>


<!-- ========================================
     CONTENEDOR PRINCIPAL
========================================= -->

<div class="app-container">


    <?php include '../templates/sidebar.php'; ?>


    <!-- ========================================
         CONTENIDO PRINCIPAL
    ========================================= -->

    <main class="main-content">


        <!-- ========================================
             CABECERA DE PÁGINA
        ========================================= -->

        <div class="page-header">

            <div>

                <h1>
                    Acerca de
                </h1>

                <p>
                    Información sobre la aplicación
                </p>

            </div>

        </div>


        <!-- ========================================
             GRID ACERCA DE
        ========================================= -->

        <div class="help-grid">


            <!-- ==================================
                 LA APLICACIÓN
            ================================== -->

            <section class="panel help-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Comparador Eléctrico
                        </h2>

                        <p>
                            Plataforma de gestión para comercializadoras
                        </p>

                    </div>

                </div>


                <div class="help-content">

                    <p>
                        <strong>Comparador Eléctrico</strong> es la
                        aplicación de gestión interna utilizada para
                        administrar clientes, empresas, usuarios y la
                        actividad diaria de la comercializadora.
                    </p>

                    <p>
                        Desde aquí se centralizan el planificador de
                        tareas, la gestión de incidencias y partes, el
                        comparador de tarifas y las opciones de
                        seguridad y configuración del sistema.
                    </p>

                </div>

            </section>


            <!-- ==================================
                 INFORMACIÓN DEL SISTEMA
            ================================== -->

            <section class="panel help-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Información del sistema
                        </h2>

                        <p>
                            Datos técnicos de la versión instalada
                        </p>

                    </div>

                </div>


                <div class="system-info">

                    <div class="system-item">

                        <span>
                            Versión
                        </span>

                        <strong>
                            1.0.0
                        </strong>

                    </div>


                    <div class="system-item">

                        <span>
                            Estado
                        </span>

                        <span class="system-status">
                            ● Sistema operativo
                        </span>

                    </div>


                    <div class="system-item">

                        <span>
                            © 2026 Comparador
                        </span>

                        <strong>
                            Todos los derechos reservados
                        </strong>

                    </div>

                </div>

            </section>


        </div>


    </main>

</div>


<?php include '../templates/footer.php'; ?>


</body>

</html>
