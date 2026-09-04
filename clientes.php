<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Clientes - Comparador Eléctrico</title>

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

                <h1>
                    Clientes
                </h1>

                <p>
                    Gestión y consulta de los clientes del sistema
                </p>

            </div>


            <div class="page-header-actions">

                <div class="page-date">
                    4 septiembre 2026
                </div>

                <button class="config-save-button">
                    + Nuevo cliente
                </button>

            </div>

        </div>


        <!-- =========================
             RESUMEN
        ========================== -->

        <section class="dashboard-cards">


            <!-- TOTAL CLIENTES -->

            <div class="dashboard-card">

                <div class="card-icon blue">
                    👥
                </div>

                <div class="card-info">

                    <span class="card-label">
                        Total clientes
                    </span>

                    <strong>
                        186
                    </strong>

                    <small>
                        Clientes registrados
                    </small>

                </div>

            </div>


            <!-- ACTIVOS -->

            <div class="dashboard-card">

                <div class="card-icon green">
                    ✓
                </div>

                <div class="card-info">

                    <span class="card-label">
                        Clientes activos
                    </span>

                    <strong>
                        164
                    </strong>

                    <small>
                        Actualmente activos
                    </small>

                </div>

            </div>


            <!-- PENDIENTES -->

            <div class="dashboard-card">

                <div class="card-icon orange">
                    ◷
                </div>

                <div class="card-info">

                    <span class="card-label">
                        Pendientes
                    </span>

                    <strong>
                        14
                    </strong>

                    <small>
                        Requieren atención
                    </small>

                </div>

            </div>


            <!-- NUEVOS -->

            <div class="dashboard-card">

                <div class="card-icon purple">
                    +
                </div>

                <div class="card-info">

                    <span class="card-label">
                        Nuevos este mes
                    </span>

                    <strong>
                        8
                    </strong>

                    <small>
                        Clientes incorporados
                    </small>

                </div>

            </div>


        </section>


        <!-- =========================
             FILTROS
        ========================== -->

        <section class="panel clientes-filters">


            <div class="panel-header">

                <div>

                    <h2>
                        Buscar clientes
                    </h2>

                    <p>
                        Filtra los clientes por estado, comercializadora o tarifa
                    </p>

                </div>

            </div>


            <div class="clientes-filter-row">


                <!-- BUSCADOR -->

                <div class="filter-group">

                    <label for="buscarCliente">
                        Buscar
                    </label>

                    <input
                        type="text"
                        id="buscarCliente"
                        class="planner-input"
                        placeholder="Nombre, DNI, email..."
                    >

                </div>


                <!-- ESTADO -->

                <div class="filter-group">

                    <label for="filtroEstadoCliente">
                        Estado
                    </label>

                    <select
                        id="filtroEstadoCliente"
                        class="planner-select"
                    >

                        <option>
                            Todos los estados
                        </option>

                        <option>
                            Activo
                        </option>

                        <option>
                            Pendiente
                        </option>

                        <option>
                            Inactivo
                        </option>

                    </select>

                </div>


                <!-- COMERCIALIZADORA -->

                <div class="filter-group">

                    <label for="filtroComercializadora">
                        Comercializadora
                    </label>

                    <select
                        id="filtroComercializadora"
                        class="planner-select"
                    >

                        <option>
                            Todas
                        </option>

                        <option>
                            Endesa
                        </option>

                        <option>
                            Iberdrola
                        </option>

                        <option>
                            Naturgy
                        </option>

                        <option>
                            Repsol
                        </option>

                        <option>
                            TotalEnergies
                        </option>

                    </select>

                </div>


                <!-- TIPO TARIFA -->

                <div class="filter-group">

                    <label for="filtroTarifa">
                        Tarifa
                    </label>

                    <select
                        id="filtroTarifa"
                        class="planner-select"
                    >

                        <option>
                            Todas
                        </option>

                        <option>
                            PVPC
                        </option>

                        <option>
                            Mercado libre
                        </option>

                        <option>
                            Tarifa fija
                        </option>

                    </select>

                </div>


                <!-- BOTONES -->

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
             TABLA DE CLIENTES
        ========================== -->

        <section class="panel clientes-table-panel">


            <div class="panel-header">

                <div>

                    <h2>
                        Clientes registrados
                    </h2>

                    <p>
                        186 clientes encontrados
                    </p>

                </div>


                <button class="panel-action">
                    Exportar
                </button>

            </div>


            <div class="clientes-table-container">


                <table class="clientes-table">


                    <thead>

    <tr>

        <th>
            <span class="sortable-header">
                Cliente
                <button
                    class="sort-button"
                    type="button"
                    data-column="0"
                    title="Ordenar por cliente"
                >
                    ↕
                </button>
            </span>
        </th>


        <th>
            <span class="sortable-header">
                Identificación
                <button
                    class="sort-button"
                    type="button"
                    data-column="1"
                    title="Ordenar por identificación"
                >
                    ↕
                </button>
            </span>
        </th>


        <th>
            <span class="sortable-header">
                Comercializadora
                <button
                    class="sort-button"
                    type="button"
                    data-column="2"
                    title="Ordenar por comercializadora"
                >
                    ↕
                </button>
            </span>
        </th>


        <th>
            <span class="sortable-header">
                Tarifa
                <button
                    class="sort-button"
                    type="button"
                    data-column="3"
                    title="Ordenar por tarifa"
                >
                    ↕
                </button>
            </span>
        </th>


        <th>
            <span class="sortable-header">
                Última gestión
                <button
                    class="sort-button"
                    type="button"
                    data-column="4"
                    title="Ordenar por última gestión"
                >
                    ↕
                </button>
            </span>
        </th>


        <th>
            <span class="sortable-header">
                Estado
                <button
                    class="sort-button"
                    type="button"
                    data-column="5"
                    title="Ordenar por estado"
                >
                    ↕
                </button>
            </span>
        </th>


        <th>
            <span class="sortable-header">
                Acciones
                <button
                    class="sort-button sort-disabled"
                    type="button"
                    title="No se puede ordenar esta columna"
                    disabled
                >
                    ↕
                </button>
            </span>
        </th>

    </tr>

