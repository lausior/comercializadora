<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('usuarios');

require_once '../../config/database.php';
require_once '../../includes/logs.php';
require_once '../../includes/form_flash.php';


// =====================================================
// COMPROBAR QUE LA PETICIÓN SEA POST
// =====================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: usuarios.php');
    exit;

}


// =====================================================
// RECIBIR DATOS DEL FORMULARIO
// =====================================================

$id = isset($_POST['id'])
    ? (int) $_POST['id']
    : 0;

$username = trim($_POST['username'] ?? '');

$nombre = trim($_POST['nombre'] ?? '');

$apellidos = trim($_POST['apellidos'] ?? '');

$email = trim($_POST['email'] ?? '');

$telefono = trim($_POST['telefono'] ?? '');

$idEmpresa = isset($_POST['id_empresa'])
    ? (int) $_POST['id_empresa']
    : 0;

$idRol = isset($_POST['id_rol'])
    ? (int) $_POST['id_rol']
    : 0;

$estado = trim($_POST['estado'] ?? '');

$motivoInactivo = trim($_POST['motivo_inactivo'] ?? '');


// =====================================================
// COMPROBAR CAMPOS OBLIGATORIOS
// =====================================================

$estadosValidos = ['Activo', 'Inactivo'];

if ($id <= 0) {

    header('Location: usuarios.php');
    exit;

}

if (
    $username === '' ||
    $nombre === '' ||
    $apellidos === '' ||
    $email === '' ||
    $idEmpresa <= 0 ||
    $idRol <= 0 ||
    !in_array($estado, $estadosValidos, true)
) {

    establecerErrorFormulario('Todos los campos obligatorios deben estar completos.', $_POST, 'editar_usuario.php?id=' . $id);

}

if ($estado === 'Inactivo' && $motivoInactivo === '') {

    establecerErrorFormulario('Indica el motivo por el que el usuario se marca como inactivo.', $_POST, 'editar_usuario.php?id=' . $id, 'motivo_inactivo');

}


// =====================================================
// COMPROBAR FORMATO DEL EMAIL
// =====================================================

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    establecerErrorFormulario('El correo electrónico no tiene un formato válido.', $_POST, 'editar_usuario.php?id=' . $id, 'email');

}


// =====================================================
// COMPROBAR QUE EL USUARIO EXISTA
// =====================================================

