<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Logs - Comparador Eléctrico</title>

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


        <div class="logs-header">

            <div>

                <h1>Logs</h1>

                <p>
                    Registro de actividad del sistema
                </p>

            </div>


            <div class="logs-actions">

                <button class="logs-btn">
                    🔄 Actualizar
                </button>

                <button class="logs-btn primary">
                    ⬇ Exportar
                </button>

            </div>

        </div>


        <!-- =========================
             FILTROS
        ========================== -->

        <div class="logs-filters">


            <div class="filter-group">

                <label for="tipo">
                    Tipo de evento
                </label>

                <select id="tipo">

                    <option>Todos</option>
                    <option>Información</option>
                    <option>Éxito</option>
                    <option>Advertencia</option>
                    <option>Error</option>

                </select>

            </div>


            <div class="filter-group">

                <label for="usuario">
                    Usuario
                </label>

                <select id="usuario">

                    <option>
                        Todos los usuarios
                    </option>

                    <option>
                        admin
                    </option>

                    <option>
                        juan
                    </option>

                    <option>
                        maria
                    </option>

                    <option>
                        soporte
                    </option>

                </select>

            </div>


            <div class="filter-group">

                <label for="fecha">
                    Fecha
                </label>

                <input
                    type="date"
                    id="fecha"
                >

            </div>


            <button class="logs-btn primary">
                🔎 Filtrar
            </button>


        </div>


        <!-- =========================
             TABLA DE LOGS
        ========================== -->

        <div class="logs-table-container">


            <table class="logs-table">


               <thead>

    <tr>

        <th>
            <span class="sortable-header">
                Fecha y hora
                <button class="sort-button" type="button" title="Ordenar por fecha">
                    ↕
                </button>
            </span>
        </th>

        <th>
            <span class="sortable-header">
                Tipo
                <button class="sort-button" type="button" title="Ordenar por tipo">
                    ↕
                </button>
            </span>
        </th>

        <th>
            <span class="sortable-header">
                Usuario
                <button class="sort-button" type="button" title="Ordenar por usuario">
                    ↕
                </button>
            </span>
        </th>

        <th>
            <span class="sortable-header">
                Evento
                <button class="sort-button" type="button" title="Ordenar por evento">
                    ↕
                </button>
            </span>
        </th>

        <th>
            <span class="sortable-header">
                Descripción
                <button class="sort-button" type="button" title="Ordenar por descripción">
                    ↕
                </button>
            </span>
        </th>

        <th>
            <span class="sortable-header">
                IP
                <button class="sort-button" type="button" title="Ordenar por IP">
                    ↕
                </button>
            </span>
        </th>

    </tr>

