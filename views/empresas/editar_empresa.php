<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('empresas');

require_once '../../config/database.php';


// =====================================================
// COMPROBAR ID DE LA EMPRESA
// =====================================================

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    header('Location: empresas.php');
    exit;

}

$idEmpresa = (int) $_GET['id'];


// =====================================================
// OBTENER EMPRESA
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
        creado_por
    FROM empresas
    WHERE id = ?
");

$stmtEmpresa->execute([$idEmpresa]);

$empresa = $stmtEmpresa->fetch(PDO::FETCH_ASSOC);


// =====================================================
// COMPROBAR QUE EXISTE
// =====================================================

if (!$empresa) {

    header('Location: empresas.php');
    exit;

}


// =====================================================
// COMPROBAR QUE PUEDE VER/EDITAR ESTA EMPRESA
// =====================================================
//
// Oculto en el listado no es suficiente: sin esto, NG
// podría editar la empresa de SRG tecleando su id en
// la URL directamente.
//
// =====================================================

if (
    !puedeVerEmpresa(
        $empresa['creado_por'] !== null ? (int) $empresa['creado_por'] : null
    )
) {

    header('Location: empresas.php?error=sin_permiso');
    exit;

}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Editar empresa - Comparador Eléctrico</title>

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

                    <h1>Empresas</h1>

                    <p>
                        Editar empresa
                    </p>

                </div>


                <div class="page-header-actions">

                    <a href="empresas.php" class="config-save-button">
                        ← Volver
                    </a>

                </div>

            </div>


            <!-- =================================================
                 FORMULARIO
            ================================================== -->

            <div class="config-card">

                <h2>Datos de la empresa</h2>

                <form action="actualizar_empresa.php" method="POST" novalidate>


                    <!-- =================================================
                         ID DE LA EMPRESA
                    ================================================== -->

                    <input type="hidden" name="id" value="<?= (int) $empresa['id'] ?>">


                    <!-- =========================
                         MENSAJE DE ERROR GENERAL
                    ========================== -->

                    <div class="form-error-general" id="form-error-general" role="alert" style="display: none;"></div>


                    <div class="form-grid">


                        <!-- =========================
                             CÓDIGO DE EMPRESA
                        ========================== -->

                        <div class="form-group">

                            <label for="codigo_empresa">
                                Código de empresa
                            </label>

                            <input type="text" id="codigo_empresa" name="codigo_empresa" maxlength="6"
                                value="<?= htmlspecialchars($empresa['codigo_empresa']) ?>" readonly>

                            <span class="field-error" id="error-codigo_empresa"></span>

                        </div>


                        <!-- =========================
                             NOMBRE
                        ========================== -->

                        <div class="form-group">

                            <label for="nombre">
                                Nombre
                            </label>

                            <input type="text" id="nombre" name="nombre"
                                value="<?= htmlspecialchars($empresa['nombre']) ?>" required>

                            <span class="field-error" id="error-nombre"></span>

                        </div>


                        <!-- =========================
                             CIF
                        ========================== -->

                        <div class="form-group">

                            <label for="cif">
                                CIF
                            </label>

                            <input type="text" id="cif" name="cif" value="<?= htmlspecialchars($empresa['cif']) ?>"
                                required>

                            <span class="field-error" id="error-cif"></span>

                        </div>


                        <!-- =========================
                             DIRECCIÓN
                        ========================== -->

                        <div class="form-group">

                            <label for="direccion">
                                Dirección
                            </label>

                            <input type="text" id="direccion" name="direccion"
                                value="<?= htmlspecialchars($empresa['direccion'] ?? '') ?>">

                            <span class="field-error" id="error-direccion"></span>

                        </div>


                        <!-- =========================
                             TELÉFONO
                        ========================== -->

                        <div class="form-group">

                            <label for="telefono">
                                Teléfono
                            </label>

                            <input type="tel" id="telefono" name="telefono"
                                value="<?= htmlspecialchars($empresa['telefono'] ?? '') ?>">

                            <span class="field-error" id="error-telefono"></span>

                        </div>


                        <!-- =========================
                             EMAIL
                        ========================== -->

                        <div class="form-group">

                            <label for="email">
                                Email
                            </label>

                            <input type="email" id="email" name="email"
                                value="<?= htmlspecialchars($empresa['email'] ?? '') ?>">

                            <span class="field-error" id="error-email"></span>

                        </div>


                    </div>


                    <!-- =================================================
                         BOTONES
                    ================================================== -->

                    <div class="form-actions">

                        <button type="submit" class="config-save-button">
                            Guardar cambios
                        </button>

                        <a href="empresas.php" class="config-cancel-button">
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


    <script src="../../js/empresas.js"></script>

</body>

</html>