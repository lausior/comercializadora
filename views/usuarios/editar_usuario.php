<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('usuarios');

require_once '../../config/database.php';


// =====================================================
// COMPROBAR ID DEL USUARIO
// =====================================================

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    header('Location: usuarios.php');
    exit;

}

$idUsuario = (int) $_GET['id'];


// =====================================================
// OBTENER USUARIO
// =====================================================

$stmtUsuario = $pdo->prepare("
    SELECT
        id,
        username,
        nombre,
        apellidos,
        email,
        telefono,
        id_empresa,
        id_rol,
        creado_por
    FROM usuarios
    WHERE id = ?
");

$stmtUsuario->execute([$idUsuario]);

$usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);


// =====================================================
// COMPROBAR QUE EXISTE
// =====================================================

if (!$usuario) {

    header('Location: usuarios.php');
    exit;

}


// =====================================================
// COMPROBAR QUE PUEDE VER/EDITAR ESTE USUARIO
// =====================================================
//
// Oculto en el listado no es suficiente: sin esto, una
// EMPRESA podría editar un usuario de otra empresa (o de
// NG/SRG) tecleando su id en la URL directamente.
//
// =====================================================

if (!puedeVerUsuario($usuario['creado_por'] !== null ? (int) $usuario['creado_por'] : null)) {

    header('Location: usuarios.php?error=sin_permiso');
    exit;

}


// =====================================================
// OBTENER EMPRESAS
// =====================================================

// SRG puede reasignar el usuario a cualquier empresa.
// El resto de roles solo puede editar usuarios de su
// propia empresa (ya comprobado arriba), así que el
// desplegable solo le ofrece esa misma empresa.

