<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('usuarios');

require_once '../../config/database.php';
require_once '../../includes/logs.php';
require_once '../../includes/validaciones.php';
require_once '../../includes/form_flash.php';
require_once '../../includes/empresas.php';


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
// Se acumulan TODOS los errores encontrados en vez de
// cortar en el primero: así se avisa de todo lo que falla
// en un único intento, en lugar de descubrirlo de uno en
// uno en varias vueltas (mismo criterio que
// guardar_empresa.php).
//
// =====================================================

$errores = [];

if ($nombre !== '' && preg_match("/^[-']/", $nombre)) {

    $errores[] = ['mensaje' => 'El nombre debe empezar con una letra.', 'campo' => 'nombre'];

}

if ($apellidos !== '' && preg_match("/^[-']/", $apellidos)) {

    $errores[] = ['mensaje' => 'Los apellidos deben empezar con una letra.', 'campo' => 'apellidos'];

}

if ($nombre !== '' && !validarCaracteresNombre($nombre)) {

    $errores[] = ['mensaje' => 'El nombre contiene caracteres no permitidos. Solo se permiten letras, espacios, guiones y apóstrofes.', 'campo' => 'nombre'];

}

if ($apellidos !== '' && !validarCaracteresNombre($apellidos)) {

    $errores[] = ['mensaje' => 'Los apellidos contienen caracteres no permitidos. Solo se permiten letras, espacios, guiones y apóstrofes.', 'campo' => 'apellidos'];

}

if (!validarNombre($nombre)) {

    $errores[] = ['mensaje' => 'El nombre es obligatorio y debe tener entre 2 y 50 caracteres.', 'campo' => 'nombre'];

}

if (!validarApellidos($apellidos)) {

    $errores[] = ['mensaje' => 'Los apellidos son obligatorios y deben tener entre 2 y 100 caracteres.', 'campo' => 'apellidos'];

}

if (!validarUsername($username)) {

    $errores[] = ['mensaje' => 'El username solo puede contener letras minúsculas (incluida la ñ), sin números, espacios ni otros caracteres especiales.', 'campo' => 'username'];

}

if (!validarEmail($email)) {

    $errores[] = ['mensaje' => 'El email contiene caracteres no permitidos o no tiene un formato válido.', 'campo' => 'email'];

}

// El teléfono es opcional; solo se valida cuando se ha introducido.
if ($telefono !== '' && !validarTelefono($telefono)) {

    $errores[] = ['mensaje' => 'El teléfono no es válido. Introduce un número nacional o internacional (7 a 15 dígitos).', 'campo' => 'telefono'];

}


// =====================================================
// VALIDAR EMPRESA
// =====================================================

if ($id_empresa <= 0) {

    $errores[] = ['mensaje' => 'Debes seleccionar una empresa válida.', 'campo' => 'id_empresa'];

}


// =====================================================
// COMPROBAR QUE PUEDE CREAR USUARIOS PARA ESA EMPRESA
// =====================================================
//
// EMPRESA solo puede crear usuarios para su propia
// empresa. Lo mismo aplica a NG con SU propia empresa (NG
// Asesores): desde la sección Usuarios, NG solo da de alta
// a su propio equipo interno, no al de sus empresas
// cliente (esas ya tienen su acceso creado junto a la
// empresa, ver guardar_empresa.php). El desplegable de
// crear_usuario.php ya se lo impide a ambos (empresa/rol
// van ocultos), pero esto es lo que de verdad lo impide si
// se manipula el formulario (ver el mismo patrón en
// actualizar_usuario.php).
//
// =====================================================

if (
    in_array(rolActual(), [ROL_EMPRESA, ROL_NG], true) &&
    $id_empresa !== (int) ($_SESSION['id_empresa'] ?? 0)
) {

    $errores[] = ['mensaje' => 'No puedes crear usuarios para esa empresa.', 'campo' => 'id_empresa'];

}

// =====================================================
// VALIDAR ROL
// =====================================================

if ($id_rol <= 0) {

    $errores[] = ['mensaje' => 'Debes seleccionar un rol válido.', 'campo' => 'id_rol'];

}


