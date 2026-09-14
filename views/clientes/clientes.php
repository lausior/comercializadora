<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('usuarios');

require_once '../../config/database.php';


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
        u.creado_por,
        e.nombre AS empresa,
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


// Actualmente no tenemos campo "estado" en usuarios.
// Por ahora todos los usuarios creados están activos.
$usuariosActivos = $totalUsuarios;
$usuariosInactivos = 0;


// =====================================================
// CONTAR ADMINISTRADORES
// =====================================================

$administradores = 0;

foreach ($usuarios as $usuario) {

    if (strcasecmp($usuario['rol'], 'Administrador') === 0) {
        $administradores++;
    }
}

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

                    <div class="page-date">
                        9 septiembre 2026
                    </div>

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


                    <div class="panel-header-actions" style="display:flex; gap:10px; align-items:center;">

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


                        <button type="button" class="filtros-toggle-button" id="btnToggleFiltros"
                            aria-expanded="false" aria-controls="panelFiltrosUsuarios">
                            <span>Filtros</span>
                            <span class="chevron">▾</span>
                        </button>

                    </div>

                </div>


                <!-- =================================================
                     PANEL DE FILTROS (DESPLEGABLE)
                ================================================== -->

                <div class="filtros-panel" id="panelFiltrosUsuarios" style="display:none;">

                    <div class="filtros-panel-campos">

                        <div class="filter-group">
                            <label for="filtroUsuario">Usuario</label>
                            <input type="text" id="filtroUsuario" class="column-filter" data-field="usuario"
                                placeholder="Buscar usuario...">
                        </div>

                        <div class="filter-group">
                            <label for="filtroEmail">Email</label>
                            <input type="text" id="filtroEmail" class="column-filter" data-field="email"
                                placeholder="Buscar email...">
                        </div>

                        <div class="filter-group">
                            <label for="filtroRol">Rol</label>
                            <select id="filtroRol" class="column-filter" data-field="rol">

                                <option value="">Todos</option>
                                <option value="Administrador">Administrador</option>
                                <option value="Supervisor">Supervisor</option>
                                <option value="Operador">Operador</option>
                                <option value="Consulta">Consulta</option>

                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="filtroEmpresa">Empresa</label>
                            <select id="filtroEmpresa" class="column-filter" data-field="empresa">

                                <option value="">Todas</option>

                                <?php

                                // Solo las empresas que aparecen en
                                // $usuarios (ya filtrado por rol), para
                                // no listar en el desplegable empresas
                                // que este usuario no puede ver.

                                $empresasFiltro = [];

                                foreach ($usuarios as $usuarioFiltro) {
                                    $empresasFiltro[$usuarioFiltro['empresa']] = true;
                                }

                                $empresasFiltro = array_keys($empresasFiltro);

                                sort($empresasFiltro);

                                foreach ($empresasFiltro as $empresaFiltro):

                                    ?>

                                    <option value="<?= htmlspecialchars($empresaFiltro) ?>">
                                        <?= htmlspecialchars($empresaFiltro) ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>
                        </div>

                    </div>

                    <div class="filtros-panel-acciones">
                        <button type="button" class="panel-action" id="btnLimpiarFiltros">
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

                            <tr>

                                <th>

                                    <div class="table-header-content">

                                        <span>
                                            Usuario
                                        </span>

                                        <button type="button" class="sort-button" id="botonOrdenNombre"
                                            title="Ordenar por usuario">
                                            ↕
                                        </button>

                                    </div>

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

                                    <td style="text-align:center; padding:40px;">

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

                                    ?>


                                    <tr class="fila-clicable"
                                        data-id="<?= (int) $usuario['id'] ?>"
                                        data-nombre="<?= htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8') ?>"
                                        data-iniciales="<?= htmlspecialchars($iniciales, ENT_QUOTES, 'UTF-8') ?>"
                                        data-usuario="<?= htmlspecialchars($usuario['username'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-email="<?= htmlspecialchars($usuario['email'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-telefono="<?= htmlspecialchars($usuario['telefono'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        data-rol="<?= htmlspecialchars($usuario['rol'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-empresa="<?= htmlspecialchars($usuario['empresa'], ENT_QUOTES, 'UTF-8') ?>"
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

                                                        @<?= htmlspecialchars($usuario['username']) ?>

                                                    </span>


                                                </div>


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

        </div>

        <div class="modal-detalle-acciones">

        <button type="button" class="table-action-button danger" id="btnDetalleEliminarUsuario">
                Eliminar
            </button>

         <a href="#" class="config-save-button" id="btnDetalleEditarUsuario">
                Editar
            </a>

            

           

        </div>

    </div>

</div>


<script src="../../js/usuarios.js"></script>

</body>

</html>