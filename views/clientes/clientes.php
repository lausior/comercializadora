<?php

session_start();

require_once __DIR__ . '/../../config/permisos.php';
requerirPermiso('clientes');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/filtro_multiselect.php';


// =====================================================
// OBTENER CLIENTES DE LA BASE DE DATOS
// =====================================================

$stmtClientes = $pdo->query("
    SELECT
        id,
        nombre,
        apellidos,
        direccion,
        telefono,
        email,
        nif,
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
// ESTADÍSTICAS
// =====================================================
//
// Clientes no tiene un campo "estado" (a diferencia de
// Empresas/Usuarios), así que el resumen se basa en qué
// tan completos están los datos de contacto.
//
// =====================================================

$clientesConTelefono = count(array_filter(
    $clientes,
    fn(array $cliente): bool => !empty($cliente['telefono'])
));

$clientesConDireccion = count(array_filter(
    $clientes,
    fn(array $cliente): bool => !empty($cliente['direccion'])
));


// =====================================================
// VALORES DISTINTOS PARA LOS DESPLEGABLES DE CLIENTE/
// DIRECCIÓN/TELÉFONO/EMAIL/NIF
// =====================================================
//
// La lista de opciones del desplegable son los valores
// que realmente aparecen en $clientes (ya filtrado por
// permisos).
//
// =====================================================

$nombresFiltro = array_values(array_unique(array_map(
    fn(array $cliente): string => $cliente['nombre'] . ' ' . $cliente['apellidos'],
    $clientes
)));
sort($nombresFiltro);

$direccionesFiltro = array_values(array_unique(array_filter(
    array_column($clientes, 'direccion')
)));
sort($direccionesFiltro);

$telefonosFiltro = array_values(array_unique(array_filter(
    array_column($clientes, 'telefono')
)));
sort($telefonosFiltro);

$emailsFiltro = array_values(array_unique(array_column($clientes, 'email')));
sort($emailsFiltro);

$nifsFiltro = array_values(array_unique(array_column($clientes, 'nif')));
sort($nifsFiltro);

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

                    <button type="button" class="config-secondary-button" id="btnExportarPDF">
                        📄 Exportar PDF
                    </button>

                    <a href="crear_cliente.php" class="config-save-button">
                        + Nuevo cliente
                    </a>

                </div>

            </div>


            <!-- =================================================
                 RESUMEN
            ================================================== -->

            <section class="dashboard-cards resumen-entidad-cards">

                <!-- TOTAL CLIENTES -->

                <div class="dashboard-card">

                    <div class="card-icon blue">
                        👤
                    </div>

                    <div class="card-info">

                        <span class="card-label">
                            Total clientes
                        </span>

                        <strong>
                            <?= $totalClientes ?>
                        </strong>

                    </div>

                </div>


                <!-- CON TELÉFONO -->

                <div class="dashboard-card">

                    <div class="card-icon green">
                        📞
                    </div>

                    <div class="card-info">

                        <span class="card-label">
                            Con teléfono
                        </span>

                        <strong>
                            <?= $clientesConTelefono ?>
                        </strong>

                    </div>

                </div>


                <!-- CON DIRECCIÓN -->

                <div class="dashboard-card">

                    <div class="card-icon orange">
                        📍
                    </div>

                    <div class="card-info">

                        <span class="card-label">
                            Con dirección
                        </span>

                        <strong>
                            <?= $clientesConDireccion ?>
                        </strong>

                    </div>

                </div>

            </section>


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
                            <label>Cliente</label>
                            <?php filtroMultiSelect('filtroClienteMovil', 0, 'Todos', $nombresFiltro); ?>
                        </div>

                        <div class="filter-group">
                            <label>DNI/NIE</label>
                            <?php filtroMultiSelect('filtroNifMovil', 1, 'Todos', $nifsFiltro); ?>
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
                                        <span>DNI/NIE</span>

                                        <button type="button" class="sort-button" data-column="1"
                                            title="Ordenar por DNI/NIE">
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
                                    <span>Acciones</span>
                                </th>

                            </tr>


                            <!-- =================================================
                                 FILTROS POR COLUMNA (SOLO ESCRITORIO)
                            ================================================== -->

                            <tr class="clientes-filter-row-table vista-escritorio">

                                <!-- Cliente -->
                                <th>

                                    <?php filtroMultiSelect('filtroClienteEscritorio', 0, 'Todos', $nombresFiltro); ?>

                                </th>


                                <!-- DNI/NIE -->
                                <th>

                                    <?php filtroMultiSelect('filtroNifEscritorio', 1, 'Todos', $nifsFiltro); ?>

                                </th>


                                <!-- Dirección -->
                                <th>

                                    <?php filtroMultiSelect('filtroDireccionEscritorio', 2, 'Todas', $direccionesFiltro); ?>

                                </th>


                                <!-- Teléfono -->
                                <th>

                                    <?php filtroMultiSelect('filtroTelefonoEscritorio', 3, 'Todos', $telefonosFiltro); ?>

                                </th>


                                <!-- Email -->
                                <th>

                                    <?php filtroMultiSelect('filtroEmailEscritorio', 4, 'Todos', $emailsFiltro); ?>

                                </th>


                                <!-- Acciones -->
                                <th>
                                    <button type="button" class="panel-action" id="btnLimpiarFiltros">
                                        Limpiar filtros
                                    </button>
                                </th>

                            </tr>

                        </thead>


                        <tbody id="clientesBody">

                            <?php if (empty($clientes)): ?>

                                <tr>

                                    <td colspan="6" style="text-align:center; padding:40px;">
                                        No hay clientes registrados.
                                    </td>

                                </tr>

                            <?php else: ?>

                                <?php foreach ($clientes as $cliente): ?>

                                    <?php

                                    $nombreCompleto = $cliente['nombre'] . ' ' . $cliente['apellidos'];

                                    $iniciales =
                                        mb_substr($cliente['nombre'], 0, 1, 'UTF-8') .
                                        mb_substr($cliente['apellidos'], 0, 1, 'UTF-8');

                                    $iniciales = mb_strtoupper($iniciales, 'UTF-8');

                                    ?>

                                    <!-- fila-detalle: en móvil, pulsar la fila abre la
                                         tarjeta con toda la información (ver clientes.js);
                                         en escritorio no hace nada, ahí ya se ve todo. -->

                                    <tr class="fila-detalle"
                                        data-id="<?= (int) $cliente['id'] ?>"
                                        data-nombre="<?= htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8') ?>"
                                        data-iniciales="<?= htmlspecialchars($iniciales, ENT_QUOTES, 'UTF-8') ?>"
                                        data-direccion="<?= htmlspecialchars($cliente['direccion'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        data-telefono="<?= htmlspecialchars($cliente['telefono'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        data-email="<?= htmlspecialchars($cliente['email'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-nif="<?= htmlspecialchars($cliente['nif'], ENT_QUOTES, 'UTF-8') ?>"
                                    >

                                        <td>

                                            <div class="cliente-cell">

                                                <div class="cliente-avatar">
                                                    <?= htmlspecialchars($iniciales) ?>
                                                </div>

                                                <div class="cliente-info">

                                                    <strong>
                                                        <?= htmlspecialchars($nombreCompleto) ?>
                                                    </strong>

                                                    <span>
                                                        <?= htmlspecialchars($cliente['id']) ?>
                                                    </span>

                                                </div>

                                            </div>

                                        </td>

                                        <td class="vista-escritorio"><?= htmlspecialchars($cliente['nif']) ?></td>

                                        <td class="vista-escritorio"><?= htmlspecialchars($cliente['direccion'] ?? '—') ?></td>

                                        <td class="vista-escritorio"><?= htmlspecialchars($cliente['telefono'] ?? '—') ?></td>

                                        <td class="vista-escritorio"><?= htmlspecialchars($cliente['email']) ?></td>

                                        <td class="vista-escritorio">
                                            <div class="cliente-actions">

                                                <button type="button" class="table-action-button icon-action-button list-edit"
                                                    title="Editar"
                                                    onclick="event.stopPropagation(); window.location.href='editar_cliente.php?id=<?= (int) $cliente['id'] ?>'">
                                                    <i class="bi bi-pencil"></i>
                                                </button>

                                                <button type="button" class="table-action-button icon-action-button danger"
                                                    title="Eliminar" onclick="event.stopPropagation(); abrirModalEliminarCliente(
        <?= (int) $cliente['id'] ?>,
        '<?= htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8') ?>'
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

                <div class="clientes-pagination">

                    <div class="clientes-por-pagina">
                        <label for="selectorPorPagina">Mostrar:</label>
                        <select id="selectorPorPagina" class="por-pagina-select">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="todos">Todos</option>
                        </select>
                    </div>

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
    <script src="../../js/notificacion-eliminacion.js"></script>
    <script src="../../js/clientes.js"></script>
    <script src="../../js/modal-detalle.js"></script>


    <!-- =====================================================
         MODAL CONFIRMAR ELIMINACIÓN
    ====================================================== -->

    <div id="modalEliminarCliente" class="modal-overlay" style="display: none;">

        <div class="modal-confirmacion deletion-modal">

            <div class="modal-icon" aria-hidden="true"><i class="bi bi-trash3"></i></div>

            <h2>Eliminar cliente</h2>

            <p>
                ¿Estás seguro de que quieres eliminar al cliente <strong id="nombreClienteEliminar"></strong>?
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
                    <span id="detalleClienteNif"></span>
                </div>

                <button type="button" class="modal-detalle-close" onclick="cerrarModalDetalleCliente()"
                    aria-label="Cerrar">
                    ✕
                </button>

            </div>

            <div class="usuario-detalle-grid">

                <div class="usuario-detalle-item">
                    <span>DNI/NIE</span>
                    <strong id="detalleClienteDniNie"></strong>
                </div>

                <div class="usuario-detalle-item">
                    <span>Dirección</span>
                    <strong id="detalleClienteDireccion"></strong>
                </div>

                <div class="usuario-detalle-item">
                    <span>Teléfono</span>
                    <strong id="detalleClienteTelefono"></strong>
                </div>

                <div class="usuario-detalle-item">
                    <span>Email</span>
                    <strong id="detalleClienteEmail"></strong>
                </div>

            </div>

            <div class="modal-detalle-acciones">

                <a href="#" class="config-save-button" id="btnDetalleEditarCliente">
                    Editar
                </a>

                <button type="button" class="table-action-button danger" id="btnDetalleEliminarCliente">
                    Eliminar
                </button>

            </div>

        </div>

    </div>

</body>

</html>
