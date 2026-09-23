<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('comercializadoras');

require_once '../../config/database.php';
require_once '../../includes/filtro_multiselect.php';


// =====================================================
// OBTENER COMERCIALIZADORAS DE LA BASE DE DATOS
// =====================================================

$stmtComercializadoras = $pdo->query("
    SELECT
        id,
        nombre,
        cif,
        direccion,
        telefono,
        email,
        suministra_luz,
        suministra_gas,
        creado_por
    FROM comercializadoras
    ORDER BY nombre
");

$comercializadoras = $stmtComercializadoras->fetchAll(PDO::FETCH_ASSOC);


// =====================================================
// FILTRAR SEGÚN QUÉ COMERCIALIZADORAS PUEDE VER EL ROL ACTUAL
// =====================================================
//
// SRG las ve todas. NG y EMPRESA solo las que ellos mismos
// han creado (ver puedeVerComercializadora() en permisos.php).
//
// =====================================================

$comercializadoras = array_values(array_filter(
    $comercializadoras,
    fn(array $comercializadora): bool =>
        puedeVerComercializadora(
            $comercializadora['creado_por'] !== null ? (int) $comercializadora['creado_por'] : null
        )
));

$totalComercializadoras = count($comercializadoras);


// =====================================================
// VALORES DISTINTOS PARA LOS DESPLEGABLES DE FILTRO
// =====================================================
//
// La lista de opciones de cada desplegable son los valores
// que realmente aparecen en $comercializadoras (ya filtrado
// por permisos).
//
// =====================================================

$nombresFiltro = array_values(array_unique(array_column($comercializadoras, 'nombre')));
sort($nombresFiltro);

$cifsFiltro = array_values(array_unique(array_column($comercializadoras, 'cif')));
sort($cifsFiltro);

$direccionesFiltro = array_values(array_unique(array_filter(
    array_column($comercializadoras, 'direccion')
)));
sort($direccionesFiltro);

$telefonosFiltro = array_values(array_unique(array_filter(
    array_column($comercializadoras, 'telefono')
)));
sort($telefonosFiltro);

$emailsFiltro = array_values(array_unique(array_filter(
    array_column($comercializadoras, 'email')
)));
sort($emailsFiltro);


// =====================================================
// ETIQUETA DE SERVICIOS (LUZ / GAS / LUZ Y GAS)
// =====================================================
//
// Al crear/editar se exige marcar al menos uno de los dos
// (ver guardar_comercializadora.php/actualizar_comercializadora.php),
// así que aquí siempre hay algo que mostrar.
//
// =====================================================

function etiquetaServiciosComercializadora(array $comercializadora): string
{
    if ($comercializadora['suministra_luz'] && $comercializadora['suministra_gas']) {
        return 'Luz y gas';
    }

    if ($comercializadora['suministra_luz']) {
        return 'Luz';
    }

    if ($comercializadora['suministra_gas']) {
        return 'Gas';
    }

    return '—';
}

$serviciosFiltro = ['Luz', 'Gas', 'Luz y gas'];

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Comercializadoras - Comparador Eléctrico</title>

    <link rel="stylesheet" href="../../css/style.css">

</head>

<body>

    <?php include '../../templates/header.php'; ?>

    <div class="app-container">

        <?php include '../../templates/sidebar.php'; ?>

        <main class="main-content">

            <!-- =================================================
                 CABECERA
            ================================================== -->

            <div class="page-header">

                <div>

                    <h1>Comercializadoras</h1>

                    <p>
                        Gestión y consulta de las comercializadoras registradas
                    </p>

                </div>

                <div class="page-header-actions">

                    <button type="button" class="config-secondary-button" id="btnExportarPDF">
                        📄 Exportar PDF
                    </button>

                    <a href="crear_comercializadora.php" class="config-save-button">
                        + Añadir comercializadora
                    </a>

                </div>

            </div>


            <!-- =================================================
                 RESUMEN
            ================================================== -->

            <section class="dashboard-cards">

                <div class="dashboard-card">

                    <div class="card-icon blue">
                        🏬
                    </div>

                    <div class="card-info">

                        <span class="card-label">
                            Total comercializadoras
                        </span>

                        <strong>
                            <?= $totalComercializadoras ?>
                        </strong>

                    </div>

                </div>

            </section>


            <!-- =====================================================
                 TABLA DE COMERCIALIZADORAS
            ====================================================== -->

            <section class="panel usuarios-table-panel">

                <div class="panel-header">

                    <div>
                        <h2>Comercializadoras registradas</h2>
                        <p id="comercializadorasContador">

                            <?= $totalComercializadoras ?>

                            <?= $totalComercializadoras === 1 ? 'comercializadora encontrada' : 'comercializadoras encontradas' ?>

                        </p>
                    </div>

                    <div class="panel-header-actions"
                        style="display:flex; gap:10px; align-items:center; flex-wrap: wrap;">

                        <!-- Solo en móvil: despliega el panel de filtros apilados -->
                        <button type="button" class="filtros-toggle-button vista-movil" id="btnToggleFiltros"
                            aria-expanded="false" aria-controls="panelFiltrosComercializadoras">
                            <span>Filtros</span>
                            <span class="chevron">▾</span>
                        </button>

                    </div>

                </div>


                <!-- =================================================
                     PANEL DE FILTROS DESPLEGABLE (SOLO MÓVIL)
                ================================================== -->

                <div class="filtros-panel vista-movil" id="panelFiltrosComercializadoras" style="display:none;">

                    <div class="filtros-panel-campos">

                        <div class="filter-group">
                            <label>Comercializadora</label>
                            <?php filtroMultiSelect('filtroNombreMovil', 0, 'Todas', $nombresFiltro); ?>
                        </div>

                        <div class="filter-group">
                            <label>CIF</label>
                            <?php filtroMultiSelect('filtroCifMovil', 1, 'Todos', $cifsFiltro); ?>
                        </div>

                        <div class="filter-group">
                            <label>Dirección</label>
                            <?php filtroMultiSelect('filtroDireccionMovil', 2, 'Todas', $direccionesFiltro); ?>
                        </div>

                        <div class="filter-group">
                            <label>Teléfono</label>
                            <?php filtroMultiSelect('filtroTelefonoMovil', 3, 'Todos', $telefonosFiltro); ?>
                        </div>

                        <div class="filter-group">
                            <label>Email</label>
                            <?php filtroMultiSelect('filtroEmailMovil', 4, 'Todos', $emailsFiltro); ?>
                        </div>

                        <div class="filter-group">
                            <label>Servicios</label>
                            <?php filtroMultiSelect('filtroServiciosMovil', 5, 'Todos', $serviciosFiltro); ?>
                        </div>

                    </div>

                    <div class="filtros-panel-acciones">
                        <button type="button" class="panel-action" id="btnLimpiarFiltrosMovil">
                            Limpiar filtros
                        </button>
                    </div>

                </div>


                <div class="usuarios-table-container">

                    <table class="usuarios-table">

                        <thead>

                            <!-- =================================================
                                 CABECERAS (las columnas 2ª en adelante y la
                                 fila de filtros solo se ven en escritorio;
                                 en móvil el CSS las oculta y deja solo
                                 "Comercializadora")
                            ================================================== -->

                            <tr>

                                <th>
                                    <div class="table-header-content">
                                        <span>Comercializadora</span>

                                        <button type="button" class="sort-button" data-column="0"
                                            title="Ordenar por comercializadora">
                                            ↕
                                        </button>
                                    </div>
                                </th>

                                <th class="vista-escritorio">
                                    <div class="table-header-content">
                                        <span>CIF</span>

                                        <button type="button" class="sort-button" data-column="1"
                                            title="Ordenar por CIF">
                                            ↕
                                        </button>
                                    </div>
                                </th>

                                <th class="vista-escritorio">
                                    <div class="table-header-content">
                                        <span>Dirección</span>

                                        <button type="button" class="sort-button" data-column="2"
                                            title="Ordenar por dirección">
                                            ↕
                                        </button>
                                    </div>
                                </th>

                                <th class="vista-escritorio">
                                    <div class="table-header-content">
                                        <span>Teléfono</span>

                                        <button type="button" class="sort-button" data-column="3"
                                            title="Ordenar por teléfono">
                                            ↕
                                        </button>
                                    </div>
                                </th>

                                <th class="vista-escritorio">
                                    <div class="table-header-content">
                                        <span>Email</span>

                                        <button type="button" class="sort-button" data-column="4"
                                            title="Ordenar por email">
                                            ↕
                                        </button>
                                    </div>
                                </th>

                                <th class="vista-escritorio">
                                    <div class="table-header-content">
                                        <span>Servicios</span>

                                        <button type="button" class="sort-button" data-column="5"
                                            title="Ordenar por servicios">
                                            ↕
                                        </button>
                                    </div>
                                </th>

                                <th class="vista-escritorio">
                                    <span>Acciones</span>
                                </th>

                            </tr>


                            <!-- =================================================
                                 FILTROS POR COLUMNA (SOLO ESCRITORIO)
                            ================================================== -->

                            <tr class="usuarios-filter-row-table vista-escritorio">

                                <th>
                                    <?php filtroMultiSelect('filtroNombreEscritorio', 0, 'Todas', $nombresFiltro); ?>
                                </th>

                                <th>
                                    <?php filtroMultiSelect('filtroCifEscritorio', 1, 'Todos', $cifsFiltro); ?>
                                </th>

                                <th>
                                    <?php filtroMultiSelect('filtroDireccionEscritorio', 2, 'Todas', $direccionesFiltro); ?>
                                </th>

                                <th>
                                    <?php filtroMultiSelect('filtroTelefonoEscritorio', 3, 'Todos', $telefonosFiltro); ?>
                                </th>

                                <th>
                                    <?php filtroMultiSelect('filtroEmailEscritorio', 4, 'Todos', $emailsFiltro); ?>
                                </th>

                                <th>
                                    <?php filtroMultiSelect('filtroServiciosEscritorio', 5, 'Todos', $serviciosFiltro); ?>
                                </th>

                                <th>
                                    <button type="button" class="panel-action" id="btnLimpiarFiltros">
                                        Limpiar filtros
                                    </button>
                                </th>

                            </tr>

                        </thead>


                        <tbody id="comercializadorasBody">

                            <?php if (empty($comercializadoras)): ?>

                                <tr>

                                    <td colspan="7" style="text-align:center; padding:40px;">
                                        No hay comercializadoras registradas.
                                    </td>

                                </tr>

                            <?php else: ?>

                                <?php foreach ($comercializadoras as $comercializadora): ?>

                                    <?php

                                    $inicialesComercializadora = mb_substr($comercializadora['nombre'], 0, 1, 'UTF-8');

                                    $segundaPalabra = strpos($comercializadora['nombre'], ' ');

                                    if ($segundaPalabra !== false) {
                                        $inicialesComercializadora .= mb_substr(
                                            $comercializadora['nombre'],
                                            $segundaPalabra + 1,
                                            1,
                                            'UTF-8'
                                        );
                                    }

                                    $inicialesComercializadora = mb_strtoupper($inicialesComercializadora, 'UTF-8');

                                    ?>

                                    <!-- fila-detalle: en móvil, pulsar la fila abre la
                                         tarjeta con toda la información (ver
                                         comercializadoras.js); en escritorio no hace
                                         nada, ahí ya se ve todo. -->

                                    <tr class="fila-detalle" data-id="<?= (int) $comercializadora['id'] ?>"
                                        data-nombre="<?= htmlspecialchars($comercializadora['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-iniciales="<?= htmlspecialchars($inicialesComercializadora, ENT_QUOTES, 'UTF-8') ?>"
                                        data-cif="<?= htmlspecialchars($comercializadora['cif'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-direccion="<?= htmlspecialchars($comercializadora['direccion'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        data-telefono="<?= htmlspecialchars($comercializadora['telefono'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        data-email="<?= htmlspecialchars($comercializadora['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        data-servicios="<?= htmlspecialchars(etiquetaServiciosComercializadora($comercializadora), ENT_QUOTES, 'UTF-8') ?>">

                                        <td>

                                            <div class="usuario-cell">

                                                <div class="usuario-avatar">
                                                    <?= htmlspecialchars($inicialesComercializadora) ?>
                                                </div>

                                                <div class="usuario-info">

                                                    <strong>
                                                        <?= htmlspecialchars($comercializadora['nombre']) ?>
                                                    </strong>

                                                </div>

                                            </div>

                                        </td>

                                        <td class="vista-escritorio"><?= htmlspecialchars($comercializadora['cif']) ?></td>

                                        <td class="vista-escritorio"><?= htmlspecialchars($comercializadora['direccion'] ?? '—') ?></td>

                                        <td class="vista-escritorio"><?= htmlspecialchars($comercializadora['telefono'] ?? '—') ?></td>

                                        <td class="vista-escritorio"><?= htmlspecialchars($comercializadora['email'] ?? '—') ?></td>

                                        <td class="vista-escritorio"><?= htmlspecialchars(etiquetaServiciosComercializadora($comercializadora)) ?></td>

                                        <td class="vista-escritorio">

                                            <div class="user-actions">

                                                <button type="button" class="table-action-button icon-action-button list-edit"
                                                    title="Editar"
                                                    onclick="event.stopPropagation(); window.location.href='editar_comercializadora.php?id=<?= (int) $comercializadora['id'] ?>'">
                                                    <i class="bi bi-pencil"></i>
                                                </button>

                                                <button type="button" class="table-action-button icon-action-button danger"
                                                    title="Eliminar" onclick="event.stopPropagation(); abrirModalEliminarComercializadora(
        <?= (int) $comercializadora['id'] ?>,
        '<?= htmlspecialchars($comercializadora['nombre'], ENT_QUOTES, 'UTF-8') ?>'
    )">
                                                    <i class="bi bi-trash3"></i>
                                                </button>

                                            </div>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>


                <!-- =====================================================
                     PAGINACIÓN
                ====================================================== -->

                <div class="usuarios-pagination">

                    <div class="usuarios-por-pagina">
                        <label for="selectorPorPagina">Mostrar:</label>
                        <select id="selectorPorPagina" class="por-pagina-select">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="todos">Todos</option>
                        </select>
                    </div>

                    <span id="comercializadorasMostrando">
                        Mostrando <?= $totalComercializadoras ?> de <?= $totalComercializadoras ?>
                        <?= $totalComercializadoras === 1 ? 'comercializadora' : 'comercializadoras' ?>
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


    <?php include '../../templates/footer.php'; ?>


    <script src="../../js/exportar-pdf.js"></script>
    <script src="../../js/multi-select-filter.js"></script>
    <script src="../../js/notificacion-eliminacion.js"></script>
    <script src="../../js/comercializadoras.js"></script>
    <script src="../../js/modal-detalle.js"></script>


    <!-- =====================================================
         MODAL CONFIRMAR ELIMINACIÓN
    ====================================================== -->

    <div id="modalEliminarComercializadora" class="modal-overlay" style="display: none;">

        <div class="modal-confirmacion deletion-modal">

            <div class="modal-icon" aria-hidden="true"><i class="bi bi-trash3"></i></div>

            <h2>Eliminar comercializadora</h2>

            <p>
                ¿Estás seguro de que quieres eliminar la comercializadora <strong id="nombreComercializadoraEliminar"></strong>?
            </p>

            <p class="modal-warning">
                Esta acción no se puede deshacer.
            </p>

            <div class="modal-actions">

                <button type="button" class="modal-button modal-button-cancel" onclick="cerrarModalEliminarComercializadora()">
                    Cancelar
                </button>

                <button type="button" class="modal-button modal-button-delete" onclick="confirmarEliminarComercializadora()">
                    Eliminar comercializadora
                </button>

            </div>

        </div>

    </div>


    <!-- =====================================================
         TARJETA DE DETALLE (SOLO MÓVIL)
    ====================================================== -->

    <div id="modalDetalleComercializadora" class="modal-overlay" style="display: none;">

        <div class="modal-detalle">

            <div class="modal-detalle-header">

                <div class="modal-detalle-avatar" id="detalleComercializadoraAvatar"></div>

                <div class="modal-detalle-titulo">
                    <h2 id="detalleComercializadoraNombre"></h2>
                </div>

                <button type="button" class="modal-detalle-close" onclick="cerrarModalDetalleComercializadora()"
                    aria-label="Cerrar">
                    ✕
                </button>

            </div>

            <div class="usuario-detalle-grid">

                <div class="usuario-detalle-item">
                    <span>CIF</span>
                    <strong id="detalleComercializadoraCif"></strong>
                </div>

                <div class="usuario-detalle-item">
                    <span>Dirección</span>
                    <strong id="detalleComercializadoraDireccion"></strong>
                </div>

                <div class="usuario-detalle-item">
                    <span>Teléfono</span>
                    <strong id="detalleComercializadoraTelefono"></strong>
                </div>

                <div class="usuario-detalle-item">
                    <span>Email</span>
                    <strong id="detalleComercializadoraEmail"></strong>
                </div>

                <div class="usuario-detalle-item">
                    <span>Servicios</span>
                    <strong id="detalleComercializadoraServicios"></strong>
                </div>

            </div>

            <div class="modal-detalle-acciones">

                <a href="#" class="config-save-button" id="btnDetalleEditarComercializadora">
                    Editar
                </a>

                <button type="button" class="table-action-button danger" id="btnDetalleEliminarComercializadora">
                    Eliminar
                </button>

            </div>

        </div>

    </div>

</body>

</html>
