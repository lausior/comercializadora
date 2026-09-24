<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('empresas');

require_once '../../config/database.php';
require_once '../../includes/logs.php';
require_once '../../includes/validaciones.php';
require_once '../../includes/form_flash.php';
require_once '../../includes/empresas.php';


// =====================================================
// COMPROBAR QUE EL FORMULARIO SE ENVÍA POR POST
// =====================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: empresas.php');
    exit;

}


// =====================================================
// RECOGER DATOS DEL FORMULARIO
// =====================================================

$codigoEmpresa   = trim($_POST['codigo_empresa'] ?? '');
$nombre          = trim($_POST['nombre'] ?? '');
$cif             = trim($_POST['cif'] ?? '');
$direccion       = trim($_POST['direccion'] ?? '');
$telefono        = trim($_POST['telefono'] ?? '');
$email           = trim($_POST['email'] ?? '');
$estado          = trim($_POST['estado'] ?? '');
$motivoInactivo  = trim($_POST['motivo_inactivo'] ?? '');

// Acceso de la empresa (rol EMPRESA): no es un usuario
// aparte que "pertenece" a la empresa, es el login de la
// propia empresa, así que se crea a la vez y no hace falta
// pasar por crear_usuario.php aparte. Solo hace falta pedir
// el username: el nombre, el email y el teléfono se
// reutilizan de los datos de la empresa de arriba (por eso
// el email de la empresa pasa a ser obligatorio: hace
// también de email de acceso).
$usuarioUsername = trim($_POST['usuario_username'] ?? '');


// =====================================================
// VALIDACIONES
// =====================================================
//
// Se acumulan TODOS los errores encontrados (formato y
// duplicados) en vez de cortar en el primero: así, si el
// CIF no es válido Y el username ya existe, se avisa de
// las dos cosas a la vez en el primer intento, en lugar de
// obligar a corregir una, reenviar, y solo entonces
// enterarse de la siguiente.
//
// =====================================================

$errores = [];

if (!preg_match('/^[0-9]{6}$/', $codigoEmpresa)) {

    $errores[] = ['mensaje' => 'El código de empresa no es válido.', 'campo' => null];

}

// Nombre, CIF, dirección, teléfono, email, estado y motivo:
// mismas reglas que al editar (ver includes/empresas.php). Al
// crear, el email es obligatorio: es también el del acceso.
$errores = array_merge($errores, validarDatosEmpresa([
    'nombre'          => $nombre,
    'cif'             => $cif,
    'direccion'       => $direccion,
    'telefono'        => $telefono,
    'email'           => $email,
    'estado'          => $estado,
    'motivo_inactivo' => $motivoInactivo,
], true));


// =====================================================
// VALIDAR EL ACCESO DE LA EMPRESA
// =====================================================
//
// Solo el username es un dato propio del acceso; nombre,
// email y teléfono se reutilizan de la empresa (ya
// validados arriba, así que no hace falta revalidarlos).
//
// =====================================================

if (!validarUsername($usuarioUsername)) {

    $errores[] = ['mensaje' => 'El username del usuario solo puede contener letras minúsculas (incluida la ñ), sin números, espacios ni otros caracteres especiales.', 'campo' => 'usuario_username'];

}


// =====================================================
// COMPROBAR QUE EL USERNAME DEL USUARIO NO EXISTE
// =====================================================

