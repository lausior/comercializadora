<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Usuarios - Comparador Eléctrico</title>

    <link rel="stylesheet" href="css/style.css?v=20260904">

</head>

<body>


<?php include 'templates/header.php'; ?>


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

                <h1>Usuarios</h1>

                <p>
                    Gestión y administración de los usuarios del sistema
                </p>

            </div>


            <div class="page-header-actions">

                <div class="page-date">
                    4 septiembre 2026
                </div>

                <button class="config-save-button">
                    + Añadir usuario
                </button>

            </div>

        </div>



        <!-- =========================
             RESUMEN
        ========================== -->

        <section class="dashboard-cards">


            <div class="dashboard-card">

                <div class="card-icon blue">
                    👥
                </div>

                <div class="card-info">

                    <span class="card-label">
                        Total usuarios
                    </span>

                    <strong>
                        42
                    </strong>

                    <small>
                        Usuarios registrados
                    </small>

                </div>

            </div>



            <div class="dashboard-card">

                <div class="card-icon green">
                    ✓
                </div>

                <div class="card-info">

                    <span class="card-label">
                        Usuarios activos
                    </span>

                    <strong>
                        36
                    </strong>

                    <small>
                        Actualmente activos
                    </small>

                </div>

            </div>



            <div class="dashboard-card">

                <div class="card-icon orange">
                    ◷
                </div>

                <div class="card-info">

                    <span class="card-label">
                        Usuarios inactivos
                    </span>

                    <strong>
                        6
                    </strong>

                    <small>
                        Requieren revisión
                    </small>

                </div>

            </div>



            <div class="dashboard-card">

                <div class="card-icon purple">
                    #
                </div>

                <div class="card-info">

                    <span class="card-label">
                        Administradores
                    </span>

                    <strong>
                        4
                    </strong>

                    <small>
                        Con permisos elevados
                    </small>

                </div>

            </div>


        </section>



        <!-- =========================
             FILTROS Y BÚSQUEDA
        ========================== -->

        <section class="panel usuarios-filters">


            <div class="panel-header">

                <div>

                    <h2>
                        Buscar usuarios
                    </h2>

                    <p>
                        Filtra los usuarios por nombre, estado o rol
                    </p>

                </div>

            </div>


            <div class="usuarios-filter-row">


                <div class="filter-group">

                    <label for="buscarUsuario">
                        Buscar
                    </label>

                    <input
                        type="text"
                        id="buscarUsuario"
                        class="planner-input"
                        placeholder="Nombre, email..."
                    >

                </div>



                <div class="filter-group">

                    <label for="filtroRol">
                        Rol
                    </label>

                    <select
                        id="filtroRol"
                        class="planner-select"
                    >

                        <option>
                            Todos los roles
                        </option>

                        <option>
                            Administrador
                        </option>

                        <option>
                            Supervisor
                        </option>

                        <option>
                            Operador
                        </option>

                        <option>
                            Consulta
                        </option>

                    </select>

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
                            Activo
                        </option>

                        <option>
                            Inactivo
                        </option>

                        <option>
                            Bloqueado
                        </option>

                    </select>

                </div>



                <div class="filter-group">

                    <label for="filtroDepartamento">
                        Departamento
                    </label>

                    <select
                        id="filtroDepartamento"
                        class="planner-select"
                    >

                        <option>
                            Todos
                        </option>

                        <option>
                            Administración
                        </option>

                        <option>
                            Comercial
                        </option>

                        <option>
                            Soporte
                        </option>

                        <option>
                            Sistemas
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
             TABLA DE USUARIOS
        ========================== -->

        <section class="panel usuarios-table-panel">


            <div class="panel-header">

                <div>

                    <h2>
                        Usuarios registrados
                    </h2>

                    <p>
                        42 usuarios encontrados
                    </p>

                </div>


                <button class="panel-action">
                    Exportar
                </button>

            </div>



            <div class="usuarios-table-container">

                <table class="usuarios-table">

                    <thead>

                        <tr>

                            <th>
                                Usuario
                            </th>

                            <th>
                                Email
                            </th>

                            <th>
                                Rol
                            </th>

                            <th>
                                Departamento
                            </th>

                            <th>
                                Último acceso
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


                        <!-- USUARIO 1 -->

                        <tr>

                            <td>

                                <div class="usuario-cell">

                                    <div class="usuario-avatar">
                                        AG
                                    </div>

                                    <div class="usuario-info">

                                        <strong>
                                            Antonio García
                                        </strong>

                                        <span>
                                            @antonio.garcia
                                        </span>

                                    </div>

                                </div>

                            </td>


                            <td>
                                antonio.garcia@comparador.es
                            </td>


                            <td>

                                <span class="role-badge role-admin">
                                    Administrador
                                </span>

                            </td>


                            <td>
                                Sistemas
                            </td>


                            <td>
                                Hoy, 09:42
                            </td>


                            <td>

                                <span class="status-badge status-active">
                                    Activo
                                </span>

                            </td>


                            <td>

                                <div class="user-actions">

                                    <button class="table-action-button">
                                        Ver
                                    </button>

                                    <button class="table-action-button">
                                        Editar
                                    </button>

                                </div>

                            </td>

                        </tr>



                        <!-- USUARIO 2 -->

                        <tr>

                            <td>

                                <div class="usuario-cell">

                                    <div class="usuario-avatar">
                                        JL
                                    </div>

                                    <div class="usuario-info">

                                        <strong>
                                            Juan López
                                        </strong>

                                        <span>
                                            @juan.lopez
                                        </span>

                                    </div>

                                </div>

                            </td>


                            <td>
                                juan.lopez@comparador.es
                            </td>


                            <td>

                                <span class="role-badge role-supervisor">
                                    Supervisor
                                </span>

                            </td>


                            <td>
                                Comercial
                            </td>


                            <td>
                                Hoy, 08:56
                            </td>


                            <td>

                                <span class="status-badge status-active">
                                    Activo
                                </span>

                            </td>


                            <td>

                                <div class="user-actions">

                                    <button class="table-action-button">
                                        Ver
                                    </button>

                                    <button class="table-action-button">
                                        Editar
                                    </button>

                                </div>

                            </td>

                        </tr>



                        <!-- USUARIO 3 -->

                        <tr>

                            <td>

                                <div class="usuario-cell">

                                    <div class="usuario-avatar">
                                        ML
                                    </div>

                                    <div class="usuario-info">

                                        <strong>
                                            María López
                                        </strong>

                                        <span>
                                            @maria.lopez
                                        </span>

                                    </div>

                                </div>

                            </td>


                            <td>
                                maria.lopez@comparador.es
                            </td>


                            <td>

                                <span class="role-badge role-operator">
                                    Operador
                                </span>

                            </td>


                            <td>
                                Soporte
                            </td>


                            <td>
                                Hoy, 08:31
                            </td>


                            <td>

                                <span class="status-badge status-active">
                                    Activo
                                </span>

                            </td>


                            <td>

                                <div class="user-actions">

                                    <button class="table-action-button">
                                        Ver
                                    </button>

                                    <button class="table-action-button">
                                        Editar
                                    </button>

                                </div>

                            </td>

                        </tr>



                        <!-- USUARIO 4 -->

                        <tr>

                            <td>

                                <div class="usuario-cell">

                                    <div class="usuario-avatar">
                                        CP
                                    </div>

                                    <div class="usuario-info">

                                        <strong>
                                            Carlos Pérez
                                        </strong>

                                        <span>
                                            @carlos.perez
                                        </span>

                                    </div>

                                </div>

                            </td>


                            <td>
                                carlos.perez@comparador.es
                            </td>


                            <td>

                                <span class="role-badge role-operator">
                                    Operador
                                </span>

                            </td>


                            <td>
                                Comercial
                            </td>


                            <td>
                                Ayer, 17:42
                            </td>


                            <td>

                                <span class="status-badge status-active">
                                    Activo
                                </span>

                            </td>


                            <td>

                                <div class="user-actions">

                                    <button class="table-action-button">
                                        Ver
                                    </button>

                                    <button class="table-action-button">
                                        Editar
                                    </button>

                                </div>

                            </td>

                        </tr>



                        <!-- USUARIO 5 -->

                        <tr>

                            <td>

                                <div class="usuario-cell">

                                    <div class="usuario-avatar">
                                        SV
                                    </div>

                                    <div class="usuario-info">

                                        <strong>
                                            Sara Vicente
                                        </strong>

                                        <span>
                                            @sara.vicente
                                        </span>

                                    </div>

                                </div>

                            </td>


                            <td>
                                sara.vicente@comparador.es
                            </td>


                            <td>

                                <span class="role-badge role-viewer">
                                    Consulta
                                </span>

                            </td>


                            <td>
                                Administración
                            </td>


                            <td>
                                02/09/2026 · 11:20
                            </td>


                            <td>

                                <span class="status-badge status-inactive">
                                    Inactivo
                                </span>

                            </td>


                            <td>

                                <div class="user-actions">

                                    <button class="table-action-button">
                                        Ver
                                    </button>

                                    <button class="table-action-button">
                                        Editar
                                    </button>

                                </div>

                            </td>

                        </tr>



                        <!-- USUARIO 6 -->

                        <tr>

                            <td>

                                <div class="usuario-cell">

                                    <div class="usuario-avatar">
                                        PM
                                    </div>

                                    <div class="usuario-info">

                                        <strong>
                                            Pedro Martín
                                        </strong>

                                        <span>
                                            @pedro.martin
                                        </span>

                                    </div>

                                </div>

                            </td>


                            <td>
                                pedro.martin@comparador.es
                            </td>


                            <td>

                                <span class="role-badge role-supervisor">
                                    Supervisor
                                </span>

                            </td>


                            <td>
                                Sistemas
                            </td>


                            <td>
                                02/09/2026 · 09:14
                            </td>


                            <td>

                                <span class="status-badge status-blocked">
                                    Bloqueado
                                </span>

                            </td>


                            <td>

                                <div class="user-actions">

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

            <div class="usuarios-pagination">

                <span>
                    Mostrando 1-6 de 42 usuarios
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

        <section class="panel usuarios-activity-panel">


            <div class="panel-header">

                <div>

                    <h2>
                        Actividad reciente
                    </h2>

                    <p>
                        Últimas acciones relacionadas con usuarios
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
                            Antonio García inició sesión
                        </strong>

                        <span>
                            Hace 18 minutos · 192.168.1.24
                        </span>

                    </div>

                </div>


                <div class="activity-item">

                    <span class="activity-dot blue"></span>

                    <div>

                        <strong>
                            Nuevo usuario creado
                        </strong>

                        <span>
                            María López · Hace 1 hora
                        </span>

                    </div>

                </div>


                <div class="activity-item">

                    <span class="activity-dot orange"></span>

                    <div>

                        <strong>
                            Usuario Pedro Martín bloqueado
                        </strong>

                        <span>
                            Administrador · Hace 2 horas
                        </span>

                    </div>

                </div>


                <div class="activity-item">

                    <span class="activity-dot purple"></span>

                    <div>

                        <strong>
                            Rol actualizado
                        </strong>

                        <span>
                            Juan López · Hace 3 horas
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