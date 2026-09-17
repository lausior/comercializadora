<?php

session_start();

require_once __DIR__ . '/../config/permisos.php';
requerirPermiso('partes');

?>
<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Panel de gestión</title>

    <link rel="stylesheet" href="../css/style.css">

</head>

<body>

<?php include '../templates/header.php'; ?>

<!-- =========================
     CONTENEDOR PRINCIPAL
========================== -->

<div class="app-container">

    <?php include '../templates/sidebar.php'; ?>


    <!-- =========================
         CONTENIDO
    ========================== -->

    <main class="main-content">


        <!-- =========================
             CABECERA
        ========================== -->

        <div class="page-header">

            <div>

                <h1>Partes</h1>

                <p>
                    Gestión y seguimiento de los partes registrados
                </p>

            </div>


            <div class="page-header-actions">

                <button class="config-save-button">
                    + Nuevo parte
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
                        Total partes
                    </span>

                    <strong>
                        32
                    </strong>

                    <small>
                        Registrados este mes
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
                        9
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
                        6
                    </strong>

                    <small>
                        Actualmente gestionados
                    </small>

                </div>

            </div>


            <div class="dashboard-card">

                <div class="card-icon red">
                    ✓
                </div>

                <div class="card-info">

                    <span class="card-label">
                        Cerrados
                    </span>

                    <strong>
                        17
                    </strong>

                    <small>
                        Partes finalizados
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
                        Buscar partes
                    </h2>

                    <p>
                        Filtra los partes por estado, tipo, prioridad o responsable
                    </p>

                </div>

            </div>


            <div class="incidencias-filter-row partes-filter-row">


                <div class="filter-group">

                    <label for="buscarParte">
                        Buscar
                    </label>

                    <input
                        type="text"
                        id="buscarParte"
                        class="planner-input"
                        placeholder="Buscar por título, cliente o referencia..."
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

                        <option>Todos</option>
                        <option>Pendiente</option>
                        <option>En curso</option>
                        <option>Completado</option>
                        <option>Cerrado</option>

                    </select>

                </div>


                <div class="filter-group">

                    <label for="filtroTipo">
                        Tipo de parte
                    </label>

                    <select
                        id="filtroTipo"
                        class="planner-select"
                    >

                        <option>Todos</option>
                        <option>Instalación</option>
                        <option>Mantenimiento</option>
                        <option>Revisión</option>
                        <option>Visita</option>
                        <option>Avería</option>

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

                        <option>Todos</option>
                        <option>Administrador</option>
                        <option>Juan García</option>
                        <option>María López</option>

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

                        <option>Todas</option>
                        <option>Alta</option>
                        <option>Media</option>
                        <option>Baja</option>

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
             LISTADO DE PARTES
        ========================== -->

        <section class="incidencias-layout">


            <div class="panel incidencias-list-panel">


                <!-- CABECERA LISTADO -->

                <div class="panel-header">

                    <div>

                        <h2>
                            Partes recientes
                        </h2>

                        <p>
                            Listado de partes registrados
                        </p>

                    </div>


                    <div class="incidencias-list-actions">

                        <div class="incidencias-per-page">

                            <label for="partesPorPagina">
                                Mostrar
                            </label>

                            <select id="partesPorPagina">

                                <option value="5">
                                    5
                                </option>

                                <option value="10" selected>
                                    10
                                </option>

                                <option value="all">
                                    Todas
                                </option>

                            </select>

                            <span>
                                partes
                            </span>

                        </div>

                    </div>

                </div>



                <!-- =========================
                     LISTA
                ========================== -->

                <div class="incidencias-list">


                    <!-- PARTE 1 -->

                    <div class="incidencia-item">

                        <div class="incidencia-priority priority-high"></div>

                        <div class="incidencia-main">

                            <div class="incidencia-title">

                                <strong>
                                    Instalación de nuevo punto de suministro
                                </strong>

                                <span class="incidencia-id">
                                    #PAR-2048
                                </span>

                            </div>

                            <p>
                                Parte correspondiente a la instalación y puesta en servicio de un nuevo punto de suministro.
                            </p>

                            <div class="incidencia-meta">

                                <span>
                                    Cliente: Energía Norte
                                </span>

                                <span>
                                    Juan García
                                </span>

                                <span>
                                    04/09/2026 · 09:15
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



                    <!-- PARTE 2 -->

                    <div class="incidencia-item">

                        <div class="incidencia-priority priority-medium"></div>

                        <div class="incidencia-main">

                            <div class="incidencia-title">

                                <strong>
                                    Revisión de documentación de contrato
                                </strong>

                                <span class="incidencia-id">
                                    #PAR-2047
                                </span>

                            </div>

                            <p>
                                Revisión de la documentación necesaria para completar la contratación del suministro.
                            </p>

                            <div class="incidencia-meta">

                                <span>
                                    Cliente: Solar Iberia
                                </span>

                                <span>
                                    María López
                                </span>

                                <span>
                                    03/09/2026 · 16:05
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



                    <!-- PARTE 3 -->

                    <div class="incidencia-item">

                        <div class="incidencia-priority priority-low"></div>

                        <div class="incidencia-main">

                            <div class="incidencia-title">

                                <strong>
                                    Visita comercial para cambio de tarifa
                                </strong>

                                <span class="incidencia-id">
                                    #PAR-2046
                                </span>

                            </div>

                            <p>
                                Visita programada para revisar las condiciones actuales y proponer una nueva tarifa.
                            </p>

                            <div class="incidencia-meta">

                                <span>
                                    Cliente: Comercializadora Levante
                                </span>

                                <span>
                                    Administrador
                                </span>

                                <span>
                                    03/09/2026 · 11:30
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



                    <!-- PARTE 4 -->

                    <div class="incidencia-item">

                        <div class="incidencia-priority priority-high"></div>

                        <div class="incidencia-main">

                            <div class="incidencia-title">

                                <strong>
                                    Mantenimiento de datos de suministro
                                </strong>

                                <span class="incidencia-id">
                                    #PAR-2045
                                </span>

                            </div>

                            <p>
                                Actualización y comprobación de los datos asociados al punto de suministro del cliente.
                            </p>

                            <div class="incidencia-meta">

                                <span>
                                    Cliente: Grupo Eléctrico SL
                                </span>

                                <span>
                                    Juan García
                                </span>

                                <span>
                                    02/09/2026 · 14:00
                                </span>

                            </div>

                        </div>

                        <div class="incidencia-right">

                            <span class="status-badge status-complete">
                                Completado
                            </span>

                            <span class="priority-badge priority-high-badge">
                                Alta
                            </span>

                        </div>

                    </div>



                    <!-- PARTE 5 -->

                    <div class="incidencia-item">

                        <div class="incidencia-priority priority-medium"></div>

                        <div class="incidencia-main">

                            <div class="incidencia-title">

                                <strong>
                                    Verificación de potencia contratada
                                </strong>

                                <span class="incidencia-id">
                                    #PAR-2044
                                </span>

                            </div>

                            <p>
                                Comprobación de la potencia contratada y revisión de los datos registrados.
                            </p>

                            <div class="incidencia-meta">

                                <span>
                                    Cliente: Luz Global
                                </span>

                                <span>
                                    María López
                                </span>

                                <span>
                                    01/09/2026 · 10:10
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



                    <!-- PARTE 6 -->

                    <div class="incidencia-item">

                        <div class="incidencia-priority priority-high"></div>

                        <div class="incidencia-main">

                            <div class="incidencia-title">

                                <strong>
                                    Alta de nuevo cliente
                                </strong>

                                <span class="incidencia-id">
                                    #PAR-2043
                                </span>

                            </div>

                            <p>
                                Gestión del alta de un nuevo cliente y registro de la información contractual.
                            </p>

                            <div class="incidencia-meta">

                                <span>
                                    Cliente: Energía Atlántica
                                </span>

                                <span>
                                    Administrador
                                </span>

                                <span>
                                    31/08/2026 · 17:20
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



                    <!-- PARTE 7 -->

                    <div class="incidencia-item">

                        <div class="incidencia-priority priority-low"></div>

                        <div class="incidencia-main">

                            <div class="incidencia-title">

                                <strong>
                                    Revisión de documentación comercial
                                </strong>

                                <span class="incidencia-id">
                                    #PAR-2042
                                </span>

                            </div>

                            <p>
                                Comprobación de contratos y documentación comercial antes de finalizar la gestión.
                            </p>

                            <div class="incidencia-meta">

                                <span>
                                    Cliente: Iberluz Comercial
                                </span>

                                <span>
                                    María López
                                </span>

                                <span>
                                    31/08/2026 · 12:00
                                </span>

                            </div>

                        </div>

                        <div class="incidencia-right">

                            <span class="status-badge status-complete">
                                Completado
                            </span>

                            <span class="priority-badge priority-low-badge">
                                Baja
                            </span>

                        </div>

                    </div>



                    <!-- PARTE 8 -->

                    <div class="incidencia-item">

                        <div class="incidencia-priority priority-medium"></div>

                        <div class="incidencia-main">

                            <div class="incidencia-title">

                                <strong>
                                    Visita técnica de suministro
                                </strong>

                                <span class="incidencia-id">
                                    #PAR-2041
                                </span>

                            </div>

                            <p>
                                Visita técnica para comprobar el estado del suministro y recoger información necesaria.
                            </p>

                            <div class="incidencia-meta">

                                <span>
                                    Cliente: Norte Energía
                                </span>

                                <span>
                                    Juan García
                                </span>

                                <span>
                                    30/08/2026 · 15:20
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



                    <!-- PARTE 9 -->

                    <div class="incidencia-item">

                        <div class="incidencia-priority priority-high"></div>

                        <div class="incidencia-main">

                            <div class="incidencia-title">

                                <strong>
                                    Actualización de tarifa contratada
                                </strong>

                                <span class="incidencia-id">
                                    #PAR-2040
                                </span>

                            </div>

                            <p>
                                Actualización de las condiciones de la tarifa contratada y comprobación de los nuevos precios.
                            </p>

                            <div class="incidencia-meta">

                                <span>
                                    Cliente: Solar Galicia
                                </span>

                                <span>
                                    Administrador
                                </span>

                                <span>
                                    29/08/2026 · 09:35
                                </span>

                            </div>

                        </div>

                        <div class="incidencia-right">

                            <span class="status-badge status-complete">
                                Completado
                            </span>

                            <span class="priority-badge priority-high-badge">
                                Alta
                            </span>

                        </div>

                    </div>



                    <!-- PARTE 10 -->

                    <div class="incidencia-item">

                        <div class="incidencia-priority priority-low"></div>

                        <div class="incidencia-main">

                            <div class="incidencia-title">

                                <strong>
                                    Comprobación de documentación
                                </strong>

                                <span class="incidencia-id">
                                    #PAR-2039
                                </span>

                            </div>

                            <p>
                                Comprobación de la documentación aportada para completar el expediente del cliente.
                            </p>

                            <div class="incidencia-meta">

                                <span>
                                    Cliente: Energía Verde SL
                                </span>

                                <span>
                                    María López
                                </span>

                                <span>
                                    28/08/2026 · 15:50
                                </span>

                            </div>

                        </div>

                        <div class="incidencia-right">

                            <span class="status-badge status-pending">
                                Pendiente
                            </span>

                            <span class="priority-badge priority-low-badge">
                                Baja
                            </span>

                        </div>

                    </div>



                    <!-- PARTE 11 -->

                    <div class="incidencia-item">

                        <div class="incidencia-priority priority-medium"></div>

                        <div class="incidencia-main">

                            <div class="incidencia-title">

                                <strong>
                                    Parte de mantenimiento preventivo
                                </strong>

                                <span class="incidencia-id">
                                    #PAR-2038
                                </span>

                            </div>

                            <p>
                                Mantenimiento preventivo de los datos y elementos asociados al suministro.
                            </p>

                            <div class="incidencia-meta">

                                <span>
                                    Cliente: Luz Mediterránea
                                </span>

                                <span>
                                    Administrador
                                </span>

                                <span>
                                    27/08/2026 · 13:10
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



                    <!-- PARTE 12 -->

                    <div class="incidencia-item">

                        <div class="incidencia-priority priority-high"></div>

                        <div class="incidencia-main">

                            <div class="incidencia-title">

                                <strong>
                                    Instalación de nuevo suministro
                                </strong>

                                <span class="incidencia-id">
                                    #PAR-2037
                                </span>

                            </div>

                            <p>
                                Parte finalizado correspondiente a la instalación y puesta en funcionamiento del suministro.
                            </p>

                            <div class="incidencia-meta">

                                <span>
                                    Cliente: Sistema interno
                                </span>

                                <span>
                                    Administrador
                                </span>

                                <span>
                                    26/08/2026 · 08:30
                                </span>

                            </div>

                        </div>

                        <div class="incidencia-right">

                            <span class="status-badge status-complete">
                                Cerrado
                            </span>

                            <span class="priority-badge priority-high-badge">
                                Alta
                            </span>

                        </div>

                    </div>


                </div>



                <!-- =========================
                     PAGINACIÓN
                ========================== -->

                <div class="incidencias-pagination">

                    <span id="partesMostrando">
                        Mostrando 1-10 de 12 partes
                    </span>


                    <div class="pagination-buttons">

                        <button
                            type="button"
                            class="pagination-button disabled"
                            data-page="prev"
                        >
                            ‹
                        </button>


                        <button
                            type="button"
                            class="pagination-button active"
                            data-page="1"
                        >
                            1
                        </button>


                        <button
                            type="button"
                            class="pagination-button"
                            data-page="2"
                        >
                            2
                        </button>


                        <button
                            type="button"
                            class="pagination-button"
                            data-page="3"
                        >
                            3
                        </button>


                        <button
                            type="button"
                            class="pagination-button"
                            data-page="4"
                        >
                            4
                        </button>


                        <button
                            type="button"
                            class="pagination-button"
                            data-page="5"
                        >
                            5
                        </button>


                        <button
                            type="button"
                            class="pagination-button"
                            data-page="next"
                        >
                            ›
                        </button>

                    </div>

                </div>


            </div>


        </section>


    </main>

</div>


<?php include '../templates/footer.php'; ?>


<script src="../js/partes.js"></script>


</body>

</html>

