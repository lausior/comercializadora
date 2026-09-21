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

$estadosValidos = ['Activo', 'Inactivo'];

if ($id <= 0) {

    header('Location: usuarios.php');
    exit;

}


// =====================================================
// COMPROBAR QUE EL USUARIO EXISTA
// =====================================================

$stmtUsuario = $pdo->prepare("
    SELECT
        u.id,
        u.nombre,
        u.apellidos,
        u.email,
        u.telefono,
        u.id_empresa,
        u.id_rol,
        u.creado_por,
        r.nombre AS rol
    FROM usuarios u

    INNER JOIN roles r
        ON r.id = u.id_rol

    WHERE u.id = ?
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
// CUENTA EMPRESA: NOMBRE, EMAIL Y TELÉFONO NO SON EDITABLES
// =====================================================
//
// Esos tres campos pertenecen a la empresa (se copiaron de
// ahí al crearla, ver guardar_empresa.php) y ahí es donde
// hay que cambiarlos; editar_usuario.php ya los muestra de
// solo lectura, pero esto es lo que de verdad lo impide,
// por si se manipula el formulario a mano. El username sí
// es propio del acceso y se puede modificar con normalidad.
//
// =====================================================

if ($usuarioExiste['rol'] === ROL_EMPRESA) {

    $nombre    = $usuarioExiste['nombre'];
    $apellidos = $usuarioExiste['apellidos'];
    $email     = $usuarioExiste['email'];
    $telefono  = $usuarioExiste['telefono'] ?? '';

}


// =====================================================
// COMPROBAR CAMPOS OBLIGATORIOS
// =====================================================
//
// Se acumulan TODOS los errores encontrados en vez de
// cortar en el primero: así se avisa de todo lo que falla
// en un único intento (mismo criterio que
// guardar_empresa.php/guardar_usuario.php).
//
// =====================================================

$errores = [];

if (
    $username === '' ||
    $nombre === '' ||
    ($apellidos === '' && $usuarioExiste['rol'] !== ROL_EMPRESA) ||
    $email === '' ||
    $idEmpresa <= 0 ||
    $idRol <= 0 ||
    !in_array($estado, $estadosValidos, true)
) {

    $errores[] = ['mensaje' => 'Todos los campos obligatorios deben estar completos.', 'campo' => null];

}

if ($estado === 'Inactivo' && $motivoInactivo === '') {

    $errores[] = ['mensaje' => 'Indica el motivo por el que el usuario se marca como inactivo.', 'campo' => 'motivo_inactivo'];

}


// =====================================================
// COMPROBAR FORMATO DEL EMAIL
// =====================================================

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {

    $errores[] = ['mensaje' => 'El correo electrónico no tiene un formato válido.', 'campo' => 'email'];

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

    $errores[] = ['mensaje' => 'No puedes cambiar la empresa de este usuario.', 'campo' => 'id_empresa'];

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

    $errores[] = ['mensaje' => 'El username ya está siendo utilizado por otro usuario.', 'campo' => 'username'];

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

    $errores[] = ['mensaje' => 'El email ya está siendo utilizado por otro usuario.', 'campo' => 'email'];

}


// =====================================================
// COMPROBAR QUE LA EMPRESA EXISTA
// =====================================================

$empresa = null;

if ($idEmpresa > 0) {

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

        $errores[] = ['mensaje' => 'La empresa seleccionada no existe.', 'campo' => 'id_empresa'];

    }

}


// =====================================================
// COMPROBAR QUE EL ROL EXISTA
// =====================================================

$rol = null;

if ($idRol > 0) {

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

        $errores[] = ['mensaje' => 'El rol seleccionado no existe.', 'campo' => 'id_rol'];

    }

}


// =====================================================
// COMPROBAR QUE PUEDE ASIGNAR ESE ROL
// =====================================================
//
// Ni EMPRESA ni NG eligen rol en el formulario para su
// propio equipo: el campo no se muestra, así que no pueden
// cambiar el rol del usuario (el desplegable normal ya no
// se lo deja elegir, pero esto es lo que de verdad lo
// impide).
//
// =====================================================

if (
    in_array(rolActual(), [ROL_EMPRESA, ROL_NG], true) &&
    $idRol !== (int) $usuarioExiste['id_rol']
) {

    $errores[] = ['mensaje' => 'No puedes cambiar el rol de este usuario.', 'campo' => 'id_rol'];

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

if ($rol && $rol['nombre'] === ROL_EMPRESA) {

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

        $errores[] = ['mensaje' => 'Esa empresa ya tiene un usuario con rol Empresa.', 'campo' => 'id_rol'];

    }

}


// =====================================================
// SI HAY ALGÚN ERROR, VOLVER AL FORMULARIO CON TODOS
// =====================================================

