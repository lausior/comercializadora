<?php

session_start();

require_once __DIR__ . '/../../config/permisos.php';
requerirPermiso('clientes');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/filtro_multiselect.php';


// =====================================================
// VALORES DE LOS FILTROS DE COMERCIALIZADORA/TARIFA/ESTADO
// =====================================================
//
// Mismas listas que validan guardar_cliente.php /
// actualizar_cliente.php: son los únicos valores que un
// cliente puede tener, así que el filtro los muestra todos
// (aunque en un momento dado no haya ningún cliente con
// alguno de ellos).
//
// =====================================================

$comercializadorasFiltro = ['Endesa', 'Iberdrola', 'Naturgy', 'Repsol', 'TotalEnergies'];
$tarifasFiltro           = ['PVPC', 'Mercado libre', 'Tarifa fija'];
$estadosFiltro           = ['Activo', 'Pendiente', 'Inactivo'];


// =====================================================
// OBTENER CLIENTES DE LA BASE DE DATOS
// =====================================================

$stmtClientes = $pdo->query("
    SELECT
        id,
        nombre,
        tipo,
        identificacion,
        correo,
        comercializadora,
        tarifa,
        estado,
        creado_por
    FROM clientes
    ORDER BY nombre
");

$clientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);


// =====================================================
// FILTRAR SEGÚN QUÉ CLIENTES PUEDE VER EL ROL ACTUAL
// =====================================================
//
// SRG los ve a todos. NG y EMPRESA solo ven a los que
// ellos mismos han dado de alta (ver puedeVerCliente()
// en permisos.php).
//
// =====================================================

$clientes = array_values(array_filter(
    $clientes,
    fn(array $cliente): bool =>
        puedeVerCliente(
            $cliente['creado_por'] !== null ? (int) $cliente['creado_por'] : null
        )
));

$totalClientes = count($clientes);


// =====================================================
// CLASES CSS PARA LAS ETIQUETAS DE TARIFA Y ESTADO
// =====================================================

$clasesTarifa = [
    'PVPC'          => 'tarifa-pvpc',
    'Mercado libre' => 'tarifa-libre',
    'Tarifa fija'   => 'tarifa-fija',
];

$clasesEstado = [
    'Activo'    => 'cliente-active',
    'Pendiente' => 'cliente-pending',
    'Inactivo'  => 'cliente-inactive',
];

?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Clientes - Comparador Eléctrico</title>

    <link rel="stylesheet" href="../../css/style.css">

</head>

