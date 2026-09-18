<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('usuarios');

require_once '../../config/database.php';
require_once '../../includes/form_flash.php';


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
        estado,
        motivo_inactivo,
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
// ERROR PENDIENTE (SI VENIMOS DE actualizar_usuario.php)
// =====================================================

$errorFormulario = obtenerErrorFormulario();
$datosPrevios = $errorFormulario['datos'] ?? [];


// =====================================================
// EMPRESA: NI EMPRESA NI ROL SON EDITABLES
// =====================================================
//
// Un usuario con rol EMPRESA no puede mover a su gente de
// empresa (siempre es la misma) ni cambiarle el rol, así
// que ninguno de los dos campos se muestra: se envían
// como campos ocultos con el valor actual (ver el mismo
// criterio en crear_usuario.php).
//
// =====================================================

$esEmpresa = rolActual() === ROL_EMPRESA;

if (!$esEmpresa) {


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

    if (rolActual() === ROL_NG) {

        $roles = array_values(array_filter(
            $roles,
            fn(array $r): bool => $r['nombre'] !== ROL_SRG
        ));

    }


    // =====================================================
    // EMPRESAS QUE YA TIENEN UN USUARIO CON ROL EMPRESA
    // =====================================================
    //
    // Mismo criterio que crear_usuario.php: si la empresa
    // elegida ya tiene un usuario con rol EMPRESA, el
    // desplegable Rol se bloquea en USUARIO. Se excluye al
    // propio usuario que se está editando, para no bloquearle
    // a él mismo si es precisamente el que tiene ese rol.
    //
    // =====================================================

    $stmtEmpresasConAdmin = $pdo->prepare("
        SELECT DISTINCT id_empresa
        FROM usuarios
        WHERE id_rol = (SELECT id FROM roles WHERE nombre = ? LIMIT 1)
            AND id != ?
    ");

    $stmtEmpresasConAdmin->execute([ROL_EMPRESA, $idUsuario]);

    $empresasConRolEmpresa = array_map(
        'intval',
        array_column($stmtEmpresasConAdmin->fetchAll(PDO::FETCH_ASSOC), 'id_empresa')
    );

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

                    <div class="form-error-general" id="form-error-general" role="alert"
                        style="display: <?= $errorFormulario ? 'block' : 'none' ?>;">
                        <?= $errorFormulario ? htmlspecialchars($errorFormulario['mensaje'], ENT_QUOTES, 'UTF-8') : '' ?>
                    </div>


                    <div class="form-grid">



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
                            class="<?= claseErrorCampo($errorFormulario, 'nombre') ?>"
                            value="<?= valorFormulario($datosPrevios, 'nombre', $usuario['nombre']) ?>"
                            required
                        >

                        <span class="field-error" id="error-nombre"><?= mensajeErrorCampo($errorFormulario, 'nombre') ?></span>

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
                            class="<?= claseErrorCampo($errorFormulario, 'apellidos') ?>"
                            value="<?= valorFormulario($datosPrevios, 'apellidos', $usuario['apellidos']) ?>"
                            required
                        >

                        <span class="field-error" id="error-apellidos"><?= mensajeErrorCampo($errorFormulario, 'apellidos') ?></span>

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
                            class="<?= claseErrorCampo($errorFormulario, 'username') ?>"
                            value="<?= valorFormulario($datosPrevios, 'username', $usuario['username']) ?>"
                            required
                        >

                        <span class="field-error" id="error-username"><?= mensajeErrorCampo($errorFormulario, 'username') ?></span>

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
                            class="<?= claseErrorCampo($errorFormulario, 'email') ?>"
                            value="<?= valorFormulario($datosPrevios, 'email', $usuario['email']) ?>"
                            required
                        >

                        <span class="field-error" id="error-email"><?= mensajeErrorCampo($errorFormulario, 'email') ?></span>

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
                            class="<?= claseErrorCampo($errorFormulario, 'telefono') ?>"
                            value="<?= valorFormulario($datosPrevios, 'telefono', $usuario['telefono'] ?? '') ?>"
                        >

                        <span class="field-error" id="error-telefono"><?= mensajeErrorCampo($errorFormulario, 'telefono') ?></span>

                    </div>



                    <?php if ($esEmpresa): ?>

                        <!-- Un usuario con rol EMPRESA no puede cambiar
                             la empresa ni el rol de su gente: se envían
                             como campos ocultos con el valor actual. -->

                        <input
                            type="hidden"
                            id="id_empresa"
                            name="id_empresa"
                            value="<?= (int) $usuario['id_empresa'] ?>"
                        >

                        <input
                            type="hidden"
                            id="id_rol"
                            name="id_rol"
                            value="<?= (int) $usuario['id_rol'] ?>"
                        >

                    <?php else: ?>

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
                                class="<?= claseErrorCampo($errorFormulario, 'id_empresa') ?>"
                                required
                            >

                                <option value="">
                                    Seleccionar empresa
                                </option>


                                <?php $idEmpresaPrevia = $datosPrevios['id_empresa'] ?? $usuario['id_empresa']; ?>

                                <?php foreach ($empresas as $empresa): ?>

                                    <option
                                        value="<?= (int) $empresa['id'] ?>"
                                        <?= $empresa['id'] == $idEmpresaPrevia ? 'selected' : '' ?>
                                    >

                                        <?= htmlspecialchars($empresa['nombre']) ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                            <span class="field-error" id="error-id_empresa"><?= mensajeErrorCampo($errorFormulario, 'id_empresa') ?></span>

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
                                class="<?= claseErrorCampo($errorFormulario, 'id_rol') ?>"
                                required
                            >

                                <option value="">
                                    Seleccionar rol
                                </option>


                                <?php $idRolPrevio = $datosPrevios['id_rol'] ?? $usuario['id_rol']; ?>

                                <?php foreach ($roles as $rol): ?>

                                    <option
                                        value="<?= (int) $rol['id'] ?>"
                                        data-rol="<?= htmlspecialchars($rol['nombre']) ?>"
                                        <?= $rol['id'] == $idRolPrevio ? 'selected' : '' ?>
                                    >

                                        <?= htmlspecialchars($rol['nombre']) ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                            <span class="field-error" id="error-id_rol"><?= mensajeErrorCampo($errorFormulario, 'id_rol') ?></span>

                        </div>

                    <?php endif; ?>



                    <!-- =========================
                         ESTADO
                    ========================== -->

                    <div class="form-group">

                        <label for="estado">
                            Estado
                        </label>

                        <?php $estadoPrevio = $datosPrevios['estado'] ?? $usuario['estado']; ?>

                        <select id="estado" name="estado"
                            class="<?= claseErrorCampo($errorFormulario, 'estado') ?>" required>

                            <?php foreach (['Activo', 'Inactivo'] as $estadoOpcion): ?>

                                <option value="<?= $estadoOpcion ?>" <?= $estadoPrevio === $estadoOpcion ? 'selected' : '' ?>>
                                    <?= $estadoOpcion ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                        <span class="field-error" id="error-estado"><?= mensajeErrorCampo($errorFormulario, 'estado') ?></span>

                    </div>


                    </div>


                    <!-- =========================
                         MOTIVO (SOLO SI INACTIVO)
                    ========================== -->

                    <div class="form-group <?= $estadoPrevio === 'Inactivo' ? '' : 'hidden' ?>" id="grupo_motivo_inactivo">

                        <label for="motivo_inactivo">
                            Motivo
                        </label>

                        <textarea id="motivo_inactivo" name="motivo_inactivo" rows="3"
                            class="<?= claseErrorCampo($errorFormulario, 'motivo_inactivo') ?>"
                            placeholder="Explica por qué el usuario se marca como inactivo"><?= valorFormulario($datosPrevios, 'motivo_inactivo', $usuario['motivo_inactivo'] ?? '') ?></textarea>

                        <span class="field-error" id="error-motivo_inactivo"><?= mensajeErrorCampo($errorFormulario, 'motivo_inactivo') ?></span>

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

    <?php if (!$esEmpresa): ?>

        <!-- Igual que en crear_usuario.php: bloquea el desplegable
             Rol en "Usuario" si la empresa elegida ya tiene un
             usuario con rol Empresa (ver bloquearRolSegunEmpresa()
             en usuarios.js). -->

        <script>
            window.EMPRESAS_CON_ROL_EMPRESA = <?= json_encode($empresasConRolEmpresa) ?>;
        </script>

    <?php endif; ?>

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