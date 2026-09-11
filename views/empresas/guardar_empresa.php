<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('empresas');

require_once '../../config/database.php';


// =====================================================
// COMPROBAR QUE EL FORMULARIO SE ENVÍA POR POST
// =====================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: empresas.php');
    exit;

}


// =====================================================
// RECOGER DATOS DEL FORMULARIO
// =====================================================

$nombre        = trim($_POST['nombre'] ?? '');
$cif           = trim($_POST['cif'] ?? '');
$direccion     = trim($_POST['direccion'] ?? '');
$telefono      = trim($_POST['telefono'] ?? '');
$email         = trim($_POST['email'] ?? '');


// =====================================================
// VALIDACIONES
// =====================================================

if (
    $nombre === '' ||
    $cif === ''
) {

    die('
        <h2>Error</h2>

        <p>
            Faltan datos obligatorios.
        </p>

        <p>
            <a href="crear_empresa.php">
                Volver al formulario
            </a>
        </p>
    ');

}


if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {

    die('
        <h2>Error</h2>

        <p>
            El email no es válido.
        </p>

        <p>
            <a href="crear_empresa.php">
                Volver al formulario
            </a>
        </p>
    ');

}


// =====================================================
// COMPROBAR QUE EL CIF NO EXISTE
// =====================================================

$stmt = $pdo->prepare("
    SELECT id
    FROM empresas
    WHERE cif = ?
    LIMIT 1
");

$stmt->execute([$cif]);

if ($stmt->fetch()) {

    die('
        <h2>Error</h2>

        <p>
            Ya existe una empresa con ese CIF.
        </p>

        <p>
            <a href="crear_empresa.php">
                Volver al formulario
            </a>
        </p>
    ');

}


// =====================================================
// GENERAR CÓDIGO DE EMPRESA ALEATORIO
// =====================================================
//
// 6 dígitos, comprobando que no coincida con uno ya
// existente antes de darlo por válido.
//
// =====================================================

do {

    $codigoEmpresa = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    $stmtCodigo = $pdo->prepare("
        SELECT id
        FROM empresas
        WHERE codigo_empresa = ?
        LIMIT 1
    ");

    $stmtCodigo->execute([$codigoEmpresa]);

} while ($stmtCodigo->fetch());


// =====================================================
// INSERTAR EMPRESA
// =====================================================

$stmt = $pdo->prepare("
    INSERT INTO empresas (
        codigo_empresa,
        nombre,
        cif,
        direccion,
        telefono,
        email
    )
    VALUES (
        :codigo_empresa,
        :nombre,
        :cif,
        :direccion,
        :telefono,
        :email
    )
");

$stmt->execute([
    ':codigo_empresa' => $codigoEmpresa,
    ':nombre'         => $nombre,
    ':cif'            => $cif,
    ':direccion'      => $direccion !== '' ? $direccion : null,
    ':telefono'       => $telefono !== '' ? $telefono : null,
    ':email'          => $email !== '' ? $email : null,
]);

$idEmpresa = $pdo->lastInsertId();


// =====================================================
// RECUPERAR LA EMPRESA CREADA
// =====================================================

$stmtDatos = $pdo->prepare("
    SELECT
        id,
        codigo_empresa,
        nombre,
        cif,
        direccion,
        telefono,
        email
    FROM empresas
    WHERE id = ?
    LIMIT 1
");

$stmtDatos->execute([$idEmpresa]);

$empresa = $stmtDatos->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Empresa creada - Comparador Eléctrico</title>

    <link rel="stylesheet" href="../../css/style.css">

</head>

<body>


    <?php include '../../templates/header.php'; ?>


    <div class="app-container">


        <?php include '../../templates/sidebar.php'; ?>


        <main class="main-content">


            <div class="page-header">

                <div>

                    <h1>Empresas</h1>

                    <p>
                        Empresa creada correctamente
                    </p>

                </div>

                <div class="page-header-actions">

                    <div class="page-date">
                        11 septiembre 2026
                    </div>

                </div>

            </div>


            <div class="config-card">

                <h2>Empresa creada correctamente</h2>

                <div class="form-info">

                    <p>

                        La empresa

                        <strong>
                            <?= htmlspecialchars($empresa['nombre']) ?>
                        </strong>

                        se ha creado correctamente.

                    </p>

                </div>


                <div class="usuario-detalle">

                    <div class="usuario-detalle-grid">

                        <div class="usuario-detalle-item">
                            <span>ID de empresa</span>
                            <strong><?= htmlspecialchars($empresa['id']) ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Código de empresa</span>
                            <strong><?= htmlspecialchars($empresa['codigo_empresa']) ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Nombre</span>
                            <strong><?= htmlspecialchars($empresa['nombre']) ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>CIF</span>
                            <strong><?= htmlspecialchars($empresa['cif']) ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Dirección</span>
                            <strong><?= htmlspecialchars($empresa['direccion'] ?? 'No indicada') ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Teléfono</span>
                            <strong><?= htmlspecialchars($empresa['telefono'] ?? 'No indicado') ?></strong>
                        </div>

                        <div class="usuario-detalle-item">
                            <span>Email</span>
                            <strong><?= htmlspecialchars($empresa['email'] ?? 'No indicado') ?></strong>
                        </div>

                    </div>

                </div>


                <div class="form-actions">

                    <a href="crear_empresa.php" class="config-save-button">
                        + Añadir empresa
                    </a>

                    <a href="empresas.php" class="config-cancel-button">
                        Volver a empresas
                    </a>

                </div>

            </div>


        </main>


    </div>


    <?php include '../../templates/footer.php'; ?>


</body>

</html>