</thead>


                <tbody>


                    <tr>

                        <td class="log-date">
                            03/09/2026 10:42:15
                        </td>

                        <td>

                            <span class="log-badge log-success">
                                Éxito
                            </span>

                        </td>

                        <td class="log-user">
                            admin
                        </td>

                        <td class="log-action">
                            Inicio de sesión
                        </td>

                        <td>
                            Inicio de sesión realizado correctamente.
                        </td>

                        <td>
                            192.168.1.10
                        </td>

                    </tr>


                    <tr>

                        <td class="log-date">
                            03/09/2026 10:35:48
                        </td>

                        <td>

                            <span class="log-badge log-info">
                                Información
                            </span>

                        </td>

                        <td class="log-user">
                            maria
                        </td>

                        <td class="log-action">
                            Consulta
                        </td>

                        <td>
                            Se ha realizado una comparación de tarifas.
                        </td>

                        <td>
                            192.168.1.24
                        </td>

                    </tr>


                    <tr>

                        <td class="log-date">
                            03/09/2026 10:21:03
                        </td>

                        <td>

                            <span class="log-badge log-warning">
                                Advertencia
                            </span>

                        </td>

                        <td class="log-user">
                            juan
                        </td>

                        <td class="log-action">
                            Cambio de configuración
                        </td>

                        <td>
                            Se ha modificado la configuración del comparador.
                        </td>

                        <td>
                            192.168.1.15
                        </td>

                    </tr>


                    <tr>

                        <td class="log-date">
                            03/09/2026 09:58:27
                        </td>

                        <td>

                            <span class="log-badge log-error">
                                Error
                            </span>

                        </td>

                        <td class="log-user">
                            juan
                        </td>

                        <td class="log-action">
                            Acceso fallido
                        </td>

                        <td>
                            Contraseña incorrecta.
                        </td>

                        <td>
                            192.168.1.15
                        </td>

                    </tr>


                    <tr>

                        <td class="log-date">
                            03/09/2026 09:45:12
                        </td>

                        <td>

                            <span class="log-badge log-success">
                                Éxito
                            </span>

                        </td>

                        <td class="log-user">
                            soporte
                        </td>

                        <td class="log-action">
                            Cliente creado
                        </td>

                        <td>
                            Se ha creado el cliente "Electricidad García S.L."
                        </td>

                        <td>
                            192.168.1.30
                        </td>

                    </tr>


                    <tr>

                        <td class="log-date">
                            03/09/2026 09:32:41
                        </td>

                        <td>

                            <span class="log-badge log-info">
                                Información
                            </span>

                        </td>

                        <td class="log-user">
                            admin
                        </td>

                        <td class="log-action">
                            Inicio de sesión
                        </td>

                        <td>
                            Inicio de sesión realizado correctamente.
                        </td>

                        <td>
                            192.168.1.10
                        </td>

                    </tr>


                    <tr>

                        <td class="log-date">
                            03/09/2026 09:15:22
                        </td>

                        <td>

                            <span class="log-badge log-success">
                                Éxito
                            </span>

                        </td>

                        <td class="log-user">
                            maria
                        </td>

                        <td class="log-action">
                            Tarifa actualizada
                        </td>

                        <td>
                            Se ha actualizado la tarifa "PVPC".
                        </td>

                        <td>
                            192.168.1.24
                        </td>

                    </tr>


                    <tr>

                        <td class="log-date">
                            03/09/2026 08:57:09
                        </td>

                        <td>

                            <span class="log-badge log-warning">
                                Advertencia
                            </span>

                        </td>

                        <td class="log-user">
                            admin
                        </td>

                        <td class="log-action">
                            Intento de acceso
                        </td>

                        <td>
                            Se detectó un intento de acceso desde un dispositivo nuevo.
                        </td>

                        <td>
                            192.168.1.50
                        </td>

                    </tr>


                    <tr>

                        <td class="log-date">
                            02/09/2026 18:43:55
                        </td>

                        <td>

                            <span class="log-badge log-success">
                                Éxito
                            </span>

                        </td>

                        <td class="log-user">
                            juan
                        </td>

                        <td class="log-action">
                            Cierre de sesión
                        </td>

                        <td>
                            Sesión cerrada correctamente.
                        </td>

                        <td>
                            192.168.1.15
                        </td>

                    </tr>


                    <tr>

                        <td class="log-date">
                            02/09/2026 17:25:31
                        </td>

                        <td>

                            <span class="log-badge log-info">
                                Información
                            </span>

                        </td>

                        <td class="log-user">
                            admin
                        </td>

                        <td class="log-action">
                            Usuario creado
                        </td>

                        <td>
                            Se ha creado el usuario "soporte".
                        </td>

                        <td>
                            192.168.1.10
                        </td>

                    </tr>


                    <tr>

                        <td class="log-date">
                            02/09/2026 16:48:17
                        </td>

                        <td>

                            <span class="log-badge log-error">
                                Error
                            </span>

                        </td>

                        <td class="log-user">
                            soporte
                        </td>

                        <td class="log-action">
                            Error de conexión
                        </td>

                        <td>
                            No se pudo conectar con el servidor de base de datos.
                        </td>

                        <td>
                            192.168.1.30
                        </td>

                    </tr>


                    <tr>

                        <td class="log-date">
                            02/09/2026 15:12:44
                        </td>

                        <td>

                            <span class="log-badge log-success">
                                Éxito
                            </span>

                        </td>

                        <td class="log-user">
                            admin
                        </td>

                        <td class="log-action">
                            Configuración modificada
                        </td>

                        <td>
                            Se ha cambiado el intervalo de actualización de tarifas.
                        </td>

                        <td>
                            192.168.1.10
                        </td>

                    </tr>


                </tbody>

            </table>


        </div>


        <!-- =========================
             PIE DE TABLA
        ========================== -->

        <div class="logs-footer">


            <span>
                Mostrando 12 registros
            </span>


            <div class="pagination">


                <button class="active">
                    1
                </button>

                <button>
                    2
                </button>

                <button>
                    3
                </button>

                <button>
                    →
                </button>


            </div>


        </div>


    </main>


</div>


<?php include 'templates/footer.php'; ?>

<script src="js/logs.js"></script>

</body>

</html>

