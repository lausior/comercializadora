<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('empresas');

require_once '../../config/database.php';
require_once '../../includes/logs.php';
require_once '../../includes/empresas.php';


// =====================================================
// RESPUESTA DE ERROR (HTML O JSON SEGÚN LA PETICIÓN)
// =====================================================
//
// Si la petición viene por AJAX (ajax=1, ver empresas.js), un
// error a mitad de proceso no puede devolver HTML: el fetch()
// del listado espera JSON y su .json() rompería con
// "unexpected token '<'" al recibirlo (por ejemplo, al hacer
// doble clic y reenviar el borrado de la misma empresa).
//
// =====================================================

function responderErrorEliminarEmpresa(string $mensaje): void
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
            <a href="empresas.php">
                Volver a empresas
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

    responderErrorEliminarEmpresa('La empresa seleccionada no es válida.');

}


// =====================================================
// BUSCAR LA EMPRESA
// =====================================================

$stmtEmpresa = $pdo->prepare("
    SELECT
        id,
        codigo_empresa,
        nombre,
        cif,
        direccion,
        telefono,
        email,
        estado,
        motivo_inactivo,
        creado_por
    FROM empresas
    WHERE id = ?
");

$stmtEmpresa->execute([$id]);

$empresa = $stmtEmpresa->fetch(PDO::FETCH_ASSOC);

// Usuario de acceso (rol EMPRESA), para mostrar su login en la
// confirmación. Se elimina junto con la empresa, más abajo.
$usuario = obtenerUsuarioAccesoEmpresa($pdo, $id);


// =====================================================
// COMPROBAR QUE LA EMPRESA EXISTA
// =====================================================

if (!$empresa) {

    responderErrorEliminarEmpresa('La empresa que intentas eliminar no existe.');

}


// =====================================================
// COMPROBAR QUE PUEDE ELIMINAR ESTA EMPRESA
// =====================================================

if (!puedeVerEmpresa(
    $empresa['creado_por'] !== null ? (int) $empresa['creado_por'] : null
)) {

    responderErrorEliminarEmpresa('No tienes permiso para eliminar esta empresa.');

}


// =====================================================
// ELIMINAR EMPRESA (Y SU USUARIO ASOCIADO)
// =====================================================
//
// El usuario de acceso de la empresa no tiene sentido sin
// ella, así que se elimina en la misma transacción.
//
// =====================================================

$pdo->beginTransaction();

try {

    $pdo->prepare("
        DELETE FROM usuarios
        WHERE id_empresa = ?
    ")->execute([$id]);

    $stmtEliminar = $pdo->prepare("
        DELETE FROM empresas
        WHERE id = ?
    ");

    $stmtEliminar->execute([$id]);

    $pdo->commit();

} catch (PDOException $e) {

    $pdo->rollBack();

    responderErrorEliminarEmpresa('No se ha podido eliminar la empresa.');

}


if ($stmtEliminar->rowCount() !== 1) {

    responderErrorEliminarEmpresa('No se ha podido eliminar la empresa.');

}

registrarLog(
    LOG_ADVERTENCIA,
    'Empresa eliminada',
    'Se ha eliminado la empresa "' . $empresa['nombre'] . '"' .
        ($usuario ? ' junto con su usuario asociado "' . $usuario['username'] . '"' : '') .
        '.'
);

// Datos del login para la confirmación (la notificación del
// listado y la página, ver tarjeta_empresa.php). La contraseña
// no se muestra: la cuenta ya no existe.
$datosLogin = $usuario !== null
    ? [['Username', loginAcceso($empresa['codigo_empresa'], (int) $usuario['id'], $usuario['username'])]]
    : null;

$avisoLogin = 'El acceso de la empresa se ha eliminado junto con ella: ya no podrá iniciar sesión con este usuario.';

if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {

    header('Content-Type: application/json; charset=UTF-8');

    // Mismos campos y orden que las tarjetas (camposDatosEmpresa()).
    $seccionesRespuesta = [
        [
            'titulo' => 'Datos de la empresa',
            'campos' => array_map(
                fn(array $campo): array => [$campo['etiqueta'], $campo['valor']],
                camposDatosEmpresa($empresa)
            ),
        ],
    ];

    if ($datosLogin !== null) {

        $seccionesRespuesta[] = [
            'titulo' => 'Datos del login',
            'campos' => $datosLogin,
        ];

    }

    echo json_encode([
        'ok' => true,
        'tipo' => 'Empresa',
        'nombre' => $empresa['nombre'],
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

    <title>Empresa eliminada - Comparador Eléctrico</title>

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

                    <h1>Empresas</h1>

                    <p>
                        Empresa eliminada correctamente
                    </p>

                </div>

                <div class="page-header-actions">

                    <a href="crear_empresa.php" class="config-save-button">
                        + Añadir empresa
                    </a>

                </div>

            </div>


            <!-- =================================================
                 CONFIRMACIÓN
                 =================================================
                 Misma tarjeta que guardar_empresa.php y
                 actualizar_empresa.php (empresa creada /
                 actualizada), para que las tres se vean igual.
            ================================================== -->

            <div class="config-card confirmation-card">


                <div class="form-info registration-success">

                    <p>

                        La empresa

                        <strong>
                            <?= htmlspecialchars($empresa['nombre']) ?>
                        </strong>

                        ha sido eliminada correctamente.

                    </p>

                </div>


                <?php include 'tarjeta_empresa.php'; ?>


                <!-- =================================================
                     BOTONES
                ================================================== -->

                <div class="form-actions">

                    <a href="empresas.php" class="config-cancel-button">
                        Volver a empresas
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