</thead>


                    <tbody>


                        <!-- =========================
                             CLIENTE 1
                        ========================== -->

                        <tr>

                            <td>

                                <div class="cliente-cell">

                                    <div class="cliente-avatar">
                                        EG
                                    </div>

                                    <div class="cliente-info">

                                        <strong>
                                            Electricidad García S.L.
                                        </strong>

                                        <span>
                                            cliente@electricidadgarcia.es
                                        </span>

                                    </div>

                                </div>

                            </td>


                            <td>
                                B12345678
                            </td>


                            <td>
                                Endesa
                            </td>


                            <td>

                                <span class="tarifa-badge tarifa-pvpc">
                                    PVPC
                                </span>

                            </td>


                            <td>
                                Hoy, 09:42
                            </td>


                            <td>

                                <span class="status-badge cliente-active">
                                    Activo
                                </span>

                            </td>


                            <td>

                                <div class="cliente-actions">

                                    <button class="table-action-button">
                                        Ver
                                    </button>

                                    <button class="table-action-button">
                                        Editar
                                    </button>

                                </div>

                            </td>

                        </tr>


                        <!-- =========================
                             CLIENTE 2
                        ========================== -->

                        <tr>

                            <td>

                                <div class="cliente-cell">

                                    <div class="cliente-avatar">
                                        SN
                                    </div>

                                    <div class="cliente-info">

                                        <strong>
                                            Solar Norte S.A.
                                        </strong>

                                        <span>
                                            contacto@solarnorte.es
                                        </span>

                                    </div>

                                </div>

                            </td>


                            <td>
                                A28012345
                            </td>


                            <td>
                                Iberdrola
                            </td>


                            <td>

                                <span class="tarifa-badge tarifa-libre">
                                    Mercado libre
                                </span>

                            </td>


                            <td>
                                Hoy, 08:55
                            </td>


                            <td>

                                <span class="status-badge cliente-active">
                                    Activo
                                </span>

                            </td>


                            <td>

                                <div class="cliente-actions">

                                    <button class="table-action-button">
                                        Ver
                                    </button>

                                    <button class="table-action-button">
                                        Editar
                                    </button>

                                </div>

                            </td>

                        </tr>


                        <!-- =========================
                             CLIENTE 3
                        ========================== -->

                        <tr>

                            <td>

                                <div class="cliente-cell">

                                    <div class="cliente-avatar">
                                        LM
                                    </div>

                                    <div class="cliente-info">

                                        <strong>
                                            Luis Martín
                                        </strong>

                                        <span>
                                            luis.martin@email.es
                                        </span>

                                    </div>

                                </div>

                            </td>


                            <td>
                                12345678A
                            </td>


                            <td>
                                Naturgy
                            </td>


                            <td>

                                <span class="tarifa-badge tarifa-fija">
                                    Tarifa fija
                                </span>

                            </td>


                            <td>
                                Ayer, 17:32
                            </td>


                            <td>

                                <span class="status-badge cliente-pending">
                                    Pendiente
                                </span>

                            </td>


                            <td>

                                <div class="cliente-actions">

                                    <button class="table-action-button">
                                        Ver
                                    </button>

                                    <button class="table-action-button">
                                        Editar
                                    </button>

                                </div>

                            </td>

                        </tr>


                        <!-- =========================
                             CLIENTE 4
                        ========================== -->

                        <tr>

                            <td>

                                <div class="cliente-cell">

                                    <div class="cliente-avatar">
                                        MG
                                    </div>

                                    <div class="cliente-info">

                                        <strong>
                                            María González
                                        </strong>

                                        <span>
                                            maria.gonzalez@email.es
                                        </span>

                                    </div>

                                </div>

                            </td>


                            <td>
                                45678912B
                            </td>


                            <td>
                                Repsol
                            </td>


                            <td>

                                <span class="tarifa-badge tarifa-libre">
                                    Mercado libre
                                </span>

                            </td>


                            <td>
                                02/09/2026 · 15:20
                            </td>


                            <td>

                                <span class="status-badge cliente-active">
                                    Activo
                                </span>

                            </td>


                            <td>

                                <div class="cliente-actions">

                                    <button class="table-action-button">
                                        Ver
                                    </button>

                                    <button class="table-action-button">
                                        Editar
                                    </button>

                                </div>

                            </td>

                        </tr>


                        <!-- =========================
                             CLIENTE 5
                        ========================== -->

                        <tr>

                            <td>

                                <div class="cliente-cell">

                                    <div class="cliente-avatar">
                                        JC
                                    </div>

                                    <div class="cliente-info">

                                        <strong>
                                            Juan Carlos López
                                        </strong>

                                        <span>
                                            juan.lopez@email.es
                                        </span>

                                    </div>

                                </div>

                            </td>


                            <td>
                                78451236C
                            </td>


                            <td>
                                TotalEnergies
                            </td>


                            <td>

                                <span class="tarifa-badge tarifa-pvpc">
                                    PVPC
                                </span>

                            </td>


                            <td>
                                02/09/2026 · 11:14
                            </td>


                            <td>

                                <span class="status-badge cliente-inactive">
                                    Inactivo
                                </span>

                            </td>


                            <td>

                                <div class="cliente-actions">

                                    <button class="table-action-button">
                                        Ver
                                    </button>

                                    <button class="table-action-button">
                                        Editar
                                    </button>

                                </div>

                            </td>

                        </tr>


                        <!-- =========================
                             CLIENTE 6
                        ========================== -->

                        <tr>

                            <td>

                                <div class="cliente-cell">

                                    <div class="cliente-avatar">
                                        AR
                                    </div>

                                    <div class="cliente-info">

                                        <strong>
                                            Ana Rodríguez
                                        </strong>

                                        <span>
                                            ana.rodriguez@email.es
                                        </span>

                                    </div>

                                </div>

                            </td>


                            <td>
                                52987461D
                            </td>


                            <td>
                                Endesa
                            </td>


                            <td>

                                <span class="tarifa-badge tarifa-fija">
                                    Tarifa fija
                                </span>

                            </td>


                            <td>
                                01/09/2026 · 09:45
                            </td>


                            <td>

                                <span class="status-badge cliente-active">
                                    Activo
                                </span>

                            </td>


                            <td>

                                <div class="cliente-actions">

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


            <!-- =========================
                 PAGINACIÓN
            ========================== -->

            <div class="clientes-pagination">

                <span>
                    Mostrando 1-6 de 186 clientes
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


        <!-- =========================
             ACTIVIDAD RECIENTE
        ========================== -->

        <section class="panel clientes-activity-panel">


            <div class="panel-header">

                <div>

                    <h2>
                        Actividad reciente
                    </h2>

                    <p>
                        Últimas acciones realizadas sobre clientes
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
                            Nuevo cliente registrado
                        </strong>

                        <span>
                            Electricidad García S.L. · Hace 20 minutos
                        </span>

                    </div>

                </div>


                <div class="activity-item">

                    <span class="activity-dot blue"></span>

                    <div>

                        <strong>
                            Datos del cliente actualizados
                        </strong>

                        <span>
                            Solar Norte S.A. · Hace 1 hora
                        </span>

                    </div>

                </div>


                <div class="activity-item">

                    <span class="activity-dot orange"></span>

                    <div>

                        <strong>
                            Cliente pendiente de documentación
                        </strong>

                        <span>
                            Luis Martín · Hace 2 horas
                        </span>

                    </div>

                </div>


                <div class="activity-item">

                    <span class="activity-dot purple"></span>

                    <div>

                        <strong>
                            Tarifa modificada
                        </strong>

                        <span>
                            María González · Hace 3 horas
                        </span>

                    </div>

                </div>


            </div>


        </section>


    </main>


</div>


<?php include 'templates/footer.php'; ?>

<script src="js/clientes.js"></script>

</body>

</html>