if (!empty($errores)) {

    $mensajes = array_unique(array_column($errores, 'mensaje'));
    $primerCampo = array_values(array_filter(array_column($errores, 'campo')))[0] ?? null;

    establecerErrorFormulario(implode(' ', $mensajes), $_POST, 'editar_usuario.php?id=' . $id, $primerCampo);

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
        e.codigo_empresa,
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

// El usuario con rol EMPRESA es el acceso de la propia
// empresa: en su tarjeta de confirmación solo tiene sentido
// mostrar el username, el usuario de acceso completo
// (codigo_empresa-id-username, el mismo formato que se
// escribe en login.php) y el estado — el resto de datos
// (nombre, email, teléfono, empresa, rol...) son los de la
// empresa y ya se muestran en su propia tarjeta.
$usuarioEsCuentaEmpresa = $usuario['rol'] === ROL_EMPRESA;

// Igual que arriba, pero para el rol USUARIO: su tarjeta se
// organiza en "Datos del usuario" / "Datos del login" en vez
// del listado plano que usan SRG y NG.
$esRolUsuario = $usuario['rol'] === ROL_USUARIO;

$usuarioAcceso =
    $usuario['codigo_empresa'] . '-' .
    $usuario['id'] . '-' .
    $usuario['username'];


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

    $estadoPassword = 'Cambiada';

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

                    <a href="crear_usuario.php" class="config-save-button">
                        + Añadir usuario
                    </a>

                </div>

            </div>


            <!-- =================================================
                 MENSAJE DE CONFIRMACIÓN
            ================================================== -->

            <div class="config-card confirmation-card">


                <div class="form-info registration-success">

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

                <?php if ($usuarioEsCuentaEmpresa): ?>


                    <div class="usuario-detalle">

                        <div class="usuario-detalle-grid">


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


                            <!-- USUARIO DE ACCESO (LOGIN) -->

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


                            <!-- ESTADO -->

                            <div class="usuario-detalle-item">

                                <span>
                                    Estado
                                </span>

                                <strong class="<?= $usuario['estado'] === 'Activo' ? 'text-success' : 'text-danger' ?>">
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


                        </div>

                    </div>


                <?php elseif ($esRolUsuario): ?>


                    <h2 class="confirmation-section-title">Datos del usuario</h2>

                    <div class="usuario-detalle">

                        <div class="usuario-detalle-grid">

                            <div class="usuario-detalle-item">
                                <span>ID</span>
                                <strong><?= htmlspecialchars($usuario['id'], ENT_QUOTES, 'UTF-8') ?></strong>
                            </div>

                            <div class="usuario-detalle-item">
                                <span>Username</span>
                                <strong><?= htmlspecialchars($usuario['username'], ENT_QUOTES, 'UTF-8') ?></strong>
                            </div>

                            <div class="usuario-detalle-item">
                                <span>Email</span>
                                <strong><?= htmlspecialchars($usuario['email'], ENT_QUOTES, 'UTF-8') ?></strong>
                            </div>

                            <div class="usuario-detalle-item">
                                <span>Teléfono</span>
                                <strong><?= htmlspecialchars($usuario['telefono'] ?? 'No indicado', ENT_QUOTES, 'UTF-8') ?></strong>
                            </div>

                            <div class="usuario-detalle-item">
                                <span>Rol</span>
                                <strong><?= htmlspecialchars($usuario['rol'], ENT_QUOTES, 'UTF-8') ?></strong>
                            </div>

                            <div class="usuario-detalle-item">
                                <span>Empresa</span>
                                <strong><?= htmlspecialchars($usuario['empresa'], ENT_QUOTES, 'UTF-8') ?></strong>
                            </div>

                        </div>

                    </div>


                    <div class="access-section">

                        <h2 class="confirmation-section-title">Datos del login</h2>

                        <div class="usuario-detalle">

                            <div class="usuario-detalle-grid">

                                <div class="usuario-detalle-item">
                                    <span>Username</span>
                                    <strong><?= htmlspecialchars($usuarioAcceso, ENT_QUOTES, 'UTF-8') ?></strong>
                                </div>

                                <div class="usuario-detalle-item">
                                    <span>Contraseña</span>
                                    <strong><?= htmlspecialchars($estadoPassword, ENT_QUOTES, 'UTF-8') ?></strong>
                                </div>

                                <div class="usuario-detalle-item">
                                    <span>Estado</span>
                                    <strong class="<?= $usuario['estado'] === 'Activo' ? 'text-success' : 'text-danger' ?>">
                                        <?= htmlspecialchars($usuario['estado'], ENT_QUOTES, 'UTF-8') ?>
                                    </strong>
                                </div>

                                <?php if ($usuario['estado'] === 'Inactivo'): ?>

                                    <div class="usuario-detalle-item">
                                        <span>Motivo</span>
                                        <strong><?= htmlspecialchars($usuario['motivo_inactivo'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    </div>

                                <?php endif; ?>

                            </div>

                        </div>

                    </div>


                <?php else: ?>


                    <div class="usuario-detalle">

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

                                <strong class="<?= $usuario['estado'] === 'Activo' ? 'text-success' : 'text-danger' ?>">
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


                <?php endif; ?>


                <!-- =================================================
                     BOTONES
                ================================================== -->

                <div class="form-actions">


                    <a
                        href="editar_usuario.php?id=<?= (int) $usuario['id'] ?>"
                        class="config-save-button"
                    >
                        Editar
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