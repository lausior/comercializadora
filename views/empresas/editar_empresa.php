==> editar_empresa.php <==
<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('empresas');

require_once '../../config/database.php';
require_once '../../includes/form_flash.php';
require_once '../../includes/empresas.php';


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
        motivo_inactivo,
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


// =====================================================
// USUARIO DE ACCESO DE LA EMPRESA (USERNAME Y
// "RESTABLECER CONTRASEÑA")
// =====================================================
//
// Es el usuario con rol EMPRESA que se crea junto con la
// empresa (ver guardar_empresa.php). Si no lo tuviera, no se
// muestran ni el campo Username ni el botón.
//
// =====================================================

$usuarioAcceso = obtenerUsuarioAccesoEmpresa($pdo, $idEmpresa);

$tieneUsuarioAcceso = $usuarioAcceso !== null;

// Para formulario_empresa.php: valores iniciales desde
// $empresa, email opcional y textos de edición.
$esEdicion = true;


// =====================================================
// ERROR PENDIENTE (SI VENIMOS DE actualizar_empresa.php)
// =====================================================

$errorFormulario = obtenerErrorFormulario();
$datosPrevios = $errorFormulario['datos'] ?? [];

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


                    <?php include 'formulario_empresa.php'; ?>


                    <!-- =================================================
                         BOTONES
                         =================================================
                         Mismo reparto que en editar_usuario.php:
                         "Restablecer contraseña" a la izquierda y
                         Guardar/Cancelar a la derecha.
                    ================================================== -->

                    <div class="form-actions <?= $tieneUsuarioAcceso ? 'form-actions-split' : '' ?>">

                        <?php if ($tieneUsuarioAcceso): ?>

                            <button type="button" class="config-cancel-button" onclick="abrirModalResetPassword()">
                                Restablecer contraseña
                            </button>

                        <?php endif; ?>

                        <div class="form-actions-right">

                            <button type="submit" class="config-save-button">
                                Guardar cambios
                            </button>

                            <a href="empresas.php" class="config-cancel-button">
                                Cancelar
                            </a>

                        </div>

                    </div>


                </form>

            </div>


        </main>


    </div>


    <!-- =================================================
         FOOTER
    ================================================== -->

    <?php include '../../templates/footer.php'; ?>


    <?php if ($tieneUsuarioAcceso): ?>

        <!-- =====================================================
             MODAL CONFIRMAR RESTABLECER CONTRASEÑA
             =====================================================
             Mismo modal que en editar_usuario.php, pero confirma
             con un formulario POST a resetear_password_empresa.php.
        ====================================================== -->

        <div id="modalResetPassword" class="modal-overlay" style="display: none;">

            <div class="modal-confirmacion">

                <div class="modal-icon">
                    🔑
                </div>

                <h2>Restablecer contraseña</h2>

                <p>
                    ¿Seguro que quieres restablecer la contraseña de acceso de
                    <strong><?= htmlspecialchars($empresa['nombre'], ENT_QUOTES, 'UTF-8') ?></strong>
                    a la contraseña inicial?
                </p>

                <p class="modal-warning">
                    La empresa deberá cambiarla en su próximo acceso.
                </p>

                <form action="resetear_password_empresa.php" method="POST">

                    <input type="hidden" name="id" value="<?= (int) $empresa['id'] ?>">

                    <div class="modal-actions">

                        <button type="button" class="modal-button modal-button-cancel"
                            onclick="cerrarModalResetPassword()">
                            Cancelar
                        </button>

                        <button type="submit" class="modal-button modal-button-primary">
                            Restablecer contraseña
                        </button>

                    </div>

                </form>

            </div>

        </div>

    <?php endif; ?>


    <script src="../../js/empresas.js"></script>

    <?php if ($tieneUsuarioAcceso): ?>

        <script>

            const modalResetPassword = document.getElementById('modalResetPassword');

            window.abrirModalResetPassword = function () {

                modalResetPassword.style.display = 'flex';
                document.body.classList.add('modal-abierto');

            };

            window.cerrarModalResetPassword = function () {

                modalResetPassword.style.display = 'none';
                document.body.classList.remove('modal-abierto');

            };

            modalResetPassword.addEventListener('click', event => {

                if (event.target === modalResetPassword) {
                    window.cerrarModalResetPassword();
                }

            });

            document.addEventListener('keydown', event => {

                if (event.key === 'Escape' && modalResetPassword.style.display !== 'none') {
                    window.cerrarModalResetPassword();
                }

            });

        </script>

    <?php endif; ?>

</body>

</html>