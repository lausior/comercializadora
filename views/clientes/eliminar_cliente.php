<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('usuarios');

require_once '../../config/database.php';


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
        u.email,
        u.telefono,
        u.id_empresa,
        u.creado_por,
        e.nombre AS empresa,
        r.nombre AS rol
    FROM usuarios u

    INNER JOIN empresas e
        ON u.id_empresa = e.id

    INNER JOIN roles r
        ON u.id_rol = r.id

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
            El usuario que intentas eliminar no existe.
        </p>

        <p>
            <a href="usuarios.php">
                Volver a usuarios
            </a>
        </p>
    ');

}


// =====================================================
// COMPROBAR QUE PUEDE ELIMINAR ESTE USUARIO
// =====================================================

if (!puedeVerUsuario($usuario['creado_por'] !== null ? (int) $usuario['creado_por'] : null)) {

    die('
        <h2>Error</h2>

        <p>
            No tienes permiso para eliminar este usuario.
        </p>

        <p>
            <a href="usuarios.php">
                Volver a usuarios
            </a>
        </p>
    ');

}


// =====================================================
// GUARDAR DATOS PARA MOSTRAR DESPUÉS
// =====================================================

$nombreCompleto =
    $usuario['nombre'] . ' ' . $usuario['apellidos'];


// =====================================================
// ELIMINAR USUARIO
// =====================================================

try {

    $stmtEliminar = $pdo->prepare("
        DELETE FROM usuarios
        WHERE id = ?
    ");

    $stmtEliminar->execute([$id]);


} catch (PDOException $e) {

    die('
        <h2>Error</h2>

        <p>
            No se ha podido eliminar el usuario.
        </p>

        <p>
            <a href="usuarios.php">
                Volver a usuarios
            </a>
        </p>
    ');

}


// =====================================================
// COMPROBAR QUE REALMENTE SE HA ELIMINADO
// =====================================================

if ($stmtEliminar->rowCount() !== 1) {

    die('
        <h2>Error</h2>

        <p>
            No se ha podido eliminar el usuario.
        </p>

        <p>
            <a href="usuarios.php">
                Volver a usuarios
            </a>
        </p>
    ');

}

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Usuario eliminado - Comparador Eléctrico</title>

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
                        Usuarios
                    </h1>

                    <p>
                        Usuario eliminado correctamente
                    </p>

                </div>


                <div class="page-header-actions">

                    <div class="page-date">
                        9 septiembre 2026
                    </div>

                </div>

            </div>


            <!-- =================================================
                 CONFIRMACIÓN
            ================================================== -->

            <div class="config-card">


                <h2>
                    Usuario eliminado
                </h2>


                <div class="form-info">


                    <p>

                        El usuario

                        <strong>
                            <?= htmlspecialchars($nombreCompleto) ?>
                        </strong>

                        ha sido eliminado correctamente.

                    </p>


                    <p>

                        ID de usuario:

                        <strong>
                            <?= htmlspecialchars($usuario['id']) ?>
                        </strong>

                    </p>


                    <p>

                        Username:

                        <strong>
                            @<?= htmlspecialchars($usuario['username']) ?>
                        </strong>

                    </p>


                    <p>

                        Email:

                        <strong>
                            <?= htmlspecialchars($usuario['email']) ?>
                        </strong>

                    </p>


                    <p>

                        Empresa:

                        <strong>
                            <?= htmlspecialchars($usuario['empresa']) ?>
                        </strong>

                    </p>


                    <p>

                        Rol:

                        <strong>
                            <?= htmlspecialchars($usuario['rol']) ?>
                        </strong>

                    </p>


                </div>


                <!-- =================================================
                     BOTONES
                ================================================== -->

                <div class="form-actions">


                    <a href="crear_usuario.php" class="config-save-button">
                        + Añadir usuario
                    </a>


                    <a href="usuarios.php" class="config-cancel-button">
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


</body>

</html>