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
         CONTENIDO
    ========================== -->

    <main class="main-content">


        <!-- =========================
             CABECERA
        ========================== -->

        <div class="page-header">

            <div>

                <h1>Incidencias</h1>

                <p>
                    Gestión y seguimiento de las incidencias del sistema
                </p>

            </div>


            <div class="page-header-actions">

                <div class="page-date">
                    4 septiembre 2026
                </div>

                <button class="config-save-button">
                    + Nueva incidencia
                </button>

            </div>

        </div>



        <!-- =========================
             RESUMEN
        ========================== -->

        <section class="dashboard-cards">


            <div class="dashboard-card">

                <div class="card-icon blue">
                    #
                </div>

                <div class="card-info">

                    <span class="card-label">
                        Total incidencias
                    </span>

                    <strong>
                        24
                    </strong>

                    <small>
                        Registradas este mes
                    </small>

                </div>

            </div>


            <div class="dashboard-card">

                <div class="card-icon orange">
                    !
                </div>

                <div class="card-info">

                    <span class="card-label">
                        Pendientes
                    </span>

                    <strong>
                        8
                    </strong>

                    <small>
                        Requieren atención
                    </small>

                </div>

            </div>


            <div class="dashboard-card">

                <div class="card-icon purple">
                    ◷
                </div>

                <div class="card-info">

                    <span class="card-label">
                        En curso
                    </span>

                    <strong>
                        5
                    </strong>

                    <small>
                        Actualmente gestionadas
                    </small>

                </div>

            </div>


            <div class="dashboard-card">

                <div class="card-icon red">
                    !
                </div>

                <div class="card-info">

                    <span class="card-label">
                        Urgentes
                    </span>

                    <strong>
                        2
                    </strong>

                    <small>
                        Prioridad alta
                    </small>

                </div>

            </div>


        </section>



        <!-- =========================
             FILTROS
        ========================== -->

        <section class="panel incidencias-filters">


            <div class="panel-header">

                <div>

                    <h2>
                        Buscar incidencias
                    </h2>

                    <p>
                        Filtra las incidencias por estado, prioridad o responsable
                    </p>

                </div>

            </div>


            <div class="incidencias-filter-row">


                <div class="filter-group">

                    <label for="buscarIncidencia">
                        Buscar
                    </label>

                    <input
                        type="text"
                        id="buscarIncidencia"
                        class="planner-input"
                        placeholder="Buscar por título o cliente..."
                    >

                </div>


                <div class="filter-group">

                    <label for="filtroEstado">
                        Estado
                    </label>

                    <select
                        id="filtroEstado"
                        class="planner-select"
                    >

                        <option>
                            Todos los estados
                        </option>

                        <option>
                            Pendiente
                        </option>

                        <option>
                            En curso
                        </option>

                        <option>
                            Resuelta
                        </option>

                        <option>
                            Cerrada
                        </option>

                    </select>

                </div>


                <div class="filter-group">

                    <label for="filtroPrioridad">
                        Prioridad
                    </label>

                    <select
                        id="filtroPrioridad"
                        class="planner-select"
                    >

                        <option>
                            Todas las prioridades
                        </option>

                        <option>
                            Alta
                        </option>

                        <option>
                            Media
                        </option>

                        <option>
                            Baja
                        </option>

                    </select>

                </div>


                <div class="filter-group">

                    <label for="filtroResponsable">
                        Responsable
                    </label>

                    <select
                        id="filtroResponsable"
                        class="planner-select"
                    >

                        <option>
                            Todos
                        </option>

                        <option>
                            Administrador
                        </option>

                        <option>
                            Juan García
                        </option>

                        <option>
                            María López
                        </option>

                    </select>

                </div>


                <div class="filter-actions">

                    <button class="config-save-button">
                        Buscar
                    </button>

                    <button class="panel-action">
                        Limpiar
                    </button>

                </div>


            </div>

        </section>



        <!-- =========================
             CONTENIDO PRINCIPAL
        ========================== -->

        <section class="incidencias-layout">


            <!-- =========================
                 LISTADO
            ========================== -->

            <div class="panel incidencias-list-panel">


                <div class="panel-header">

                    <div>

                        <h2>
                            Incidencias recientes
                        </h2>

                        <p>
                            Listado de incidencias registradas
                        </p>

                    </div>

                    <button class="panel-action">
                        Ver todas
                    </button>

                </div>



                <div class="incidencias-list">


                    <!-- INCIDENCIA 1 -->

                    <div class="incidencia-item">


                        <div class="incidencia-priority priority-high">
                        </div>


                        <div class="incidencia-main">

                            <div class="incidencia-title">

                                <strong>
                                    Error al actualizar tarifa Endesa
                                </strong>

                                <span class="incidencia-id">
                                    #INC-1048
                                </span>

                            </div>


                            <p>
                                El sistema no permite actualizar la tarifa contratada del cliente.
                            </p>


                            <div class="incidencia-meta">

                                <span>
                                    Cliente: Energía Norte
                                </span>

                                <span>
                                    Juan García
                                </span>

                                <span>
                                    04/09/2026 · 09:32
                                </span>

                            </div>

                        </div>


                        <div class="incidencia-right">


                            <span class="status-badge status-pending">
                                Pendiente
                            </span>


                            <span class="priority-badge priority-high-badge">
                                Alta
                            </span>


                        </div>


                    </div>



                    <!-- INCIDENCIA 2 -->

                    <div class="incidencia-item">


                        <div class="incidencia-priority priority-medium">
                        </div>


                        <div class="incidencia-main">

                            <div class="incidencia-title">

                                <strong>
                                    Datos incorrectos del cliente
                                </strong>

                                <span class="incidencia-id">
                                    #INC-1047
                                </span>

                            </div>


                            <p>
                                El correo electrónico mostrado no coincide con los datos registrados.
                            </p>


                            <div class="incidencia-meta">

                                <span>
                                    Cliente: Solar Iberia
                                </span>

                                <span>
                                    María López
                                </span>

                                <span>
                                    03/09/2026 · 16:20
                                </span>

                            </div>

                        </div>


                        <div class="incidencia-right">


                            <span class="status-badge status-progress">
                                En curso
                            </span>


                            <span class="priority-badge priority-medium-badge">
                                Media
                            </span>


                        </div>


                    </div>



                    <!-- INCIDENCIA 3 -->

                    <div class="incidencia-item">


                        <div class="incidencia-priority priority-low">
                        </div>


                        <div class="incidencia-main">

                            <div class="incidencia-title">

                                <strong>
                                    Lentitud en el comparador
                                </strong>

                                <span class="incidencia-id">
                                    #INC-1046
                                </span>

                            </div>


                            <p>
                                La página de resultados tarda varios segundos en mostrar las comparativas.
                            </p>


                            <div class="incidencia-meta">

                                <span>
                                    Cliente: Comercializadora Levante
                                </span>

                                <span>
                                    Administrador
                                </span>

                                <span>
                                    03/09/2026 · 11:45
                                </span>

                            </div>

                        </div>


                        <div class="incidencia-right">


                            <span class="status-badge status-progress">
                                En curso
                            </span>


                            <span class="priority-badge priority-low-badge">
                                Baja
                            </span>


                        </div>


                    </div>



                    <!-- INCIDENCIA 4 -->

                    <div class="incidencia-item">


                        <div class="incidencia-priority priority-high">
                        </div>


                        <div class="incidencia-main">

                            <div class="incidencia-title">

                                <strong>
                                    Fallo en generación de informe
                                </strong>

                                <span class="incidencia-id">
                                    #INC-1045
                                </span>

                            </div>


                            <p>
                                No se puede generar el informe mensual de consumo.
                            </p>


                            <div class="incidencia-meta">

                                <span>
                                    Cliente: Grupo Eléctrico SL
                                </span>

                                <span>
                                    Juan García
                                </span>

                                <span>
                                    02/09/2026 · 14:10
                                </span>

                            </div>

                        </div>


                        <div class="incidencia-right">


                            <span class="status-badge status-complete">
                                Resuelta
                            </span>


                            <span class="priority-badge priority-high-badge">
                                Alta
                            </span>


                        </div>


                    </div>



                    <!-- INCIDENCIA 5 -->

                    <div class="incidencia-item">


                        <div class="incidencia-priority priority-medium">
                        </div>


                        <div class="incidencia-main">

                            <div class="incidencia-title">

                                <strong>
                                    Incidencia en importación de tarifas
                                </strong>

                                <span class="incidencia-id">
                                    #INC-1044
                                </span>

                            </div>


                            <p>
                                Algunas tarifas no aparecen después de la importación automática.
                            </p>


                            <div class="incidencia-meta">

                                <span>
                                    Cliente: Luz Global
                                </span>

                                <span>
                                    María López
                                </span>

                                <span>
                                    01/09/2026 · 10:25
                                </span>

                            </div>

                        </div>


                        <div class="incidencia-right">


                            <span class="status-badge status-pending">
                                Pendiente
                            </span>


                            <span class="priority-badge priority-medium-badge">
                                Media
                            </span>


                        </div>


                    </div>


                </div>


            </div>



            <!-- =========================
                 RESUMEN LATERAL
            ========================== -->

            <div class="incidencias-sidebar">


                <!-- DISTRIBUCIÓN -->

                <div class="panel">

                    <div class="panel-header">

                        <div>

                            <h2>
                                Por prioridad
                            </h2>

                            <p>
                                Distribución actual
                            </p>

                        </div>

                    </div>


                    <div class="incidencias-priority-list">


                        <div class="priority-row">

                            <div>

                                <span class="priority-dot priority-high-dot"></span>

                                <span>
                                    Alta
                                </span>

                            </div>

                            <strong>
                                6
                            </strong>

                        </div>


                        <div class="priority-row">

                            <div>

                                <span class="priority-dot priority-medium-dot"></span>

                                <span>
                                    Media
                                </span>

                            </div>

                            <strong>
                                11
                            </strong>

                        </div>


                        <div class="priority-row">

                            <div>

                                <span class="priority-dot priority-low-dot"></span>

                                <span>
                                    Baja
                                </span>

                            </div>

                            <strong>
                                7
                            </strong>

                        </div>


                    </div>

                </div>



                <!-- RESPONSABLES -->

                <div class="panel">

                    <div class="panel-header">

                        <div>

                            <h2>
                                Responsables
                            </h2>

                            <p>
                                Incidencias asignadas
                            </p>

                        </div>

                    </div>


                    <div class="responsables-list">


                        <div class="responsable-item">

                            <div class="responsable-avatar">
                                JG
                            </div>

                            <div class="responsable-info">

                                <strong>
                                    Juan García
                                </strong>

                                <span>
                                    8 incidencias
                                </span>

                            </div>

                        </div>


                        <div class="responsable-item">

                            <div class="responsable-avatar">
                                ML
                            </div>

                            <div class="responsable-info">

                                <strong>
                                    María López
                                </strong>

                                <span>
                                    6 incidencias
                                </span>

                            </div>

                        </div>


                        <div class="responsable-item">

                            <div class="responsable-avatar">
                                AD
                            </div>

                            <div class="responsable-info">

                                <strong>
                                    Administrador
                                </strong>

                                <span>
                                    10 incidencias
                                </span>

                            </div>

                        </div>


                    </div>

                </div>



                <!-- ACTIVIDAD -->

                <div class="panel">

                    <div class="panel-header">

                        <div>

                            <h2>
                                Última actividad
                            </h2>

                            <p>
                                Actividad reciente
                            </p>

                        </div>

                    </div>


                    <div class="activity-list">


                        <div class="activity-item">

                            <span class="activity-dot green"></span>

                            <div>

                                <strong>
                                    Incidencia #1045 resuelta
                                </strong>

                                <span>
                                    Hace 25 minutos
                                </span>

                            </div>

                        </div>


                        <div class="activity-item">

                            <span class="activity-dot blue"></span>

                            <div>

                                <strong>
                                    Nueva incidencia #1048
                                </strong>

                                <span>
                                    Hace 1 hora
                                </span>

                            </div>

                        </div>


                        <div class="activity-item">

                            <span class="activity-dot orange"></span>

                            <div>

                                <strong>
                                    Incidencia #1047 asignada
                                </strong>

                                <span>
                                    Hace 2 horas
                                </span>

                            </div>

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