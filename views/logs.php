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
            id,
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
        'Éxito' => 'log-success',
        'Información' => 'log-info',
        'Advertencia' => 'log-warning',
        'Error' => 'log-error',
        default => 'log-info',
    };
}


// =====================================================
// USUARIOS DISTINTOS PARA EL DESPLEGABLE DE FILTRO
// =====================================================

$usuariosFiltro = array_values(array_unique(array_column($logs, 'usuario')));
sort($usuariosFiltro);

$tiposFiltro = ['Información', 'Éxito', 'Advertencia', 'Error'];


// =====================================================
// VALORES DISTINTOS PARA LOS DESPLEGABLES DE FECHA/
// EVENTO/IP
// =====================================================

$fechasFiltro = array_values(array_unique(array_map(
    fn(array $log): string => date('d/m/Y', strtotime($log['fecha_hora'])),
    $logs
)));

usort(
    $fechasFiltro,
    fn(string $a, string $b): int =>
        DateTime::createFromFormat('d/m/Y', $a) <=> DateTime::createFromFormat('d/m/Y', $b)
);

$eventosFiltro = array_values(array_unique(array_column($logs, 'evento')));
sort($eventosFiltro);

$descripcionesFiltro = array_values(array_unique(array_column($logs, 'descripcion')));
sort($descripcionesFiltro);

