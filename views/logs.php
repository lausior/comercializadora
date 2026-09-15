<?php

session_start();

require_once __DIR__ . '/../config/permisos.php';
requerirPermiso('logs');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/filtro_multiselect.php';


// =====================================================
// CARGAR LOS LOGS VISIBLES PARA EL ROL ACTUAL
// =====================================================
//
// rolesVisiblesEnLogs() (config/permisos.php) decide qué
// ROLES puede ver cada uno en el listado: SRG ve todo, NG
// ve todo menos lo hecho por SRG, EMPRESA ve todo menos lo
// hecho por SRG y NG. No es un filtro por creado_por (no
// aplica aquí, un log no tiene "dueño"), es un filtro por
// el rol de quien generó cada evento.
//
// =====================================================

$rolesVisibles = rolesVisiblesEnLogs();

$logs = [];

if (!empty($rolesVisibles)) {

    $marcadores = implode(',', array_fill(0, count($rolesVisibles), '?'));

    $stmt = $pdo->prepare("
        SELECT
            fecha_hora,
            tipo,
            usuario,
            rol,
            evento,
            descripcion,
            ip
        FROM logs
        WHERE rol IN ($marcadores)
        ORDER BY fecha_hora DESC
    ");

    $stmt->execute($rolesVisibles);

    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

}


// =====================================================
// CLASE CSS DEL BADGE SEGÚN EL TIPO DE EVENTO
// =====================================================

function claseBadgeLog(string $tipo): string
{
    return match ($tipo) {
        'Éxito'       => 'log-success',
        'Información' => 'log-info',
        'Advertencia' => 'log-warning',
        'Error'       => 'log-error',
        default       => 'log-info',
    };
}


// =====================================================
// USUARIOS DISTINTOS PARA EL DESPLEGABLE DE FILTRO
// =====================================================

$usuariosFiltro = array_values(array_unique(array_column($logs, 'usuario')));
sort($usuariosFiltro);

$tiposFiltro = ['Información', 'Éxito', 'Advertencia', 'Error'];

?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Logs - Comparador Eléctrico</title>

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


        <div class="logs-header">

            <div>

                <h1>Logs</h1>

                <p>
                    Registro de actividad del sistema
                </p>

            </div>


            <div class="logs-actions">

                <button class="logs-btn" onclick="location.reload()">
                    🔄 Actualizar
                </button>

            </div>

        </div>


        <!-- =========================
             FILTROS
        ========================== -->

        <div class="logs-filters">


            <div class="filter-group">

                <label>
                    Tipo de evento
                </label>

                <?php filtroMultiSelect('tipo', 1, 'Todos', $tiposFiltro); ?>

            </div>


            <div class="filter-group">

                <label>
                    Usuario
                </label>

                <?php filtroMultiSelect('usuario', 2, 'Todos los usuarios', $usuariosFiltro); ?>

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


            <div class="filter-group">

                <label for="evento">
                    Evento
                </label>

                <input
                    type="text"
                    id="evento"
                    placeholder="Buscar evento..."
                >

            </div>


            <div class="filter-group">

                <label for="ip">
                    IP
                </label>

                <input
                    type="text"
                    id="ip"
                    placeholder="Buscar IP..."
                >

            </div>



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

                    <?php foreach ($logs as $log): ?>

                        <tr>

                            <td class="log-date">
                                <?= htmlspecialchars(date('d/m/Y H:i:s', strtotime($log['fecha_hora']))) ?>
                            </td>

                            <td>

                                <span class="log-badge <?= claseBadgeLog($log['tipo']) ?>">
                                    <?= htmlspecialchars($log['tipo']) ?>
                                </span>

                            </td>

                            <td class="log-user">
                                <?= htmlspecialchars($log['usuario']) ?>
                            </td>

                            <td class="log-action">
                                <?= htmlspecialchars($log['evento']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($log['descripcion']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($log['ip']) ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>


        </div>


        <!-- =========================
             PIE DE TABLA
        ========================== -->

        <div class="logs-footer">

            <div class="logs-per-page">

                <label for="logsPorPagina">
                    Mostrar:
                </label>

                <select id="logsPorPagina">
                    <option value="5" selected>5</option>
                    <option value="10">10</option>
                    <option value="all">Todos</option>
                </select>

            </div>

            <span>
                Mostrando 0 de 0 registros
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


    </main>


</div>


<?php include '../templates/footer.php'; ?>

<script src="../js/multi-select-filter.js"></script>
<script src="../js/logs.js"></script>

</body>

</html>