<body>

    <?php include '../../templates/header.php'; ?>

    <div class="app-container">

        <?php include '../../templates/sidebar.php'; ?>

        <main class="main-content">

            <!-- =====================================================
             CABECERA
        ====================================================== -->

            <div class="page-header">

                <div>

                    <h1>Clientes</h1>

                    <p>
                        Gestión y consulta de los clientes registrados
                    </p>

                </div>

                <div class="page-header-actions">

                    <div class="page-date">
                        15 septiembre 2026
                    </div>

                    <button type="button" class="config-secondary-button" id="btnExportarPDF">
                        📄 Exportar PDF
                    </button>

                    <a href="crear_cliente.php" class="config-save-button">
                        + Nuevo cliente
                    </a>

                </div>

            </div>


            <!-- =====================================================
             TABLA DE CLIENTES
        ====================================================== -->

            <section class="panel clientes-table-panel">

                <div class="panel-header">

                    <div>
                        <h2>Clientes registrados</h2>
                        <p id="clientesContador">

                            <?= $totalClientes ?>

                            <?= $totalClientes === 1 ? 'cliente encontrado' : 'clientes encontrados' ?>

                        </p>
                    </div>

                    <div class="panel-header-actions" style="display:flex; gap:10px; align-items:center; flex-wrap: wrap;">

                        <div class="clientes-por-pagina">
                            <label for="selectorPorPagina">Mostrar:</label>
                            <select id="selectorPorPagina" class="por-pagina-select">
                                <option value="5">5</option>
                                <option value="10">10</option>
                                <option value="todos">Todos</option>
                            </select>
                        </div>

                        <!-- Solo en escritorio: limpia los filtros de columna de la tabla -->
                        <button type="button" class="panel-action vista-escritorio" id="btnLimpiarFiltros">
                            Limpiar filtros
                        </button>

                        <!-- Solo en móvil: despliega el panel de filtros apilados -->
                        <button type="button" class="filtros-toggle-button vista-movil" id="btnToggleFiltros"
                            aria-expanded="false" aria-controls="panelFiltrosClientes">
                            <span>Filtros</span>
                            <span class="chevron">▾</span>
                        </button>

                    </div>

                </div>


                <!-- =================================================
                     PANEL DE FILTROS DESPLEGABLE (SOLO MÓVIL)
                     =================================================
                     Filtra las mismas columnas que la fila de
                     filtros de la tabla de escritorio (mismo
                     data-column), solo que apiladas en vertical
                     y ocultas hasta que se pulsa "Filtros".
                ================================================== -->

                <div class="filtros-panel vista-movil" id="panelFiltrosClientes" style="display:none;">

                    <div class="filtros-panel-campos">

                        <div class="filter-group">
                            <label for="filtroClienteMovil">Cliente</label>
                            <input type="text" id="filtroClienteMovil" class="column-filter" data-column="0"
                                placeholder="Buscar cliente...">
                        </div>

                        <div class="filter-group">
                            <label for="filtroIdentificacionMovil">Identificación</label>
                            <input type="text" id="filtroIdentificacionMovil" class="column-filter" data-column="1"
                                placeholder="DNI / CIF...">
                        </div>

                        <div class="filter-group">
                            <label for="filtroCorreoMovil">Correo</label>
                            <input type="text" id="filtroCorreoMovil" class="column-filter" data-column="2"
                                placeholder="Buscar correo...">
                        </div>

                        <div class="filter-group">
                            <label>Comercializadora</label>
                            <?php filtroMultiSelect('filtroComercializadoraMovil', 3, 'Todas', $comercializadorasFiltro); ?>
                        </div>

                        <div class="filter-group">
                            <label>Tarifa</label>
                            <?php filtroMultiSelect('filtroTarifaMovil', 4, 'Todas', $tarifasFiltro); ?>
                        </div>

                        <div class="filter-group">
                            <label>Estado</label>
                            <?php filtroMultiSelect('filtroEstadoMovil', 5, 'Todos', $estadosFiltro); ?>
                        </div>

                    </div>

                    <div class="filtros-panel-acciones">
                        <button type="button" class="panel-action" id="btnLimpiarFiltrosMovil">
                            Limpiar filtros
                        </button>
                    </div>

                </div>


                <div class="clientes-table-container">

                    <table class="clientes-table">

                        <thead>

                            <!-- =================================================
                                 CABECERAS (las columnas 2ª en adelante y la
                                 fila de filtros solo se ven en escritorio;
                                 en móvil el CSS las oculta y deja solo
                                 "Cliente")
                            ================================================== -->

                            <tr>

                                <th>
                                    <div class="table-header-content">
                                        <span>Cliente</span>

                                        <button type="button" class="sort-button" data-column="0"
                                            title="Ordenar por cliente">
                                            ↕
                                        </button>
                                    </div>
                                </th>


                                <th class="vista-escritorio">
                                    <div class="table-header-content">
                                        <span>Identificación</span>

                                        <button type="button" class="sort-button" data-column="1"
                                            title="Ordenar por identificación">
                                            ↕
                                        </button>
                                    </div>
                                </th>


                                <th class="vista-escritorio">
                                    <div class="table-header-content">
                                        <span>Correo</span>

                                        <button type="button" class="sort-button" data-column="2"
                                            title="Ordenar por correo">
                                            ↕
                                        </button>
                                    </div>
                                </th>


                                <th class="vista-escritorio">
                                    <div class="table-header-content">
                                        <span>Comercializadora</span>

                                        <button type="button" class="sort-button" data-column="3"
                                            title="Ordenar por comercializadora">
                                            ↕
                                        </button>
                                    </div>
                                </th>


                                <th class="vista-escritorio">
                                    <div class="table-header-content">
                                        <span>Tarifa</span>

                                        <button type="button" class="sort-button" data-column="4"
                                            title="Ordenar por tarifa">
                                            ↕
                                        </button>
                                    </div>
                                </th>


                                <th class="vista-escritorio">
                                    <div class="table-header-content">
                                        <span>Estado</span>

                                        <button type="button" class="sort-button" data-column="5"
                                            title="Ordenar por estado">
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

                            <tr class="clientes-filter-row-table vista-escritorio">

                                <!-- Cliente -->
                                <th>

                                    <input type="text" class="column-filter" data-column="0"
                                        placeholder="Buscar cliente...">

                                </th>


                                <!-- Identificación -->
                                <th>

                                    <input type="text" class="column-filter" data-column="1" placeholder="DNI / CIF...">

                                </th>


                                <!-- Correo -->
                                <th>

                                    <input type="text" class="column-filter" data-column="2"
                                        placeholder="Buscar correo...">

                                </th>


                                <!-- Comercializadora -->
                                <th>

                                    <?php filtroMultiSelect('filtroComercializadoraEscritorio', 3, 'Todas', $comercializadorasFiltro); ?>

                                </th>


                                <!-- Tarifa -->
                                <th>

                                    <?php filtroMultiSelect('filtroTarifaEscritorio', 4, 'Todas', $tarifasFiltro); ?>

                                </th>


                                <!-- Estado -->
                                <th>

                                    <?php filtroMultiSelect('filtroEstadoEscritorio', 5, 'Todos', $estadosFiltro); ?>

                                </th>


                                <!-- Acciones -->
                                <th></th>

                            </tr>

                        </thead>


                        <tbody id="clientesBody">

                            <?php if (empty($clientes)): ?>

                                <tr>

                                    <td colspan="7" style="text-align:center; padding:40px;">
                                        No hay clientes registrados.
                                    </td>

                                </tr>

                            <?php else: ?>

                                <?php foreach ($clientes as $cliente): ?>

                                    <?php

                                    $inicialesCliente = mb_substr($cliente['nombre'], 0, 1, 'UTF-8');

                                    $segundaPalabra = strpos($cliente['nombre'], ' ');

                                    if ($segundaPalabra !== false) {
                                        $inicialesCliente .= mb_substr(
                                            $cliente['nombre'],
                                            $segundaPalabra + 1,
                                            1,
                                            'UTF-8'
                                        );
                                    }

                                    $inicialesCliente = mb_strtoupper($inicialesCliente, 'UTF-8');

                                    $tarifaClase = $clasesTarifa[$cliente['tarifa']] ?? '';
                                    $estadoClase = $clasesEstado[$cliente['estado']] ?? '';

                                    ?>

                                    <!-- fila-detalle: en móvil, pulsar la fila abre la
                                         tarjeta con toda la información (ver clientes.js);
                                         en escritorio no hace nada, ahí ya se ve todo. -->

                                    <tr class="fila-detalle"
                                        data-id="<?= (int) $cliente['id'] ?>"
                                        data-nombre="<?= htmlspecialchars($cliente['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-iniciales="<?= htmlspecialchars($inicialesCliente, ENT_QUOTES, 'UTF-8') ?>"
                                        data-tipo="<?= htmlspecialchars($cliente['tipo'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-identificacion="<?= htmlspecialchars($cliente['identificacion'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-correo="<?= htmlspecialchars($cliente['correo'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-comercializadora="<?= htmlspecialchars($cliente['comercializadora'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-tarifa="<?= htmlspecialchars($cliente['tarifa'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-tarifaclase="<?= htmlspecialchars($tarifaClase, ENT_QUOTES, 'UTF-8') ?>"
                                        data-estado="<?= htmlspecialchars($cliente['estado'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-estadoclase="<?= htmlspecialchars($estadoClase, ENT_QUOTES, 'UTF-8') ?>"
                                    >

                                        <td>

                                            <div class="cliente-cell">

                                                <div class="cliente-avatar">
                                                    <?= htmlspecialchars($inicialesCliente) ?>
                                                </div>

                                                <div class="cliente-info">

                                                    <strong>
                                                        <?= htmlspecialchars($cliente['nombre']) ?>
                                                    </strong>

                                                    <span>
                                                        <?= htmlspecialchars($cliente['tipo']) ?>
                                                    </span>

                                                </div>

                                            </div>

                                        </td>

                                        <td class="vista-escritorio"><?= htmlspecialchars($cliente['identificacion']) ?></td>

                                        <td class="vista-escritorio"><?= htmlspecialchars($cliente['correo']) ?></td>

                                        <td class="vista-escritorio"><?= htmlspecialchars($cliente['comercializadora']) ?></td>

                                        <td class="vista-escritorio">
                                            <span class="tarifa-badge <?= $tarifaClase ?>">
                                                <?= htmlspecialchars($cliente['tarifa']) ?>
                                            </span>
                                        </td>

                                        <td class="vista-escritorio">
                                            <span class="status-badge <?= $estadoClase ?>">
                                                <?= htmlspecialchars($cliente['estado']) ?>
                                            </span>
                                        </td>

                                        <td class="vista-escritorio">
                                            <div class="cliente-actions">

                                                <button type="button" class="table-action-button"
                                                    onclick="event.stopPropagation(); window.location.href='editar_cliente.php?id=<?= (int) $cliente['id'] ?>'">
                                                    Editar
                                                </button>

                                                <button type="button" class="table-action-button danger" onclick="event.stopPropagation(); abrirModalEliminarCliente(
        <?= (int) $cliente['id'] ?>,
        '<?= htmlspecialchars($cliente['nombre'], ENT_QUOTES, 'UTF-8') ?>'
    )">
                                                    Borrar
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

                <div class="clientes-pagination">

                    <span id="clientesMostrando">
                        Mostrando <?= $totalClientes ?> de <?= $totalClientes ?>
                        <?= $totalClientes === 1 ? 'cliente' : 'clientes' ?>
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
    <script src="../../js/clientes.js"></script>


    <!-- =====================================================
         MODAL CONFIRMAR ELIMINACIÓN
    ====================================================== -->

    <div id="modalEliminarCliente" class="modal-overlay" style="display: none;">

        <div class="modal-confirmacion">

            <div class="modal-icon">
                ⚠
            </div>

            <h2>Eliminar cliente</h2>

            <p>
                ¿Estás seguro de que quieres eliminar al cliente
                <strong id="nombreClienteEliminar"></strong>?
            </p>

            <p class="modal-warning">
                Esta acción no se puede deshacer.
            </p>

            <div class="modal-actions">

                <button type="button" class="modal-button modal-button-cancel"
                    onclick="cerrarModalEliminarCliente()">
                    Cancelar
                </button>

                <button type="button" class="modal-button modal-button-delete"
                    onclick="confirmarEliminarCliente()">
                    Eliminar cliente
                </button>

            </div>

        </div>

    </div>


    <!-- =====================================================
         TARJETA DE DETALLE (SOLO MÓVIL)
    ====================================================== -->

    <div id="modalDetalleCliente" class="modal-overlay" style="display: none;">

        <div class="modal-detalle">

            <div class="modal-detalle-header">

                <div class="modal-detalle-avatar" id="detalleClienteAvatar"></div>

                <div class="modal-detalle-titulo">
                    <h2 id="detalleClienteNombre"></h2>
                    <span id="detalleClienteTipo"></span>
                </div>

                <button type="button" class="modal-detalle-close" onclick="cerrarModalDetalleCliente()"
                    aria-label="Cerrar">
                    ✕
                </button>

            </div>

            <div class="usuario-detalle-grid">

                <div class="usuario-detalle-item">
                    <span>Identificación</span>
                    <strong id="detalleClienteIdentificacion"></strong>
                </div>

                <div class="usuario-detalle-item">
                    <span>Correo</span>
                    <strong id="detalleClienteCorreo"></strong>
                </div>

                <div class="usuario-detalle-item">
                    <span>Comercializadora</span>
                    <strong id="detalleClienteComercializadora"></strong>
                </div>

                <div class="usuario-detalle-item">
                    <span>Tarifa</span>
                    <strong id="detalleClienteTarifa"></strong>
                </div>

                <div class="usuario-detalle-item">
                    <span>Estado</span>
                    <strong id="detalleClienteEstado"></strong>
                </div>

            </div>

            <div class="modal-detalle-acciones">

                <button type="button" class="table-action-button danger" id="btnDetalleEliminarCliente">
                    Eliminar
                </button>

                <a href="#" class="config-save-button" id="btnDetalleEditarCliente">
                    Editar
                </a>

            </div>

        </div>

    </div>

</body>

</html>
