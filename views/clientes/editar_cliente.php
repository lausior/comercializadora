<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('clientes');

require_once '../../config/database.php';


// =====================================================
// COMPROBAR ID DEL CLIENTE
// =====================================================

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    header('Location: clientes.php');
    exit;

}

$idCliente = (int) $_GET['id'];


// =====================================================
// OBTENER CLIENTE
// =====================================================

$stmtCliente = $pdo->prepare("
    SELECT
        id,
        nombre,
        tipo,
        identificacion,
        correo,
        comercializadora,
        tarifa,
        estado,
        creado_por
    FROM clientes
    WHERE id = ?
");

$stmtCliente->execute([$idCliente]);

$cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);


// =====================================================
// COMPROBAR QUE EXISTE
// =====================================================

if (!$cliente) {

    header('Location: clientes.php');
    exit;

}


// =====================================================
// COMPROBAR QUE PUEDE VER/EDITAR ESTE CLIENTE
// =====================================================
//
// Oculto en el listado no es suficiente: sin esto,
// alguien podría editar un cliente ajeno tecleando su
// id en la URL directamente.
//
// =====================================================

if (
    !puedeVerCliente(
        $cliente['creado_por'] !== null ? (int) $cliente['creado_por'] : null
    )
) {

    header('Location: clientes.php?error=sin_permiso');
    exit;

}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Editar cliente - Comparador Eléctrico</title>

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
             CONTENIDO PRINCIPAL
        ================================================== -->

        <main class="main-content">


            <!-- =================================================
                 CABECERA
            ================================================== -->

            <div class="page-header">

                <div>

                    <h1>Clientes</h1>

                    <p>
                        Editar cliente
                    </p>

                </div>


                <div class="page-header-actions">

                    <div class="page-date">
                        15 septiembre 2026
                    </div>

                    <a href="clientes.php" class="config-save-button">
                        ← Volver
                    </a>

                </div>

            </div>


            <!-- =================================================
                 FORMULARIO
            ================================================== -->

            <div class="config-card">

                <h2>Datos del cliente</h2>

                <form action="actualizar_cliente.php" method="POST" novalidate>


                    <!-- =================================================
                         ID DEL CLIENTE
                    ================================================== -->

                    <input type="hidden" name="id" value="<?= (int) $cliente['id'] ?>">


                    <!-- =========================
                         MENSAJE DE ERROR GENERAL
                    ========================== -->

                    <div class="form-error-general" id="form-error-general" role="alert" style="display: none;"></div>


                    <!-- =========================
                         NOMBRE
                    ========================== -->

                    <div class="form-group">

                        <label for="nombre">
                            Nombre
                        </label>

                        <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($cliente['nombre']) ?>"
                            required>

                        <span class="field-error" id="error-nombre"></span>

                    </div>


                    <!-- =========================
                         TIPO
                    ========================== -->

                    <div class="form-group">

                        <label for="tipo">
                            Tipo
                        </label>

                        <select id="tipo" name="tipo" required>

                            <?php foreach (['Particular', 'Empresa'] as $tipoOpcion): ?>

                                <option value="<?= $tipoOpcion ?>" <?= $cliente['tipo'] === $tipoOpcion ? 'selected' : '' ?>>
                                    <?= $tipoOpcion ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                        <span class="field-error" id="error-tipo"></span>

                    </div>


                    <!-- =========================
                         IDENTIFICACIÓN
                    ========================== -->

                    <div class="form-group">

                        <label for="identificacion">
                            Identificación (DNI / CIF)
                        </label>

                        <input type="text" id="identificacion" name="identificacion"
                            value="<?= htmlspecialchars($cliente['identificacion']) ?>" required>

                        <span class="field-error" id="error-identificacion"></span>

                    </div>


                    <!-- =========================
                         CORREO
                    ========================== -->

                    <div class="form-group">

                        <label for="correo">
                            Correo
                        </label>

                        <input type="email" id="correo" name="correo"
                            value="<?= htmlspecialchars($cliente['correo']) ?>" required>

                        <span class="field-error" id="error-correo"></span>

                    </div>


                    <!-- =========================
                         COMERCIALIZADORA
                    ========================== -->

                    <div class="form-group">

                        <label for="comercializadora">
                            Comercializadora
                        </label>

                        <select id="comercializadora" name="comercializadora" required>

                            <?php foreach (['Endesa', 'Iberdrola', 'Naturgy', 'Repsol', 'TotalEnergies'] as $comercializadoraOpcion): ?>

                                <option value="<?= $comercializadoraOpcion ?>"
                                    <?= $cliente['comercializadora'] === $comercializadoraOpcion ? 'selected' : '' ?>>
                                    <?= $comercializadoraOpcion ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                        <span class="field-error" id="error-comercializadora"></span>

                    </div>


                    <!-- =========================
                         TARIFA
                    ========================== -->

                    <div class="form-group">

                        <label for="tarifa">
                            Tarifa
                        </label>

                        <select id="tarifa" name="tarifa" required>

                            <?php foreach (['PVPC', 'Mercado libre', 'Tarifa fija'] as $tarifaOpcion): ?>

                                <option value="<?= $tarifaOpcion ?>" <?= $cliente['tarifa'] === $tarifaOpcion ? 'selected' : '' ?>>
                                    <?= $tarifaOpcion ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                        <span class="field-error" id="error-tarifa"></span>

                    </div>


                    <!-- =========================
                         ESTADO
                    ========================== -->

                    <div class="form-group">

                        <label for="estado">
                            Estado
                        </label>

                        <select id="estado" name="estado" required>

                            <?php foreach (['Activo', 'Pendiente', 'Inactivo'] as $estadoOpcion): ?>

                                <option value="<?= $estadoOpcion ?>" <?= $cliente['estado'] === $estadoOpcion ? 'selected' : '' ?>>
                                    <?= $estadoOpcion ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                        <span class="field-error" id="error-estado"></span>

                    </div>


                    <!-- =================================================
                         BOTONES
                    ================================================== -->

                    <div class="form-actions">

                        <button type="submit" class="config-save-button">
                            Guardar cambios
                        </button>

                        <a href="clientes.php" class="config-cancel-button">
                            Cancelar
                        </a>



                    </div>


                </form>

            </div>


        </main>


    </div>


    <!-- =================================================
         FOOTER
    ================================================== -->

    <?php include '../../templates/footer.php'; ?>


</body>

</html>