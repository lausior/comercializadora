<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('usuarios');

require_once '../../config/database.php';
require_once '../../includes/logs.php';
require_once '../../includes/validaciones.php';


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

if ($nombre !== '' && preg_match("/^[-']/", $nombre)) {

    die('El nombre debe empezar con una letra.');

}

if ($apellidos !== '' && preg_match("/^[-']/", $apellidos)) {

    die('Los apellidos deben empezar con una letra.');

}

if ($nombre !== '' && !validarCaracteresNombre($nombre)) {

    die('El nombre contiene caracteres no permitidos. Solo se permiten letras, espacios, guiones y apóstrofes.');

}

if ($apellidos !== '' && !validarCaracteresNombre($apellidos)) {

    die('Los apellidos contienen caracteres no permitidos. Solo se permiten letras, espacios, guiones y apóstrofes.');

}

if (!validarNombre($nombre)) {

    die('El nombre es obligatorio y debe tener entre 2 y 50 caracteres.');

}

if (!validarApellidos($apellidos)) {

    die('Los apellidos son obligatorios y deben tener entre 2 y 100 caracteres.');

}

if (!validarUsername($username)) {

    die('El username solo puede contener letras minúsculas, sin números, espacios ni caracteres especiales.');

}

if (!validarEmail($email)) {

    die('El email contiene caracteres no permitidos o no tiene un formato válido.');

}

// El teléfono es opcional; solo se valida cuando se ha introducido.
if ($telefono !== '' && !validarTelefono($telefono)) {

    die('El teléfono contiene caracteres no permitidos. Debe tener 9 dígitos y comenzar por 6, 7, 8 o 9.');

}


// =====================================================
// VALIDAR EMPRESA
// =====================================================

if ($id_empresa <= 0) {

    die('
        <h2>Error</h2>

        <p>
            Debes seleccionar una empresa válida.
        </p>

        <p>
            <a href="crear_usuario.php">
                Volver al formulario
            </a>
        </p>
    ');

}


// =====================================================
// VALIDAR ROL
// =====================================================

if ($id_rol <= 0) {

    die('
        <h2>Error</h2>

        <p>
            Debes seleccionar un rol válido.
        </p>

        <p>
            <a href="crear_usuario.php">
                Volver al formulario
            </a>
        </p>
    ');

}


// =====================================================
// VALIDAR ESTADO
// =====================================================

if (!in_array($estado, $estadosValidos, true)) {

    die('
        <h2>Error</h2>

        <p>
            El estado seleccionado no es válido.
        </p>

        <p>
            <a href="crear_usuario.php">
                Volver al formulario
            </a>
        </p>
    ');

}


// =====================================================
// VALIDAR MOTIVO DE INACTIVIDAD
// =====================================================

if ($estado === 'Inactivo' && $motivoInactivo === '') {

    die('
        <h2>Error</h2>

        <p>
            Indica el motivo por el que el usuario se marca como inactivo.
        </p>

        <p>
            <a href="crear_usuario.php">
                Volver al formulario
            </a>
        </p>
    ');

}

if (
    mb_strlen($motivoInactivo, 'UTF-8') > 500
) {

    die('
        <h2>Error</h2>

        <p>
            El motivo de inactividad no puede superar los 500 caracteres.
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
    SELECT id
    FROM empresas
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id_empresa]);

if (!$stmt->fetch()) {

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
    SELECT id
    FROM roles
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id_rol]);

if (!$stmt->fetch()) {

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
// GENERAR CONTRASEÑA TEMPORAL
// =====================================================

$passwordTemporal = bin2hex(random_bytes(4));

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