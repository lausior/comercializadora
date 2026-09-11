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


                                <!-- USUARIO -->

                                <th>

                                    <div class="table-header-content">

                                        <span>
                                            Usuario
                                        </span>

                                        <button type="button" class="sort-button" data-column="0"
                                            title="Ordenar por usuario">
                                            ↕
                                        </button>

                                    </div>

                                </th>



                                <!-- EMAIL -->

                                <th>

                                    <div class="table-header-content">

                                        <span>
                                            Email
                                        </span>

                                        <button type="button" class="sort-button" data-column="1"
                                            title="Ordenar por email">
                                            ↕
                                        </button>

                                    </div>

                                </th>



                                <!-- ROL -->

                                <th>

                                    <div class="table-header-content">

                                        <span>
                                            Rol
                                        </span>

                                        <button type="button" class="sort-button" data-column="2"
                                            title="Ordenar por rol">
                                            ↕
                                        </button>

                                    </div>

                                </th>



                                <!-- EMPRESA -->

                                <th>

                                    <div class="table-header-content">

                                        <span>
                                            Empresa
                                        </span>

                                        <button type="button" class="sort-button" data-column="3"
                                            title="Ordenar por empresa">
                                            ↕
                                        </button>

                                    </div>

                                </th>



                                <!-- ÚLTIMO ACCESO -->

                                <th>

                                    <div class="table-header-content">

                                        <span>
                                            Último acceso
                                        </span>

                                        <button type="button" class="sort-button" data-column="4"
                                            title="Ordenar por último acceso">
                                            ↕
                                        </button>

                                    </div>

                                </th>



                                <!-- ESTADO -->

                                <th>

                                    <div class="table-header-content">

                                        <span>
                                            Estado
                                        </span>

                                        <button type="button" class="sort-button" data-column="5"
                                            title="Ordenar por estado">
                                            ↕
                                        </button>

                                    </div>

                                </th>



                                <!-- ACCIONES -->

                                <th>

                                    <span>
                                        Acciones
                                    </span>

                                </th>

                            </tr>



                            <!-- =================================================
                                 FILTROS
                            ================================================== -->

                            <tr class="usuarios-filter-row-table">


                                <!-- USUARIO -->

                                <th>

                                    <input type="text" class="column-filter" data-column="0"
                                        placeholder="Buscar usuario...">

                                </th>



                                <!-- EMAIL -->

                                <th>

                                    <input type="text" class="column-filter" data-column="1"
                                        placeholder="Buscar email...">

                                </th>



                                <!-- ROL -->

                                <th>

                                    <select class="column-filter" data-column="2">

                                        <option value="">
                                            Todos
                                        </option>

                                        <option value="Administrador">
                                            Administrador
                                        </option>

                                        <option value="Supervisor">
                                            Supervisor
                                        </option>

                                        <option value="Operador">
                                            Operador
                                        </option>

                                        <option value="Consulta">
                                            Consulta
                                        </option>

                                    </select>

                                </th>



                                <!-- EMPRESA -->

                                <th>

                                    <select class="column-filter" data-column="3">

                                        <option value="">
                                            Todas
                                        </option>

                                        <?php

                                        $stmtEmpresas = $pdo->query("
                                            SELECT id, nombre
                                            FROM empresas
                                            ORDER BY nombre
                                        ");

                                        $empresasFiltro = $stmtEmpresas->fetchAll(PDO::FETCH_ASSOC);

                                        foreach ($empresasFiltro as $empresaFiltro):

                                            ?>

                                            <option value="<?= htmlspecialchars($empresaFiltro['nombre']) ?>">
                                                <?= htmlspecialchars($empresaFiltro['nombre']) ?>
                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                </th>



                                <!-- ÚLTIMO ACCESO -->

                                <th>

                                    <input type="text" class="column-filter" data-column="4"
                                        placeholder="Buscar fecha...">

                                </th>



                                <!-- ESTADO -->

                                <th>

                                    <select class="column-filter" data-column="5">

                                        <option value="">
                                            Todos
                                        </option>

                                        <option value="Activo">
                                            Activo
                                        </option>

                                        <option value="Inactivo">
                                            Inactivo
                                        </option>

                                        <option value="Bloqueado">
                                            Bloqueado
                                        </option>

                                    </select>

                                </th>



                                <!-- ACCIONES -->

                                <th></th>

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

                                    ?>


                                    <tr>


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



                                        <!-- =================================================
                                             EMAIL
                                        ================================================== -->

                                        <td>

                                            <?= htmlspecialchars($usuario['email']) ?>

                                        </td>



                                        <!-- =================================================
                                             ROL
                                        ================================================== -->

                                        <td>

                                            <span class="role-badge <?= htmlspecialchars($roleClass) ?>">

                                                <?= htmlspecialchars($usuario['rol']) ?>

                                            </span>

                                        </td>



                                        <!-- =================================================
                                             EMPRESA
                                        ================================================== -->

                                        <td>

                                            <?= htmlspecialchars($usuario['empresa']) ?>

                                        </td>



                                        <!-- =================================================
                                             ÚLTIMO ACCESO
                                        ================================================== -->

                                        <td>

                                            —

                                        </td>



                                        <!-- =================================================
                                             ESTADO
                                        ================================================== -->

                                        <td>

                                            <span class="status-badge status-active">

                                                Activo

                                            </span>

                                        </td>



                                        <!-- =================================================
                                             ACCIONES
                                        ================================================== -->

                                        <td>

                                            <div class="user-actions">

                                                <div class="user-actions">

                                                    <button type="button" class="table-action-button" onclick="abrirModalEliminar(
        <?= (int) $usuario['id'] ?>,
        '<?= htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8') ?>'
    )">
                                                        Eliminar
                                                    </button>



                                                <button type="button" class="table-action-button"
                                                    onclick="window.location.href='editar_usuario.php?id=<?= (int) $usuario['id'] ?>'">
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


    <script src="../../js/usuarios.js"></script>

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


<script src="../../js/usuarios.js"></script>

</body>

</html>