// =====================================================
// VALIDAR ESTADO
// =====================================================

if (!in_array($estado, $estadosValidos, true)) {

    $errores[] = ['mensaje' => 'El estado seleccionado no es válido.', 'campo' => 'estado'];

}


// =====================================================
// VALIDAR MOTIVO DE INACTIVIDAD
// =====================================================
// Si el estado elegido es Inactivo, el motivo es obligatorio
// (comprobación de valores válidos más abajo, una vez se
// conoce el rol elegido: las opciones del select dependen de
// si es USUARIO o EMPRESA).

if (mb_strlen($motivoInactivo, 'UTF-8') > 500) {

    $errores[] = ['mensaje' => 'El motivo de inactividad no puede superar los 500 caracteres.', 'campo' => 'motivo_inactivo'];

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

    $errores[] = ['mensaje' => 'El username ya existe.', 'campo' => 'username'];

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

    $errores[] = ['mensaje' => 'El email ya está registrado.', 'campo' => 'email'];

}


// =====================================================
// COMPROBAR QUE LA EMPRESA EXISTE
// =====================================================
//
// Solo se comprueba si de verdad se ha elegido una: si
// $id_empresa ya es 0, el aviso de "selecciona una
// empresa válida" de arriba es suficiente, no hace falta
// duplicarlo con "no existe".
//
// =====================================================

$empresaSeleccionada = null;

if ($id_empresa > 0) {

    $stmt = $pdo->prepare("
        SELECT id, creado_por, estado
        FROM empresas
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$id_empresa]);

    $empresaSeleccionada = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$empresaSeleccionada) {

        $errores[] = ['mensaje' => 'La empresa seleccionada no existe.', 'campo' => 'id_empresa'];

    }

}


// =====================================================
// COMPROBAR QUE EL ROL EXISTE
// =====================================================

$rolSeleccionado = null;

if ($id_rol > 0) {

    $stmt = $pdo->prepare("
        SELECT id, nombre
        FROM roles
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$id_rol]);

    $rolSeleccionado = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$rolSeleccionado) {

        $errores[] = ['mensaje' => 'El rol seleccionado no existe.', 'campo' => 'id_rol'];

    }

}


// =====================================================
// COMPROBAR QUE PUEDE ASIGNAR ESE ROL
// =====================================================
//
// Ni EMPRESA ni NG eligen rol en el formulario: todo lo
// que crean desde la sección Usuarios es rol USUARIO
// (equipo simple, de su propia empresa). SRG no tiene esta
// restricción.
//
// =====================================================

