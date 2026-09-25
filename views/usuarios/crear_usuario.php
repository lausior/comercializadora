<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('usuarios');

require_once '../../config/database.php';
require_once '../../includes/form_flash.php';
require_once '../../includes/empresas.php';


// =====================================================
// ERROR PENDIENTE (SI VENIMOS DE guardar_usuario.php)
// =====================================================

$errorFormulario = obtenerErrorFormulario();
$datosPrevios = $errorFormulario['datos'] ?? [];


// =====================================================
// QUÉ EMPRESA Y QUÉ ROL SE PUEDEN ELEGIR
// =====================================================
//
// - EMPRESA y ADMIN solo dan de alta gente de su propia
//   empresa (la empresa va oculta) y eligen entre Usuario y
//   Admin (ROLES_EQUIPO_EMPRESA). La cuenta EMPRESA no se
//   elige: es única y se crea junto a la empresa.
// - NG, desde la sección Usuarios, solo da de alta a su
//   propio equipo interno (rol USUARIO) — para el resto de
//   empresas (sus clientes), el acceso ya se crea junto a
//   la empresa (ver guardar_empresa.php). Empresa y rol van
//   ocultos.
// - SRG elige cualquier empresa y cualquier rol.
//
// guardar_usuario.php vuelve a comprobarlo todo.
//
// =====================================================

$empresaFija = rolActual() === ROL_NG || esGestorEmpresa();
$rolFijo = rolActual() === ROL_NG;

