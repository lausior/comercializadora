<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('usuarios');

require_once '../../config/database.php';
require_once '../../includes/logs.php';
require_once '../../includes/validaciones.php';
require_once '../../includes/form_flash.php';


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

$nombre = trim($_POST['nombre'] ?? '');
$apellidos = trim($_POST['apellidos'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$id_empresa = (int) ($_POST['id_empresa'] ?? 0);
$id_rol = (int) ($_POST['id_rol'] ?? 0);
$estado = trim($_POST['estado'] ?? '');
$motivoInactivo = trim($_POST['motivo_inactivo'] ?? '');


// =====================================================
// ESTADOS VÁLIDOS
// =====================================================

$estadosValidos = ['Activo', 'Inactivo'];


// =====================================================
// VALIDAR FORMATO DE LOS DATOS
// =====================================================
//
// Cualquier dato inválido vuelve al formulario (en vez de
// dejar al usuario en una página en blanco) con el mensaje
// de error y lo que ya había escrito, vía
// establecerErrorFormulario() (ver includes/form_flash.php).
//
// =====================================================

if ($nombre !== '' && preg_match("/^[-']/", $nombre)) {

    establecerErrorFormulario('El nombre debe empezar con una letra.', $_POST, 'crear_usuario.php', 'nombre');

}

if ($apellidos !== '' && preg_match("/^[-']/", $apellidos)) {

    establecerErrorFormulario('Los apellidos deben empezar con una letra.', $_POST, 'crear_usuario.php', 'apellidos');

}

if ($nombre !== '' && !validarCaracteresNombre($nombre)) {

    establecerErrorFormulario('El nombre contiene caracteres no permitidos. Solo se permiten letras, espacios, guiones y apóstrofes.', $_POST, 'crear_usuario.php', 'nombre');

}

if ($apellidos !== '' && !validarCaracteresNombre($apellidos)) {

    establecerErrorFormulario('Los apellidos contienen caracteres no permitidos. Solo se permiten letras, espacios, guiones y apóstrofes.', $_POST, 'crear_usuario.php', 'apellidos');

}

if (!validarNombre($nombre)) {

    establecerErrorFormulario('El nombre es obligatorio y debe tener entre 2 y 50 caracteres.', $_POST, 'crear_usuario.php', 'nombre');

}

if (!validarApellidos($apellidos)) {

    establecerErrorFormulario('Los apellidos son obligatorios y deben tener entre 2 y 100 caracteres.', $_POST, 'crear_usuario.php', 'apellidos');

}

if (!validarUsername($username)) {

    establecerErrorFormulario('El username solo puede contener letras minúsculas, sin números, espacios ni caracteres especiales.', $_POST, 'crear_usuario.php', 'username');

}

if (!validarEmail($email)) {

    establecerErrorFormulario('El email contiene caracteres no permitidos o no tiene un formato válido.', $_POST, 'crear_usuario.php', 'email');

}

// El teléfono es opcional; solo se valida cuando se ha introducido.
if ($telefono !== '' && !validarTelefono($telefono)) {

    establecerErrorFormulario('El teléfono no es válido. Introduce un número nacional o internacional (7 a 15 dígitos).', $_POST, 'crear_usuario.php', 'telefono');

}


// =====================================================
// VALIDAR EMPRESA
// =====================================================

if ($id_empresa <= 0) {

    establecerErrorFormulario('Debes seleccionar una empresa válida.', $_POST, 'crear_usuario.php', 'id_empresa');

}


// =====================================================
// COMPROBAR QUE PUEDE CREAR USUARIOS PARA ESA EMPRESA
// =====================================================
//
// EMPRESA solo puede crear usuarios para su propia
// empresa. El desplegable de crear_usuario.php ya se lo
// impide, pero esto es lo que de verdad lo impide si
// manipula el formulario (ver el mismo patrón en
// actualizar_usuario.php).
//
// =====================================================

if (
    rolActual() === ROL_EMPRESA &&
    $id_empresa !== (int) ($_SESSION['id_empresa'] ?? 0)
) {

    establecerErrorFormulario('No puedes crear usuarios para esa empresa.', $_POST, 'crear_usuario.php', 'id_empresa');

}

// =====================================================
// VALIDAR ROL
// =====================================================

if ($id_rol <= 0) {

    establecerErrorFormulario('Debes seleccionar un rol válido.', $_POST, 'crear_usuario.php', 'id_rol');

}


// =====================================================
// VALIDAR ESTADO
// =====================================================

if (!in_array($estado, $estadosValidos, true)) {

    establecerErrorFormulario('El estado seleccionado no es válido.', $_POST, 'crear_usuario.php', 'estado');

}


// =====================================================
// VALIDAR MOTIVO DE INACTIVIDAD
// =====================================================

if ($estado === 'Inactivo' && $motivoInactivo === '') {

    establecerErrorFormulario('Indica el motivo por el que el usuario se marca como inactivo.', $_POST, 'crear_usuario.php', 'motivo_inactivo');

}

if (
    mb_strlen($motivoInactivo, 'UTF-8') > 500
) {

    establecerErrorFormulario('El motivo de inactividad no puede superar los 500 caracteres.', $_POST, 'crear_usuario.php', 'motivo_inactivo');

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

    establecerErrorFormulario('El username ya existe.', $_POST, 'crear_usuario.php', 'username');

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

    establecerErrorFormulario('El email ya está registrado.', $_POST, 'crear_usuario.php', 'email');

}


// =====================================================
// COMPROBAR QUE LA EMPRESA EXISTE
// =====================================================

$stmt = $pdo->prepare("
    SELECT id, creado_por
    FROM empresas
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id_empresa]);

$empresaSeleccionada = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$empresaSeleccionada) {

    establecerErrorFormulario('La empresa seleccionada no existe.', $_POST, 'crear_usuario.php', 'id_empresa');

}


