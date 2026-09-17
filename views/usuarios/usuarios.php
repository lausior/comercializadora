<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('usuarios');

require_once '../../config/database.php';
require_once '../../includes/filtro_multiselect.php';


// =====================================================
// OBTENER USUARIOS DE LA BASE DE DATOS
// =====================================================

$stmtUsuarios = $pdo->query("
    SELECT
        u.id,
        u.username,
        u.nombre,
        u.apellidos,
        u.email,
        u.telefono,
        u.cambiar_password,
        u.id_empresa,
        u.estado,
        u.motivo_inactivo,
        u.creado_por,
        e.nombre AS empresa,
        e.codigo_empresa,
        r.nombre AS rol
    FROM usuarios u

    INNER JOIN empresas e
        ON u.id_empresa = e.id

    INNER JOIN roles r
        ON u.id_rol = r.id

    ORDER BY u.id DESC
");

$usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);


// =====================================================
// FILTRAR SEGÚN QUÉ USUARIOS PUEDE VER EL ROL ACTUAL
// =====================================================
//
// SRG los ve a todos. NG y EMPRESA solo ven a los que
// ellos mismos han dado de alta (ver puedeVerUsuario()
// en permisos.php). Y en este LISTADO, además, nadie se
// ve a sí mismo — sigue siendo gestionable si se entra a
// su edición por la URL directamente, solo se oculta aquí.
//
// =====================================================

$usuarios = array_values(array_filter(
    $usuarios,
    fn(array $usuario): bool =>
        (int) $usuario['id'] !== (int) ($_SESSION['id_usuario'] ?? 0)
        && puedeVerUsuario(
            $usuario['creado_por'] !== null ? (int) $usuario['creado_por'] : null
        )
));


// =====================================================
// ESTADÍSTICAS
// =====================================================

$totalUsuarios = count($usuarios);


$usuariosActivos = count(array_filter(
    $usuarios,
    fn(array $usuario): bool => $usuario['estado'] === 'Activo'
));

$usuariosInactivos = $totalUsuarios - $usuariosActivos;


// =====================================================
// CONTAR ADMINISTRADORES
// =====================================================

$administradores = 0;

foreach ($usuarios as $usuario) {

    if (strcasecmp($usuario['rol'], 'Administrador') === 0) {
        $administradores++;
    }
}


// =====================================================
// VALORES DISTINTOS PARA LOS DESPLEGABLES DE USUARIO/
// EMAIL/TELÉFONO
// =====================================================
//
// Igual que $rolesFiltro/$empresasFiltro más abajo: la
// lista de opciones del desplegable son los valores que
// realmente aparecen en $usuarios (ya filtrado por
// permisos).
//
// =====================================================

$nombresUsuarioFiltro = array_values(array_unique(array_map(
    fn(array $usuario): string => $usuario['nombre'] . ' ' . $usuario['apellidos'],
    $usuarios
)));
sort($nombresUsuarioFiltro);

$emailsFiltro = array_values(array_unique(array_column($usuarios, 'email')));
sort($emailsFiltro);

$telefonosFiltro = array_values(array_unique(array_filter(
    array_column($usuarios, 'telefono')
)));
sort($telefonosFiltro);

$estadosFiltro = ['Activo', 'Inactivo'];

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Usuarios - Comparador Eléctrico</title>

    <link rel="stylesheet" href="../../css/style.css">

</head>