if (rolActual() === ROL_SRG) {

    $stmtEmpresas = $pdo->query("
        SELECT id, nombre
        FROM empresas
        ORDER BY nombre
    ");

    $empresas = $stmtEmpresas->fetchAll(PDO::FETCH_ASSOC);

} else {

    $stmtEmpresas = $pdo->prepare("
        SELECT id, nombre
        FROM empresas
        WHERE id = ?
    ");

    $stmtEmpresas->execute([$usuario['id_empresa']]);

    $empresas = $stmtEmpresas->fetchAll(PDO::FETCH_ASSOC);

}


// =====================================================
// OBTENER ROLES
// =====================================================

$stmtRoles = $pdo->query("
    SELECT id, nombre
    FROM roles
    ORDER BY id
");

$roles = $stmtRoles->fetchAll(PDO::FETCH_ASSOC);

// Igual que en crear_usuario.php: nadie puede asignar un
// rol más privilegiado que el suyo propio.

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

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Editar usuario - Comparador Eléctrico
    </title>

    <link
        rel="stylesheet"
        href="../../css/style.css"
    >

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

                    <h1>
                        Usuarios
                    </h1>

                    <p>
                        Editar usuario
                    </p>

                </div>


                <div class="page-header-actions">

                    <div class="page-date">
                        9 septiembre 2026
                    </div>


                    <a
                        href="usuarios.php"
                        class="config-save-button"
                    >
                        ← Volver
                    </a>

                </div>

            </div>



            <!-- =================================================
                 FORMULARIO
            ================================================== -->

            <div class="config-card">

                <h2>
                    Datos del usuario
                </h2>


                <form
                    action="actualizar_usuario.php"
                    method="POST"
                    novalidate
                >


                    <!-- =================================================
                         ID DEL USUARIO
                    ================================================== -->

                    <input
                        type="hidden"
                        name="id"
                        value="<?= (int) $usuario['id'] ?>"
                    >


                    <!-- =========================
                         MENSAJE DE ERROR GENERAL
                    ========================== -->

                    <div class="form-error-general" id="form-error-general" role="alert" style="display: none;"></div>



                    <!-- =========================
                         NOMBRE
                    ========================== -->

                    <div class="form-group">

                        <label for="nombre">
                            Nombre
                        </label>

                        <input
                            type="text"
                            id="nombre"
                            name="nombre"
                            value="<?= htmlspecialchars($usuario['nombre']) ?>"
                            required
                        >

                        <span class="field-error" id="error-nombre"></span>

                    </div>



                    <!-- =========================
                         APELLIDOS
                    ========================== -->

                    <div class="form-group">

                        <label for="apellidos">
                            Apellidos
                        </label>

                        <input
                            type="text"
                            id="apellidos"
                            name="apellidos"
                            value="<?= htmlspecialchars($usuario['apellidos']) ?>"
                            required
                        >

                        <span class="field-error" id="error-apellidos"></span>

                    </div>



                    <!-- =========================
                         USERNAME
                    ========================== -->

                    <div class="form-group">

                        <label for="username">
                            Username
                        </label>

                        <input
                            type="text"
                            id="username"
                            name="username"
                            value="<?= htmlspecialchars($usuario['username']) ?>"
                            required
                        >

                        <span class="field-error" id="error-username"></span>

                    </div>



                    <!-- =========================
                         EMAIL
                    ========================== -->

                    <div class="form-group">

                        <label for="email">
                            Email
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="<?= htmlspecialchars($usuario['email']) ?>"
                            required
                        >

                        <span class="field-error" id="error-email"></span>

                    </div>



                    <!-- =========================
                         TELEFONO
                    ========================== -->

                    <div class="form-group">

                        <label for="telefono">
                            Teléfono
                        </label>

                        <input
                            type="tel"
                            id="telefono"
                            name="telefono"
                            value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>"
                        >

                        <span class="field-error" id="error-telefono"></span>

                    </div>



                    <!-- =========================
                         EMPRESA
                    ========================== -->

                    <div class="form-group">

                        <label for="id_empresa">
                            Empresa
                        </label>


                        <select
                            id="id_empresa"
                            name="id_empresa"
                            required
                        >

                            <option value="">
                                Seleccionar empresa
                            </option>


                            <?php foreach ($empresas as $empresa): ?>

                                <option
                                    value="<?= (int) $empresa['id'] ?>"
                                    <?= $empresa['id'] == $usuario['id_empresa'] ? 'selected' : '' ?>
                                >

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


                        <select
                            id="id_rol"
                            name="id_rol"
                            required
                        >

                            <option value="">
                                Seleccionar rol
                            </option>


                            <?php foreach ($roles as $rol): ?>

                                <option
                                    value="<?= (int) $rol['id'] ?>"
                                    <?= $rol['id'] == $usuario['id_rol'] ? 'selected' : '' ?>
                                >

                                    <?= htmlspecialchars($rol['nombre']) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                        <span class="field-error" id="error-id_rol"></span>

                    </div>



                    <!-- =================================================
                         INFORMACIÓN DE CONTRASEÑA
                    ================================================== -->

                    <div class="form-info">

                        <p>
                            La contraseña actual del usuario
                            no se modificará al guardar estos cambios.
                        </p>

                        <p>
                            Para restablecerla a la contraseña inicial,
                            usa el botón "Restablecer contraseña".
                        </p>

                    </div>



                    <!-- =================================================
                         BOTONES
                    ================================================== -->

                    <div class="form-actions form-actions-split">


                        <button
                            type="button"
                            class="config-cancel-button"
                            onclick="abrirModalResetPassword()"
                        >
                            Restablecer contraseña
                        </button>


                        <div class="form-actions-right">

                            
                            <button
                                type="submit"
                                class="config-save-button"
                            >
                                Guardar cambios
                            </button>

                            <a
                                href="usuarios.php"
                                class="config-cancel-button"
                            >
                                Cancelar
                            </a>

                        </div>


                    </div>


                </form>

            </div>


        </main>


    </div>



    <!-- =================================================
         FOOTER
    ================================================== -->

    <?php include '../../templates/footer.php'; ?>


    <!-- =====================================================
         MODAL CONFIRMAR RESTABLECER CONTRASEÑA
    ====================================================== -->

    <div id="modalResetPassword" class="modal-overlay" style="display: none;">

        <div class="modal-confirmacion">

            <div class="modal-icon">
                🔑
            </div>

            <h2>Restablecer contraseña</h2>

            <p>
                ¿Seguro que quieres restablecer la contraseña de
                <strong><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos'], ENT_QUOTES, 'UTF-8') ?></strong>
                a la contraseña inicial?
            </p>

            <p class="modal-warning">
                El usuario deberá cambiarla en su próximo acceso.
            </p>

            <div class="modal-actions">

                <button type="button" class="modal-button modal-button-cancel"
                    onclick="cerrarModalResetPassword()">
                    Cancelar
                </button>

                <button type="button" class="modal-button modal-button-primary"
                    onclick="confirmarResetPassword()">
                    Restablecer contraseña
                </button>

            </div>

        </div>

    </div>


    <script src="../../js/usuarios.js"></script>

    <script>

        const modalResetPassword = document.getElementById('modalResetPassword');

        window.abrirModalResetPassword = function () {

            if (modalResetPassword) {
                modalResetPassword.style.display = 'flex';
                document.body.classList.add('modal-abierto');
            }

        };

        window.cerrarModalResetPassword = function () {

            if (modalResetPassword) {
                modalResetPassword.style.display = 'none';
                document.body.classList.remove('modal-abierto');
            }

        };

        window.confirmarResetPassword = function () {

            window.location.href = 'resetear_password.php?id=<?= (int) $usuario['id'] ?>';

        };

        if (modalResetPassword) {

            modalResetPassword.addEventListener('click', event => {

                if (event.target === modalResetPassword) {
                    window.cerrarModalResetPassword();
                }

            });

        }

        document.addEventListener('keydown', event => {

            if (
                event.key === 'Escape' &&
                modalResetPassword &&
                modalResetPassword.style.display !== 'none'
            ) {
                window.cerrarModalResetPassword();
            }

        });

    </script>

</body>

</html>