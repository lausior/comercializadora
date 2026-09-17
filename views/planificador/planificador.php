<?php

session_start();

require_once __DIR__ . '/../../config/permisos.php';
requerirPermiso('planificador');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/fechas.php';
require_once __DIR__ . '/../../includes/filtro_multiselect.php';


// =====================================================
// MES QUE SE ESTÁ VIENDO
// =====================================================
//
// Viene en la URL como ?mes=YYYY-MM (los enlaces ‹ Hoy ›
// lo cambian). Si no viene o tiene un formato inválido,
// se usa el mes actual.
//
// =====================================================

$mesParam = $_GET['mes'] ?? date('Y-m');

$inicioMes = DateTime::createFromFormat('Y-m-d', $mesParam . '-01');

if (!$inicioMes) {
    $inicioMes = new DateTime(date('Y-m-01'));
}

$inicioMes->setTime(0, 0);

$mesActualUrl = $inicioMes->format('Y-m');


// =====================================================
// OBTENER TAREAS DE LA BASE DE DATOS
// =====================================================

$stmtTareas = $pdo->query("
    SELECT id, titulo, area, fecha, hora, responsable, estado, creado_por
    FROM tareas
    ORDER BY fecha ASC, hora ASC
");

$tareas = $stmtTareas->fetchAll(PDO::FETCH_ASSOC);


// =====================================================
// FILTRAR SEGÚN QUÉ TAREAS PUEDE VER EL ROL ACTUAL
// =====================================================
//
// SRG las ve todas. NG y EMPRESA solo ven las que ellos
// mismos han creado (puedeVerTarea() en permisos.php).
//
// =====================================================

$tareas = array_values(array_filter(
    $tareas,
    fn(array $tarea): bool =>
        puedeVerTarea($tarea['creado_por'] !== null ? (int) $tarea['creado_por'] : null)
));


// =====================================================
// ESTADO EFECTIVO
// =====================================================
//
// Una tarea "Pendiente" cuya fecha ya pasó se muestra como
// "Vencida", aunque en la BD su estado siga siendo
// "Pendiente" - así no hace falta ir actualizando estados
// a mano cada día, se calcula solo al leerla.
//
// =====================================================

$hoy = date('Y-m-d');

function estadoEfectivoTarea(array $tarea, string $hoy): string
{
    if ($tarea['estado'] === 'Pendiente' && $tarea['fecha'] < $hoy) {
        return 'Vencida';
    }

    return $tarea['estado'];
}

foreach ($tareas as &$tarea) {
    $tarea['estado_efectivo'] = estadoEfectivoTarea($tarea, $hoy);
}
unset($tarea);


// =====================================================
// CLASES CSS SEGÚN ÁREA Y ESTADO
// =====================================================

function claseAreaTarea(string $area): string
{
    return match ($area) {
        'Tarifas'     => 'blue-task',
        'Clientes'    => 'green-task',
        'Incidencias' => 'orange-task',
        'Comparador'  => 'purple-task',
        'Sistema'     => 'gray-task',
        default       => 'gray-task',
    };
}

function claseEstadoTarea(string $estadoEfectivo): string
{
    return match ($estadoEfectivo) {
        'Pendiente'  => 'status-pending',
        'En curso'   => 'status-progress',
        'Completada' => 'status-complete',
        'Vencida'    => 'status-overdue',
        default      => 'status-pending',
    };
}


// =====================================================
// AGRUPAR TAREAS POR FECHA (para pintar el calendario)
// =====================================================

$tareasPorFecha = [];

foreach ($tareas as $tarea) {
    $tareasPorFecha[$tarea['fecha']][] = $tarea;
}


// =====================================================
// CONSTRUIR LA CUADRÍCULA DEL MES
// =====================================================
//
// Se completan las semanas con días del mes anterior/
// siguiente (en gris, sin tareas) para que la cuadrícula
// siempre tenga semanas completas de lunes a domingo.
//
// =====================================================

$diasEnMes = (int) $inicioMes->format('t');
$diaSemanaInicio = (int) $inicioMes->format('N'); // 1=lunes ... 7=domingo
$celdasAntes = $diaSemanaInicio - 1;

$primerDiaGrid = (clone $inicioMes)->modify('-' . $celdasAntes . ' days');

$totalCeldas = $celdasAntes + $diasEnMes;
$totalFilas = (int) ceil($totalCeldas / 7);
$totalCeldasGrid = $totalFilas * 7;

$diasCalendario = [];

for ($i = 0; $i < $totalCeldasGrid; $i++) {
    $diasCalendario[] = (clone $primerDiaGrid)->modify('+' . $i . ' days');
}

$semanas = array_chunk($diasCalendario, 7);

$diasSemanaAbrev = ['LUN', 'MAR', 'MIÉ', 'JUE', 'VIE', 'SÁB', 'DOM'];


// =====================================================
// NAVEGACIÓN ENTRE MESES
// =====================================================

$mesAnterior = (clone $inicioMes)->modify('-1 month')->format('Y-m');
$mesSiguiente = (clone $inicioMes)->modify('+1 month')->format('Y-m');
$mesHoy = date('Y-m');


// =====================================================
// TARJETAS RESUMEN (del mes que se está viendo)
// =====================================================

$tareasDelMes = array_filter(
    $tareas,
    fn(array $tarea): bool => substr($tarea['fecha'], 0, 7) === $mesActualUrl
);

$pendientesMes = 0;
$enCursoMes = 0;
$completadasMes = 0;
$vencidasMes = 0;

foreach ($tareasDelMes as $tarea) {

    switch ($tarea['estado_efectivo']) {

        case 'Pendiente':
            $pendientesMes++;
            break;

        case 'En curso':
            $enCursoMes++;
            break;

        case 'Completada':
            $completadasMes++;
            break;

        case 'Vencida':
            $vencidasMes++;
            break;

    }

}


// =====================================================
// PRÓXIMAS TAREAS (de cualquier mes, no solo el visible)
// =====================================================
//
// $tareas ya viene ordenado por fecha/hora ascendente
// desde el SELECT, así que basta con filtrar.
//
// =====================================================

$proximasTareas = array_values(array_filter(
    $tareas,
    fn(array $tarea): bool =>
        $tarea['fecha'] >= $hoy && $tarea['estado'] !== 'Completada'
));

$proximasTareas = array_slice($proximasTareas, 0, 5);


// =====================================================
// TAREAS RECIENTES (las últimas creadas)
// =====================================================

$tareasRecientes = $tareas;

usort($tareasRecientes, fn(array $a, array $b): int => (int) $b['id'] <=> (int) $a['id']);

$tareasRecientes = array_slice($tareasRecientes, 0, 8);


// =====================================================
// VALORES DEL FILTRO DE ESTADO
// =====================================================

$estadosFiltroTarea = ['Pendiente', 'En curso', 'Completada', 'Vencida'];

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Planificador - Comparador Eléctrico</title>

    <link rel="stylesheet" href="../../css/style.css">
</head>

<body>

    <!-- =========================
         HEADER
    ========================== -->
    <?php include '../../templates/header.php'; ?>


    <!-- =========================
         CONTENEDOR PRINCIPAL
    ========================== -->
    <div class="app-container">


        <!-- =========================
             SIDEBAR
        ========================== -->
        <?php include '../../templates/sidebar.php'; ?>



        <!-- =========================
             CONTENIDO
        ========================== -->

        <main class="main-content">

            <!-- CABECERA -->
            <div class="page-header">

                <div>
                    <h1>Planificador</h1>
                    <p>Organiza y gestiona las tareas del sistema</p>
                </div>

                <div class="page-header-actions">

                    <div class="page-date">
                        <?= htmlspecialchars(fechaLargaEs(new DateTime($hoy))) ?>
                    </div>

                    <a href="crear_tarea.php?fecha=<?= htmlspecialchars($hoy) ?>" class="config-save-button">
                        + Nueva planificación
                    </a>

                </div>

            </div>


            <!-- RESUMEN (del mes que se está viendo) -->
            <section class="dashboard-cards">

                <div class="dashboard-card">

                    <div class="card-icon blue">
                        ▣
                    </div>

                    <div class="card-info">
                        <span class="card-label">Tareas pendientes</span>
                        <strong><?= $pendientesMes ?></strong>
                        <small>Este mes</small>
                    </div>

                </div>


                <div class="dashboard-card">

                    <div class="card-icon orange">
                        ◷
                    </div>

                    <div class="card-info">
                        <span class="card-label">En curso</span>
                        <strong><?= $enCursoMes ?></strong>
                        <small>Este mes</small>
                    </div>

                </div>


                <div class="dashboard-card">

                    <div class="card-icon green">
                        ✓
                    </div>

                    <div class="card-info">
                        <span class="card-label">Completadas</span>
                        <strong><?= $completadasMes ?></strong>
                        <small>Este mes</small>
                    </div>

                </div>


                <div class="dashboard-card">

                    <div class="card-icon purple">
                        !
                    </div>

                    <div class="card-info">
                        <span class="card-label">Vencidas</span>
                        <strong><?= $vencidasMes ?></strong>
                        <small>Requieren atención</small>
                    </div>

                </div>

            </section>


            <!-- CONTROLES -->
            <section class="planner-toolbar">

                <div class="planner-filters">

                    <?php filtroMultiSelect('filtroEstadoTarea', 0, 'Todos los estados', $estadosFiltroTarea); ?>

                    <input type="text" id="filtroResponsableTarea" class="planner-select"
                        placeholder="Buscar responsable...">

                </div>

            </section>


            <!-- PLANIFICADOR -->
            <section class="planner-grid">


                <!-- CALENDARIO -->
                <div class="panel planner-calendar">

                    <div class="panel-header">

                        <div>
                            <h2>
                                <?= htmlspecialchars(nombreMesEs((int) $inicioMes->format('n'))) ?>
                                <?= $inicioMes->format('Y') ?>
                            </h2>
                            <p>Planificación mensual</p>
                        </div>

                        <div class="calendar-navigation">

                            <a class="calendar-button" href="?mes=<?= $mesAnterior ?>">
                                ‹
                            </a>

                            <a class="calendar-today" href="?mes=<?= $mesHoy ?>">
                                Hoy
                            </a>

                            <a class="calendar-button" href="?mes=<?= $mesSiguiente ?>">
                                ›
                            </a>

                        </div>

                    </div>


                    <div class="calendar-month">

                        <?php foreach ($semanas as $indiceSemana => $semana): ?>

                            <div class="calendar-week">

                                <?php foreach ($semana as $indiceDia => $diaFecha): ?>

                                    <?php

                                    $claveFecha = $diaFecha->format('Y-m-d');
                                    $esDelMes = $diaFecha->format('Y-m') === $mesActualUrl;
                                    $esHoy = $claveFecha === $hoy;
                                    $esFinDeSemana = (int) $diaFecha->format('N') >= 6;

                                    $tareasDia = $tareasPorFecha[$claveFecha] ?? [];

                                    $claseDia = 'calendar-day';
                                    if (!$esDelMes) {
                                        $claseDia .= ' otro-mes';
                                    }
                                    if ($esFinDeSemana) {
                                        $claseDia .= ' weekend';
                                    }
                                    if ($esHoy) {
                                        $claseDia .= ' today';
                                    }

                                    ?>

                                    <div class="<?= $claseDia ?>">

                                        <div class="calendar-day-header">

                                            <span>
                                                <?= $indiceSemana === 0 ? $diasSemanaAbrev[$indiceDia] : '' ?>
                                            </span>

                                            <strong><?= (int) $diaFecha->format('j') ?></strong>

                                            <a class="calendar-add-task" title="Añadir tarea este día"
                                                href="crear_tarea.php?fecha=<?= $claveFecha ?>">
                                                +
                                            </a>

                                        </div>

                                        <?php foreach (array_slice($tareasDia, 0, 3) as $tareaDia): ?>

                                            <a
                                                class="calendar-task <?= claseAreaTarea($tareaDia['area']) ?>"
                                                data-estado="<?= htmlspecialchars($tareaDia['estado_efectivo']) ?>"
                                                data-responsable="<?= htmlspecialchars(strtolower($tareaDia['responsable'] ?? '')) ?>"
                                                href="editar_tarea.php?id=<?= (int) $tareaDia['id'] ?>"
                                            >
                                                <strong><?= htmlspecialchars($tareaDia['titulo']) ?></strong>
                                                <small>
                                                    <?= $tareaDia['hora'] ? substr($tareaDia['hora'], 0, 5) . ' · ' : '' ?><?= htmlspecialchars($tareaDia['area']) ?>
                                                </small>
                                            </a>

                                        <?php endforeach; ?>

                                        <?php if (count($tareasDia) > 3): ?>

                                            <div class="calendar-task-mas">
                                                +<?= count($tareasDia) - 3 ?> más
                                            </div>

                                        <?php endif; ?>

                                    </div>

                                <?php endforeach; ?>

                            </div>

                        <?php endforeach; ?>

                    </div>

                </div>


                <!-- PRÓXIMAS TAREAS -->
                <div class="panel upcoming-panel">

                    <div class="panel-header">

                        <div>
                            <h2>Próximas tareas</h2>
                            <p>Tareas programadas</p>
                        </div>

                    </div>


                    <div class="upcoming-list">

                        <?php if (empty($proximasTareas)): ?>

                            <p class="form-info">No hay tareas próximas.</p>

                        <?php endif; ?>

                        <?php foreach ($proximasTareas as $tareaProxima): ?>

                            <a class="upcoming-item" href="editar_tarea.php?id=<?= (int) $tareaProxima['id'] ?>">

                                <div class="upcoming-date">
                                    <strong><?= date('d', strtotime($tareaProxima['fecha'])) ?></strong>
                                    <span><?= mb_strtoupper(substr(nombreMesEs((int) date('n', strtotime($tareaProxima['fecha']))), 0, 3), 'UTF-8') ?></span>
                                </div>

                                <div class="upcoming-content">
                                    <strong><?= htmlspecialchars($tareaProxima['titulo']) ?></strong>
                                    <span>
                                        <?= htmlspecialchars($tareaProxima['area']) ?>
                                        <?= $tareaProxima['hora'] ? ' · ' . substr($tareaProxima['hora'], 0, 5) : '' ?>
                                    </span>
                                </div>

                                <span class="status-badge <?= claseEstadoTarea($tareaProxima['estado_efectivo']) ?>">
                                    <?= htmlspecialchars($tareaProxima['estado_efectivo']) ?>
                                </span>

                            </a>

                        <?php endforeach; ?>

                    </div>

                </div>

            </section>


            <!-- TAREAS RECIENTES -->
            <section class="panel planner-tasks-panel">

                <div class="panel-header">

                    <div>
                        <h2>Tareas recientes</h2>
                        <p>Últimas tareas gestionadas</p>
                    </div>

                </div>


                <div class="planner-table">

                    <div class="planner-table-header">
                        <span>Tarea</span>
                        <span>Área</span>
                        <span>Responsable</span>
                        <span>Fecha</span>
                        <span>Estado</span>
                        <span>Acciones</span>
                    </div>

                    <?php if (empty($tareasRecientes)): ?>

                        <div class="planner-table-row">
                            <span>No hay tareas todavía.</span>
                        </div>

                    <?php endif; ?>

                    <?php foreach ($tareasRecientes as $tareaReciente): ?>

                        <div class="planner-table-row" data-estado="<?= htmlspecialchars($tareaReciente['estado_efectivo']) ?>"
                            data-responsable="<?= htmlspecialchars(strtolower($tareaReciente['responsable'] ?? '')) ?>">

                            <span>
                                <?= htmlspecialchars($tareaReciente['titulo']) ?>
                            </span>

                            <span>
                                <?= htmlspecialchars($tareaReciente['area']) ?>
                            </span>

                            <span>
                                <?= htmlspecialchars($tareaReciente['responsable'] ?: '—') ?>
                            </span>

                            <span>
                                <?= date('d/m/Y', strtotime($tareaReciente['fecha'])) ?>
                            </span>

                            <span class="status-badge <?= claseEstadoTarea($tareaReciente['estado_efectivo']) ?>">
                                <?= htmlspecialchars($tareaReciente['estado_efectivo']) ?>
                            </span>

                            <span class="planner-table-actions">

                                <button type="button" class="table-action-button"
                                    onclick="window.location.href='editar_tarea.php?id=<?= (int) $tareaReciente['id'] ?>'">
                                    Editar
                                </button>

                                <button type="button" class="table-action-button danger" onclick="abrirModalEliminarTarea(
                                    <?= (int) $tareaReciente['id'] ?>,
                                    '<?= htmlspecialchars($tareaReciente['titulo'], ENT_QUOTES, 'UTF-8') ?>'
                                )">
                                    Eliminar
                                </button>

                            </span>

                        </div>

                    <?php endforeach; ?>

                </div>

            </section>

        </main>



    </div>


    <!-- =========================================================
         MODAL CONFIRMAR ELIMINACIÓN
    ========================================================= -->

    <div id="modalEliminarTarea" class="modal-overlay" style="display: none;">

        <div class="modal-confirmacion">

            <div class="modal-icon">
                ⚠
            </div>

            <h2>Eliminar tarea</h2>

            <p>
                ¿Estás seguro de que quieres eliminar la tarea
                <strong id="nombreTareaEliminar"></strong>?
            </p>

            <p class="modal-warning">
                Esta acción no se puede deshacer.
            </p>

            <div class="modal-actions">

                <button type="button" class="modal-button modal-button-cancel" onclick="cerrarModalEliminarTarea()">
                    Cancelar
                </button>

                <button type="button" class="modal-button modal-button-delete" onclick="confirmarEliminarTarea()">
                    Eliminar tarea
                </button>

            </div>

        </div>

    </div>


    <!-- =========================
         FOOTER
    ========================== -->
    <?php include '../../templates/footer.php'; ?>

    <script src="../../js/multi-select-filter.js"></script>
    <script src="../../js/planificador.js"></script>

</body>

</html>