$stmtUsername = $pdo->prepare("
    SELECT id
    FROM usuarios
    WHERE username = ?
    LIMIT 1
");

$stmtUsername->execute([$usuarioUsername]);

if ($stmtUsername->fetch()) {

    $errores[] = ['mensaje' => 'El username ya existe.', 'campo' => 'usuario_username'];

}


// =====================================================
// COMPROBAR QUE EL EMAIL (REUTILIZADO DE LA EMPRESA) NO
// EXISTE YA COMO EMAIL DE OTRO USUARIO
// =====================================================

$stmtEmailUsuario = $pdo->prepare("
    SELECT id
    FROM usuarios
    WHERE email = ?
    LIMIT 1
");

$stmtEmailUsuario->execute([$email]);

if ($stmtEmailUsuario->fetch()) {

    $errores[] = ['mensaje' => 'Ese email ya está registrado como email de acceso de otro usuario.', 'campo' => 'email'];

}


// =====================================================
// COMPROBAR QUE EL CIF NO EXISTE
// =====================================================

$stmt = $pdo->prepare("
    SELECT id
    FROM empresas
    WHERE cif = ?
    LIMIT 1
");

$stmt->execute([$cif]);

if ($stmt->fetch()) {

    $errores[] = ['mensaje' => 'Ya existe una empresa con ese CIF.', 'campo' => 'cif'];

}


// =====================================================
// COMPROBAR QUE EL CÓDIGO DE EMPRESA SIGUE LIBRE
// =====================================================
//
// Se generó al cargar el formulario (ver crear_empresa.php);
// se vuelve a comprobar aquí por si, entretanto, otra
// empresa se ha creado con el mismo código.
//
// =====================================================

$stmtCodigo = $pdo->prepare("
    SELECT id
    FROM empresas
    WHERE codigo_empresa = ?
    LIMIT 1
");

$stmtCodigo->execute([$codigoEmpresa]);

if ($stmtCodigo->fetch()) {

    $errores[] = ['mensaje' => 'El código de empresa ya no está disponible, vuelve a intentarlo.', 'campo' => null];

}


// =====================================================
// SI HAY ALGÚN ERROR, VOLVER AL FORMULARIO CON TODOS
// =====================================================

volverConErroresEmpresa($errores, 'crear_empresa.php');


// =====================================================
// OBTENER EL ID DEL ROL EMPRESA
// =====================================================

$stmtRolEmpresa = $pdo->prepare("
    SELECT id
    FROM roles
    WHERE nombre = ?
    LIMIT 1
");

$stmtRolEmpresa->execute([ROL_EMPRESA]);

$idRolEmpresa = (int) $stmtRolEmpresa->fetchColumn();

if ($idRolEmpresa <= 0) {

    establecerErrorFormulario('No se encuentra el rol Empresa. Contacta con soporte.', $_POST, 'crear_empresa.php');

}


// =====================================================
// INSERTAR EMPRESA Y SU ACCESO (MISMA TRANSACCIÓN)
// =====================================================
//
// Las dos filas se crean juntas: si algo falla al insertar
// el acceso, la empresa tampoco se queda creada sin forma
// de entrar a ella.
//
// =====================================================

$pdo->beginTransaction();

try {

    $stmt = $pdo->prepare("
        INSERT INTO empresas (
            codigo_empresa,
            nombre,
            cif,
            direccion,
            telefono,
            email,
            estado,
            motivo_inactivo,
            creado_por
        )
        VALUES (
            :codigo_empresa,
            :nombre,
            :cif,
            :direccion,
            :telefono,
            :email,
            :estado,
            :motivo_inactivo,
            :creado_por
        )
    ");

    $stmt->execute([
        ':codigo_empresa'   => $codigoEmpresa,
        ':nombre'           => $nombre,
        ':cif'              => $cif,
        ':direccion'        => $direccion !== '' ? $direccion : null,
        ':telefono'         => $telefono !== '' ? $telefono : null,
        ':email'            => $email !== '' ? $email : null,
        ':estado'           => $estado,
        ':motivo_inactivo'  => $estado === 'Inactivo' && $motivoInactivo !== '' ? $motivoInactivo : null,
        ':creado_por'       => $_SESSION['id_usuario'],
    ]);

    $idEmpresa = $pdo->lastInsertId();

    // Misma contraseña inicial fija que guardar_usuario.php y
    // resetear_password.php, para que sea consistente en toda
    // la app. cambiar_password obliga a cambiarla al entrar.
    $passwordTemporal = PASSWORD_INICIAL;

    $passwordHash = password_hash($passwordTemporal, PASSWORD_DEFAULT);

    $stmtUsuario = $pdo->prepare("
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

    // El usuario de acceso debe nacer con el MISMO estado que
    // la empresa (y, si es Inactivo, el mismo motivo): si no,
    // una empresa creada directamente como Inactiva se queda
    // con su login en Activo, mostrando estados contradictorios
    // entre el listado de Empresas y el de Usuarios (ver
    // actualizar_empresa.php/cambiar_estado_empresa.php, que sí
    // mantienen ambos sincronizados al editar/activar/desactivar).
    $stmtUsuario->execute([
        ':username'        => $usuarioUsername,
        ':nombre'          => $nombre,
        ':apellidos'       => '',
        ':email'           => $email,
        ':telefono'        => $telefono !== '' ? $telefono : null,
        ':password'        => $passwordHash,
        ':id_empresa'      => $idEmpresa,
        ':id_rol'          => $idRolEmpresa,
        ':estado'          => $estado,
        ':motivo_inactivo' => $estado === 'Inactivo' && $motivoInactivo !== '' ? $motivoInactivo : null,
        ':creado_por'      => $_SESSION['id_usuario'],
    ]);

    $idUsuario = $pdo->lastInsertId();

    $pdo->commit();

} catch (Throwable $e) {

    $pdo->rollBack();

    establecerErrorFormulario('No se ha podido crear la empresa y el usuario. Vuelve a intentarlo.', $_POST, 'crear_empresa.php');

}


// =====================================================
// RECUPERAR LA EMPRESA Y SU ACCESO CREADOS
// =====================================================

$stmtDatos = $pdo->prepare("
    SELECT
        id,
        codigo_empresa,
        nombre,
        cif,
        direccion,
        telefono,
        email,
        estado,
        motivo_inactivo
    FROM empresas
    WHERE id = ?
    LIMIT 1
");

$stmtDatos->execute([$idEmpresa]);

$empresa = $stmtDatos->fetch(PDO::FETCH_ASSOC);

$stmtDatosUsuario = $pdo->prepare("
    SELECT
        id,
        username,
        nombre,
        apellidos,
        email,
        telefono
    FROM usuarios
    WHERE id = ?
    LIMIT 1
");

$stmtDatosUsuario->execute([$idUsuario]);

$usuarioCreado = $stmtDatosUsuario->fetch(PDO::FETCH_ASSOC);

// Usuario de acceso (login): codigo_empresa-id-username, el
// mismo formato que se escribe en login.php.
$usuarioAcceso = loginAcceso($empresa['codigo_empresa'], (int) $usuarioCreado['id'], $usuarioCreado['username']);

// Datos del login para la tarjeta (ver tarjeta_empresa.php).
$datosLogin = [
    ['Username', $usuarioAcceso],
    ['Contraseña', 'Pendiente de cambio'],
];

$avisoLogin = 'La contraseña inicial se ha asignado automáticamente. La empresa deberá cambiarla en su primer acceso.';

registrarLog(
    LOG_EXITO,
    'Empresa creada',
    'Se ha creado la empresa "' . $empresa['nombre'] . '" con su acceso "' . $usuarioCreado['username'] . '".'
);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Empresa creada - Comparador Eléctrico</title>

    <link rel="stylesheet" href="../../css/style.css">

</head>

<body>


    <?php include '../../templates/header.php'; ?>


    <div class="app-container">


        <?php include '../../templates/sidebar.php'; ?>


        <main class="main-content">


            <div class="page-header">

                <div>

                    <h1>Empresas</h1>

                    <p>
                        Empresa creada correctamente
                    </p>

                </div>

                <div class="page-header-actions">

                    <a href="crear_empresa.php" class="config-save-button">
                        + Añadir empresa
                    </a>

                    <div class="page-date">
                        11 septiembre 2026
                    </div>

                </div>

            </div>


            <div class="config-card confirmation-card">

                <div class="form-info registration-success">

                    <p>

                        La empresa

                        <strong>
                            <?= htmlspecialchars($empresa['nombre']) ?>
                        </strong> se ha creado correctamente.

                    </p>

                </div>


                <?php include 'tarjeta_empresa.php'; ?>


                <div class="form-actions">

                    <a href="editar_empresa.php?id=<?= (int) $empresa['id'] ?>" class="config-save-button">
                        Editar
                    </a>

                    <a href="empresas.php" class="config-cancel-button">
                        Volver a empresas
                    </a>

                </div>

            </div>


        </main>


    </div>


    <?php include '../../templates/footer.php'; ?>


</body>

</html>
