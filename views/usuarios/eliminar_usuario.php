<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('usuarios');

require_once '../../config/database.php';
require_once '../../includes/logs.php';
require_once '../../includes/empresas.php';


// =====================================================
// RESPUESTA DE ERROR (HTML O JSON SEGÚN LA PETICIÓN)
// =====================================================
//
// Si la petición viene por AJAX (ajax=1, ver usuarios.js),
// un error a mitad de proceso no puede devolver HTML: el
// fetch() del listado espera JSON y su .json() rompería con
// "unexpected token '<'" al recibirlo, como pasó al borrar
// dos veces seguidas el mismo usuario (doble clic).
//
// =====================================================

function responderErrorEliminarUsuario(string $mensaje): void
{
    if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {

        header('Content-Type: application/json; charset=UTF-8');

        echo json_encode([
            'ok' => false,
            'error' => $mensaje,
        ], JSON_UNESCAPED_UNICODE);

        exit;

    }

    die('
        <h2>Error</h2>

        <p>' . htmlspecialchars($mensaje) . '</p>

        <p>
            <a href="usuarios.php">
                Volver a usuarios
            </a>
        </p>
    ');
}


// =====================================================
// COMPROBAR QUE SE HA RECIBIDO UN ID
// =====================================================

$id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;


if ($id <= 0) {

    responderErrorEliminarUsuario('El usuario seleccionado no es válido.');

}


// =====================================================
// BUSCAR EL USUARIO
// =====================================================

$stmtUsuario = $pdo->prepare("
    SELECT
        u.id,
        u.username,
        u.nombre,
        u.apellidos,
        u.email,
        u.telefono,
        u.estado,
        u.motivo_inactivo,
        u.id_empresa,
        u.creado_por,
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

$stmtUsuario->execute([$id]);

$usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);


// =====================================================
// COMPROBAR QUE EL USUARIO EXISTA
// =====================================================

if (!$usuario) {

    responderErrorEliminarUsuario('El usuario que intentas eliminar no existe.');

}


// =====================================================
// COMPROBAR QUE PUEDE ELIMINAR ESTE USUARIO
// =====================================================

if (!puedeVerUsuario($usuario)) {

    responderErrorEliminarUsuario('No tienes permiso para eliminar este usuario.');

}


// =====================================================
// GUARDAR DATOS PARA MOSTRAR DESPUÉS
// =====================================================

$nombreCompleto =
    $usuario['nombre'] . ' ' . $usuario['apellidos'];

// Mismo formato que se escribe en login.php.
$usuarioAcceso = loginAcceso($usuario['codigo_empresa'], (int) $usuario['id'], $usuario['username']);


// =====================================================
// ELIMINAR USUARIO (Y SU EMPRESA, SI ES EL ACCESO DE ELLA)
// =====================================================
//
// El rol EMPRESA es el propio acceso de login de la
// empresa (se crea junto a ella en guardar_empresa.php),
// así que borrarlo deja a la empresa sin forma de entrar:
// se elimina también la empresa. Un usuario normal
// (rol USUARIO) que pertenece a una empresa no se lleva
// la empresa por delante.
// =====================================================

$esUsuarioEmpresa = $usuario['rol'] === ROL_EMPRESA;


// =====================================================
// DATOS DE LA EMPRESA (SI SE VA A BORRAR TAMBIÉN)
// =====================================================
//
// Se leen antes de borrar nada: una vez eliminada la
// empresa no habría forma de recuperarlos para mostrarlos
// en la tarjeta de confirmación de abajo.
//
// =====================================================

$empresaEliminada = null;

if ($esUsuarioEmpresa) {

    $stmtEmpresaEliminada = $pdo->prepare("
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
    ");

    $stmtEmpresaEliminada->execute([$usuario['id_empresa']]);

    $empresaEliminada = $stmtEmpresaEliminada->fetch(PDO::FETCH_ASSOC);

}

$pdo->beginTransaction();

try {

    $stmtEliminar = $pdo->prepare("
        DELETE FROM usuarios
        WHERE id = ?
    ");

    $stmtEliminar->execute([$id]);

    if ($esUsuarioEmpresa) {

        // Cualquier otro usuario que quedara en esa empresa
        // (aparte del de acceso, ya borrado arriba) se borra
        // también: sin la empresa no tiene sentido mantenerlos,
        // y de paso evita el error de clave foránea al borrar
        // la empresa más abajo.
        $pdo->prepare("
            DELETE FROM usuarios
            WHERE id_empresa = ?
        ")->execute([$usuario['id_empresa']]);

        $pdo->prepare("
            DELETE FROM empresas
            WHERE id = ?
        ")->execute([$usuario['id_empresa']]);

    }

    $pdo->commit();

} catch (PDOException $e) {

    $pdo->rollBack();

    responderErrorEliminarUsuario('No se ha podido eliminar el usuario.');

}


// =====================================================
// COMPROBAR QUE REALMENTE SE HA ELIMINADO
// =====================================================

if ($stmtEliminar->rowCount() !== 1) {

    responderErrorEliminarUsuario('No se ha podido eliminar el usuario.');

}

registrarLog(
    LOG_ADVERTENCIA,
    'Usuario eliminado',
    'Se ha eliminado el usuario "' . $usuario['username'] . '"' .
        ($esUsuarioEmpresa ? ' junto con la empresa asociada "' . $usuario['empresa'] . '"' : '') .
        '.'
);

if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {

    header('Content-Type: application/json; charset=UTF-8');

    $seccionesRespuesta = [
        [
            'titulo' => 'Datos del login',
            'campos' => $esUsuarioEmpresa
                ? [
                    ['Username', $usuarioAcceso],
                ]
                : [
                    ['ID de usuario', $usuario['id']],
                    ['Email', $usuario['email']],
                    ['Username', $usuarioAcceso],
                    ['Empresa', $usuario['empresa']],
                    ['Rol', $usuario['rol']],
                    [null, null],
                    ['Estado', $usuario['estado']],
                    ...($usuario['estado'] === 'Inactivo'
                        ? [['Motivo', etiquetaMotivoInactivo($usuario['motivo_inactivo'])]]
                        : []),
                ],
        ],
    ];

    if ($esUsuarioEmpresa && $empresaEliminada) {

        $seccionesRespuesta[] = [
            'titulo' => 'Datos de la empresa',
            'campos' => [
                ['ID de empresa', $empresaEliminada['id']],
                ['Código de empresa', $empresaEliminada['codigo_empresa']],
                ['Nombre', $empresaEliminada['nombre']],
                ['CIF', $empresaEliminada['cif']],
                ['Dirección', $empresaEliminada['direccion'] ?? 'No indicada'],
                ['Teléfono', $empresaEliminada['telefono'] ?? 'No indicado'],
                ['Email', $empresaEliminada['email'] ?? 'No indicado'],
                [null, null],
                ['Estado', $empresaEliminada['estado']],
                ...($empresaEliminada['estado'] === 'Inactivo'
                    ? [['Motivo', etiquetaMotivoInactivo($empresaEliminada['motivo_inactivo'])]]
                    : []),
            ],
        ];

    }

    echo json_encode([
        'ok' => true,
        'tipo' => 'Usuario',
        'nombre' => $nombreCompleto,
        'secciones' => $seccionesRespuesta,
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Usuario eliminado - Comparador Eléctrico</title>

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
             CONTENIDO
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
                        Usuario eliminado correctamente
                    </p>

                </div>


                <div class="page-header-actions">

                    <div class="page-date">
                        9 septiembre 2026
                    </div>

                </div>

            </div>


            <!-- =================================================
                 CONFIRMACIÓN
            ================================================== -->

            <div class="config-card">


                <h2>
                    Usuario eliminado
                </h2>


                <div class="form-info registration-success">

                    <p>

                        El usuario

                        <strong>
                            <?= htmlspecialchars($nombreCompleto) ?>
                        </strong>

                        ha sido eliminado correctamente.

                    </p>

                </div>


                <div class="form-info">


                    <p>

                        ID de usuario:

                        <strong>
                            <?= htmlspecialchars($usuario['id']) ?>
                        </strong>

                    </p>


                    <p>

                        Username:

                        <strong>
                            @<?= htmlspecialchars($usuario['username']) ?>
                        </strong>

                    </p>


                    <p>

                        Email:

                        <strong>
                            <?= htmlspecialchars($usuario['email']) ?>
                        </strong>

                    </p>


                    <p>

                        Empresa:

                        <strong>
                            <?= htmlspecialchars($usuario['empresa']) ?>
                        </strong>

                    </p>

                    <?php if ($esUsuarioEmpresa): ?>

                        <p class="modal-warning">
                            Era el usuario de acceso de la empresa: se ha eliminado también la empresa.
                        </p>

                    <?php endif; ?>


                    <p>

                        Rol:

                        <strong>
                            <?= htmlspecialchars($usuario['rol']) ?>
                        </strong>

                    </p>


                </div>


                <?php if ($esUsuarioEmpresa && $empresaEliminada): ?>

                    <div class="access-section">

                        <div class="form-info registration-success">

                            <p>

                                La empresa

                                <strong>
                                    <?= htmlspecialchars($empresaEliminada['nombre']) ?>
                                </strong>

                                ha sido eliminada correctamente.

                            </p>

                        </div>

                        <div class="usuario-detalle">

                            <div class="usuario-detalle-grid">

                                <div class="usuario-detalle-item">
                                    <span>ID de empresa</span>
                                    <strong><?= htmlspecialchars($empresaEliminada['id']) ?></strong>
                                </div>

                                <div class="usuario-detalle-item">
                                    <span>Código de empresa</span>
                                    <strong><?= htmlspecialchars($empresaEliminada['codigo_empresa']) ?></strong>
                                </div>

                                <div class="usuario-detalle-item">
                                    <span>Nombre</span>
                                    <strong><?= htmlspecialchars($empresaEliminada['nombre']) ?></strong>
                                </div>

                                <div class="usuario-detalle-item">
                                    <span>CIF</span>
                                    <strong><?= htmlspecialchars($empresaEliminada['cif']) ?></strong>
                                </div>

                                <div class="usuario-detalle-item">
                                    <span>Dirección</span>
                                    <strong><?= htmlspecialchars($empresaEliminada['direccion'] ?? 'No indicada') ?></strong>
                                </div>

                                <div class="usuario-detalle-item">
                                    <span>Teléfono</span>
                                    <strong><?= htmlspecialchars($empresaEliminada['telefono'] ?? 'No indicado') ?></strong>
                                </div>

                                <div class="usuario-detalle-item">
                                    <span>Email</span>
                                    <strong><?= htmlspecialchars($empresaEliminada['email'] ?? 'No indicado') ?></strong>
                                </div>

                                <div class="usuario-detalle-item empresa-estado-item">
                                    <span>Estado</span>
                                    <strong class="<?= $empresaEliminada['estado'] === 'Activo' ? 'text-success' : 'text-danger' ?>">
                                        <?= htmlspecialchars($empresaEliminada['estado']) ?>
                                    </strong>
                                </div>

                                <?php if ($empresaEliminada['estado'] === 'Inactivo'): ?>

                                    <div class="usuario-detalle-item empresa-motivo-item">
                                        <span>Motivo</span>
                                        <strong><?= htmlspecialchars(etiquetaMotivoInactivo($empresaEliminada['motivo_inactivo'])) ?></strong>
                                    </div>

                                <?php endif; ?>

                            </div>

                        </div>

                    </div>

                <?php endif; ?>


                <!-- =================================================
                     BOTONES
                ================================================== -->

                <div class="form-actions">


                    <a href="crear_usuario.php" class="config-save-button">
                        + Añadir usuario
                    </a>


                    <a href="usuarios.php" class="config-cancel-button">
                        Volver
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