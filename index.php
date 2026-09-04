<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Panel de gestión</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>


<?php include 'templates/header.php'; ?>


<!-- =========================
     CONTENEDOR PRINCIPAL
========================== -->

<div class="app-container">


    <?php include 'templates/sidebar.php'; ?>


    <!-- =========================
         CONTENIDO PRINCIPAL
    ========================== -->

    <main class="main-content">


        <!-- =========================
             CABECERA
        ========================== -->

        <div class="page-header">

            <div>

                <h1>
                    Dashboard
                </h1>

                <p>
                    Resumen general del sistema
                </p>

            </div>


            <div class="page-date">
                3 septiembre 2026
            </div>

        </div>


        <!-- =========================
             TARJETAS
        ========================== -->

        <section class="dashboard-cards">


            <!-- COMERCIALIZADORAS -->

            <div class="dashboard-card">

                <div class="card-icon blue">
                    €
                </div>

                <div class="card-info">

                    <span class="card-label">
                        Comercializadoras
                    </span>

                    <strong>
                        24
                    </strong>

                    <small>
                        Registradas
                    </small>

                </div>

            </div>


            <!-- CLIENTES -->

            <div class="dashboard-card">

                <div class="card-icon green">
                    ▣
                </div>

                <div class="card-info">

                    <span class="card-label">
                        Clientes
                    </span>

                    <strong>
                        186
                    </strong>

                    <small>
                        Clientes activos
                    </small>

                </div>

            </div>


            <!-- INCIDENCIAS -->

            <div class="dashboard-card">

                <div class="card-icon orange">
                    !
                </div>

                <div class="card-info">

                    <span class="card-label">
                        Incidencias
                    </span>

                    <strong>
                        7
                    </strong>

                    <small>
                        Pendientes
                    </small>

                </div>

            </div>


            <!-- COMPARACIONES -->

            <div class="dashboard-card">

                <div class="card-icon purple">
                    ↔
                </div>

                <div class="card-info">

                    <span class="card-label">
                        Comparaciones
                    </span>

                    <strong>
                        42
                    </strong>

                    <small>
                        Este mes
                    </small>

                </div>

            </div>


        </section>


        <!-- =========================
             ZONA INFERIOR
        ========================== -->

        <section class="dashboard-grid">


            <!-- =========================
                 ACTIVIDAD RECIENTE
            ========================== -->

            <div class="panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Actividad reciente
                        </h2>

                        <p>
                            Últimos movimientos del sistema
                        </p>

                    </div>


                    <button class="panel-action">
                        Ver todo
                    </button>

                </div>


                <div class="activity-list">


                    <!-- ACTIVIDAD 1 -->

                    <div class="activity-item">

                        <div class="activity-icon blue">
                            €
                        </div>


                        <div class="activity-content">

                            <strong>
                                Nueva tarifa añadida
                            </strong>

                            <span>
                                Tarifa de Endesa actualizada
                            </span>

                        </div>


                        <span class="activity-time">
                            Hace 10 min
                        </span>

                    </div>


                    <!-- ACTIVIDAD 2 -->

                    <div class="activity-item">

                        <div class="activity-icon green">
                            +
                        </div>


                        <div class="activity-content">

                            <strong>
                                Nuevo cliente
                            </strong>

                            <span>
                                Cliente registrado correctamente
                            </span>

                        </div>


                        <span class="activity-time">
                            Hace 35 min
                        </span>

                    </div>


                    <!-- ACTIVIDAD 3 -->

                    <div class="activity-item">

                        <div class="activity-icon orange">
                            !
                        </div>


                        <div class="activity-content">

                            <strong>
                                Incidencia registrada
                            </strong>

                            <span>
                                Revisión de datos pendiente
                            </span>

                        </div>


                        <span class="activity-time">
                            Hace 1 h
                        </span>

                    </div>


                </div>

            </div>


            <!-- =========================
                 ACCIONES RÁPIDAS
            ========================== -->

            <div class="panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Acciones rápidas
                        </h2>

                        <p>
                            Accesos frecuentes
                        </p>

                    </div>

                </div>


                <div class="quick-actions">


                    <!-- NUEVO CLIENTE -->

                    <a href="clientes.php" class="quick-action">

                        <span class="quick-icon">
                            +
                        </span>


                        <span>

                            <strong>
                                Nuevo cliente
                            </strong>

                            <small>
                                Registrar cliente
                            </small>

                        </span>

                    </a>


                    <!-- NUEVA TARIFA -->

                    <a href="#" class="quick-action">

                        <span class="quick-icon">
                            €
                        </span>


                        <span>

                            <strong>
                                Nueva tarifa
                            </strong>

                            <small>
                                Añadir tarifa eléctrica
                            </small>

                        </span>

                    </a>


                    <!-- PLANIFICADOR -->

                    <a href="planificador.php" class="quick-action">

                        <span class="quick-icon">
                            ▣
                        </span>


                        <span>

                            <strong>
                                Planificador
                            </strong>

                            <small>
                                Crear planificación
                            </small>

                        </span>

                    </a>


                    <!-- NUEVA INCIDENCIA -->

                    <a href="incidencias.php" class="quick-action">

                        <span class="quick-icon">
                            !
                        </span>


                        <span>

                            <strong>
                                Nueva incidencia
                            </strong>

                            <small>
                                Registrar incidencia
                            </small>

                        </span>

                    </a>


                </div>

            </div>


        </section>


    </main>

</div>


<?php include 'templates/footer.php'; ?>


</body>

</html>

