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
        apellidos,
        direccion,
        telefono,
        email,
        nif,
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


                    <div class="form-grid">


                        <!-- =========================
                             NOMBRE
                        ========================== -->

                        <div class="form-group">

                            <label for="nombre">
                                Nombre
                            </label>

                            <input type="text" id="nombre" name="nombre"
                                value="<?= htmlspecialchars($cliente['nombre']) ?>" required>

                            <span class="field-error" id="error-nombre"></span>

                        </div>


                        <!-- =========================
                             APELLIDOS
                        ========================== -->

                        <div class="form-group">

                            <label for="apellidos">
                                Apellidos
                            </label>

                            <input type="text" id="apellidos" name="apellidos"
                                value="<?= htmlspecialchars($cliente['apellidos']) ?>" required>

                            <span class="field-error" id="error-apellidos"></span>

                        </div>


                        <!-- =========================
                             DNI/NIE
                        ========================== -->

                        <div class="form-group">

                            <label for="nif">
                                DNI/NIE
                            </label>

                            <input type="text" id="nif" name="nif"
                                value="<?= htmlspecialchars($cliente['nif']) ?>" required>

                            <span class="field-error" id="error-nif"></span>

                        </div>


                        <!-- =========================
                             DIRECCIÓN
                        ========================== -->

                        <div class="form-group">

                            <label for="direccion">
                                Dirección
                            </label>

                            <input type="text" id="direccion" name="direccion"
                                value="<?= htmlspecialchars($cliente['direccion'] ?? '') ?>">

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
                                value="<?= htmlspecialchars($cliente['telefono'] ?? '') ?>">

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
                                value="<?= htmlspecialchars($cliente['email']) ?>" required>

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
