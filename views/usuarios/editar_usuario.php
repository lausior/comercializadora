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
        u.id,
        u.username,
        u.nombre,
        u.apellidos,
        u.email,
        u.telefono,
        u.id_empresa,
        u.id_rol,
        u.estado,
        u.motivo_inactivo,
        u.inactivo_por_empresa,
        u.creado_por,
        r.nombre AS rol,
        e.estado AS empresa_estado
    FROM usuarios u

    INNER JOIN roles r
        ON r.id = u.id_rol

    INNER JOIN empresas e
        ON e.id = u.id_empresa

    WHERE u.id = ?
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

if (!puedeVerUsuario($usuario)) {

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
// empresa (siempre es la misma) ni cambiarle el rol. Lo
// mismo aplica a NG con su propio equipo (rol USUARIO de
// NG Asesores): desde la sección Usuarios, NG solo gestiona
// su propia empresa. Así que en ninguno de los dos casos se
// muestran esos campos: se envían ocultos con el valor
// actual (ver el mismo criterio en crear_usuario.php).
//
// =====================================================

$empresaYRolFijos = in_array(rolActual(), [ROL_EMPRESA, ROL_NG], true);


// =====================================================
// ¿EL USUARIO EDITADO ES LA CUENTA DE ACCESO DE SU EMPRESA?
// =====================================================
//
// El usuario con rol EMPRESA no es "un usuario más": es el
// login de la propia empresa, creado junto a ella reutilizando
// su nombre, email y teléfono (ver guardar_empresa.php). Esos
// tres campos ya tienen una única fuente de verdad en la tabla
// empresas, así que aquí se muestran de solo lectura — para
// cambiarlos hay que editar la empresa. El username sí es
// propio del acceso (no existe en empresas), así que se puede
// modificar con normalidad.
//
// =====================================================

$usuarioEsCuentaEmpresa = $usuario['rol'] === ROL_EMPRESA;


// =====================================================
// ¿ESTÁ INACTIVO PORQUE SU EMPRESA SE INACTIVÓ?
// =====================================================
//
// Si es así, el motivo ("Empresa inactiva") lo controla la
// cascada de la empresa (ver includes/empresas.php), no se
// elige a mano aquí: el select de motivo se sustituye por un
// aviso informativo (ver más abajo). Si en cambio ya estaba
// Inactivo por su cuenta (vacaciones, baja laboral...) antes de
// que la empresa se inactivara, sigue siendo un motivo normal y
// editable.
//
// =====================================================

$bloqueadoPorEmpresa = $usuario['estado'] === 'Inactivo' && (int) $usuario['inactivo_por_empresa'] === 1;


// =====================================================
// ¿SU EMPRESA ESTÁ INACTIVA AHORA MISMO?
// =====================================================
//
// Mismo criterio que el listado (ver usuarios.php): un usuario
// normal (rol USUARIO) no puede quedar Activo mientras su
// empresa esté Inactiva. Aquí, en vez de dejar elegir "Activo"
// en el select y que el guardado lo rechace, el select se fija
// en "Inactivo" y se avisa del motivo (ver el select de Estado
// más abajo). El usuario con rol EMPRESA no entra aquí: activarlo
// A ÉL es precisamente cómo se reactiva la empresa.
//
// =====================================================

$empresaDelUsuarioInactiva = in_array($usuario['rol'], ROLES_EQUIPO_EMPRESA, true) && $usuario['empresa_estado'] === 'Inactivo';

if (!$empresaYRolFijos && !$usuarioEsCuentaEmpresa) {


    // =====================================================
    // OBTENER EMPRESAS Y ROLES
    // =====================================================
    //
    // Solo SRG llega hasta aquí (EMPRESA y NG ya tienen la
    // empresa y el rol fijos, ver arriba), así que puede
    // reasignar el usuario a cualquier empresa y cualquier
    // rol.
    //
    // =====================================================

    // Se excluye la propia empresa de quien ha iniciado sesión
    // (aquí siempre SRG, ver arriba): no tiene sentido reasignar
    // un usuario "a" la empresa del propio SRG desde este
    // formulario genérico (mismo criterio que ya oculta esa fila
    // en los listados de empresas.php/usuarios.php). Salvo que
    // el usuario que se está editando YA pertenezca a esa
    // empresa: entonces se deja esa opción, para que el
    // desplegable no se quede sin la que tiene seleccionada
    // (si no, el navegador seleccionaría otra distinta sin
    // querer en el primer guardado).
    $stmtEmpresas = $pdo->prepare("
        SELECT id, nombre
        FROM empresas
        WHERE id != :propia OR id = :actual
        ORDER BY nombre
    ");

    $stmtEmpresas->execute([
        ':propia' => (int) ($_SESSION['id_empresa'] ?? 0),
        ':actual' => (int) $usuario['id_empresa'],
    ]);

    $empresas = $stmtEmpresas->fetchAll(PDO::FETCH_ASSOC);

    // Igual que con la empresa: se excluye el rol SRG, salvo que
    // el usuario editado ya lo tenga (mismo motivo: no perder la
    // opción actualmente seleccionada).
    $stmtRoles = $pdo->prepare("
        SELECT id, nombre
        FROM roles
        WHERE nombre != :srg OR nombre = :actual
        ORDER BY id
    ");

    $stmtRoles->execute([
        ':srg'    => ROL_SRG,
        ':actual' => $usuario['rol'],
    ]);

    $roles = $stmtRoles->fetchAll(PDO::FETCH_ASSOC);


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


                    <?php if ($usuarioEsCuentaEmpresa): ?>

                        <div class="form-info">
                            <p>
                                Este usuario es el acceso de la empresa: su
                                nombre, email, teléfono, empresa y rol son
                                los datos de la propia empresa y se editan
                                desde <a href="../empresas/editar_empresa.php?id=<?= (int) $usuario['id_empresa'] ?>">su ficha</a>.
                                Aquí solo se puede modificar el username de
                                acceso y el estado.
                            </p>
                        </div>

                    <?php endif; ?>


                    <div class="form-grid">


                    <?php if ($usuarioEsCuentaEmpresa): ?>

                        <!-- Nombre, apellidos, email y teléfono pertenecen
                             a la empresa (ver guardar_empresa.php): no se
                             muestran aquí. actualizar_usuario.php ignora
                             igualmente cualquier valor que llegara para
                             ellos y conserva el que ya había en la BD. -->

                    <?php else: ?>

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

                    <?php endif; ?>



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


                    <?php if (!$usuarioEsCuentaEmpresa): ?>

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

                    <?php endif; ?>



                    <?php if ($empresaYRolFijos || $usuarioEsCuentaEmpresa): ?>

                        <!-- Un usuario con rol EMPRESA (o NG, para su
                             propio equipo) no puede cambiar la empresa ni
                             el rol de su gente, y tampoco se puede cambiar
                             la empresa/rol de LA cuenta EMPRESA desde
                             aquí: se envían como campos ocultos con el
                             valor actual. -->

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

                        <?php $estadoPrevio = $empresaDelUsuarioInactiva ? 'Inactivo' : ($datosPrevios['estado'] ?? $usuario['estado']); ?>

                        <?php if ($empresaDelUsuarioInactiva): ?>

                            <!-- Fijo en Inactivo: no se puede activar a este
                                 usuario mientras su empresa esté inactiva
                                 (ver el aviso debajo). disabled evita que se
                                 elija a mano; el hidden es lo que de verdad
                                 se envía, porque un select disabled no
                                 manda su valor al enviar el formulario. -->

                            <select id="estado" class="<?= claseErrorCampo($errorFormulario, 'estado') ?>" disabled>
                                <option value="Inactivo" selected>Inactivo</option>
                            </select>

                            <input type="hidden" name="estado" value="Inactivo">

                        <?php else: ?>

                            <select id="estado" name="estado"
                                class="<?= claseErrorCampo($errorFormulario, 'estado') ?>" required>

                                <?php foreach (['Activo', 'Inactivo'] as $estadoOpcion): ?>

                                    <option value="<?= $estadoOpcion ?>" <?= $estadoPrevio === $estadoOpcion ? 'selected' : '' ?>>
                                        <?= $estadoOpcion ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        <?php endif; ?>

                        <span class="field-error" id="error-estado"><?= mensajeErrorCampo($errorFormulario, 'estado') ?></span>

                        <?php if ($empresaDelUsuarioInactiva): ?>

                            <p class="form-hint">
                                La empresa de este usuario está inactiva, así que no se puede activar.
                                Actívala primero desde <a href="../empresas/empresas.php">Empresas</a>.
                            </p>

                        <?php endif; ?>

                    </div>


                    </div>


                    <!-- =========================
                         MOTIVO (SOLO SI INACTIVO)
                    ========================== -->

                    <div class="form-group <?= $estadoPrevio === 'Inactivo' ? '' : 'hidden' ?>" id="grupo_motivo_inactivo">

                        <label for="motivo_inactivo">
                            Motivo
                        </label>

                        <?php if ($bloqueadoPorEmpresa): ?>

                            <!-- Motivo controlado por la cascada de la
                                 empresa: no se elige a mano (ver
                                 includes/empresas.php). Se conserva tal
                                 cual al guardar el resto del formulario. -->

                            <input type="hidden" name="motivo_inactivo" value="<?= MOTIVO_EMPRESA_INACTIVA ?>">

                            <div class="form-info">
                                <p>
                                    Empresa inactiva: este usuario se inactivó
                                    automáticamente al inactivarse su empresa.
                                    Volverá a Activo solo si reactivas la
                                    <a href="../empresas/empresas.php">empresa</a>.
                                </p>
                            </div>

                        <?php else: ?>

                            <?php $motivoPrevio = valorFormulario($datosPrevios, 'motivo_inactivo', $usuario['motivo_inactivo'] ?? ''); ?>

                            <select id="motivo_inactivo" name="motivo_inactivo"
                                class="<?= claseErrorCampo($errorFormulario, 'motivo_inactivo') ?>">

                                <option value="">Selecciona un motivo</option>

                                <?php
                                pintarOpcionesMotivo(
                                    $usuarioEsCuentaEmpresa ? MOTIVOS_INACTIVO_EMPRESA : MOTIVOS_INACTIVO_USUARIO,
                                    $motivoPrevio
                                );
                                ?>

                            </select>

                        <?php endif; ?>

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

    <?php if (!$empresaYRolFijos && !$usuarioEsCuentaEmpresa): ?>

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