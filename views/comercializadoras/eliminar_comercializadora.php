<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('comercializadoras');

require_once '../../config/database.php';
require_once '../../includes/logs.php';


// =====================================================
// RESPUESTA DE ERROR (HTML O JSON SEGÚN LA PETICIÓN)
// =====================================================
//
// Si la petición viene por AJAX (ajax=1, ver
// comercializadoras.js), un error a mitad de proceso no
// puede devolver HTML: el fetch() del listado espera JSON
// y su .json() rompería con "unexpected token '<'" al
// recibirlo (mismo criterio que eliminar_empresa.php).
//
// =====================================================

function responderErrorEliminarComercializadora(string $mensaje): void
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
            <a href="comercializadoras.php">
                Volver a comercializadoras
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

    responderErrorEliminarComercializadora('La comercializadora seleccionada no es válida.');

}


// =====================================================
// BUSCAR LA COMERCIALIZADORA
// =====================================================

$stmtComercializadora = $pdo->prepare("
    SELECT
        id,
        nombre,
        cif,
        direccion,
        telefono,
        email,
        suministra_luz,
        suministra_gas,
        creado_por
    FROM comercializadoras
    WHERE id = ?
");

$stmtComercializadora->execute([$id]);

$comercializadora = $stmtComercializadora->fetch(PDO::FETCH_ASSOC);


// =====================================================
// COMPROBAR QUE LA COMERCIALIZADORA EXISTA
// =====================================================

if (!$comercializadora) {

    responderErrorEliminarComercializadora('La comercializadora que intentas eliminar no existe.');

}


// =====================================================
// COMPROBAR QUE PUEDE ELIMINAR ESTA COMERCIALIZADORA
// =====================================================

if (
    !puedeVerComercializadora(
        $comercializadora['creado_por'] !== null ? (int) $comercializadora['creado_por'] : null
    )
) {

    responderErrorEliminarComercializadora('No tienes permiso para eliminar esta comercializadora.');

}


// =====================================================
// ELIMINAR COMERCIALIZADORA
// =====================================================

$stmtEliminar = $pdo->prepare("
    DELETE FROM comercializadoras
    WHERE id = ?
");

$stmtEliminar->execute([$id]);

if ($stmtEliminar->rowCount() !== 1) {

    responderErrorEliminarComercializadora('No se ha podido eliminar la comercializadora.');

}

registrarLog(
    LOG_ADVERTENCIA,
    'Comercializadora eliminada',
    'Se ha eliminado la comercializadora "' . $comercializadora['nombre'] . '".'
);

$serviciosComercializadora = [];
if ($comercializadora['suministra_luz']) {
    $serviciosComercializadora[] = 'Luz';
}
if ($comercializadora['suministra_gas']) {
    $serviciosComercializadora[] = 'Gas';
}

if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {

    header('Content-Type: application/json; charset=UTF-8');

    echo json_encode([
        'ok' => true,
        'tipo' => 'Comercializadora',
        'nombre' => $comercializadora['nombre'],
        'secciones' => [
            [
                'titulo' => 'Datos de la comercializadora',
                'campos' => [
                    ['ID', $comercializadora['id']],
                    ['Nombre', $comercializadora['nombre']],
                    ['CIF', $comercializadora['cif']],
                    ['Dirección', $comercializadora['direccion'] ?? 'No indicada'],
                    ['Teléfono', $comercializadora['telefono'] ?? 'No indicado'],
                    ['Email', $comercializadora['email'] ?? 'No indicado'],
                    ['Servicios', implode(' y ', $serviciosComercializadora)],
                ],
            ],
        ],
    ], JSON_UNESCAPED_UNICODE);

    exit;

}

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Comercializadora eliminada - Comparador Eléctrico</title>

    <link rel="stylesheet" href="../../css/style.css">

</head>


<body>


    <?php include '../../templates/header.php'; ?>


    <div class="app-container">


        <?php include '../../templates/sidebar.php'; ?>


        <main class="main-content">


            <div class="page-header">

                <div>

                    <h1>
                        Comercializadoras
                    </h1>

                </div>

            </div>


            <div class="config-card deletion-confirmation-card" id="deletion-confirmation-card">

                <h2 data-drag-handle>
                    Comercializadora eliminada
                </h2>

                <div class="form-info registration-success">

                    <p>

                        La comercializadora

                        <strong>
                            <?= htmlspecialchars($comercializadora['nombre']) ?>
                        </strong>

                        ha sido eliminada correctamente.

                    </p>

                </div>


                <p class="confirmation-section-label">Datos de la comercializadora</p>

                <div class="usuario-detalle">

                    <div class="usuario-detalle-grid">

                        <div class="usuario-detalle-item">
                            <span>Nombre</span>
                            <strong><?= htmlspecialchars($comercializadora['nombre']) ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>ID</span>
                            <strong><?= htmlspecialchars($comercializadora['id']) ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>CIF</span>
                            <strong><?= htmlspecialchars($comercializadora['cif']) ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Dirección</span>
                            <strong><?= htmlspecialchars($comercializadora['direccion'] ?? 'No indicada') ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Teléfono</span>
                            <strong><?= htmlspecialchars($comercializadora['telefono'] ?? 'No indicado') ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Email</span>
                            <strong><?= htmlspecialchars($comercializadora['email'] ?? 'No indicado') ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Servicios</span>
                            <strong><?= htmlspecialchars(implode(' y ', $serviciosComercializadora)) ?></strong>
                        </div>

                    </div>

                </div>


                <div class="form-actions">

                    <a href="crear_comercializadora.php" class="config-save-button">
                        + Añadir comercializadora
                    </a>

                    <a href="comercializadoras.php" class="config-cancel-button">
                        Volver
                    </a>

                </div>

            </div>


        </main>


    </div>


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