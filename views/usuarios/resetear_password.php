<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('usuarios');

require_once '../../config/database.php';
require_once '../../includes/logs.php';


// =====================================================
// COMPROBAR QUE SE HA RECIBIDO UN ID
// =====================================================

$id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;


if ($id <= 0) {

    die('
        <h2>Error</h2>

        <p>
            El usuario seleccionado no es válido.
        </p>

        <p>
            <a href="usuarios.php">
                Volver a usuarios
            </a>
        </p>
    ');

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
        u.creado_por,
        e.codigo_empresa
    FROM usuarios u

    INNER JOIN empresas e
        ON u.id_empresa = e.id

    WHERE u.id = ?
");

$stmtUsuario->execute([$id]);

$usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);


// =====================================================
// COMPROBAR QUE EL USUARIO EXISTA
// =====================================================

if (!$usuario) {

    die('
        <h2>Error</h2>

        <p>
            El usuario seleccionado no existe.
        </p>

        <p>
            <a href="usuarios.php">
                Volver a usuarios
            </a>
        </p>
    ');

}


// =====================================================
// COMPROBAR QUE PUEDE GESTIONAR ESTE USUARIO
// =====================================================
//
// Misma comprobación que al editar/eliminar: sin esto,
// alguien podría restablecer la contraseña de un usuario
// ajeno tecleando su id en la URL directamente.
//
// =====================================================

if (!puedeVerUsuario($usuario['creado_por'] !== null ? (int) $usuario['creado_por'] : null)) {

    die('
        <h2>Error</h2>

        <p>
            No tienes permiso para restablecer la contraseña de este usuario.
        </p>

        <p>
            <a href="usuarios.php">
                Volver a usuarios
            </a>
        </p>
    ');

}


// =====================================================
// RESTABLECER LA CONTRASEÑA A LA INICIAL
// =====================================================
//
// Misma contraseña inicial que en guardar_usuario.php.
// cambiar_password vuelve a 1, para que el usuario esté
// obligado a cambiarla en su próximo acceso.
//
// =====================================================

$passwordInicial = '123456';

$passwordHash = password_hash($passwordInicial, PASSWORD_DEFAULT);

$stmtActualizar = $pdo->prepare("
    UPDATE usuarios
    SET
        password = :password,
        cambiar_password = 1
    WHERE id = :id
");

$stmtActualizar->execute([
    ':password' => $passwordHash,
    ':id'       => $id,
]);

registrarLog(
    LOG_ADVERTENCIA,
    'Contraseña restablecida',
    'Se ha restablecido la contraseña del usuario "' . $usuario['username'] . '".'
);


// =====================================================
// DATOS PARA MOSTRAR
// =====================================================

$nombreCompleto = $usuario['nombre'] . ' ' . $usuario['apellidos'];

$usuarioAcceso =
    $usuario['codigo_empresa'] . '-' .
    $usuario['id'] . '-' .
    $usuario['username'];

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Contraseña restablecida - Comparador Eléctrico</title>

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

                    <h1>Usuarios</h1>

                    <p>
                        Contraseña restablecida correctamente
                    </p>

                </div>


                <div class="page-header-actions">

                    <div class="page-date">
                        15 septiembre 2026
                    </div>

                </div>

            </div>


            <!-- =================================================
                 CONFIRMACIÓN
            ================================================== -->

            <div class="config-card">


                <h2>
                    Contraseña restablecida
                </h2>


                <div class="form-info">

                    <p>

                        La contraseña del usuario

                        <strong>
                            <?= htmlspecialchars($nombreCompleto) ?>
                        </strong>

                        se ha restablecido a la contraseña inicial.

                    </p>

                    <p>

                        En su próximo acceso deberá cambiarla
                        obligatoriamente.

                    </p>

                </div>


                <div class="usuario-detalle">

                    <div class="usuario-detalle-grid">

                        <div class="usuario-detalle-item">
                            <span>Usuario de acceso (login)</span>
                            <strong><?= htmlspecialchars($usuarioAcceso) ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Contraseña inicial</span>
                            <strong><?= htmlspecialchars($passwordInicial) ?></strong>
                        </div>

                    </div>

                </div>


                <!-- =================================================
                     BOTONES
                ================================================== -->

                <div class="form-actions">

                    <a href="usuarios.php" class="config-cancel-button">
                        Volver a usuarios
                    </a>

                    <a href="editar_usuario.php?id=<?= (int) $usuario['id'] ?>" class="config-save-button">
                        Volver a editar
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