if (
    in_array(rolActual(), [ROL_EMPRESA, ROL_NG], true) &&
    $rolSeleccionado &&
    $rolSeleccionado['nombre'] !== ROL_USUARIO
) {

    $errores[] = ['mensaje' => 'No puedes asignar ese rol.', 'campo' => 'id_rol'];

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

if ($rolSeleccionado && $rolSeleccionado['nombre'] === ROL_EMPRESA) {

    $stmtEmpresaYaTieneAdmin = $pdo->prepare("
        SELECT id
        FROM usuarios
        WHERE id_empresa = ?
            AND id_rol = (SELECT id FROM roles WHERE nombre = ? LIMIT 1)
        LIMIT 1
    ");

    $stmtEmpresaYaTieneAdmin->execute([$id_empresa, ROL_EMPRESA]);

    if ($stmtEmpresaYaTieneAdmin->fetch()) {

        $errores[] = ['mensaje' => 'Esa empresa ya tiene un usuario con rol Empresa. El nuevo usuario debe ser Usuario.', 'campo' => 'id_rol'];

    }

}


// =====================================================
// MOTIVO OBLIGATORIO SI PASA A INACTIVO
// =====================================================
//
// Las opciones válidas dependen del rol elegido (mismas
// listas que en el select de crear_usuario.php/
// editar_usuario.php): USUARIO usa vacaciones/baja_laboral/
// baja_empresa, EMPRESA usa impago/fin_contrato.
//
// =====================================================

if ($estado === 'Inactivo' && $rolSeleccionado) {

    // "empresa_inactiva" no está aquí a propósito: ese motivo lo
    // pone solo la cascada de la empresa (ver includes/empresas.php),
    // nunca se elige a mano al crear un usuario.
    $motivosValidos = $rolSeleccionado['nombre'] === ROL_EMPRESA
        ? ['impago', 'fin_contrato']
        : ['vacaciones', 'baja_laboral', 'baja_empresa'];

    if (!in_array($motivoInactivo, $motivosValidos, true)) {

        $errores[] = ['mensaje' => 'Debes seleccionar un motivo.', 'campo' => 'motivo_inactivo'];

    }

}


// =====================================================
// NO CREAR ACTIVO UN USUARIO SUELTO DE EMPRESA INACTIVA
// =====================================================
//
// Mismo criterio que actualizar_usuario.php: un usuario normal
// (rol USUARIO) no puede nacer Activo si la empresa elegida
// está Inactiva.
//
// =====================================================

if (
    $estado === 'Activo' &&
    $rolSeleccionado &&
    $rolSeleccionado['nombre'] === ROL_USUARIO &&
    $empresaSeleccionada &&
    $empresaSeleccionada['estado'] === 'Inactivo'
) {

    $errores[] = ['mensaje' => 'No puedes crear este usuario como Activo: la empresa está inactiva.', 'campo' => 'estado'];

}


// =====================================================
// SI HAY ALGÚN ERROR, VOLVER AL FORMULARIO CON TODOS
// =====================================================

if (!empty($errores)) {

    $mensajes = array_unique(array_column($errores, 'mensaje'));
    $primerCampo = array_values(array_filter(array_column($errores, 'campo')))[0] ?? null;

    establecerErrorFormulario(implode(' ', $mensajes), $_POST, 'crear_usuario.php', $primerCampo);

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
    ':motivo_inactivo' => $estado === 'Inactivo' && $motivoInactivo !== ''
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


// =====================================================
// ¿ES UN USUARIO CON ROL "USUARIO"?
// =====================================================
//
// Su tarjeta de confirmación se organiza en dos bloques
// (Datos del usuario / Datos del login) en vez del listado
// plano que usan SRG y NG. Ver el mismo criterio en
// actualizar_usuario.php.
//
// =====================================================

$esRolUsuario = $usuario['rol'] === ROL_USUARIO;

$estadoPassword = (int) $usuario['cambiar_password'] === 1
    ? 'Pendiente de cambio'
    : 'Cambiada';

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

                <div class="page-header-actions">

                    <a href="crear_usuario.php" class="config-save-button">
                        + Añadir usuario
                    </a>

                </div>

            </div>

            <div class="config-card confirmation-card">

                <div class="form-info registration-success">
                    <p>
                        El usuario
                        <strong><?= htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8') ?></strong>
                        se ha creado correctamente.
                    </p>
                </div>

                <?php if ($esRolUsuario): ?>

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
                                        <strong><?= htmlspecialchars(etiquetaMotivoInactivo($usuario['motivo_inactivo']), ENT_QUOTES, 'UTF-8') ?></strong>
                                    </div>

                                <?php endif; ?>

                            </div>

                        </div>

                    </div>

                <?php else: ?>

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
                                <strong class="<?= $usuario['estado'] === 'Activo' ? 'text-success' : 'text-danger' ?>">
                                    <?= htmlspecialchars($usuario['estado'], ENT_QUOTES, 'UTF-8') ?>
                                </strong>
                            </div>

                            <?php if ($usuario['estado'] === 'Inactivo'): ?>

                                <div class="usuario-detalle-item">
                                    <span>Motivo</span>
                                    <strong><?= htmlspecialchars(etiquetaMotivoInactivo($usuario['motivo_inactivo']), ENT_QUOTES, 'UTF-8') ?></strong>
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

                <?php endif; ?>

                <div class="form-actions">

                    <a href="editar_usuario.php?id=<?= (int) $usuario['id'] ?>" class="config-save-button">
                        Editar
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
