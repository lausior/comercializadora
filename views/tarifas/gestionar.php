<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('tarifas');

require_once '../../config/database.php';
require_once '../../includes/empresas.php';


// =====================================================
// COMPROBAR COMERCIALIZADORA Y SERVICIO RECIBIDOS
// =====================================================

$idComercializadora = isset($_GET['id_comercializadora']) ? (int) $_GET['id_comercializadora'] : 0;
$tipoSuministro = $_GET['tipo_suministro'] ?? '';

if ($idComercializadora <= 0 || !in_array($tipoSuministro, ['luz', 'gas'], true)) {

    header('Location: tarifas.php');
    exit;

}

$stmtComercializadora = $pdo->prepare("
    SELECT id, nombre, suministra_luz, suministra_gas, creado_por
    FROM comercializadoras
    WHERE id = ?
");

$stmtComercializadora->execute([$idComercializadora]);

$comercializadora = $stmtComercializadora->fetch(PDO::FETCH_ASSOC);

if (!$comercializadora) {

    header('Location: tarifas.php');
    exit;

}

if (
    !puedeVerComercializadora(
        $comercializadora['creado_por'] !== null ? (int) $comercializadora['creado_por'] : null
    )
) {

    header('Location: tarifas.php?error=sin_permiso');
    exit;

}

// La comercializadora elegida debe suministrar de verdad el
// servicio pedido: si no, aquí se corta igual que si se
// llega sin pasar por el formulario (por ejemplo,
// manipulando la URL a mano).

$campoServicio = $tipoSuministro === 'luz' ? 'suministra_luz' : 'suministra_gas';

if (!$comercializadora[$campoServicio]) {

    header('Location: tarifas.php');
    exit;

}

$etiquetaServicio = $tipoSuministro === 'luz' ? 'Luz' : 'Gas';

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tarifas - Comparador Eléctrico</title>

    <link rel="stylesheet" href="../../css/style.css">

</head>

<body>

    <?php include '../../templates/header.php'; ?>

    <div class="app-container">

        <?php include '../../templates/sidebar.php'; ?>

        <main class="main-content">

            <div class="page-header">

                <div>

                    <h1>Tarifas</h1>

                    <p>
                        <?= htmlspecialchars($comercializadora['nombre'], ENT_QUOTES, 'UTF-8') ?>
                        — <?= htmlspecialchars($etiquetaServicio, ENT_QUOTES, 'UTF-8') ?>
                    </p>

                </div>

                <div class="page-header-actions">

                    <a href="tarifas.php" class="config-save-button">
                        ← Cambiar selección
                    </a>

                </div>

            </div>

            <div class="config-card">

                <div class="form-info">
                    <p>
                        Aquí irá la gestión de las tarifas de
                        <strong><?= htmlspecialchars($comercializadora['nombre'], ENT_QUOTES, 'UTF-8') ?></strong>
                        para <?= htmlspecialchars(mb_strtolower($etiquetaServicio, 'UTF-8'), ENT_QUOTES, 'UTF-8') ?>
                        (2.0TD/3.0TD/6.1TD, franjas de energía y potencia, IVA, impuesto eléctrico...).
                        Todavía está por construir.
                    </p>
                </div>

            </div>

        </main>

    </div>

    <?php include '../../templates/footer.php'; ?>

</body>

</html>
