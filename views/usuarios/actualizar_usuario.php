<?php

require_once '../../config/database.php';


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


// =====================================================
// COMPROBAR CAMPOS OBLIGATORIOS
// =====================================================

if (
    $id <= 0 ||
    $username === '' ||
    $nombre === '' ||
    $apellidos === '' ||
    $email === '' ||
    $idEmpresa <= 0 ||
    $idRol <= 0
) {

    die('
        <h2>Error</h2>

        <p>
            Todos los campos obligatorios deben estar completos.
        </p>

        <p>
            <a href="usuarios.php">
                Volver a usuarios
            </a>
        </p>
    ');

}


// =====================================================
// COMPROBAR FORMATO DEL EMAIL
// =====================================================

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    die('
        <h2>Error</h2>

        <p>
            El correo electrónico no tiene un formato válido.
        </p>

        <p>
            <a href="usuarios.php">
                Volver a usuarios
            </a>
        </p>
    ');

}


// =====================================================
// COMPROBAR QUE EL USUARIO EXISTA
// =====================================================

$stmtUsuario = $pdo->prepare("
    SELECT id
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

    die('
        <h2>Error</h2>

        <p>
            El username ya está siendo utilizado por otro usuario.
        </p>

        <p>
            <a href="javascript:history.back()">
                Volver al formulario
            </a>
        </p>
    ');

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

    die('
        <h2>Error</h2>

        <p>
            El email ya está siendo utilizado por otro usuario.
        </p>

        <p>
            <a href="javascript:history.back()">
                Volver al formulario
            </a>
        </p>
    ');

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

    die('
        <h2>Error</h2>

        <p>
            La empresa seleccionada no existe.
        </p>

        <p>
            <a href="javascript:history.back()">
                Volver al formulario
            </a>
        </p>
    ');

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

    die('
        <h2>Error</h2>

        <p>
            El rol seleccionado no existe.
        </p>

        <p>
            <a href="javascript:history.back()">
                Volver al formulario
            </a>
        </p>
    ');

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
        id_rol = ?
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
    $id
]);


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

                    <div class="page-date">
                        9 septiembre 2026
                    </div>

                </div>

            </div>


            <!-- =================================================
                 MENSAJE DE CONFIRMACIÓN
            ================================================== -->

            <div class="config-card">


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