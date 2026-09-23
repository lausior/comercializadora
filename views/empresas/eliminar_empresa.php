<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('empresas');

require_once '../../config/database.php';
require_once '../../includes/logs.php';


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
        r.nombre AS rol
    FROM usuarios u

    INNER JOIN roles r
        ON u.id_rol = r.id

    WHERE u.id_empresa = ?
    ORDER BY u.id
    LIMIT 1
");

$stmtUsuario->execute([$id]);

$usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC) ?: null;


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

if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {

    header('Content-Type: application/json; charset=UTF-8');

    $seccionesRespuesta = [
        [
            'titulo' => 'Datos de la empresa',
            'campos' => [
                ['ID de empresa', $empresa['id']],
                ['Código de empresa', $empresa['codigo_empresa']],
                ['Nombre', $empresa['nombre']],
                ['CIF', $empresa['cif']],
                ['Dirección', $empresa['direccion'] ?? 'No indicada'],
                ['Teléfono', $empresa['telefono'] ?? 'No indicado'],
                ['Email', $empresa['email'] ?? 'No indicado'],
                [null, null],
                ['Estado', $empresa['estado']],
                ...($empresa['estado'] === 'Inactivo'
                    ? [['Motivo', $empresa['motivo_inactivo'] ?: '-']]
                    : []),
            ],
        ],
    ];

    if ($usuario) {

        $usuarioAcceso =
            $empresa['codigo_empresa'] . '-' .
            $usuario['id'] . '-' .
            $usuario['username'];

        $seccionesRespuesta[] = [
            'titulo' => 'Datos del login',
            'campos' => [
                ['Username', $usuarioAcceso],
            ],
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

                    <h1>
                        Empresas
                    </h1>


                </div>


                <div class="page-header-actions">

                   

                </div>

            </div>


            <!-- =================================================
                 CONFIRMACIÓN
            ================================================== -->

            <div class="config-card deletion-confirmation-card" id="deletion-confirmation-card">


                <h2 data-drag-handle>
                    Empresa eliminada
                </h2>


                <div class="form-info registration-success">

                    <p>

                        La empresa

                        <strong>
                            <?= htmlspecialchars($empresa['nombre']) ?>
                        </strong>

                        ha sido eliminada correctamente.

                    </p>

                </div>


                <p class="confirmation-section-label">Datos de la empresa</p>

                <div class="usuario-detalle">

                    <div class="usuario-detalle-grid">

                        <div class="usuario-detalle-item">
                            <span>ID de empresa</span>
                            <strong><?= htmlspecialchars($empresa['id']) ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Código de empresa</span>
                            <strong><?= htmlspecialchars($empresa['codigo_empresa']) ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Nombre</span>
                            <strong><?= htmlspecialchars($empresa['nombre']) ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>CIF</span>
                            <strong><?= htmlspecialchars($empresa['cif']) ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Dirección</span>
                            <strong><?= htmlspecialchars($empresa['direccion'] ?? 'No indicada') ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Teléfono</span>
                            <strong><?= htmlspecialchars($empresa['telefono'] ?? 'No indicado') ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Email</span>
                            <strong><?= htmlspecialchars($empresa['email'] ?? 'No indicado') ?></strong>
                        </div>

                        <div class="usuario-detalle-item"></div>

                        <div class="usuario-detalle-item empresa-estado-item">
                            <span>Estado</span>
                            <strong class="<?= $empresa['estado'] === 'Activo' ? 'text-success' : 'text-danger' ?>">
                                <?= htmlspecialchars($empresa['estado']) ?>
                            </strong>
                        </div>

                        <?php if ($empresa['estado'] === 'Inactivo'): ?>

                            <div class="usuario-detalle-item empresa-motivo-item">
                                <span>Motivo</span>
                                <strong><?= htmlspecialchars($empresa['motivo_inactivo'] ?: '-') ?></strong>
                            </div>

                        <?php endif; ?>

                    </div>

                </div>


                <?php if ($usuario): ?>

                    <?php

                    $usuarioAcceso =
                        $empresa['codigo_empresa'] . '-' .
                        $usuario['id'] . '-' .
                        $usuario['username'];

                    ?>

                    <p class="confirmation-section-label">Datos del login</p>

                    <div class="usuario-detalle">

                        <div class="usuario-detalle-grid">

                            <div class="usuario-detalle-item">
                                <span>Username</span>
                                <strong><?= htmlspecialchars($usuarioAcceso) ?></strong>
                            </div>

                        </div>

                    </div>

                <?php endif; ?>


                <!-- =================================================
                     BOTONES
                ================================================== -->

                <div class="form-actions">


                    <a href="crear_empresa.php" class="config-save-button">
                        + Añadir empresa
                    </a>


                    <a href="empresas.php" class="config-cancel-button">
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


<script>
    const confirmationCard = document.getElementById('deletion-confirmation-card');
    const dragHandle = confirmationCard?.querySelector('[data-drag-handle]');

    if (confirmationCard && dragHandle) {
        let isDragging = false;
        let offsetX = 0;
        let offsetY = 0;

        dragHandle.addEventListener('pointerdown', (event) => {
            const cardRect = confirmationCard.getBoundingClientRect();

            isDragging = true;
            offsetX = event.clientX - cardRect.left;
            offsetY = event.clientY - cardRect.top;

            confirmationCard.style.position = 'fixed';
            confirmationCard.style.margin = '0';
            confirmationCard.style.left = `${cardRect.left}px`;
            confirmationCard.style.top = `${cardRect.top}px`;
            confirmationCard.classList.add('is-dragging');
            dragHandle.setPointerCapture(event.pointerId);
        });

        dragHandle.addEventListener('pointermove', (event) => {
            if (!isDragging) {
                return;
            }

            const maxLeft = window.innerWidth - confirmationCard.offsetWidth;
            const maxTop = window.innerHeight - confirmationCard.offsetHeight;

            confirmationCard.style.left = `${Math.max(0, Math.min(event.clientX - offsetX, maxLeft))}px`;
            confirmationCard.style.top = `${Math.max(0, Math.min(event.clientY - offsetY, maxTop))}px`;
        });

        dragHandle.addEventListener('pointerup', () => {
            isDragging = false;
            confirmationCard.classList.remove('is-dragging');
        });

        dragHandle.addEventListener('pointercancel', () => {
            isDragging = false;
            confirmationCard.classList.remove('is-dragging');
        });
    }
</script>

</body>

</html>