$ipsFiltro = array_values(array_unique(array_column($logs, 'ip')));
sort($ipsFiltro);

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

                    <button type="button" class="logs-btn" id="btnExportarPDF">
                        📄 Exportar PDF
                    </button>

                </div>

            </div>


            <!-- =========================
             TABLA DE LOGS
             (tabla, filtros por columna, "mostrar por
             página" y paginación, todo dentro de la misma
             "caja", igual que en Clientes/Usuarios/Empresas)
        ========================== -->

            <section class="panel logs-table-panel">

                <div class="panel-header">

                    <div>
                        <h2>Registro de eventos</h2>
                        <p id="logsContador">
                            <?= count($logs) ?>
                            <?= count($logs) === 1 ? 'registro encontrado' : 'registros encontrados' ?>
                        </p>
                    </div>

                    <div class="panel-header-actions"
                        style="display:flex; gap:10px; align-items:center; flex-wrap: wrap;">

                        <!-- Solo en móvil: despliega el panel de filtros apilados -->
                        <button type="button" class="filtros-toggle-button vista-movil" id="btnToggleFiltros"
                            aria-expanded="false" aria-controls="panelFiltrosLogs">
                            <span>Filtros</span>
                            <span class="chevron">▾</span>
                        </button>

                    </div>

                </div>


                <!-- =================================================
                     PANEL DE FILTROS DESPLEGABLE (SOLO MÓVIL)
                ================================================== -->

                <div class="filtros-panel vista-movil" id="panelFiltrosLogs" style="display:none;">

                    <div class="filtros-panel-campos">

                        <div class="filter-group">
                            <label>Usuario</label>
                            <?php filtroMultiSelect('filtroUsuarioMovil', 0, 'Todos', $usuariosFiltro); ?>
                        </div>

                        <div class="filter-group">
                            <label>Tipo</label>
                            <?php filtroMultiSelect('filtroTipoMovil', 1, 'Todos', $tiposFiltro); ?>
                        </div>

                        <div class="filter-group">
                            <label>Evento</label>
                            <?php filtroMultiSelect('filtroEventoMovil', 2, 'Todos', $eventosFiltro); ?>
                        </div>

                        <div class="filter-group">
                            <label>Descripción</label>
                            <?php filtroMultiSelect('filtroDescripcionMovil', 3, 'Todas', $descripcionesFiltro); ?>
                        </div>

                        <div class="filter-group">
                            <label>Fecha</label>
                            <?php filtroMultiSelect('filtroFechaMovil', 4, 'Todas las fechas', $fechasFiltro); ?>
                        </div>

                        <div class="filter-group">
                            <label>IP</label>
                            <?php filtroMultiSelect('filtroIpMovil', 5, 'Todas', $ipsFiltro); ?>
                        </div>

                    </div>

                    <div class="filtros-panel-acciones">
                        <button type="button" class="panel-action" id="btnLimpiarFiltrosMovil">
                            Limpiar filtros
                        </button>
                    </div>

                </div>


                <div class="logs-table-container">


                    <table class="logs-table">


                        <thead>

                            <tr>

                                <th>
                                    <div class="table-header-content">
                                        <span>Usuario</span>
                                        <button type="button" class="sort-button" data-column="0" title="Ordenar por usuario">
                                            ↕
                                        </button>
                                    </div>
                                </th>


                                <th class="vista-escritorio">
                                    <div class="table-header-content">
                                        <span>Tipo</span>
                                        <button type="button" class="sort-button" data-column="1" title="Ordenar por tipo">
                                            ↕
                                        </button>
                                    </div>
                                </th>


                                <th class="vista-escritorio">
                                    <div class="table-header-content">
                                        <span>Evento</span>
                                        <button type="button" class="sort-button" data-column="2" title="Ordenar por evento">
                                            ↕
                                        </button>
                                    </div>
                                </th>

                                <th class="vista-escritorio">
                                    <div class="table-header-content">
                                        <span>Descripción</span>
                                        <button type="button" class="sort-button" data-column="3" title="Ordenar por descripción">
                                            ↕
                                        </button>
                                    </div>
                                </th>

                                <th class="vista-escritorio">
                                    <div class="table-header-content">
                                        <span>Fecha y hora</span>
                                        <button type="button" class="sort-button" data-column="4" title="Ordenar por fecha">
                                            ↕
                                        </button>
                                    </div>
                                </th>

                                <th class="vista-escritorio">
                                    <div class="table-header-content">
                                        <span>IP</span>
                                        <button type="button" class="sort-button" data-column="5" title="Ordenar por IP">
                                            ↕
                                        </button>
                                    </div>
                                </th>

                            </tr>


                            <!-- =================================================
                                 FILTROS POR COLUMNA (SOLO ESCRITORIO)
                            ================================================== -->

                            <tr class="logs-filter-row-table vista-escritorio">


                                <th>
                                    <?php filtroMultiSelect('filtroUsuarioEscritorio', 0, 'Todos', $usuariosFiltro); ?>
                                </th>

                                <th>
                                    <?php filtroMultiSelect('filtroTipoEscritorio', 1, 'Todos', $tiposFiltro); ?>
                                </th>


                                <th>
                                    <?php filtroMultiSelect('filtroEventoEscritorio', 2, 'Todos', $eventosFiltro); ?>
                                </th>

                                <th>
                                    <?php filtroMultiSelect('filtroDescripcionEscritorio', 3, 'Todas', $descripcionesFiltro); ?>
                                </th>

                                <th>
                                    <?php filtroMultiSelect('filtroFechaEscritorio', 4, 'Todas las fechas', $fechasFiltro); ?>
                                </th>

                                <th>
                                    <?php filtroMultiSelect('filtroIpEscritorio', 5, 'Todas', $ipsFiltro); ?>
                                </th>

                                <th>
                                    <button type="button" class="panel-action" id="btnLimpiarFiltros">
                                        Limpiar filtros
                                    </button>
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php foreach ($logs as $log): ?>

                                <?php

                                $inicialesLog = mb_strtoupper(mb_substr($log['tipo'], 0, 1, 'UTF-8'), 'UTF-8');
                                $fechaMostrada = date('d/m/Y H:i:s', strtotime($log['fecha_hora']));

                                ?>

                                <!-- fila-detalle: en móvil, pulsar la fila abre la
                                     tarjeta con toda la información (ver logs.js);
                                     en escritorio no hace nada, ahí ya se ve todo. -->

                                <tr class="fila-detalle" data-id="<?= (int) $log['id'] ?>"
                                    data-usuario="<?= htmlspecialchars($log['usuario'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-tipo="<?= htmlspecialchars($log['tipo'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-evento="<?= htmlspecialchars($log['evento'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-descripcion="<?= htmlspecialchars($log['descripcion'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-fecha="<?= htmlspecialchars($fechaMostrada, ENT_QUOTES, 'UTF-8') ?>"
                                    data-fecha-orden="<?= htmlspecialchars(date('Y-m-d H:i:s', strtotime($log['fecha_hora'])), ENT_QUOTES, 'UTF-8') ?>"
                                    data-fecha-filtro="<?= htmlspecialchars(date('d/m/Y', strtotime($log['fecha_hora'])), ENT_QUOTES, 'UTF-8') ?>"
                                    data-ip="<?= htmlspecialchars($log['ip'], ENT_QUOTES, 'UTF-8') ?>">

                                    <td>

                                        <div class="usuario-cell">

                                            <div class="log-avatar <?= claseBadgeLog($log['tipo']) ?>">
                                                <?= htmlspecialchars($inicialesLog) ?>
                                            </div>

                                            <div class="usuario-info">

                                                <strong>
                                                    <?= htmlspecialchars($log['usuario']) ?>
                                                </strong>

                                                <span>
                                                    <?= htmlspecialchars($log['evento']) ?>
                                                </span>

                                            </div>

                                        </div>

                                    </td>

                                    <td class="vista-escritorio">
                                        <span class="log-badge <?= claseBadgeLog($log['tipo']) ?>">
                                            <?= htmlspecialchars($log['tipo']) ?>
                                        </span>
                                    </td>

                                    <td class="vista-escritorio log-action">
                                        <?= htmlspecialchars($log['evento']) ?>
                                    </td>

                                    <td class="vista-escritorio">
                                        <?= htmlspecialchars($log['descripcion']) ?>
                                    </td>

                                    <td class="vista-escritorio log-date">
                                        <?= htmlspecialchars($fechaMostrada) ?>
                                    </td>

                                    <td class="vista-escritorio">
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

                    <span id="logsMostrando">
                        Mostrando 0 de 0 registros
                    </span>


                    <div class="pagination-buttons">

                        <button type="button" class="pagination-button disabled" data-page="prev">
                            ‹
                        </button>

                        <button type="button" class="pagination-button active" data-page="1">
                            1
                        </button>

                        <button type="button" class="pagination-button" data-page="next">
                            ›
                        </button>

                    </div>


                </div>

            </section>


        </main>


    </div>


    <?php include '../templates/footer.php'; ?>


    <!-- =====================================================
     VENTANA DE DETALLE
====================================================== -->

    <div id="modalDetalleLog" class="modal-overlay" style="display: none;">

        <div class="modal-detalle">

            <div class="modal-detalle-header">

                <div class="modal-detalle-avatar" id="detalleLogAvatar"></div>

                <div class="modal-detalle-titulo">
                    <h2 id="detalleLogEvento"></h2>
                    <span id="detalleLogFecha"></span>
                </div>

                <button type="button" class="modal-detalle-close" onclick="window.cerrarModalDetalleLog()"
                    aria-label="Cerrar">
                    ✕
                </button>

            </div>

            <div class="usuario-detalle-grid">

                <div class="usuario-detalle-item">
                    <span>Usuario</span>
                    <strong id="detalleLogUsuario"></strong>
                </div>

                <div class="usuario-detalle-item">
                    <span>Tipo</span>
                    <strong><span id="detalleLogTipo" class="log-badge"></span></strong>
                </div>

                <div class="usuario-detalle-item">
                    <span>Evento</span>
                    <strong id="detalleLogEventoGrid"></strong>
                </div>

                <div class="usuario-detalle-item">
                    <span>Descripción</span>
                    <strong id="detalleLogDescripcion"></strong>
                </div>

                <div class="usuario-detalle-item">
                    <span>Fecha</span>
                    <strong id="detalleLogFechaGrid"></strong>
                </div>

                <div class="usuario-detalle-item">
                    <span>IP</span>
                    <strong id="detalleLogIp"></strong>
                </div>

            </div>

        </div>

    </div>


    <script src="../js/exportar-pdf.js"></script>
    <script src="../js/multi-select-filter.js"></script>
    <script src="../js/logs.js"></script>
    <script src="../js/modal-detalle.js"></script>

</body>

</html>