<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguridad - Comparador Eléctrico</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'templates/header.php'; ?>

<div class="app-container">

    <?php include 'templates/sidebar.php'; ?>

    <!-- ========================================
         CONTENIDO PRINCIPAL
    ========================================= -->

    <main class="main-content">

        <!-- CABECERA -->

        <div class="page-header">

            <div>

                <h1>Seguridad</h1>

                <p>
                    Estado y configuración de seguridad del sistema
                </p>

            </div>

        </div>


        <!-- ========================================
             ESTADO GENERAL
        ========================================= -->

        <section class="security-status panel">

            <div class="security-status-content">

                <div class="security-status-icon">
                    ✓
                </div>

                <div class="security-status-info">

                    <h2>
                        Sistema protegido
                    </h2>

                    <p>
                        No se han detectado problemas de seguridad.
                    </p>

                </div>

                <div class="security-status-badge">
                    Seguridad activa
                </div>

            </div>

        </section>


        <!-- ========================================
             TARJETAS DE SEGURIDAD
        ========================================= -->

        <section class="security-cards">

            <!-- SESIONES -->

            <div class="security-card panel">

                <div class="security-card-header">

                    <div class="security-card-icon blue">
                        ◉
                    </div>

                    <div>

                        <h2>
                            Sesiones
                        </h2>

                        <p>
                            Usuarios conectados
                        </p>

                    </div>

                </div>

                <div class="security-card-value">
                    3
                </div>

                <div class="security-card-footer">

                    <span>
                        Sesiones activas
                    </span>

                    <a href="#">
                        Ver sesiones
                    </a>

                </div>

            </div>


            <!-- USUARIOS -->

            <div class="security-card panel">

                <div class="security-card-header">

                    <div class="security-card-icon green">
                        ♙
                    </div>

                    <div>

                        <h2>
                            Usuarios
                        </h2>

                        <p>
                            Usuarios registrados
                        </p>

                    </div>

                </div>

                <div class="security-card-value">
                    12
                </div>

                <div class="security-card-footer">

                    <span>
                        Usuarios activos
                    </span>

                    <a href="#">
                        Gestionar
                    </a>

                </div>

            </div>


            <!-- ACCESOS -->

            <div class="security-card panel">

                <div class="security-card-header">

                    <div class="security-card-icon purple">
                        ↔
                    </div>

                    <div>

                        <h2>
                            Accesos
                        </h2>

                        <p>
                            Actividad de acceso
                        </p>

                    </div>

                </div>

                <div class="security-card-value">
                    128
                </div>

                <div class="security-card-footer">

                    <span>
                        Este mes
                    </span>

                    <a href="logs.php">
                        Ver logs
                    </a>

                </div>

            </div>


            <!-- ALERTAS -->

            <div class="security-card panel">

                <div class="security-card-header">

                    <div class="security-card-icon orange">
                        !
                    </div>

                    <div>

                        <h2>
                            Alertas
                        </h2>

                        <p>
                            Incidencias de seguridad
                        </p>

                    </div>

                </div>

                <div class="security-card-value warning">
                    2
                </div>

                <div class="security-card-footer">

                    <span>
                        Pendientes
                    </span>

                    <a href="#">
                        Revisar
                    </a>

                </div>

            </div>

        </section>


        <!-- ========================================
             CONTENIDO INFERIOR
        ========================================= -->

        <section class="security-grid">


            <!-- ACTIVIDAD -->

            <div class="panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Actividad de seguridad
                        </h2>

                        <p>
                            Últimos eventos registrados
                        </p>

                    </div>

                    <button class="panel-action">
                        Ver todo
                    </button>

                </div>


                <div class="security-activity">


                    <div class="security-activity-item">

                        <div class="activity-status success">
                            ✓
                        </div>

                        <div class="activity-content">

                            <strong>
                                Inicio de sesión
                            </strong>

                            <span>
                                Usuario administrador ha iniciado sesión
                            </span>

                        </div>

                        <span class="activity-time">
                            10:32
                        </span>

                    </div>


                    <div class="security-activity-item">

                        <div class="activity-status success">
                            ✓
                        </div>

                        <div class="activity-content">

                            <strong>
                                Cierre de sesión
                            </strong>

                            <span>
                                Usuario juan ha cerrado sesión
                            </span>

                        </div>

                        <span class="activity-time">
                            10:15
                        </span>

                    </div>


                    <div class="security-activity-item">

                        <div class="activity-status warning">
                            !
                        </div>

                        <div class="activity-content">

                            <strong>
                                Intento de acceso fallido
                            </strong>

                            <span>
                                Se ha producido un intento de acceso no válido
                            </span>

                        </div>

                        <span class="activity-time">
                            09:47
                        </span>

                    </div>


                    <div class="security-activity-item">

                        <div class="activity-status success">
                            ✓
                        </div>

                        <div class="activity-content">

                            <strong>
                                Configuración modificada
                            </strong>

                            <span>
                                Se ha actualizado la configuración de seguridad
                            </span>

                        </div>

                        <span class="activity-time">
                            09:20
                        </span>

                    </div>


                </div>

            </div>


            <!-- CONFIGURACIÓN -->

            <div class="panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Configuración
                        </h2>

                        <p>
                            Opciones de seguridad
                        </p>

                    </div>

                </div>


                <div class="security-settings">


                    <div class="security-setting">

                        <div>

                            <strong>
                                Bloqueo automático
                            </strong>

                            <span>
                                Bloquear después de 15 minutos
                            </span>

                        </div>

                        <div class="toggle active">
                            <div class="toggle-circle"></div>
                        </div>

                    </div>


                    <div class="security-setting">

                        <div>

                            <strong>
                                Protección de sesión
                            </strong>

                            <span>
                                Control de sesiones activas
                            </span>

                        </div>

                        <div class="toggle active">
                            <div class="toggle-circle"></div>
                        </div>

                    </div>


                    <div class="security-setting">

                        <div>

                            <strong>
                                Registro de actividad
                            </strong>

                            <span>
                                Registrar acciones de usuarios
                            </span>

                        </div>

                        <div class="toggle active">
                            <div class="toggle-circle"></div>
                        </div>

                    </div>


                    <div class="security-setting">

                        <div>

                            <strong>
                                Alertas de seguridad
                            </strong>

                            <span>
                                Notificar accesos sospechosos
                            </span>

                        </div>

                        <div class="toggle active">
                            <div class="toggle-circle"></div>
                        </div>

                    </div>


                </div>

            </div>


        </section>


    </main>

</div>


<?php include 'templates/footer.php'; ?>

</body>
</html>