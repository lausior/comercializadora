<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('empresas');

require_once '../../config/database.php';
require_once '../../includes/filtro_multiselect.php';
require_once '../../includes/empresas.php';


// =====================================================
// OBTENER EMPRESAS DE LA BASE DE DATOS
// =====================================================

$stmtEmpresas = $pdo->prepare("
    SELECT
        e.id,
        e.codigo_empresa,
        e.nombre,
        e.cif,
        e.direccion,
        e.telefono,
        e.email,
        e.estado,
        e.motivo_inactivo,
        e.creado_por,
        u.id AS usuario_id,
        u.nombre AS usuario_nombre,
        u.apellidos AS usuario_apellidos
    FROM empresas e

    LEFT JOIN usuarios u
        ON u.id_empresa = e.id
        AND u.id_rol = (SELECT id FROM roles WHERE nombre = :rol_empresa)

    ORDER BY e.nombre
");

$stmtEmpresas->execute([':rol_empresa' => ROL_EMPRESA]);

$empresas = $stmtEmpresas->fetchAll(PDO::FETCH_ASSOC);


// =====================================================
// FILTRAR SEGÚN QUÉ EMPRESAS PUEDE VER EL ROL ACTUAL
// =====================================================
//
// SRG las ve todas. NG solo las que ha creado él mismo
// (ver puedeVerEmpresa() en permisos.php). Y en este
// LISTADO, además, nadie ve su propia empresa (SRG no ve
// la fila "SRG", NG no ve la fila "NG Asesores") — sigue
// siendo gestionable si se entra a su edición por la URL
// directamente, solo se oculta aquí.
//
// =====================================================

$empresas = array_values(array_filter(
    $empresas,
    fn(array $empresa): bool =>
        (int) $empresa['id'] !== (int) ($_SESSION['id_empresa'] ?? 0)
        && puedeVerEmpresa(
            $empresa['creado_por'] !== null ? (int) $empresa['creado_por'] : null
        )
));

$totalEmpresas = count($empresas);


// =====================================================
// ESTADÍSTICAS
// =====================================================

$empresasActivas = count(array_filter(
    $empresas,
    fn(array $empresa): bool => $empresa['estado'] === 'Activo'
));

$empresasInactivas = $totalEmpresas - $empresasActivas;


// =====================================================
// VALORES DISTINTOS PARA LOS DESPLEGABLES DE FILTRO
// =====================================================
//
// La lista de opciones de cada desplegable son los
// valores que realmente aparecen en $empresas (ya
// filtrado por permisos).
//
// =====================================================

$nombresEmpresaFiltro = array_values(array_unique(array_column($empresas, 'nombre')));
sort($nombresEmpresaFiltro);

$cifsFiltro = array_values(array_unique(array_column($empresas, 'cif')));
sort($cifsFiltro);

$direccionesFiltro = array_values(array_unique(array_filter(
    array_column($empresas, 'direccion')
)));
sort($direccionesFiltro);

$telefonosEmpresaFiltro = array_values(array_unique(array_filter(
    array_column($empresas, 'telefono')
)));
sort($telefonosEmpresaFiltro);

$emailsEmpresaFiltro = array_values(array_unique(array_filter(
    array_column($empresas, 'email')
)));
sort($emailsEmpresaFiltro);

$estadosEmpresaFiltro = ['Activo', 'Inactivo'];

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

                    <button type="button" class="config-secondary-button" id="btnExportarPDF">
                        📄 Exportar PDF
                    </button>

                    <a href="crear_empresa.php" class="config-save-button">
                        + Añadir empresa
                    </a>

                </div>

            </div>


            <!-- =================================================
                 RESUMEN
            ================================================== -->

            <section class="dashboard-cards">

                <!-- TOTAL EMPRESAS -->

                <div class="dashboard-card">

                    <div class="card-icon blue">
                        🏢
                    </div>

                    <div class="card-info">

                        <span class="card-label">
                            Total empresas
                        </span>

                        <strong>
                            <?= $totalEmpresas ?>
                        </strong>

                    </div>

                </div>


                <!-- EMPRESAS ACTIVAS -->

                <div class="dashboard-card">

                    <div class="card-icon green">
                        ✓
                    </div>

                    <div class="card-info">

                        <span class="card-label">
                            Empresas activas
                        </span>

                        <strong>
                            <?= $empresasActivas ?>
                        </strong>

                    </div>

                </div>


                <!-- EMPRESAS INACTIVAS -->

                <div class="dashboard-card">

                    <div class="card-icon orange">
                        ◷
                    </div>

                    <div class="card-info">

                        <span class="card-label">
                            Empresas inactivas
                        </span>

                        <strong>
                            <?= $empresasInactivas ?>
                        </strong>

                    </div>

                </div>

            </section>


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

                    <div class="panel-header-actions"
                        style="display:flex; gap:10px; align-items:center; flex-wrap: wrap;">

                        <!-- Solo en móvil: despliega el panel de filtros apilados -->
                        <button type="button" class="filtros-toggle-button vista-movil" id="btnToggleFiltros"
                            aria-expanded="false" aria-controls="panelFiltrosEmpresas">
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

                <div class="filtros-panel vista-movil" id="panelFiltrosEmpresas" style="display:none;">

                    <div class="filtros-panel-campos">

                        <div class="filter-group">
                            <label>Empresa</label>
                            <?php filtroMultiSelect('filtroNombreMovil', 0, 'Todas', $nombresEmpresaFiltro); ?>
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
                            <?php filtroMultiSelect('filtroTelefonoMovil', 3, 'Todos', $telefonosEmpresaFiltro); ?>
                        </div>

                        <div class="filter-group">
                            <label>Email</label>
                            <?php filtroMultiSelect('filtroEmailMovil', 4, 'Todos', $emailsEmpresaFiltro); ?>
                        </div>

                        <div class="filter-group">
                            <label>Estado</label>
                            <?php filtroMultiSelect('filtroEstadoMovil', 5, 'Todos', $estadosEmpresaFiltro); ?>
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
                                 "Empresa")
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

                            <tr class="usuarios-filter-row-table vista-escritorio">

                                <th>
                                    <?php filtroMultiSelect('filtroNombreEscritorio', 0, 'Todas', $nombresEmpresaFiltro); ?>
                                </th>

                                <th>
                                    <?php filtroMultiSelect('filtroCifEscritorio', 1, 'Todos', $cifsFiltro); ?>
                                </th>

                                <th>
                                    <?php filtroMultiSelect('filtroDireccionEscritorio', 2, 'Todas', $direccionesFiltro); ?>
                                </th>

                                <th>
                                    <?php filtroMultiSelect('filtroTelefonoEscritorio', 3, 'Todos', $telefonosEmpresaFiltro); ?>
                                </th>

                                <th>
                                    <?php filtroMultiSelect('filtroEmailEscritorio', 4, 'Todos', $emailsEmpresaFiltro); ?>
                                </th>

                                <th>
                                    <?php filtroMultiSelect('filtroEstadoEscritorio', 5, 'Todos', $estadosEmpresaFiltro); ?>
                                </th>

                                <th>
                                    <button type="button" class="panel-action" id="btnLimpiarFiltros">
                                        Limpiar filtros
                                    </button>
                                </th>

                            </tr>

                        </thead>


                        <tbody id="empresasBody">

                            <?php if (empty($empresas)): ?>

                                <tr>

                                    <td colspan="7" style="text-align:center; padding:40px;">
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

                                    $estadoClase = $empresa['estado'] === 'Activo'
                                        ? 'cliente-active'
                                        : 'cliente-inactive';

                                    ?>

                                    <!-- fila-detalle: en móvil, pulsar la fila abre la
                                         tarjeta con toda la información (ver empresas.js);
                                         en escritorio no hace nada, ahí ya se ve todo. -->

                                    <tr class="fila-detalle" data-id="<?= (int) $empresa['id'] ?>"
                                        data-nombre="<?= htmlspecialchars($empresa['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-iniciales="<?= htmlspecialchars($inicialesEmpresa, ENT_QUOTES, 'UTF-8') ?>"
                                        data-codigo="<?= htmlspecialchars($empresa['codigo_empresa'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-cif="<?= htmlspecialchars($empresa['cif'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-direccion="<?= htmlspecialchars($empresa['direccion'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        data-telefono="<?= htmlspecialchars($empresa['telefono'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        data-email="<?= htmlspecialchars($empresa['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        data-estado="<?= htmlspecialchars($empresa['estado'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-motivo="<?= htmlspecialchars(etiquetaMotivoInactivo($empresa['motivo_inactivo']), ENT_QUOTES, 'UTF-8') ?>"
                                        data-usuario-id="<?= (int) ($empresa['usuario_id'] ?? 0) ?>">

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

                                        <td class="vista-escritorio"><?= htmlspecialchars($empresa['cif']) ?></td>

                                        <td class="vista-escritorio"><?= htmlspecialchars($empresa['direccion'] ?? '—') ?></td>

                                        <td class="vista-escritorio"><?= htmlspecialchars($empresa['telefono'] ?? '—') ?></td>

                                        <td class="vista-escritorio"><?= htmlspecialchars($empresa['email'] ?? '—') ?></td>

                                        <td class="vista-escritorio">
                                            <span class="status-badge <?= $estadoClase ?>">
                                                <?= htmlspecialchars($empresa['estado']) ?>
                                            </span>
                                        </td>

                                        <td class="vista-escritorio">

                                            <div class="user-actions">



                                                <button type="button" class="table-action-button icon-action-button list-edit"
                                                    title="Editar"
                                                    onclick="event.stopPropagation(); window.location.href='editar_empresa.php?id=<?= (int) $empresa['id'] ?>'">
                                                    <i class="bi bi-pencil"></i>
                                                </button>

                                                <button type="button" class="table-action-button icon-action-button danger"
                                                    title="Eliminar" onclick="event.stopPropagation(); abrirModalEliminarEmpresa(
        <?= (int) $empresa['id'] ?>,
        '<?= htmlspecialchars($empresa['nombre'], ENT_QUOTES, 'UTF-8') ?>'
    )">
                                                    <i class="bi bi-trash3"></i>
                                                </button>


                                                <?php if (!empty($empresa['usuario_id'])): ?>

                                                    <button type="button" class="table-action-button icon-action-button"
                                                        title="Restablecer contraseña" onclick="event.stopPropagation(); window.abrirModalResetPasswordEmpresa(
        <?= (int) $empresa['usuario_id'] ?>,
        '<?= htmlspecialchars(trim($empresa['usuario_nombre'] . ' ' . $empresa['usuario_apellidos']), ENT_QUOTES, 'UTF-8') ?>'
    )">
                                                        <i class="bi bi-key"></i>
                                                    </button>

                                                <?php endif; ?>


                                                <?php if ($empresa['estado'] === 'Activo'): ?>

                                                    <!-- Desactivar pide motivo, así que abre un
                                                         modal en vez de ir directo (igual que en
                                                         el listado de usuarios). -->

                                                    <button type="button"
                                                        class="table-action-button icon-action-button estado-toggle activo"
                                                        title="Activo — clic para desactivar" onclick="event.stopPropagation(); window.abrirModalDesactivarEmpresa(
        <?= (int) $empresa['id'] ?>,
        '<?= htmlspecialchars($empresa['nombre'], ENT_QUOTES, 'UTF-8') ?>'
    )">
                                                        <i class="bi bi-unlock-fill"></i>
                                                    </button>

                                                <?php else: ?>

                                                    <!-- Activar no necesita motivo: va directo. -->

                                                    <a href="cambiar_estado_empresa.php?id=<?= (int) $empresa['id'] ?>"
                                                        class="table-action-button icon-action-button estado-toggle inactivo"
                                                        title="Inactivo — clic para activar" onclick="event.stopPropagation();">
                                                        <i class="bi bi-lock-fill"></i>
                                                    </a>

                                                <?php endif; ?>




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


    <script src="../../js/exportar-pdf.js"></script>
    <script src="../../js/multi-select-filter.js"></script>
    <script src="../../js/notificacion-eliminacion.js"></script>
    <script src="../../js/empresas.js"></script>
    <script src="../../js/modal-detalle.js"></script>


    <!-- =====================================================
         MODAL CONFIRMAR ELIMINACIÓN
    ====================================================== -->

    <div id="modalEliminarEmpresa" class="modal-overlay" style="display: none;">

        <div class="modal-confirmacion deletion-modal">

            <div class="modal-icon" aria-hidden="true"><i class="bi bi-trash3"></i></div>

            <h2>Eliminar empresa</h2>

            <p>
                ¿Estás seguro de que quieres eliminar la empresa <strong id="nombreEmpresaEliminar"></strong>?
            </p>

            <p class="modal-warning">
                Se eliminará también su usuario de acceso asociado. Esta acción no se puede deshacer.
            </p>

            <div class="modal-actions">

                <button type="button" class="modal-button modal-button-cancel" onclick="cerrarModalEliminarEmpresa()">
                    Cancelar
                </button>

                <button type="button" class="modal-button modal-button-delete" onclick="confirmarEliminarEmpresa()">
                    Eliminar empresa
                </button>

            </div>

        </div>

    </div>


    <!-- =====================================================
         MODAL DESACTIVAR EMPRESA (PIDE MOTIVO)
         =====================================================
         Mismo requisito que en crear_empresa.php: pasar a
         Inactivo exige indicar un motivo. Al desactivar aquí
         también se desactiva el usuario de acceso de la
         empresa (ver cambiar_estado_empresa.php), igual que al
         desactivar ese usuario desde Usuarios se desactiva la
         empresa.
    ====================================================== -->

    <div id="modalDesactivarEmpresa" class="modal-overlay" style="display: none;">

        <div class="modal-confirmacion">

            <div class="modal-icon">
                🔒
            </div>

            <h2>Desactivar empresa</h2>

            <p>
                ¿Seguro que quieres desactivar
                <strong id="nombreEmpresaDesactivar"></strong>?
            </p>

            <p class="modal-warning">
                También se desactivará su usuario de acceso asociado.
            </p>

            <form id="formDesactivarEmpresa" action="cambiar_estado_empresa.php" method="POST" novalidate>

                <input type="hidden" name="id" id="idEmpresaDesactivar">

                <div class="form-group">

                    <label for="motivoDesactivarEmpresa">
                        Motivo
                    </label>

                    <select id="motivoDesactivarEmpresa" name="motivo" required>
                        <option value="">Selecciona un motivo</option>
                        <option value="impago">Impago</option>
                        <option value="fin_contrato">Fin de contrato</option>
                    </select>

                    <span class="field-error" id="error-motivoDesactivarEmpresa"></span>

                </div>

                <div class="modal-actions">

                    <button type="button" class="modal-button modal-button-cancel"
                        onclick="cerrarModalDesactivarEmpresa()">
                        Cancelar
                    </button>

                    <button type="submit" class="modal-button modal-button-delete">
                        Desactivar empresa
                    </button>

                </div>

            </form>

        </div>

    </div>


    <!-- =====================================================
         MODAL RESTABLECER CONTRASEÑA (USUARIO DE LA EMPRESA)
    ====================================================== -->

    <div id="modalResetPasswordEmpresa" class="modal-overlay" style="display: none;">

        <div class="modal-confirmacion">

            <div class="modal-icon">
                🔑
            </div>

            <h2>Restablecer contraseña</h2>

            <p>
                ¿Seguro que quieres restablecer la contraseña de
                <strong id="nombreEmpresaResetPassword"></strong>
                a la contraseña inicial?
            </p>

            <p class="modal-warning">
                El usuario deberá cambiarla en su próximo acceso.
            </p>

            <div class="modal-actions">

                <button type="button" class="modal-button modal-button-cancel"
                    onclick="cerrarModalResetPasswordEmpresa()">
                    Cancelar
                </button>

                <button type="button" class="modal-button modal-button-primary"
                    onclick="confirmarResetPasswordEmpresa()">
                    Restablecer contraseña
                </button>

            </div>

        </div>

    </div>


    <!-- =====================================================
         TARJETA DE DETALLE (SOLO MÓVIL)
    ====================================================== -->

    <div id="modalDetalleEmpresa" class="modal-overlay" style="display: none;">

        <div class="modal-detalle">

            <div class="modal-detalle-header">

                <div class="modal-detalle-avatar" id="detalleEmpresaAvatar"></div>

                <div class="modal-detalle-titulo">
                    <h2 id="detalleEmpresaNombre"></h2>
                </div>

                <button type="button" class="modal-detalle-close" onclick="cerrarModalDetalleEmpresa()"
                    aria-label="Cerrar">
                    ✕
                </button>

            </div>

            <div class="usuario-detalle-grid">

                <div class="usuario-detalle-item">
                    <span>Código</span>
                    <strong id="detalleEmpresaCodigo"></strong>
                </div>

                <div class="usuario-detalle-item">
                    <span>CIF</span>
                    <strong id="detalleEmpresaCif"></strong>
                </div>

                <div class="usuario-detalle-item">
                    <span>Dirección</span>
                    <strong id="detalleEmpresaDireccion"></strong>
                </div>

                <div class="usuario-detalle-item">
                    <span>Teléfono</span>
                    <strong id="detalleEmpresaTelefono"></strong>
                </div>

                <div class="usuario-detalle-item">
                    <span>Email</span>
                    <strong id="detalleEmpresaEmail"></strong>
                </div>

                <div class="usuario-detalle-item"></div>

                <div class="usuario-detalle-item">
                    <span>Estado</span>
                    <strong id="detalleEmpresaEstado"></strong>
                </div>

                <div class="usuario-detalle-item" id="detalleEmpresaMotivoItem">
                    <span>Motivo</span>
                    <strong id="detalleEmpresaMotivo"></strong>
                </div>

            </div>

            <div class="modal-detalle-acciones">

                <a href="#" class="config-save-button" id="btnDetalleEditarEmpresa">
                    Editar
                </a>

                <button type="button" class="table-action-button danger" id="btnDetalleEliminarEmpresa">
                    Eliminar
                </button>

            </div>

        </div>

    </div>

</body>

</html>