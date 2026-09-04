<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Partes - Comparador Eléctrico</title>

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


        <!-- ========================================
             CABECERA
        ========================================= -->

        <div class="page-header">

            <div>

                <h1>Partes</h1>

                <p>
                    Gestión y seguimiento de los partes del sistema
                </p>

            </div>


            <div class="page-header-actions">

                <div class="page-date">
                    4 septiembre 2026
                </div>

                <button class="config-save-button">
                    + Nuevo parte
                </button>

            </div>

        </div>


        <!-- ========================================
             RESUMEN
        ========================================= -->

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
                        48
                    </strong>

                    <small>
                        Registrados este mes
                    </small>

                </div>

            </div>


            <div class="dashboard-card">

                <div class="card-icon orange">
                    ◷
                </div>

                <div class="card-info">

                    <span class="card-label">
                        Pendientes
                    </span>

                    <strong>
                        9
                    </strong>

                    <small>
                        Pendientes de revisar
                    </small>

                </div>

            </div>


            <div class="dashboard-card">

                <div class="card-icon purple">
                    ⚙
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

                <div class="card-icon green">
                    ✓
                </div>

                <div class="card-info">

                    <span class="card-label">
                        Completados
                    </span>

                    <strong>
                        33
                    </strong>

                    <small>
                        Finalizados este mes
                    </small>

                </div>

            </div>


        </section>


        <!-- ========================================
             FILTROS
        ========================================= -->

        <section class="panel partes-filters">


            <div class="panel-header">

                <div>

                    <h2>
                        Buscar partes
                    </h2>

                    <p>
                        Filtra los partes por estado, tipo o responsable
                    </p>

                </div>

            </div>


            <div class="partes-filter-row">


                <div class="filter-group">

                    <label for="buscarParte">
                        Buscar
                    </label>

                    <input
                        type="text"
                        id="buscarParte"
                        class="planner-input"
                        placeholder="Número de parte, cliente..."
                    >

                </div>


                <div class="filter-group">

                    <label for="filtroEstadoParte">
                        Estado
                    </label>

                    <select
                        id="filtroEstadoParte"
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
                            Completado
                        </option>

                        <option>
                            Cancelado
                        </option>

                    </select>

                </div>


                <div class="filter-group">

                    <label for="filtroTipoParte">
                        Tipo
                    </label>

                    <select
                        id="filtroTipoParte"
                        class="planner-select"
                    >

                        <option>
                            Todos los tipos
                        </option>

                        <option>
                            Instalación
                        </option>

                        <option>
                            Mantenimiento
                        </option>

                        <option>
                            Revisión
                        </option>

                        <option>
                            Lectura
                        </option>

                        <option>
                            Incidencia
                        </option>

                    </select>

                </div>


                <div class="filter-group">

                    <label for="filtroResponsableParte">
                        Responsable
                    </label>

                    <select
                        id="filtroResponsableParte"
                        class="planner-select"
                    >

                        <option>
                            Todos
                        </option>

                        <option>
                            Juan García
                        </option>

                        <option>
                            María López
                        </option>

                        <option>
                            Carlos Pérez
                        </option>

                        <option>
                            Administrador
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


        <!-- ========================================
             LISTADO DE PARTES
        ========================================= -->

        <section class="panel partes-table-panel">


            <div class="panel-header">

                <div>

                    <h2>
                        Partes registrados
                    </h2>

                    <p>
                        48 partes encontrados
                    </p>

                </div>


                <button class="panel-action">
                    Exportar
                </button>

            </div>


            <div class="partes-table-container">


                <table class="partes-table">


                    <thead>

                        <tr>

                            <th>
                                Nº Parte
                            </th>

                            <th>
                                Cliente
                            </th>

                            <th>
                                Tipo
                            </th>

                            <th>
                                Fecha
                            </th>

                            <th>
                                Responsable
                            </th>

                            <th>
                                Estado
                            </th>

                            <th>
                                Acciones
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <!-- PARTE 1 -->

                        <tr>

                            <td class="parte-id">
                                #PAR-2048
                            </td>

                            <td>

                                <div class="parte-cliente">

                                    <strong>
                                        Energía Norte
                                    </strong>

                                    <span>
                                        Cliente 0184
                                    </span>

                                </div>

                            </td>

                            <td>

                                <span class="parte-type parte-install">
                                    Instalación
                                </span>

                            </td>

                            <td>
                                04/09/2026
                            </td>

                            <td>
                                Juan García
                            </td>

                            <td>

                                <span class="status-badge status-progress">
                                    En curso
                                </span>

                            </td>

                            <td>

                                <div class="parte-actions">

                                    <button class="table-action-button">
                                        Ver
                                    </button>

                                    <button class="table-action-button">
                                        Editar
                                    </button>

                                </div>

                            </td>

                        </tr>


                        <!-- PARTE 2 -->

                        <tr>

                            <td class="parte-id">
                                #PAR-2047
                            </td>

                            <td>

                                <div class="parte-cliente">

                                    <strong>
                                        Solar Iberia
                                    </strong>

                                    <span>
                                        Cliente 0162
                                    </span>

                                </div>

                            </td>

                            <td>

                                <span class="parte-type parte-maintenance">
                                    Mantenimiento
                                </span>

                            </td>

                            <td>
                                04/09/2026
                            </td>

                            <td>
                                María López
                            </td>

                            <td>

                                <span class="status-badge status-pending">
                                    Pendiente
                                </span>

                            </td>

                            <td>

                                <div class="parte-actions">

                                    <button class="table-action-button">
                                        Ver
                                    </button>

                                    <button class="table-action-button">
                                        Editar
                                    </button>

                                </div>

                            </td>

                        </tr>


                        <!-- PARTE 3 -->

                        <tr>

                            <td class="parte-id">
                                #PAR-2046
                            </td>

                            <td>

                                <div class="parte-cliente">

                                    <strong>
                                        Electricidad García S.L.
                                    </strong>

                                    <span>
                                        Cliente 0148
                                    </span>

                                </div>

                            </td>

                            <td>

                                <span class="parte-type parte-review">
                                    Revisión
                                </span>

                            </td>

                            <td>
                                03/09/2026
                            </td>

                            <td>
                                Carlos Pérez
                            </td>

                            <td>

                                <span class="status-badge status-complete">
                                    Completado
                                </span>

                            </td>

                            <td>

                                <div class="parte-actions">

                                    <button class="table-action-button">
                                        Ver
                                    </button>

                                    <button class="table-action-button">
                                        Editar
                                    </button>

                                </div>

                            </td>

                        </tr>


                        <!-- PARTE 4 -->

                        <tr>

                            <td class="parte-id">
                                #PAR-2045
                            </td>

                            <td>

                                <div class="parte-cliente">

                                    <strong>
                                        Luz Global
                                    </strong>

                                    <span>
                                        Cliente 0132
                                    </span>

                                </div>

                            </td>

                            <td>

                                <span class="parte-type parte-reading">
                                    Lectura
                                </span>

                            </td>

                            <td>
                                03/09/2026
                            </td>

                            <td>
                                Juan García
                            </td>

                            <td>

                                <span class="status-badge status-progress">
                                    En curso
                                </span>

                            </td>

                            <td>

                                <div class="parte-actions">

                                    <button class="table-action-button">
                                        Ver
                                    </button>

                                    <button class="table-action-button">
                                        Editar
                                    </button>

                                </div>

                            </td>

                        </tr>


                        <!-- PARTE 5 -->

                        <tr>

                            <td class="parte-id">
                                #PAR-2044
                            </td>

                            <td>

                                <div class="parte-cliente">

                                    <strong>
                                        Grupo Eléctrico SL
                                    </strong>

                                    <span>
                                        Cliente 0118
                                    </span>

                                </div>

                            </td>

                            <td>

                                <span class="parte-type parte-incident">
                                    Incidencia
                                </span>

                            </td>

                            <td>
                                02/09/2026
                            </td>

                            <td>
                                Administrador
                            </td>

                            <td>

                                <span class="status-badge status-pending">
                                    Pendiente
                                </span>

                            </td>

                            <td>

                                <div class="parte-actions">

                                    <button class="table-action-button">
                                        Ver
                                    </button>

                                    <button class="table-action-button">
                                        Editar
                                    </button>

                                </div>

                            </td>

                        </tr>


                        <!-- PARTE 6 -->

                        <tr>

                            <td class="parte-id">
                                #PAR-2043
                            </td>

                            <td>

                                <div class="parte-cliente">

                                    <strong>
                                        Comercializadora Levante
                                    </strong>

                                    <span>
                                        Cliente 0097
                                    </span>

                                </div>

                            </td>

                            <td>

                                <span class="parte-type parte-install">
                                    Instalación
                                </span>

                            </td>

                            <td>
                                02/09/2026
                            </td>

                            <td>
                                María López
                            </td>

                            <td>

                                <span class="status-badge status-complete">
                                    Completado
                                </span>

                            </td>

                            <td>

                                <div class="parte-actions">

                                    <button class="table-action-button">
                                        Ver
                                    </button>

                                    <button class="table-action-button">
                                        Editar
                                    </button>

                                </div>

                            </td>

                        </tr>


                    </tbody>


                </table>


            </div>


            <!-- ========================================
                 PAGINACIÓN
            ========================================= -->

            <div class="partes-pagination">

                <span>
                    Mostrando 1-6 de 48 partes
                </span>


                <div class="pagination-buttons">

                    <button class="pagination-button disabled">
                        ‹
                    </button>

                    <button class="pagination-button active">
                        1
                    </button>

                    <button class="pagination-button">
                        2
                    </button>

                    <button class="pagination-button">
                        3
                    </button>

                    <button class="pagination-button">
                        4
                    </button>

                    <button class="pagination-button">
                        5
                    </button>

                    <button class="pagination-button">
                        ›
                    </button>

                </div>

            </div>


        </section>


        <!-- ========================================
             ACTIVIDAD RECIENTE
        ========================================= -->

        <section class="panel partes-activity-panel">


            <div class="panel-header">

                <div>

                    <h2>
                        Actividad reciente
                    </h2>

                    <p>
                        Últimas acciones relacionadas con los partes
                    </p>

                </div>

                <button class="panel-action">
                    Ver historial
                </button>

            </div>


            <div class="activity-list">


                <div class="activity-item">

                    <span class="activity-dot green"></span>

                    <div>

                        <strong>
                            Parte #PAR-2046 completado
                        </strong>

                        <span>
                            Carlos Pérez · Hace 20 minutos
                        </span>

                    </div>

                </div>


                <div class="activity-item">

                    <span class="activity-dot blue"></span>

                    <div>

                        <strong>
                            Nuevo parte #PAR-2048 creado
                        </strong>

                        <span>
                            Juan García · Hace 1 hora
                        </span>

                    </div>

                </div>


                <div class="activity-item">

                    <span class="activity-dot orange"></span>

                    <div>

                        <strong>
                            Parte #PAR-2047 pendiente
                        </strong>

                        <span>
                            María López · Hace 2 horas
                        </span>

                    </div>

                </div>


                <div class="activity-item">

                    <span class="activity-dot purple"></span>

                    <div>

                        <strong>
                            Responsable actualizado
                        </strong>

                        <span>
                            Parte #PAR-2045 · Hace 3 horas
                        </span>

                    </div>

                </div>


            </div>


        </section>


    </main>


</div>


<?php include 'templates/footer.php'; ?>


</body>

</html>