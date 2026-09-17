<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('usuarios');

require_once '../../config/database.php';


// =====================================================
// OBTENER EMPRESAS
// =====================================================
//
// EMPRESA solo puede crear usuarios para su propia
// empresa. SRG y NG pueden elegir cualquiera (NG la
// necesita para dar de alta al primer usuario de una
// empresa nueva).
//
// =====================================================

if (rolActual() === ROL_EMPRESA) {

    $stmtEmpresas = $pdo->prepare("
        SELECT id, nombre
        FROM empresas
        WHERE id = ?
    ");

    $stmtEmpresas->execute([$_SESSION['id_empresa']]);

    $empresas = $stmtEmpresas->fetchAll(PDO::FETCH_ASSOC);

} else {

    $stmtEmpresas = $pdo->query("
        SELECT id, nombre
        FROM empresas
        ORDER BY nombre
    ");

    $empresas = $stmtEmpresas->fetchAll(PDO::FETCH_ASSOC);

}


// =====================================================
// OBTENER ROLES
// =====================================================
//
// Nadie puede crear un usuario con un rol más privilegiado
// que el suyo propio: EMPRESA solo puede dar de alta
// EMPRESA o USUARIO; NG no puede crear otro SRG.
//
// =====================================================

$stmtRoles = $pdo->query("
    SELECT id, nombre
    FROM roles
    ORDER BY id
");

$roles = $stmtRoles->fetchAll(PDO::FETCH_ASSOC);

if (rolActual() === ROL_EMPRESA) {

    $roles = array_values(array_filter(
        $roles,
        fn(array $r): bool => in_array($r['nombre'], [ROL_EMPRESA, ROL_USUARIO], true)
    ));

} elseif (rolActual() === ROL_NG) {

    $roles = array_values(array_filter(
        $roles,
        fn(array $r): bool => $r['nombre'] !== ROL_SRG
    ));

}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Crear usuario - Comparador Eléctrico</title>

    <link rel="stylesheet" href="../../css/style.css">

</head>

<body>


    <!-- =================================================
         HEADER
    ================================================== -->

    <?php include '../../templates/header.php'; ?>


    <div class="app-container">


        <!-- =================================================
             SIDEBAR
        ================================================== -->

        <?php include '../../templates/sidebar.php'; ?>


        <!-- =================================================
             CONTENIDO PRINCIPAL
        ================================================== -->

        <main class="main-content">


            <!-- =================================================
                 CABECERA
            ================================================== -->

            <div class="page-header">

                <div>

                    <h1>Usuarios</h1>

                    <p>
                        Crear nuevo usuario
                    </p>

                </div>


                <div class="page-header-actions">

                    <a href="usuarios.php" class="config-save-button">
                        ← Volver
                    </a>

                </div>

            </div>


            <!-- =================================================
                 FORMULARIO
            ================================================== -->

            <div class="config-card">

                <h2>Datos del usuario</h2>

                <form action="guardar_usuario.php" method="POST" novalidate>


                    <!-- =========================
                         MENSAJE DE ERROR GENERAL
                    ========================== -->

                    <div class="form-error-general" id="form-error-general" role="alert" style="display: none;"></div>


                    <div class="form-grid">


                        <!-- =========================
                             NOMBRE
                        ========================== -->

                        <div class="form-group">

                            <label for="nombre">
                                Nombre
                            </label>

                            <input type="text" id="nombre" name="nombre" required>

                            <span class="field-error" id="error-nombre"></span>

                        </div>


                        <!-- =========================
                             APELLIDOS
                        ========================== -->

                        <div class="form-group">

                            <label for="apellidos">
                                Apellidos
                            </label>

                            <input type="text" id="apellidos" name="apellidos" required>

                            <span class="field-error" id="error-apellidos"></span>

                        </div>


                        <!-- =========================
                             USERNAME
                        ========================== -->

                        <div class="form-group">

                            <label for="username">
                                Username
                            </label>

                            <input type="text" id="username" name="username" required>

                            <span class="field-error" id="error-username"></span>

                        </div>


                        <!-- =========================
                             EMAIL
                        ========================== -->

                        <div class="form-group">

                            <label for="email">
                                Email
                            </label>

                            <input type="email" id="email" name="email" required>

                            <span class="field-error" id="error-email"></span>

                        </div>


                        <!-- =========================
                             TELEFONO
                        ========================== -->

                        <div class="form-group">

                            <label for="telefono">
                                Teléfono
                            </label>

                            <input type="tel" id="telefono" name="telefono">

                            <span class="field-error" id="error-telefono"></span>

                        </div>


                        <!-- =========================
                             EMPRESA
                        ========================== -->

                        <div class="form-group">

                            <label for="id_empresa">
                                Empresa
                            </label>

                            <select id="id_empresa" name="id_empresa" required>

                                <option value="">
                                    Seleccionar empresa
                                </option>


                                <?php foreach ($empresas as $empresa): ?>

                                    <option value="<?= $empresa['id'] ?>">
                                        <?= htmlspecialchars($empresa['nombre']) ?>
                                    </option>

                                <?php endforeach; ?>


                            </select>

                            <span class="field-error" id="error-id_empresa"></span>

                        </div>


                        <!-- =========================
                             ROL
                        ========================== -->

                        <div class="form-group">

                            <label for="id_rol">
                                Rol
                            </label>

                            <select id="id_rol" name="id_rol" required>

                                <option value="">
                                    Seleccionar rol
                                </option>


                                <?php foreach ($roles as $rol): ?>

                                    <option value="<?= $rol['id'] ?>">
                                        <?= htmlspecialchars($rol['nombre']) ?>
                                    </option>

                                <?php endforeach; ?>


                            </select>

                            <span class="field-error" id="error-id_rol"></span>

                        </div>


                        <!-- =========================
                             ESTADO
                        ========================== -->

                        <div class="form-group">

                            <label for="estado">
                                Estado
                            </label>

                            <select id="estado" name="estado" required>

                                <option value="Activo" selected>Activo</option>
                                <option value="Inactivo">Inactivo</option>

                            </select>

                            <span class="field-error" id="error-estado"></span>

                        </div>


                    </div>


                    <!-- =========================
                         MOTIVO (SOLO SI INACTIVO)
                    ========================== -->

                    <div class="form-group hidden" id="grupo_motivo_inactivo">

                        <label for="motivo_inactivo">
                            Motivo
                        </label>

                        <textarea id="motivo_inactivo" name="motivo_inactivo" rows="3"
                            placeholder="Explica por qué el usuario se marca como inactivo"></textarea>

                        <span class="field-error" id="error-motivo_inactivo"></span>

                    </div>


                    <!-- =================================================
                         INFORMACIÓN DE CONTRASEÑA
                    ================================================== -->

                    <div class="form-info">

                        <p>
                            La contraseña inicial será asignada
                            automáticamente por el sistema.
                        </p>

                        <p>
                            El usuario deberá cambiarla en su
                            primer acceso.
                        </p>

                    </div>


                    <!-- =================================================
                         BOTÓN
                    ================================================== -->

                    <div class="form-actions">

                        <a href="usuarios.php" class="config-cancel-button">
                            Cancelar
                        </a>

                        <button type="submit" class="config-save-button">
                            Crear usuario
                        </button>

                    </div>


                </form>

            </div>


        </main>


    </div>


    <!-- =================================================
         FOOTER
    ================================================== -->

    <?php include '../../templates/footer.php'; ?>


    <script src="../../js/usuarios.js"></script>

</body>

</html>