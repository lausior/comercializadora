<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('tarifas');

require_once '../../config/database.php';
require_once '../../includes/empresas.php';


// =====================================================
// OBTENER COMERCIALIZADORAS QUE PUEDE VER EL ROL ACTUAL
// =====================================================
//
// Mismo criterio que el listado de Comercializadoras: SRG
// las ve todas, NG y EMPRESA solo las que ellos mismos han
// creado (ver puedeVerComercializadora() en permisos.php).
// Solo tiene sentido elegir aquí una comercializadora que
// suministre al menos un servicio, pero eso ya lo garantiza
// la validación de guardar_comercializadora.php/
// actualizar_comercializadora.php.
//
// =====================================================

$stmtComercializadoras = $pdo->query("
    SELECT id, nombre, suministra_luz, suministra_gas, creado_por
    FROM comercializadoras
    ORDER BY nombre
");

$comercializadoras = array_values(array_filter(
    $stmtComercializadoras->fetchAll(PDO::FETCH_ASSOC),
    fn(array $comercializadora): bool =>
        puedeVerComercializadora(
            $comercializadora['creado_por'] !== null ? (int) $comercializadora['creado_por'] : null
        )
));

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

            <!-- =================================================
                 CABECERA
            ================================================== -->

            <div class="page-header">

                <div>

                    <h1>Tarifas</h1>

                    <p>
                        Elige una comercializadora y el servicio para gestionar sus tarifas
                    </p>

                </div>

            </div>


            <!-- =================================================
                 FORMULARIO DE SELECCIÓN
            ================================================== -->

            <div class="config-card">

                <h2>Selecciona comercializadora y servicio</h2>

                <?php if (empty($comercializadoras)): ?>

                    <div class="form-info">
                        <p>
                            Todavía no hay comercializadoras registradas.
                            <a href="../comercializadoras/crear_comercializadora.php">Añade una</a>
                            para poder gestionar sus tarifas.
                        </p>
                    </div>

                <?php else: ?>

                    <form action="gestionar.php" method="GET" id="formSeleccionTarifa" novalidate>

                        <div class="form-grid">

                            <!-- =========================
                                 COMERCIALIZADORA
                            ========================== -->

                            <div class="form-group">

                                <label for="id_comercializadora">
                                    Comercializadora
                                </label>

                                <select id="id_comercializadora" name="id_comercializadora" required>

                                    <option value="">Selecciona una comercializadora</option>

                                    <?php foreach ($comercializadoras as $comercializadora): ?>

                                        <option value="<?= (int) $comercializadora['id'] ?>"
                                            data-luz="<?= (int) $comercializadora['suministra_luz'] ?>"
                                            data-gas="<?= (int) $comercializadora['suministra_gas'] ?>">
                                            <?= htmlspecialchars($comercializadora['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>


                            <!-- =========================
                                 SERVICIO (LUZ / GAS)
                                 =========================
                                 Las opciones dependen de lo que
                                 suministre la comercializadora
                                 elegida arriba (ver js/tarifas.js).
                            ========================== -->

                            <div class="form-group">

                                <label for="tipo_suministro">
                                    Servicio
                                </label>

                                <select id="tipo_suministro" name="tipo_suministro" required disabled>
                                    <option value="">Selecciona antes una comercializadora</option>
                                </select>

                            </div>

                        </div>

                        <div class="form-actions">

                            <button type="submit" class="config-save-button" id="btnContinuarTarifa" disabled>
                                Continuar
                            </button>

                        </div>

                    </form>

                <?php endif; ?>

            </div>

        </main>

    </div>

    <?php include '../../templates/footer.php'; ?>

    <script src="../../js/tarifas.js"></script>

</body>

</html>
