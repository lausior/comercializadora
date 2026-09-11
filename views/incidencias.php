<?php

session_start();

require_once __DIR__ . '/../config/permisos.php';
requerirPermiso('incidencias');

?>
<!DOCTYPE html>

<html lang="es">

<head>

```
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Panel de gestión</title>

<link rel="stylesheet" href="../css/style.css">
```

</head>

<body>

<?php include '../templates/header.php'; ?>

<!-- =========================
     CONTENEDOR PRINCIPAL
========================== -->

<div class="app-container">

```
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

                    <option>Todos los estados</option>
                    <option>Pendiente</option>
                    <option>En curso</option>
                    <option>Resuelta</option>
                    <option>Cerrada</option>

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

                    <option>Todas las prioridades</option>
                    <option>Alta</option>
                    <option>Media</option>
                    <option>Baja</option>

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
         LISTADO DE INCIDENCIAS
    ========================== -->

    <section class="incidencias-layout">


        <div class="panel incidencias-list-panel">


            <!-- CABECERA LISTADO -->

            <div class="panel-header">

                <div>

                    <h2>
                        Incidencias recientes
                    </h2>

                    <p>
                        Listado de incidencias registradas
                    </p>

                </div>

                <div class="incidencias-list-actions">

    <div class="incidencias-per-page">

        <label for="incidenciasPorPagina">
            Mostrar
        </label>

        <select id="incidenciasPorPagina">
            <option value="5">5</option>
            <option value="10" selected>10</option>
            <option value="all">Todas</option>
        </select>

        <span>
            incidencias
        </span>

    </div>

</div>

            </div>



            <!-- =========================
                 LISTA
            ========================== -->

            <div class="incidencias-list">


                <!-- INCIDENCIA 1 -->

                <div class="incidencia-item">

                    <div class="incidencia-priority priority-high"></div>

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

                            <span>Cliente: Energía Norte</span>

                            <span>Juan García</span>

                            <span>04/09/2026 · 09:32</span>

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

                    <div class="incidencia-priority priority-medium"></div>

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

                            <span>Cliente: Solar Iberia</span>

                            <span>María López</span>

                            <span>03/09/2026 · 16:20</span>

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

                    <div class="incidencia-priority priority-low"></div>

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

                            <span>Cliente: Comercializadora Levante</span>

                            <span>Administrador</span>

                            <span>03/09/2026 · 11:45</span>

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

                    <div class="incidencia-priority priority-high"></div>

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

                            <span>Cliente: Grupo Eléctrico SL</span>

                            <span>Juan García</span>

                            <span>02/09/2026 · 14:10</span>

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

                    <div class="incidencia-priority priority-medium"></div>

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

                            <span>Cliente: Luz Global</span>

                            <span>María López</span>

                            <span>01/09/2026 · 10:25</span>

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



                <!-- INCIDENCIA 6 -->

                <div class="incidencia-item">

                    <div class="incidencia-priority priority-high"></div>

                    <div class="incidencia-main">

                        <div class="incidencia-title">

                            <strong>
                                Error al guardar nuevo cliente
                            </strong>

                            <span class="incidencia-id">
                                #INC-1043
                            </span>

                        </div>

                        <p>
                            El formulario devuelve un error al intentar guardar los datos del cliente.
                        </p>

                        <div class="incidencia-meta">

                            <span>Cliente: Energía Atlántica</span>

                            <span>Administrador</span>

                            <span>31/08/2026 · 17:42</span>

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



                <!-- INCIDENCIA 7 -->

                <div class="incidencia-item">

                    <div class="incidencia-priority priority-low"></div>

                    <div class="incidencia-main">

                        <div class="incidencia-title">

                            <strong>
                                Error visual en pantalla de clientes
                            </strong>

                            <span class="incidencia-id">
                                #INC-1042
                            </span>

                        </div>

                        <p>
                            Algunos elementos de la tabla no se muestran correctamente en pantallas pequeñas.
                        </p>

                        <div class="incidencia-meta">

                            <span>Cliente: Iberluz Comercial</span>

                            <span>María López</span>

                            <span>31/08/2026 · 12:15</span>

                        </div>

                    </div>

                    <div class="incidencia-right">

                        <span class="status-badge status-complete">
                            Resuelta
                        </span>

                        <span class="priority-badge priority-low-badge">
                            Baja
                        </span>

                    </div>

                </div>



                <!-- INCIDENCIA 8 -->

                <div class="incidencia-item">

                    <div class="incidencia-priority priority-medium"></div>

                    <div class="incidencia-main">

                        <div class="incidencia-title">

                            <strong>
                                Tarifa no disponible en comparador
                            </strong>

                            <span class="incidencia-id">
                                #INC-1041
                            </span>

                        </div>

                        <p>
                            Una de las tarifas activas no aparece entre los resultados de búsqueda.
                        </p>

                        <div class="incidencia-meta">

                            <span>Cliente: Norte Energía</span>

                            <span>Juan García</span>

                            <span>30/08/2026 · 15:36</span>

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



                <!-- INCIDENCIA 9 -->

                <div class="incidencia-item">

                    <div class="incidencia-priority priority-high"></div>

                    <div class="incidencia-main">

                        <div class="incidencia-title">

                            <strong>
                                Fallo en actualización de precios
                            </strong>

                            <span class="incidencia-id">
                                #INC-1040
                            </span>

                        </div>

                        <p>
                            Los precios importados no se actualizan correctamente en la base de datos.
                        </p>

                        <div class="incidencia-meta">

                            <span>Cliente: Solar Galicia</span>

                            <span>Administrador</span>

                            <span>29/08/2026 · 09:50</span>

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



                <!-- INCIDENCIA 10 -->

                <div class="incidencia-item">

                    <div class="incidencia-priority priority-low"></div>

                    <div class="incidencia-main">

                        <div class="incidencia-title">

                            <strong>
                                Problema con exportación Excel
                            </strong>

                            <span class="incidencia-id">
                                #INC-1039
                            </span>

                        </div>

                        <p>
                            El archivo Excel generado no incluye todos los registros seleccionados.
                        </p>

                        <div class="incidencia-meta">

                            <span>Cliente: Energía Verde SL</span>

                            <span>María López</span>

                            <span>28/08/2026 · 16:05</span>

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



                <!-- INCIDENCIA 11 -->

                <div class="incidencia-item">

                    <div class="incidencia-priority priority-medium"></div>

                    <div class="incidencia-main">

                        <div class="incidencia-title">

                            <strong>
                                Usuario sin permisos de acceso
                            </strong>

                            <span class="incidencia-id">
                                #INC-1038
                            </span>

                        </div>

                        <p>
                            El usuario no puede acceder a determinadas opciones del panel de gestión.
                        </p>

                        <div class="incidencia-meta">

                            <span>Cliente: Luz Mediterránea</span>

                            <span>Administrador</span>

                            <span>27/08/2026 · 13:27</span>

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



                <!-- INCIDENCIA 12 -->

                <div class="incidencia-item">

                    <div class="incidencia-priority priority-high"></div>

                    <div class="incidencia-main">

                        <div class="incidencia-title">

                            <strong>
                                Error de conexión con base de datos
                            </strong>

                            <span class="incidencia-id">
                                #INC-1037
                            </span>

                        </div>

                        <p>
                            Se ha detectado una pérdida temporal de conexión con la base de datos.
                        </p>

                        <div class="incidencia-meta">

                            <span>Cliente: Sistema interno</span>

                            <span>Administrador</span>

                            <span>26/08/2026 · 08:41</span>

                        </div>

                    </div>

                    <div class="incidencia-right">

                        <span class="status-badge status-complete">
                            Cerrada
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

                <span id="incidenciasMostrando">
                    Mostrando 1-10 de 12 incidencias
                </span>

                <div class="pagination-buttons">

                    <button type="button" class="pagination-button disabled" data-page="prev">
                        ‹
                    </button>

                    <button type="button" class="pagination-button active" data-page="1">
                        1
                    </button>

                    <button type="button" class="pagination-button" data-page="2">
                        2
                    </button>

                    <button type="button" class="pagination-button" data-page="3">
                        3
                    </button>

                    <button type="button" class="pagination-button" data-page="4">
                        4
                    </button>

                    <button type="button" class="pagination-button" data-page="5">
                        5
                    </button>

                    <button type="button" class="pagination-button" data-page="next">
                        ›
                    </button>

                </div>


            </div>


        </div>


    </section>


</main>
```

</div>

<?php include '../templates/footer.php'; ?>

<script src="../js/incidencias.js"></script>

</body>

</html>
