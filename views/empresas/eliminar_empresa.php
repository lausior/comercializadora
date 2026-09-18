<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('empresas');

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
            La empresa seleccionada no es válida.
        </p>

        <p>
            <a href="empresas.php">
                Volver a empresas
            </a>
        </p>
    ');

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
        creado_por
    FROM empresas
    WHERE id = ?
");

$stmtEmpresa->execute([$id]);

$empresa = $stmtEmpresa->fetch(PDO::FETCH_ASSOC);


// =====================================================
// COMPROBAR QUE LA EMPRESA EXISTA
// =====================================================

if (!$empresa) {

    die('
        <h2>Error</h2>

        <p>
            La empresa que intentas eliminar no existe.
        </p>

        <p>
            <a href="empresas.php">
                Volver a empresas
            </a>
        </p>
    ');

}


// =====================================================
// COMPROBAR QUE PUEDE ELIMINAR ESTA EMPRESA
// =====================================================

if (!puedeVerEmpresa(
    $empresa['creado_por'] !== null ? (int) $empresa['creado_por'] : null
)) {

    die('
        <h2>Error</h2>

        <p>
            No tienes permiso para eliminar esta empresa.
        </p>

        <p>
            <a href="empresas.php">
                Volver a empresas
            </a>
        </p>
    ');

}


// =====================================================
// ELIMINAR EMPRESA
// =====================================================

try {

    $stmtEliminar = $pdo->prepare("
        DELETE FROM empresas
        WHERE id = ?
    ");

    $stmtEliminar->execute([$id]);


} catch (PDOException $e) {

    die('
        <h2>Error</h2>

        <p>
            No se ha podido eliminar la empresa porque tiene
            usuarios asociados. Reasigna o elimina antes esos
            usuarios.
        </p>

        <p>
            <a href="empresas.php">
                Volver a empresas
            </a>
        </p>
    ');

}


if ($stmtEliminar->rowCount() !== 1) {

    die('
        <h2>Error</h2>

        <p>
            No se ha podido eliminar la empresa.
        </p>

        <p>
            <a href="empresas.php">
                Volver a empresas
            </a>
        </p>
    ');

}

registrarLog(
    LOG_ADVERTENCIA,
    'Empresa eliminada',
    'Se ha eliminado la empresa "' . $empresa['nombre'] . '".'
);

if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {

    header('Content-Type: application/json; charset=UTF-8');

    echo json_encode([
        'ok' => true,
        'tipo' => 'Empresa',
        'nombre' => $empresa['nombre'],
        'campos' => [
            'ID' => $empresa['id'],
            'Código' => $empresa['codigo_empresa'],
            'CIF' => $empresa['cif'],
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

                    <p>
                        Empresa eliminada correctamente
                    </p>

                </div>


                <div class="page-header-actions">

                    <div class="page-date">
                        11 septiembre 2026
                    </div>

                </div>

            </div>


            <!-- =================================================
                 CONFIRMACIÓN
            ================================================== -->

            <div class="config-card">


                <h2>
                    Empresa eliminada
                </h2>


                <div class="form-info">


                    <p>

                        La empresa

                        <strong>
                            <?= htmlspecialchars($empresa['nombre']) ?>
                        </strong>

                        ha sido eliminada correctamente.

                    </p>


                    <p>

                        ID de empresa:

                        <strong>
                            <?= htmlspecialchars($empresa['id']) ?>
                        </strong>

                    </p>


                    <p>

                        Código de empresa:

                        <strong>
                            <?= htmlspecialchars($empresa['codigo_empresa']) ?>
                        </strong>

                    </p>


                    <p>

                        CIF:

                        <strong>
                            <?= htmlspecialchars($empresa['cif']) ?>
                        </strong>

                    </p>


                </div>


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


</body>

</html>