// =====================================================
// COMPROBAR QUE NG PUEDE CREAR USUARIOS PARA ESA EMPRESA
// =====================================================
//
// NG solo puede crear usuarios para las empresas que ha
// creado él mismo (mismo criterio que puedeVerEmpresa()
// en el resto de la app). SRG no tiene restricción.
//
// =====================================================

if (
    rolActual() === ROL_NG &&
    !puedeVerEmpresa(
        $empresaSeleccionada['creado_por'] !== null ? (int) $empresaSeleccionada['creado_por'] : null
    )
) {

    establecerErrorFormulario('No puedes crear usuarios para esa empresa.', $_POST, 'crear_usuario.php', 'id_empresa');

}


// =====================================================
// COMPROBAR QUE EL ROL EXISTE
// =====================================================

$stmt = $pdo->prepare("
    SELECT id, nombre
    FROM roles
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id_rol]);

$rolSeleccionado = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rolSeleccionado) {

    establecerErrorFormulario('El rol seleccionado no existe.', $_POST, 'crear_usuario.php', 'id_rol');

}


// =====================================================
// COMPROBAR QUE PUEDE ASIGNAR ESE ROL
// =====================================================
//
// EMPRESA no elige rol en el formulario: todo lo que crea
// es rol USUARIO (equipo simple). NG no puede crear otro
// SRG.
//
// =====================================================

if (
    rolActual() === ROL_EMPRESA &&
    $rolSeleccionado['nombre'] !== ROL_USUARIO
) {

    establecerErrorFormulario('No puedes asignar ese rol.', $_POST, 'crear_usuario.php', 'id_rol');

}

if (
    rolActual() === ROL_NG &&
    $rolSeleccionado['nombre'] === ROL_SRG
) {

    establecerErrorFormulario('No puedes asignar ese rol.', $_POST, 'crear_usuario.php', 'id_rol');

}


// =====================================================
// UNA EMPRESA SOLO PUEDE TENER UN USUARIO CON ROL EMPRESA
// =====================================================
//
// Si la empresa elegida ya tiene un usuario con rol
// EMPRESA, el desplegable Rol de crear_usuario.php ya se
// bloquea en USUARIO (ver el <script> de esa página), pero
// esto es lo que de verdad lo impide si se manipula el
// formulario.
//
// =====================================================

