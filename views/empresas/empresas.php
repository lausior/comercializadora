<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('empresas');

require_once '../../config/database.php';


// =====================================================
// OBTENER EMPRESAS DE LA BASE DE DATOS
// =====================================================

$stmtEmpresas = $pdo->query("
    SELECT
        id,
        codigo_empresa,
        nombre,
        cif,
        direccion,
        telefono,
        email
    FROM empresas
    ORDER BY nombre
");

$empresas = $stmtEmpresas->fetchAll(PDO::FETCH_ASSOC);

$totalEmpresas = count($empresas);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Empresas - Comparador Eléctrico</title>

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

                    <h1>Empresas</h1>

                    <p>
                        Gestión y consulta de las empresas registradas
                    </p>

                </div>

                <div class="page-header-actions">

                    <div class="page-date">
                        11 septiembre 2026
                    </div>

                    <a href="crear_empresa.php" class="config-save-button">
                        + Añadir empresa
                    </a>

                </div>

            </div>


            <!-- =====================================================
                 TABLA DE EMPRESAS
            ====================================================== -->

            <section class="panel usuarios-table-panel">

                <div class="panel-header">

                    <div>
                        <h2>Empresas registradas</h2>
                        <p id="empresasContador">

                            <?= $totalEmpresas ?>

                            <?= $totalEmpresas === 1 ? 'empresa encontrada' : 'empresas encontradas' ?>

                        </p>
                    </div>

                    <div class="panel-header-actions" style="display:flex; gap:10px; align-items:center;">

                        <div class="usuarios-por-pagina">
                            <label for="selectorPorPagina">Mostrar:</label>
                            <select id="selectorPorPagina" class="por-pagina-select">
                                <option value="5">5</option>
                                <option value="10">10</option>
                                <option value="todos">Todos</option>
                            </select>
                        </div>

                        <button type="button" class="panel-action" id="btnLimpiarFiltros">
                            Limpiar filtros
                        </button>

                    </div>

                </div>

                <div class="usuarios-table-container">

                    <table class="usuarios-table">

                        <thead>

                            <!-- =================================================
                                 CABECERAS
                            ================================================== -->

                            <tr>

                                <th>
                                    <div class="table-header-content">
                                        <span>Empresa</span>

                                        <button type="button" class="sort-button" data-column="0"
                                            title="Ordenar por empresa">
                                            ↕
                                        </button>
                                    </div>
                                </th>

                                <th>
                                    <div class="table-header-content">
                                        <span>CIF</span>

                                        <button type="button" class="sort-button" data-column="1"
                                            title="Ordenar por CIF">
                                            ↕
                                        </button>
                                    </div>
                                </th>

                                <th>
                                    <div class="table-header-content">
                                        <span>Dirección</span>

                                        <button type="button" class="sort-button" data-column="2"
                                            title="Ordenar por dirección">
                                            ↕
                                        </button>
                                    </div>
                                </th>

                                <th>
                                    <div class="table-header-content">
                                        <span>Teléfono</span>

                                        <button type="button" class="sort-button" data-column="3"
                                            title="Ordenar por teléfono">
                                            ↕
                                        </button>
                                    </div>
                                </th>

                                <th>
                                    <div class="table-header-content">
                                        <span>Email</span>

                                        <button type="button" class="sort-button" data-column="4"
                                            title="Ordenar por email">
                                            ↕
                                        </button>
                                    </div>
                                </th>

                                <th>
                                    <span>Acciones</span>
                                </th>

                            </tr>


                            <!-- =================================================
                                 FILTROS POR COLUMNA
                            ================================================== -->

                            <tr class="usuarios-filter-row-table">

                                <th>
                                    <input type="text" class="column-filter" data-column="0"
                                        placeholder="Buscar empresa...">
                                </th>

                                <th>
                                    <input type="text" class="column-filter" data-column="1"
                                        placeholder="Buscar CIF...">
                                </th>

                                <th>
                                    <input type="text" class="column-filter" data-column="2"
                                        placeholder="Buscar dirección...">
                                </th>

                                <th>
                                    <input type="text" class="column-filter" data-column="3"
                                        placeholder="Buscar teléfono...">
                                </th>

                                <th>
                                    <input type="text" class="column-filter" data-column="4"
                                        placeholder="Buscar email...">
                                </th>

                                <th></th>

                            </tr>

                        </thead>


                        <tbody id="empresasBody">

                            <?php if (empty($empresas)): ?>

                                <tr>

                                    <td colspan="6" style="text-align:center; padding:40px;">
                                        No hay empresas registradas.
                                    </td>

                                </tr>

                            <?php else: ?>

                                <?php foreach ($empresas as $empresa): ?>

                                    <?php

                                    $inicialesEmpresa = mb_substr($empresa['nombre'], 0, 1, 'UTF-8');

                                    $segundaPalabra = strpos($empresa['nombre'], ' ');

                                    if ($segundaPalabra !== false) {
                                        $inicialesEmpresa .= mb_substr(
                                            $empresa['nombre'],
                                            $segundaPalabra + 1,
                                            1,
                                            'UTF-8'
                                        );
                                    }

                                    $inicialesEmpresa = mb_strtoupper($inicialesEmpresa, 'UTF-8');

                                    ?>

                                    <tr>

                                        <td>

                                            <div class="usuario-cell">

                                                <div class="usuario-avatar">
                                                    <?= htmlspecialchars($inicialesEmpresa) ?>
                                                </div>

                                                <div class="usuario-info">

                                                    <strong>
                                                        <?= htmlspecialchars($empresa['nombre']) ?>
                                                    </strong>

                                                    <span>
                                                        Código <?= htmlspecialchars($empresa['codigo_empresa']) ?>
                                                    </span>

                                                </div>

                                            </div>

                                        </td>

                                        <td><?= htmlspecialchars($empresa['cif']) ?></td>

                                        <td><?= htmlspecialchars($empresa['direccion'] ?? '—') ?></td>

                                        <td><?= htmlspecialchars($empresa['telefono'] ?? '—') ?></td>

                                        <td><?= htmlspecialchars($empresa['email'] ?? '—') ?></td>

                                        <td>

                                            <div class="user-actions">

                                                <button type="button" class="table-action-button" onclick="abrirModalEliminarEmpresa(
        <?= (int) $empresa['id'] ?>,
        '<?= htmlspecialchars($empresa['nombre'], ENT_QUOTES, 'UTF-8') ?>'
    )">
                                                    Eliminar
                                                </button>

                                                <button type="button" class="table-action-button"
                                                    onclick="window.location.href='editar_empresa.php?id=<?= (int) $empresa['id'] ?>'">
                                                    Editar
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

                    <span id="empresasMostrando">
                        Mostrando <?= $totalEmpresas ?> de <?= $totalEmpresas ?>
                        <?= $totalEmpresas === 1 ? 'empresa' : 'empresas' ?>
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


    <script src="../../js/empresas.js"></script>


    <!-- =====================================================
         MODAL CONFIRMAR ELIMINACIÓN
    ====================================================== -->

    <div id="modalEliminarEmpresa" class="modal-overlay" style="display: none;">

        <div class="modal-confirmacion">

            <div class="modal-icon">
                ⚠
            </div>

            <h2>Eliminar empresa</h2>

            <p>
                ¿Estás seguro de que quieres eliminar la empresa
                <strong id="nombreEmpresaEliminar"></strong>?
            </p>

            <p class="modal-warning">
                Esta acción no se puede deshacer.
            </p>

            <div class="modal-actions">

                <button type="button" class="modal-button modal-button-cancel"
                    onclick="cerrarModalEliminarEmpresa()">
                    Cancelar
                </button>

                <button type="button" class="modal-button modal-button-delete"
                    onclick="confirmarEliminarEmpresa()">
                    Eliminar empresa
                </button>

            </div>

        </div>

    </div>

</body>

</html>