$stmtUsuario = $pdo->prepare("
    SELECT id, id_empresa, id_rol, creado_por
    FROM usuarios
    WHERE id = ?
");

$stmtUsuario->execute([$id]);

$usuarioExiste = $stmtUsuario->fetch(PDO::FETCH_ASSOC);


if (!$usuarioExiste) {

    die('
        <h2>Error</h2>

        <p>
            El usuario que intentas modificar no existe.
        </p>

        <p>
            <a href="usuarios.php">
                Volver a usuarios
            </a>
        </p>
    ');

}


// =====================================================
// COMPROBAR QUE PUEDE EDITAR ESTE USUARIO
// =====================================================

if (!puedeVerUsuario($usuarioExiste['creado_por'] !== null ? (int) $usuarioExiste['creado_por'] : null)) {

    die('
        <h2>Error</h2>

        <p>
            No tienes permiso para editar este usuario.
        </p>

        <p>
            <a href="usuarios.php">
                Volver a usuarios
            </a>
        </p>
    ');

}


// =====================================================
// NG Y EMPRESA NO PUEDEN CAMBIAR A UN USUARIO DE EMPRESA
// =====================================================
//
// La comprobación anterior ya garantiza que el usuario
// era de su propia empresa; esta impide que, aun así,
// lo manden a OTRA empresa manipulando el formulario
// (el desplegable normal ya no se lo deja elegir, pero
// esto es lo que de verdad lo impide).
//
// =====================================================

if (
    rolActual() !== ROL_SRG &&
    $idEmpresa !== (int) $usuarioExiste['id_empresa']
) {

    establecerErrorFormulario('No puedes cambiar la empresa de este usuario.', $_POST, 'editar_usuario.php?id=' . $id, 'id_empresa');

}


// =====================================================
// COMPROBAR USERNAME DUPLICADO
// =====================================================

$stmtUsername = $pdo->prepare("
    SELECT id
    FROM usuarios
    WHERE username = ?
    AND id != ?
");

$stmtUsername->execute([
    $username,
    $id
]);

if ($stmtUsername->fetch()) {

    establecerErrorFormulario('El username ya está siendo utilizado por otro usuario.', $_POST, 'editar_usuario.php?id=' . $id, 'username');

}


// =====================================================
// COMPROBAR EMAIL DUPLICADO
// =====================================================

$stmtEmail = $pdo->prepare("
    SELECT id
    FROM usuarios
    WHERE email = ?
    AND id != ?
");

$stmtEmail->execute([
    $email,
    $id
]);

if ($stmtEmail->fetch()) {

    establecerErrorFormulario('El email ya está siendo utilizado por otro usuario.', $_POST, 'editar_usuario.php?id=' . $id, 'email');

}


// =====================================================
// COMPROBAR QUE LA EMPRESA EXISTA
// =====================================================

$stmtEmpresa = $pdo->prepare("
    SELECT
        id,
        nombre
    FROM empresas
    WHERE id = ?
");

$stmtEmpresa->execute([$idEmpresa]);

$empresa = $stmtEmpresa->fetch(PDO::FETCH_ASSOC);


if (!$empresa) {

    establecerErrorFormulario('La empresa seleccionada no existe.', $_POST, 'editar_usuario.php?id=' . $id, 'id_empresa');

}


// =====================================================
// COMPROBAR QUE EL ROL EXISTA
// =====================================================

$stmtRol = $pdo->prepare("
    SELECT
        id,
        nombre
    FROM roles
    WHERE id = ?
");

$stmtRol->execute([$idRol]);

$rol = $stmtRol->fetch(PDO::FETCH_ASSOC);


if (!$rol) {

    establecerErrorFormulario('El rol seleccionado no existe.', $_POST, 'editar_usuario.php?id=' . $id, 'id_rol');

}


// =====================================================
// COMPROBAR QUE PUEDE ASIGNAR ESE ROL
// =====================================================
//
// EMPRESA no elige rol en el formulario: el campo no se
// muestra, así que no puede cambiar el rol del usuario
// (el desplegable normal ya no se lo deja elegir, pero
// esto es lo que de verdad lo impide).
//
// =====================================================

if (
    rolActual() === ROL_EMPRESA &&
    $idRol !== (int) $usuarioExiste['id_rol']
) {

    establecerErrorFormulario('No puedes cambiar el rol de este usuario.', $_POST, 'editar_usuario.php?id=' . $id, 'id_rol');

}

if (
    rolActual() === ROL_NG &&
    $rol['nombre'] === ROL_SRG
) {

    establecerErrorFormulario('No puedes asignar ese rol.', $_POST, 'editar_usuario.php?id=' . $id, 'id_rol');

}


// =====================================================
// UNA EMPRESA SOLO PUEDE TENER UN USUARIO CON ROL EMPRESA
// =====================================================
//
// Mismo criterio que guardar_usuario.php. Se excluye al
// propio usuario que se está editando, para no bloquearle
// a él mismo si es precisamente el que ya tenía ese rol.
//
// =====================================================

if ($rol['nombre'] === ROL_EMPRESA) {

    $stmtEmpresaYaTieneAdmin = $pdo->prepare("
        SELECT id
        FROM usuarios
        WHERE id_empresa = ?
            AND id_rol = (SELECT id FROM roles WHERE nombre = ? LIMIT 1)
            AND id != ?
        LIMIT 1
    ");

    $stmtEmpresaYaTieneAdmin->execute([$idEmpresa, ROL_EMPRESA, $id]);

    if ($stmtEmpresaYaTieneAdmin->fetch()) {

        establecerErrorFormulario('Esa empresa ya tiene un usuario con rol Empresa.', $_POST, 'editar_usuario.php?id=' . $id, 'id_rol');

    }

}


// =====================================================
// ACTUALIZAR USUARIO
// =====================================================
//
// IMPORTANTE:
//
// NO actualizamos:
//     password
//     cambiar_password
//
// Por tanto, la contraseña actual permanece intacta.
//
// =====================================================

$stmtActualizar = $pdo->prepare("
    UPDATE usuarios
    SET
        username = ?,
        nombre = ?,
        apellidos = ?,
        email = ?,
        telefono = ?,
        id_empresa = ?,
        id_rol = ?,
        estado = ?,
        motivo_inactivo = ?
    WHERE id = ?
");

$stmtActualizar->execute([
    $username,
    $nombre,
    $apellidos,
    $email,
    $telefono !== '' ? $telefono : null,
    $idEmpresa,
    $idRol,
    $estado,
    $estado === 'Inactivo' ? $motivoInactivo : null,
    $id
]);

// El usuario con rol EMPRESA representa el acceso de su empresa.
// Mantener ambos estados sincronizados evita que el listado de
// empresas conserve un estado distinto al del acceso principal.
if ($rol['nombre'] === ROL_EMPRESA) {

    $stmtEmpresaEstado = $pdo->prepare("
        UPDATE empresas
        SET estado = ?
        WHERE id = ?
    ");

    $stmtEmpresaEstado->execute([$estado, $idEmpresa]);

}


// =====================================================
// RECUPERAR LOS DATOS ACTUALIZADOS
// =====================================================
//
// Consultamos nuevamente la base de datos para mostrar
// exactamente la información que se ha guardado.
//
// =====================================================

$stmtDatos = $pdo->prepare("
    SELECT
        u.id,
        u.username,
        u.nombre,
        u.apellidos,
        u.email,
        u.telefono,
        u.cambiar_password,
        u.estado,
        u.motivo_inactivo,
        e.nombre AS empresa,
        r.nombre AS rol
    FROM usuarios u

    INNER JOIN empresas e
        ON u.id_empresa = e.id

    INNER JOIN roles r
        ON u.id_rol = r.id

    WHERE u.id = ?
");

$stmtDatos->execute([$id]);

$usuario = $stmtDatos->fetch(PDO::FETCH_ASSOC);


// =====================================================
// COMPROBAR QUE SE HAN RECUPERADO LOS DATOS
// =====================================================

if (!$usuario) {

    die('
        <h2>Error</h2>

        <p>
            El usuario se actualizó, pero no se pudieron
            recuperar sus datos.
        </p>

        <p>
            <a href="usuarios.php">
                Volver a usuarios
            </a>
        </p>
    ');

}

registrarLog(
    LOG_EXITO,
    'Usuario modificado',
    'Se ha modificado el usuario "' . $usuario['username'] . '".'
);


// =====================================================
// DATOS PARA MOSTRAR
// =====================================================

$nombreCompleto =
    $usuario['nombre'] . ' ' . $usuario['apellidos'];


// =====================================================
// CALCULAR INICIALES
// =====================================================

$inicialNombre = mb_substr(
    $usuario['nombre'],
    0,
    1,
    'UTF-8'
);

$inicialApellido = mb_substr(
    $usuario['apellidos'],
    0,
    1,
    'UTF-8'
);

$iniciales = mb_strtoupper(
    $inicialNombre . $inicialApellido,
    'UTF-8'
);


// =====================================================
// ESTADO DE CAMBIO DE CONTRASEÑA
// =====================================================

if ((int) $usuario['cambiar_password'] === 1) {

    $estadoPassword = 'Pendiente de cambio';

} else {

    $estadoPassword = 'Contraseña establecida';

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

    <title>Usuario actualizado - Comparador Eléctrico</title>

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

                    <h1>Usuarios</h1>

                    <p>
                        Usuario actualizado correctamente
                    </p>

                </div>


                <div class="page-header-actions">

                </div>

            </div>


            <!-- =================================================
                 MENSAJE DE CONFIRMACIÓN
            ================================================== -->

            <div class="config-card confirmation-card">


                <h2>
                    Usuario actualizado
                </h2>


                <div class="form-info">

                    <p>

                        El usuario

                        <strong>
                            <?= htmlspecialchars(
                                $usuario['username'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </strong>

                        se ha actualizado correctamente.

                    </p>

                </div>


                <!-- =================================================
                     INFORMACIÓN DEL USUARIO
                ================================================== -->

                <div class="usuario-detalle">




                    <!-- =================================================
                         DATOS
                    ================================================== -->

                    <div class="usuario-detalle-grid">


                        <!-- ID -->

                        <div class="usuario-detalle-item">

                            <span>
                                ID de usuario
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                    $usuario['id'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </strong>

                        </div>


                        <!-- NOMBRE -->

                        <div class="usuario-detalle-item">

                            <span>
                                Nombre
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                    $usuario['nombre'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </strong>

                        </div>


                        <!-- APELLIDOS -->

                        <div class="usuario-detalle-item">

                            <span>
                                Apellidos
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                    $usuario['apellidos'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </strong>

                        </div>


                        <!-- USERNAME -->

                        <div class="usuario-detalle-item">

                            <span>
                                Username
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                    $usuario['username'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </strong>

                        </div>


                        <!-- EMAIL -->

                        <div class="usuario-detalle-item">

                            <span>
                                Email
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                    $usuario['email'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </strong>

                        </div>


                        <!-- TELEFONO -->

                        <div class="usuario-detalle-item">

                            <span>
                                Teléfono
                            </span>

                            <strong>

                                <?php if (!empty($usuario['telefono'])): ?>

                                    <?= htmlspecialchars(
                                        $usuario['telefono'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                <?php else: ?>

                                    No indicado

                                <?php endif; ?>

                            </strong>

                        </div>


                        <!-- EMPRESA -->

                        <div class="usuario-detalle-item">

                            <span>
                                Empresa
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                    $usuario['empresa'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </strong>

                        </div>


                        <!-- ESTADO -->

                        <div class="usuario-detalle-item">

                            <span>
                                Estado
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                    $usuario['estado'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </strong>

                        </div>


                        <?php if ($usuario['estado'] === 'Inactivo'): ?>

                            <!-- MOTIVO -->

                            <div class="usuario-detalle-item">

                                <span>
                                    Motivo
                                </span>

                                <strong>
                                    <?= htmlspecialchars(
                                        $usuario['motivo_inactivo'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </strong>

                            </div>

                        <?php endif; ?>


                        <!-- ROL -->

                        <div class="usuario-detalle-item">

                            <span>
                                Rol
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                    $usuario['rol'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </strong>

                        </div>


                        <!-- CONTRASEÑA -->

                        <div class="usuario-detalle-item">

                            <span>
                                Contraseña
                            </span>

                            <strong>
                                Se ha mantenido sin cambios
                            </strong>

                        </div>


                    </div>


                </div>


                <!-- =================================================
                     BOTONES
                ================================================== -->

                <div class="form-actions">


                    <a
                        href="crear_usuario.php"
                        class="config-save-button"
                    >
                        + Añadir usuario
                    </a>


                    <a
                        href="usuarios.php"
                        class="config-cancel-button"
                    >
                        Volver a usuarios
                    </a>


                </div>


            </div>


        </main>


    </div>


    <!-- =================================================
         FOOTER
    ================================================== -->

    <?php include '../../templates/footer.php'; ?>


</body>

</html>