if ($rolSeleccionado['nombre'] === ROL_EMPRESA) {

    $stmtEmpresaYaTieneAdmin = $pdo->prepare("
        SELECT id
        FROM usuarios
        WHERE id_empresa = ?
            AND id_rol = (SELECT id FROM roles WHERE nombre = ? LIMIT 1)
        LIMIT 1
    ");

    $stmtEmpresaYaTieneAdmin->execute([$id_empresa, ROL_EMPRESA]);

    if ($stmtEmpresaYaTieneAdmin->fetch()) {

        establecerErrorFormulario('Esa empresa ya tiene un usuario con rol Empresa. El nuevo usuario debe ser Usuario.', $_POST, 'crear_usuario.php', 'id_rol');

    }

}


// =====================================================
// CONTRASEÑA INICIAL
// =====================================================
//
// Misma contraseña inicial fija que resetear_password.php,
// para que todo el mundo sepa con cuál acceder la primera
// vez. cambiar_password la obliga a cambiarla nada más
// entrar.
//
// =====================================================

$passwordTemporal = '123456';

$passwordHash = password_hash(
    $passwordTemporal,
    PASSWORD_DEFAULT
);


// =====================================================
// INSERTAR USUARIO
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
        estado,
        motivo_inactivo,
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
        :estado,
        :motivo_inactivo,
        :creado_por
    )
");


$stmt->execute([
    ':username' => $username,
    ':nombre' => $nombre,
    ':apellidos' => $apellidos,
    ':email' => $email,
    ':telefono' => $telefono !== ''
        ? $telefono
        : null,
    ':password' => $passwordHash,
    ':id_empresa' => $id_empresa,
    ':id_rol' => $id_rol,
    ':estado' => $estado,
    ':motivo_inactivo' => $estado === 'Inactivo'
        ? $motivoInactivo
        : null,
    ':creado_por' => $_SESSION['id_usuario'],
]);


// =====================================================
// OBTENER ID DEL USUARIO CREADO
// =====================================================

$id_usuario = $pdo->lastInsertId();


// =====================================================
// RECUPERAR TODOS LOS DATOS DEL USUARIO
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
            El usuario se ha creado, pero no se han podido recuperar sus datos.
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
    'Usuario creado',
    'Se ha creado el usuario "' . $usuario['username'] . '".'
);


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
// campo "Usuario" de login.php:
//
// codigo_empresa-id-username
//
// =====================================================

$usuarioAcceso =
    $usuario['codigo_empresa'] . '-' .
    $usuario['id'] . '-' .
    $usuario['username'];

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Usuario creado - Comparador Eléctrico</title>

    <link rel="stylesheet" href="../../css/style.css">

</head>

<body>

    <?php include '../../templates/header.php'; ?>

    <div class="app-container">

        <?php include '../../templates/sidebar.php'; ?>

        <main class="main-content">

            <div class="page-header">

                <div>
                    <h1>Usuarios</h1>
                    <p>Usuario creado correctamente</p>
                </div>

            </div>

            <div class="config-card confirmation-card">

                <h2>Usuario creado</h2>

                <div class="form-info">
                    <p>
                        El usuario
                        <strong><?= htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8') ?></strong>
                        se ha creado correctamente.
                    </p>
                </div>

                <div class="usuario-detalle">

                    <div class="usuario-detalle-grid">

                        <div class="usuario-detalle-item">
                            <span>ID de usuario</span>
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
                            <span>Empresa</span>
                            <strong><?= htmlspecialchars($usuario['empresa'], ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Rol</span>
                            <strong><?= htmlspecialchars($usuario['rol'], ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Estado</span>
                            <strong><?= htmlspecialchars($usuario['estado'], ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>

                        <?php if ($usuario['estado'] === 'Inactivo'): ?>

                            <div class="usuario-detalle-item">
                                <span>Motivo</span>
                                <strong><?= htmlspecialchars($usuario['motivo_inactivo'], ENT_QUOTES, 'UTF-8') ?></strong>
                            </div>

                        <?php endif; ?>

                        <div class="usuario-detalle-item">
                            <span>Acceso inicial</span>
                            <strong><?= htmlspecialchars($usuarioAcceso, ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Contraseña temporal</span>
                            <strong><?= htmlspecialchars($passwordTemporal, ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>

                    </div>

                </div>

                <div class="form-actions">

                    <a href="crear_usuario.php" class="config-save-button">
                        + Añadir usuario
                    </a>

                    <a href="usuarios.php" class="config-cancel-button">
                        Volver a usuarios
                    </a>

                </div>

            </div>

        </main>

    </div>

    <?php include '../../templates/footer.php'; ?>

</body>

</html>