if ($rolFijo) {

    $stmtRolUsuario = $pdo->prepare("
        SELECT id
        FROM roles
        WHERE nombre = ?
        LIMIT 1
    ");

    $stmtRolUsuario->execute([ROL_USUARIO]);

    $idRolUsuario = (int) $stmtRolUsuario->fetchColumn();

} elseif ($empresaFija) {

    $marcadores = implode(',', array_fill(0, count(ROLES_EQUIPO_EMPRESA), '?'));

    $stmtRoles = $pdo->prepare("
        SELECT id, nombre
        FROM roles
        WHERE nombre IN ($marcadores)
        ORDER BY id
    ");

    $stmtRoles->execute(ROLES_EQUIPO_EMPRESA);

    $roles = $stmtRoles->fetchAll(PDO::FETCH_ASSOC);

} else {


    // =====================================================
    // OBTENER EMPRESAS Y ROLES
    // =====================================================
    //
    // Solo SRG llega hasta aquí (EMPRESA y NG ya tienen la
    // empresa y el rol fijos, ver arriba), así que puede
    // elegir cualquier empresa y cualquier rol.
    //
    // =====================================================

    // Se excluye la propia empresa de quien ha iniciado sesión
    // (aquí siempre SRG, ver arriba): no tiene sentido dar de
    // alta un usuario "dentro de" la empresa del propio SRG
    // desde este formulario genérico (mismo criterio que ya
    // oculta esa fila en los listados de empresas.php/usuarios.php).
    $stmtEmpresas = $pdo->prepare("
        SELECT id, nombre
        FROM empresas
        WHERE id != ?
        ORDER BY nombre
    ");

    $stmtEmpresas->execute([(int) ($_SESSION['id_empresa'] ?? 0)]);

    $empresas = $stmtEmpresas->fetchAll(PDO::FETCH_ASSOC);

    // Igual que con la empresa: se excluye el rol SRG, no tiene
    // sentido dar de alta a otro usuario con ese rol desde este
    // formulario.
    $stmtRoles = $pdo->prepare("
        SELECT id, nombre
        FROM roles
        WHERE nombre != ?
        ORDER BY id
    ");

    $stmtRoles->execute([ROL_SRG]);

    $roles = $stmtRoles->fetchAll(PDO::FETCH_ASSOC);


    // =====================================================
    // EMPRESAS QUE YA TIENEN UN USUARIO CON ROL EMPRESA
    // =====================================================
    //
    // Una empresa solo puede tener UN usuario con rol
    // EMPRESA (su "admin"). Si la empresa elegida ya tiene
    // uno, el campo Rol se bloquea en USUARIO en el propio
    // formulario (ver el <script> más abajo) y también se
    // vuelve a comprobar al guardar (ver guardar_usuario.php).
    //
    // =====================================================

    $stmtEmpresasConAdmin = $pdo->prepare("
        SELECT DISTINCT id_empresa
        FROM usuarios
        WHERE id_rol = (SELECT id FROM roles WHERE nombre = ? LIMIT 1)
    ");

    $stmtEmpresasConAdmin->execute([ROL_EMPRESA]);

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

                            <input type="text" id="nombre" name="nombre"
                                class="<?= claseErrorCampo($errorFormulario, 'nombre') ?>"
                                value="<?= valorFormulario($datosPrevios, 'nombre') ?>" required>

                            <span class="field-error" id="error-nombre"><?= mensajeErrorCampo($errorFormulario, 'nombre') ?></span>

                        </div>


                        <!-- =========================
                             APELLIDOS
                        ========================== -->

                        <div class="form-group">

                            <label for="apellidos">
                                Apellidos
                            </label>

                            <input type="text" id="apellidos" name="apellidos"
                                class="<?= claseErrorCampo($errorFormulario, 'apellidos') ?>"
                                value="<?= valorFormulario($datosPrevios, 'apellidos') ?>" required>

                            <span class="field-error" id="error-apellidos"><?= mensajeErrorCampo($errorFormulario, 'apellidos') ?></span>

                        </div>


                        <!-- =========================
                             USERNAME
                        ========================== -->

                        <div class="form-group">

                            <label for="username">
                                Username
                            </label>

                            <input type="text" id="username" name="username"
                                class="<?= claseErrorCampo($errorFormulario, 'username') ?>"
                                value="<?= valorFormulario($datosPrevios, 'username') ?>" required>

                            <span class="field-error" id="error-username"><?= mensajeErrorCampo($errorFormulario, 'username') ?></span>

                        </div>


                        <!-- =========================
                             EMAIL
                        ========================== -->

                        <div class="form-group">

                            <label for="email">
                                Email
                            </label>

                            <input type="email" id="email" name="email"
                                class="<?= claseErrorCampo($errorFormulario, 'email') ?>"
                                value="<?= valorFormulario($datosPrevios, 'email') ?>" required>

                            <span class="field-error" id="error-email"><?= mensajeErrorCampo($errorFormulario, 'email') ?></span>

                        </div>


                        <!-- =========================
                             TELEFONO
                        ========================== -->

                        <div class="form-group">

                            <label for="telefono">
                                Teléfono
                            </label>

                            <input type="tel" id="telefono" name="telefono"
                                class="<?= claseErrorCampo($errorFormulario, 'telefono') ?>"
                                value="<?= valorFormulario($datosPrevios, 'telefono') ?>">

                            <span class="field-error" id="error-telefono"><?= mensajeErrorCampo($errorFormulario, 'telefono') ?></span>

                        </div>


                        <?php if ($empresaFija): ?>

                            <!-- EMPRESA, ADMIN y NG siempre dan de alta
                                 gente de su propia empresa: no hay nada que
                                 elegir, así que va como campo oculto. -->

                            <input type="hidden" id="id_empresa" name="id_empresa"
                                value="<?= (int) $_SESSION['id_empresa'] ?>">

                        <?php endif; ?>

                        <?php if ($rolFijo): ?>

                            <!-- NG solo da de alta rol USUARIO. -->

                            <input type="hidden" id="id_rol" name="id_rol"
                                value="<?= $idRolUsuario ?>">

                        <?php endif; ?>

                        <?php if (!$empresaFija): ?>

                            <!-- =========================
                                 EMPRESA
                            ========================== -->

                            <div class="form-group">

                                <label for="id_empresa">
                                    Empresa
                                </label>

                                <select id="id_empresa" name="id_empresa"
                                    class="<?= claseErrorCampo($errorFormulario, 'id_empresa') ?>" required>

                                    <option value="">
                                        Seleccionar empresa
                                    </option>


                                    <?php foreach ($empresas as $empresa): ?>

                                        <option value="<?= $empresa['id'] ?>"
                                            <?= (($datosPrevios['id_empresa'] ?? '') == $empresa['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($empresa['nombre']) ?>
                                        </option>

                                    <?php endforeach; ?>


                                </select>

                                <span class="field-error" id="error-id_empresa"><?= mensajeErrorCampo($errorFormulario, 'id_empresa') ?></span>

                            </div>

                        <?php endif; ?>

                        <?php if (!$rolFijo): ?>

                            <!-- =========================
                                 ROL
                            ========================== -->

                            <div class="form-group">

                                <label for="id_rol">
                                    Rol
                                </label>

                                <select id="id_rol" name="id_rol"
                                    class="<?= claseErrorCampo($errorFormulario, 'id_rol') ?>" required>

                                    <option value="">
                                        Seleccionar rol
                                    </option>


                                    <?php foreach ($roles as $rol): ?>

                                        <option value="<?= $rol['id'] ?>" data-rol="<?= htmlspecialchars($rol['nombre']) ?>"
                                            <?= (($datosPrevios['id_rol'] ?? '') == $rol['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars(etiquetaRol($rol['nombre'])) ?>
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

                            <?php $estadoPrevio = $datosPrevios['estado'] ?? 'Activo'; ?>

                            <select id="estado" name="estado"
                                class="<?= claseErrorCampo($errorFormulario, 'estado') ?>" required>

                                <option value="Activo" <?= $estadoPrevio === 'Activo' ? 'selected' : '' ?>>Activo</option>
                                <option value="Inactivo" <?= $estadoPrevio === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>

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

                        <?php $motivoPrevio = valorFormulario($datosPrevios, 'motivo_inactivo'); ?>

                        <select id="motivo_inactivo" name="motivo_inactivo"
                            class="<?= claseErrorCampo($errorFormulario, 'motivo_inactivo') ?>">

                            <option value="">Selecciona un motivo</option>

                            <?php
                            // Si se vuelve de un error con un motivo de
                            // empresa, se mantienen las opciones de empresa;
                            // si no, las de usuario.
                            pintarOpcionesMotivo(
                                isset(MOTIVOS_INACTIVO_EMPRESA[$motivoPrevio]) ? MOTIVOS_INACTIVO_EMPRESA : MOTIVOS_INACTIVO_USUARIO,
                                $motivoPrevio
                            );
                            ?>

                        </select>

                        <!-- Este es el estado inicial al cargar la página (o
                             tras volver de un error, ver $motivoPrevio arriba);
                             si se cambia el Rol a mano (solo SRG puede, ver
                             más abajo), actualizarOpcionesMotivoSegunRol()
                             en usuarios.js reconstruye estas opciones. -->

                        <span class="field-error" id="error-motivo_inactivo"><?= mensajeErrorCampo($errorFormulario, 'motivo_inactivo') ?></span>

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

    <?php if (!$empresaFija): ?>

        <!-- =================================================
             BLOQUEAR ROL A "USUARIO" SEGÚN LA EMPRESA ELEGIDA
             =================================================
             Una empresa solo puede tener un usuario con rol
             EMPRESA. Si la empresa seleccionada ya tiene uno,
             el desplegable Rol se fija en "Usuario" y se
             bloquea (ver bloquearRolSegunEmpresa() en
             usuarios.js). Los ids vienen ya filtrados por lo
             que este actor puede ver (ver crear_usuario.php).
        ================================================== -->

        <script>
            window.EMPRESAS_CON_ROL_EMPRESA = <?= json_encode($empresasConRolEmpresa) ?>;
        </script>

    <?php endif; ?>

</body>

</html>