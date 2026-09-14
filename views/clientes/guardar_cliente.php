<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('usuarios');

require_once '../../config/database.php';


// =====================================================
// COMPROBAR QUE EL FORMULARIO SE ENVÍA POR POST
// =====================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: usuarios.php');
    exit;

}


// =====================================================
// RECOGER DATOS DEL FORMULARIO
// =====================================================

$nombre     = trim($_POST['nombre'] ?? '');
$apellidos  = trim($_POST['apellidos'] ?? '');
$username   = trim($_POST['username'] ?? '');
$email      = trim($_POST['email'] ?? '');
$telefono   = trim($_POST['telefono'] ?? '');
$id_empresa = (int) ($_POST['id_empresa'] ?? 0);
$id_rol     = (int) ($_POST['id_rol'] ?? 0);


// =====================================================
// VALIDACIONES
// =====================================================

if (
    $nombre === '' ||
    $apellidos === '' ||
    $username === '' ||
    $email === '' ||
    $id_empresa <= 0 ||
    $id_rol <= 0
) {

    die('
        <h2>Error</h2>

        <p>
            Faltan datos obligatorios.
        </p>

        <p>
            <a href="crear_usuario.php">
                Volver al formulario
            </a>
        </p>
    ');

}


// =====================================================
// VALIDAR EMAIL
// =====================================================

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    die('
        <h2>Error</h2>

        <p>
            El email no es válido.
        </p>

        <p>
            <a href="crear_usuario.php">
                Volver al formulario
            </a>
        </p>
    ');

}


// =====================================================
// COMPROBAR QUE EL USERNAME NO EXISTE
// =====================================================

$stmt = $pdo->prepare("
    SELECT id
    FROM usuarios
    WHERE username = ?
    LIMIT 1
");

$stmt->execute([$username]);

if ($stmt->fetch()) {

    die('
        <h2>Error</h2>

        <p>
            El username ya existe.
        </p>

        <p>
            <a href="crear_usuario.php">
                Volver al formulario
            </a>
        </p>
    ');

}


// =====================================================
// COMPROBAR QUE EL EMAIL NO EXISTE
// =====================================================

$stmt = $pdo->prepare("
    SELECT id
    FROM usuarios
    WHERE email = ?
    LIMIT 1
");

$stmt->execute([$email]);

if ($stmt->fetch()) {

    die('
        <h2>Error</h2>

        <p>
            El email ya está registrado.
        </p>

        <p>
            <a href="crear_usuario.php">
                Volver al formulario
            </a>
        </p>
    ');

}


// =====================================================
// COMPROBAR QUE LA EMPRESA EXISTE
// =====================================================

$stmt = $pdo->prepare("
    SELECT
        id,
        nombre
    FROM empresas
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id_empresa]);

$empresa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$empresa) {

    die('
        <h2>Error</h2>

        <p>
            La empresa seleccionada no existe.
        </p>

        <p>
            <a href="crear_usuario.php">
                Volver al formulario
            </a>
        </p>
    ');

}


// =====================================================
// COMPROBAR QUE EL ROL EXISTE
// =====================================================

$stmt = $pdo->prepare("
    SELECT
        id,
        nombre
    FROM roles
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id_rol]);

$rol = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rol) {

    die('
        <h2>Error</h2>

        <p>
            El rol seleccionado no existe.
        </p>

        <p>
            <a href="crear_usuario.php">
                Volver al formulario
            </a>
        </p>
    ');

}


// =====================================================
// COMPROBAR QUE PUEDE CREAR UN USUARIO PARA ESA EMPRESA
// Y CON ESE ROL
// =====================================================
//
// Por si alguien manipula el formulario a mano: una
// EMPRESA no puede crear usuarios para otra empresa, ni
// dar de alta a nadie con un rol más privilegiado que el
// suyo (EMPRESA/USUARIO); NG no puede crear otro SRG.
//
// =====================================================

if (
    rolActual() === ROL_EMPRESA &&
    $id_empresa !== (int) ($_SESSION['id_empresa'] ?? 0)
) {

    die('
        <h2>Error</h2>

        <p>
            No puedes crear usuarios para otra empresa.
        </p>

        <p>
            <a href="crear_usuario.php">
                Volver al formulario
            </a>
        </p>
    ');

}

if (
    rolActual() === ROL_EMPRESA &&
    !in_array($rol['nombre'], [ROL_EMPRESA, ROL_USUARIO], true)
) {

    die('
        <h2>Error</h2>

        <p>
            No puedes asignar ese rol.
        </p>

        <p>
            <a href="crear_usuario.php">
                Volver al formulario
            </a>
        </p>
    ');

}

if (
    rolActual() === ROL_NG &&
    $rol['nombre'] === ROL_SRG
) {

    die('
        <h2>Error</h2>

        <p>
            No puedes asignar ese rol.
        </p>

        <p>
            <a href="crear_usuario.php">
                Volver al formulario
            </a>
        </p>
    ');

}


// =====================================================
// GENERAR CONTRASEÑA INICIAL
// =====================================================

$passwordInicial = '123456';


// =====================================================
// ENCRIPTAR CONTRASEÑA
// =====================================================

$passwordHash = password_hash(
    $passwordInicial,
    PASSWORD_DEFAULT
);


// =====================================================
// INSERTAR USUARIO
// =====================================================
//
// cambiar_password = 1
//
// Esto indica que el usuario deberá cambiar
// la contraseña en su primer acceso.
//
// =====================================================

$stmt = $pdo->prepare("
    INSERT INTO usuarios (
        username,
        nombre,
        apellidos,
        email,
        telefono,
        password,
        cambiar_password,
        id_empresa,
        id_rol,
        creado_por
    )
    VALUES (
        :username,
        :nombre,
        :apellidos,
        :email,
        :telefono,
        :password,
        1,
        :id_empresa,
        :id_rol,
        :creado_por
    )
");


$stmt->execute([

    ':username'   => $username,

    ':nombre'     => $nombre,

    ':apellidos'  => $apellidos,

    ':email'      => $email,

    ':telefono'   => $telefono !== ''
        ? $telefono
        : null,

    ':password'   => $passwordHash,

    ':id_empresa' => $id_empresa,

    ':id_rol'     => $id_rol,

    ':creado_por' => $_SESSION['id_usuario'],

]);


// =====================================================
// OBTENER ID DEL USUARIO CREADO
// =====================================================

$id_usuario = $pdo->lastInsertId();


// =====================================================
// RECUPERAR TODOS LOS DATOS DEL USUARIO
// =====================================================
//
// Volvemos a consultar la base de datos para mostrar
// exactamente los datos que se han guardado.
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

        e.codigo_empresa,

        r.nombre AS rol

    FROM usuarios u

    INNER JOIN empresas e
        ON u.id_empresa = e.id

    INNER JOIN roles r
        ON u.id_rol = r.id

    WHERE u.id = ?

    LIMIT 1
");


$stmtDatos->execute([$id_usuario]);

$usuario = $stmtDatos->fetch(PDO::FETCH_ASSOC);


// =====================================================
// COMPROBAR QUE SE HA RECUPERADO EL USUARIO
// =====================================================

if (!$usuario) {

    die('
        <h2>Error</h2>

        <p>
            El usuario se ha creado, pero no se han podido
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
// NOMBRE COMPLETO
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
// USUARIO DE ACCESO (LOGIN COMPUESTO)
// =====================================================
//
// Este es el valor que la persona deberá escribir en el
// campo "Usuario" de login.php: codigo_empresa-id-username
//
// =====================================================

$usuarioAcceso =
    $usuario['codigo_empresa'] . '-' .
    $usuario['id'] . '-' .
    $usuario['username'];


// =====================================================
// ESTADO DE CONTRASEÑA
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

    <title>
        Usuario creado - Comparador Eléctrico
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
                        Usuario creado correctamente
                    </p>

                </div>


                <div class="page-header-actions">

                    <div class="page-date">
                        9 septiembre 2026
                    </div>

                </div>

            </div>


            <!-- =================================================
                 TARJETA
            ================================================== -->

            <div class="config-card">


                <h2>
                    Usuario creado correctamente
                </h2>


                <!-- =================================================
                     MENSAJE
                ================================================== -->

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

                        se ha creado correctamente.

                    </p>

                </div>


                <!-- =================================================
                     INFORMACIÓN DEL USUARIO
                ================================================== -->

                <div class="usuario-detalle">


                    <!-- =================================================
                         DATOS DEL USUARIO
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


                        <!-- TELÉFONO -->

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


                        <!-- USUARIO DE ACCESO -->

                        <div class="usuario-detalle-item">

                            <span>
                                Usuario de acceso (login)
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                    $usuarioAcceso,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </strong>

                        </div>


                        <!-- CONTRASEÑA INICIAL -->

                        <div class="usuario-detalle-item">

                            <span>
                                Contraseña inicial
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                    $passwordInicial,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </strong>

                        </div>


                        <!-- ESTADO CONTRASEÑA -->

                        <div class="usuario-detalle-item">

                            <span>
                                Estado de contraseña
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                    $estadoPassword,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </strong>

                        </div>


                    </div>


                </div>


                <!-- =================================================
                     INFORMACIÓN DE CONTRASEÑA
                ================================================== -->

                <div class="form-info">

                    <p>

                        El usuario deberá acceder con el usuario de
                        login

                        <strong>
                            <?= htmlspecialchars(
                                $usuarioAcceso,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </strong>

                        y la contraseña

                        <strong>
                            <?= htmlspecialchars(
                                $passwordInicial,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </strong>.

                    </p>


                    <p>

                        En su primer acceso se le pedirá
                        cambiarla obligatoriamente.

                    </p>

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