<body>


    <?php include '../../templates/header.php'; ?>


    <div class="app-container">


        <?php include '../../templates/sidebar.php'; ?>


        <!-- =====================================================
             CONTENIDO
        ====================================================== -->

        <main class="main-content">


            <!-- =================================================
                 CABECERA
            ================================================== -->

            <div class="page-header">

                <div>

                    <h1>Usuarios</h1>

                    <p>
                        Gestión y administración de los usuarios del sistema
                    </p>

                </div>


                <div class="page-header-actions">
                    
                    <button type="button" class="config-secondary-button" id="btnExportarPDF">
                        📄 Exportar PDF
                    </button>

                    <a href="crear_usuario.php" class="config-save-button">
                        + Añadir usuario
                    </a>

                </div>

            </div>



            <!-- =================================================
                 RESUMEN
            ================================================== -->

            <section class="dashboard-cards">


                <!-- TOTAL USUARIOS -->

                <div class="dashboard-card">

                    <div class="card-icon blue">
                        👥
                    </div>

                    <div class="card-info">

                        <span class="card-label">
                            Total usuarios
                        </span>

                        <strong>
                            <?= $totalUsuarios ?>
                        </strong>

                        <small>
                            Usuarios registrados
                        </small>

                    </div>

                </div>



                <!-- USUARIOS ACTIVOS -->

                <div class="dashboard-card">

                    <div class="card-icon green">
                        ✓
                    </div>

                    <div class="card-info">

                        <span class="card-label">
                            Usuarios activos
                        </span>

                        <strong>
                            <?= $usuariosActivos ?>
                        </strong>

                        <small>
                            Actualmente activos
                        </small>

                    </div>

                </div>



                <!-- USUARIOS INACTIVOS -->

                <div class="dashboard-card">

                    <div class="card-icon orange">
                        ◷
                    </div>

                    <div class="card-info">

                        <span class="card-label">
                            Usuarios inactivos
                        </span>

                        <strong>
                            <?= $usuariosInactivos ?>
                        </strong>

                        <small>
                            Requieren revisión
                        </small>

                    </div>

                </div>



                <!-- ADMINISTRADORES -->

                <div class="dashboard-card">

                    <div class="card-icon purple">
                        #
                    </div>

                    <div class="card-info">

                        <span class="card-label">
                            Administradores
                        </span>

                        <strong>
                            <?= $administradores ?>
                        </strong>

                        <small>
                            Con permisos elevados
                        </small>

                    </div>

                </div>


            </section>



            <!-- =====================================================
                 TABLA DE USUARIOS
            ====================================================== -->

            <section class="panel usuarios-table-panel">


                <div class="panel-header">

                    <div>

                        <h2>
                            Usuarios registrados
                        </h2>

                        <p id="usuariosContador">

                            <?= $totalUsuarios ?>

                            <?= $totalUsuarios === 1 ? 'usuario' : 'usuarios' ?>

                            encontrados

                        </p>

                    </div>


                    <div class="panel-header-actions" style="display:flex; gap:10px; align-items:center; flex-wrap: wrap;">

                        <!-- Solo en móvil: despliega el panel de filtros apilados -->
                        <button type="button" class="filtros-toggle-button vista-movil" id="btnToggleFiltros"
                            aria-expanded="false" aria-controls="panelFiltrosUsuarios">
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

                <div class="filtros-panel vista-movil" id="panelFiltrosUsuarios" style="display:none;">

                    <div class="filtros-panel-campos">

                        <div class="filter-group">
                            <label>Usuario</label>
                            <?php filtroMultiSelect('filtroUsuarioMovil', 0, 'Todos', $nombresUsuarioFiltro); ?>
                        </div>

                        <div class="filter-group">
                            <label>Email</label>
                            <?php filtroMultiSelect('filtroEmailMovil', 1, 'Todos', $emailsFiltro); ?>
                        </div>

                        <div class="filter-group">
                            <label>Teléfono</label>
                            <?php filtroMultiSelect('filtroTelefonoMovil', 2, 'Todos', $telefonosFiltro); ?>
                        </div>

                        <?php

                        // Solo los roles y empresas que realmente
                        // aparecen en $usuarios (ya filtrado por
                        // permisos), para no listar en los
                        // desplegables valores que este usuario no
                        // puede ver.

                        $rolesFiltro = [];
                        $empresasFiltro = [];

                        foreach ($usuarios as $usuarioFiltro) {
                            $rolesFiltro[$usuarioFiltro['rol']] = true;
                            $empresasFiltro[$usuarioFiltro['empresa']] = true;
                        }

                        $rolesFiltro = array_keys($rolesFiltro);
                        $empresasFiltro = array_keys($empresasFiltro);

                        sort($rolesFiltro);
                        sort($empresasFiltro);

                        ?>

                        <div class="filter-group">
                            <label>Rol</label>
                            <?php filtroMultiSelect('filtroRolMovil', 3, 'Todos', $rolesFiltro); ?>
                        </div>

                        <div class="filter-group">
                            <label>Empresa</label>
                            <?php filtroMultiSelect('filtroEmpresaMovil', 4, 'Todas', $empresasFiltro); ?>
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


                <!-- =================================================
                     TABLA
                ================================================== -->

                <div class="usuarios-table-container">

                    <table class="usuarios-table">


                        <!-- =================================================
                             CABECERA TABLA
                        ================================================== -->

                        <thead>

                            <!-- =================================================
                                 CABECERAS (las columnas 2ª en adelante y la
                                 fila de filtros solo se ven en escritorio;
                                 en móvil el CSS las oculta y deja solo
                                 "Usuario")
                            ================================================== -->

                            <tr>

                                <th>
                                    <div class="table-header-content">
                                        <span>Usuario</span>

                                        <button type="button" class="sort-button" data-column="0"
                                            title="Ordenar por usuario">
                                            ↕
                                        </button>
                                    </div>
                                </th>

                                <th class="vista-escritorio">
                                    <div class="table-header-content">
                                        <span>Email</span>

                                        <button type="button" class="sort-button" data-column="1"
                                            title="Ordenar por email">
                                            ↕
                                        </button>
                                    </div>
                                </th>

                                <th class="vista-escritorio">
                                    <div class="table-header-content">
                                        <span>Teléfono</span>

                                        <button type="button" class="sort-button" data-column="2"
                                            title="Ordenar por teléfono">
                                            ↕
                                        </button>
                                    </div>
                                </th>

                                <th class="vista-escritorio">
                                    <div class="table-header-content">
                                        <span>Rol</span>

                                        <button type="button" class="sort-button" data-column="3"
                                            title="Ordenar por rol">
                                            ↕
                                        </button>
                                    </div>
                                </th>

                                <th class="vista-escritorio">
                                    <div class="table-header-content">
                                        <span>Empresa</span>

                                        <button type="button" class="sort-button" data-column="4"
                                            title="Ordenar por empresa">
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
                                    <?php filtroMultiSelect('filtroUsuarioEscritorio', 0, 'Todos', $nombresUsuarioFiltro); ?>
                                </th>

                                <th>
                                    <?php filtroMultiSelect('filtroEmailEscritorio', 1, 'Todos', $emailsFiltro); ?>
                                </th>

                                <th>
                                    <?php filtroMultiSelect('filtroTelefonoEscritorio', 2, 'Todos', $telefonosFiltro); ?>
                                </th>

                                <th>
                                    <?php filtroMultiSelect('filtroRolEscritorio', 3, 'Todos', $rolesFiltro); ?>
                                </th>

                                <th>
                                    <?php filtroMultiSelect('filtroEmpresaEscritorio', 4, 'Todas', $empresasFiltro); ?>
                                </th>

                                <th>
                                    <?php filtroMultiSelect('filtroEstadoEscritorio', 5, 'Todos', $estadosFiltro); ?>
                                </th>

                                <th>
                                    <button type="button" class="panel-action" id="btnLimpiarFiltros">
                                        Limpiar filtros
                                    </button>
                                </th>

                            </tr>

                        </thead>



                        <!-- =================================================
                             USUARIOS
                        ================================================== -->

                        <tbody id="usuariosBody">


                            <?php if (empty($usuarios)): ?>


                                <!-- SIN USUARIOS -->

                                <tr>

                                    <td colspan="7" style="text-align:center; padding:40px;">

                                        No hay usuarios registrados.

                                    </td>

                                </tr>


                            <?php else: ?>


                                <?php foreach ($usuarios as $usuario): ?>


                                    <?php

                                    // =================================================
                                    // INICIALES
                                    // =================================================

                                    $iniciales =
                                        mb_substr($usuario['nombre'], 0, 1) .
                                        mb_substr($usuario['apellidos'], 0, 1);

                                    $iniciales = mb_strtoupper($iniciales);


                                    // =================================================
                                    // NOMBRE COMPLETO
                                    // =================================================

                                    $nombreCompleto =
                                        $usuario['nombre'] .
                                        ' ' .
                                        $usuario['apellidos'];


                                    // =================================================
                                    // CLASE DEL ROL
                                    // =================================================

                                    switch (strtolower($usuario['rol'])) {

                                        case 'administrador':

                                            $roleClass = 'role-admin';

                                            break;


                                        case 'supervisor':

                                            $roleClass = 'role-supervisor';

                                            break;


                                        case 'operador':

                                            $roleClass = 'role-operator';

                                            break;


                                        case 'consulta':

                                            $roleClass = 'role-viewer';

                                            break;


                                        default:

                                            $roleClass = '';

                                            break;
                                    }


                                    // =================================================
                                    // CLASE DEL ESTADO
                                    // =================================================

                                    $estadoClase = $usuario['estado'] === 'Activo'
                                        ? 'cliente-active'
                                        : 'cliente-inactive';

                                    $estadoPasswordUsuario = (int) $usuario['cambiar_password'] === 1
                                        ? 'Pendiente de cambio'
                                        : 'Contraseña establecida';

                                    // Usuario de acceso (login): codigo_empresa-id-username,
                                    // el mismo formato que se escribe en login.php.
                                    $usuarioAcceso =
                                        $usuario['codigo_empresa'] . '-' .
                                        $usuario['id'] . '-' .
                                        $usuario['username'];

                                    ?>


                                    <!-- fila-detalle: en móvil, pulsar la fila abre la
                                         tarjeta con toda la información (ver usuarios.js);
                                         en escritorio no hace nada, ahí ya se ve todo. -->

                                    <tr class="fila-detalle"
                                        data-id="<?= (int) $usuario['id'] ?>"
                                        data-nombre="<?= htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8') ?>"
                                        data-iniciales="<?= htmlspecialchars($iniciales, ENT_QUOTES, 'UTF-8') ?>"
                                        data-usuario="<?= htmlspecialchars($usuarioAcceso, ENT_QUOTES, 'UTF-8') ?>"
                                        data-email="<?= htmlspecialchars($usuario['email'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-telefono="<?= htmlspecialchars($usuario['telefono'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        data-rol="<?= htmlspecialchars($usuario['rol'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-empresa="<?= htmlspecialchars($usuario['empresa'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-estado="<?= htmlspecialchars($usuario['estado'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-motivo="<?= htmlspecialchars($usuario['motivo_inactivo'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        data-password-estado="<?= htmlspecialchars($estadoPasswordUsuario, ENT_QUOTES, 'UTF-8') ?>"
                                    >

                                        <!-- =================================================
                                             USUARIO
                                        ================================================== -->

                                        <td>

                                            <div class="usuario-cell">


                                                <div class="usuario-avatar">

                                                    <?= htmlspecialchars($iniciales) ?>

                                                </div>


                                                <div class="usuario-info">


                                                    <strong>

                                                        <?= htmlspecialchars($nombreCompleto) ?>

                                                    </strong>


                                                    <span>

                                                        <?= htmlspecialchars($usuarioAcceso) ?>

                                                    </span>


                                                </div>


                                            </div>

                                        </td>

                                        <td class="vista-escritorio"><?= htmlspecialchars($usuario['email']) ?></td>

                                        <td class="vista-escritorio"><?= htmlspecialchars($usuario['telefono'] ?? '—') ?></td>

                                        <td class="vista-escritorio">
                                            <span class="role-badge <?= $roleClass ?>">
                                                <?= htmlspecialchars($usuario['rol']) ?>
                                            </span>
                                        </td>

                                        <td class="vista-escritorio"><?= htmlspecialchars($usuario['empresa']) ?></td>

                                        <td class="vista-escritorio">
                                            <span class="status-badge <?= $estadoClase ?>">
                                                <?= htmlspecialchars($usuario['estado']) ?>
                                            </span>
                                        </td>

                                        <td class="vista-escritorio">

                                            <div class="user-actions">

                                                <button type="button" class="table-action-button icon-action-button list-edit"
                                                    title="Editar"
                                                    onclick="event.stopPropagation(); window.location.href='editar_usuario.php?id=<?= (int) $usuario['id'] ?>'">
                                                    <i class="bi bi-pencil"></i>
                                                </button>

                                                <button type="button" class="table-action-button icon-action-button danger"
                                                    title="Eliminar"
                                                    onclick="event.stopPropagation(); window.abrirModalEliminar(
        <?= (int) $usuario['id'] ?>,
        '<?= htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8') ?>'
    )">
                                                    <i class="bi bi-trash3"></i>
                                                </button>

                                                <button type="button" class="table-action-button icon-action-button"
                                                    title="Restablecer contraseña"
                                                    onclick="event.stopPropagation(); window.abrirModalResetPasswordListado(
        <?= (int) $usuario['id'] ?>,
        '<?= htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8') ?>'
    )">
                                                    <i class="bi bi-key"></i>
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

                        <label for="selectorPorPagina">
                            Mostrar:
                        </label>

                        <select id="selectorPorPagina" class="por-pagina-select">

                            <option value="5">
                                5
                            </option>

                            <option value="10">
                                10
                            </option>

                            <option value="todos">
                                Todos
                            </option>

                        </select>

                    </div>


                    <span id="usuariosMostrando">

                        Mostrando <?= $totalUsuarios ?>

                        de <?= $totalUsuarios ?>

                        <?= $totalUsuarios === 1 ? 'usuario' : 'usuarios' ?>

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



            <!-- =====================================================
                 ACTIVIDAD RECIENTE
            ====================================================== -->

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


                    <button type="button" class="panel-action">
                        Ver historial
                    </button>

                </div>



                <div class="activity-list">


                    <div class="activity-item">

                        <span class="activity-dot green"></span>

                        <div>

                            <strong>
                                Sistema preparado
                            </strong>

                            <span>
                                Gestión de usuarios conectada con la base de datos
                            </span>

                        </div>

                    </div>


                    <div class="activity-item">

                        <span class="activity-dot blue"></span>

                        <div>

                            <strong>
                                Usuarios cargados
                            </strong>

                            <span>
                                <?= $totalUsuarios ?>
                                <?= $totalUsuarios === 1 ? 'usuario encontrado' : 'usuarios encontrados' ?>
                            </span>

                        </div>

                    </div>


                </div>

            </section>


        </main>


    </div>


    <?php include '../../templates/footer.php'; ?>


    <!-- =====================================================
     MODAL CONFIRMAR ELIMINACIÓN
====================================================== -->

    <div
    id="modalEliminar"
    class="modal-overlay"
    style="display: none;"
>

    <div class="modal-confirmacion">

        <div class="modal-icon">
            ⚠
        </div>

        <h2>Eliminar usuario</h2>

        <p>
            ¿Estás seguro de que quieres eliminar al usuario
            <strong id="nombreUsuarioEliminar"></strong>?
        </p>

        <p class="modal-warning">
            Esta acción no se puede deshacer.
        </p>


        <div class="modal-actions">

            <button
                type="button"
                class="modal-button modal-button-cancel"
                onclick="cerrarModalEliminar()"
            >
                Cancelar
            </button>

            <button
                type="button"
                class="modal-button modal-button-delete"
                onclick="confirmarEliminarUsuario()"
            >
                Eliminar usuario
            </button>

        </div>

    </div>

</div>


<!-- =====================================================
     MODAL RESTABLECER CONTRASEÑA (DESDE EL LISTADO)
====================================================== -->

<div id="modalResetPasswordListado" class="modal-overlay" style="display: none;">

    <div class="modal-confirmacion">

        <div class="modal-icon">
            🔑
        </div>

        <h2>Restablecer contraseña</h2>

        <p>
            ¿Seguro que quieres restablecer la contraseña de
            <strong id="nombreUsuarioResetPassword"></strong>
            a la contraseña inicial?
        </p>

        <p class="modal-warning">
            El usuario deberá cambiarla en su próximo acceso.
        </p>

        <div class="modal-actions">

            <button type="button" class="modal-button modal-button-cancel"
                onclick="cerrarModalResetPasswordListado()">
                Cancelar
            </button>

            <button type="button" class="modal-button modal-button-primary"
                onclick="confirmarResetPasswordListado()">
                Restablecer contraseña
            </button>

        </div>

    </div>

</div>


<!-- =====================================================
     TARJETA DE DETALLE
====================================================== -->

<div id="modalDetalleUsuario" class="modal-overlay" style="display: none;">

    <div class="modal-detalle">

        <div class="modal-detalle-header">

            <div class="modal-detalle-avatar" id="detalleUsuarioAvatar"></div>

            <div class="modal-detalle-titulo">
                <h2 id="detalleUsuarioNombre"></h2>
                <span id="detalleUsuarioUsername"></span>
            </div>

            <button type="button" class="modal-detalle-close" onclick="cerrarModalDetalleUsuario()"
                aria-label="Cerrar">
                ✕
            </button>

        </div>

        <div class="usuario-detalle-grid">

            <div class="usuario-detalle-item">
                <span>Email</span>
                <strong id="detalleUsuarioEmail"></strong>
            </div>

            <div class="usuario-detalle-item">
                <span>Teléfono</span>
                <strong id="detalleUsuarioTelefono"></strong>
            </div>

            <div class="usuario-detalle-item">
                <span>Rol</span>
                <strong id="detalleUsuarioRol"></strong>
            </div>

            <div class="usuario-detalle-item">
                <span>Empresa</span>
                <strong id="detalleUsuarioEmpresa"></strong>
            </div>

            <div class="usuario-detalle-item">
                <span>Estado</span>
                <strong id="detalleUsuarioEstado"></strong>
            </div>

            <div class="usuario-detalle-item">
                <span>Estado de contraseña</span>
                <strong id="detalleUsuarioPasswordEstado"></strong>
            </div>

            <div class="usuario-detalle-item hidden" id="detalleUsuarioMotivoItem">
                <span>Motivo</span>
                <strong id="detalleUsuarioMotivo"></strong>
            </div>

        </div>

        <div class="modal-detalle-acciones">

            <a href="#" class="config-save-button" id="btnDetalleEditarUsuario">
                Editar
            </a>

            <button type="button" class="table-action-button danger" id="btnDetalleEliminarUsuario">
                Eliminar
            </button>

        </div>

    </div>

</div>


<script src="../../js/exportar-pdf.js"></script>
<script src="../../js/multi-select-filter.js"></script>
<script src="../../js/usuarios.js"></script>
<script src="../../js/modal-detalle.js"></script>

</body>

</html>