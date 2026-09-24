<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('empresas');

require_once '../../config/database.php';
require_once '../../includes/logs.php';
require_once '../../includes/recordarme.php';
require_once '../../includes/empresas.php';


// =====================================================
// ERROR (MISMO FORMATO QUE usuarios/resetear_password.php)
// =====================================================

function responderErrorResetEmpresa(string $mensaje): void
{
    die('
        <h2>Error</h2>

        <p>' . htmlspecialchars($mensaje) . '</p>

        <p>
            <a href="empresas.php">
                Volver a empresas
            </a>
        </p>
    ');
}


// =====================================================
// COMPROBAR QUE SE ENVÍA POR POST Y CON UN ID
// =====================================================
//
// Se llama desde el modal de editar_empresa.php con un
// formulario POST: al ser una acción que cambia datos, no
// debe poder dispararse solo con abrir un enlace.
//
// =====================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: empresas.php');
    exit;

}

$idEmpresa = (int) ($_POST['id'] ?? 0);

if ($idEmpresa <= 0) {

    responderErrorResetEmpresa('La empresa seleccionada no es válida.');

}


// =====================================================
// BUSCAR LA EMPRESA Y COMPROBAR PERMISO
// =====================================================
//
// Misma comprobación que al editar la empresa: sin esto,
// NG podría restablecer la contraseña de la empresa de otro
// enviando su id a mano.
//
// =====================================================

$stmtEmpresa = $pdo->prepare("
    SELECT id, codigo_empresa, nombre, creado_por
    FROM empresas
    WHERE id = ?
");

$stmtEmpresa->execute([$idEmpresa]);

$empresa = $stmtEmpresa->fetch(PDO::FETCH_ASSOC);

if (!$empresa) {

    responderErrorResetEmpresa('La empresa seleccionada no existe.');

}

if (!puedeVerEmpresa($empresa['creado_por'] !== null ? (int) $empresa['creado_por'] : null)) {

    responderErrorResetEmpresa('No tienes permiso para restablecer la contraseña de esta empresa.');

}


// =====================================================
// BUSCAR EL USUARIO DE ACCESO DE LA EMPRESA
// =====================================================
//
// Es el usuario con rol EMPRESA que se crea junto con la
// empresa (ver guardar_empresa.php).
//
// =====================================================

$usuario = obtenerUsuarioAccesoEmpresa($pdo, $idEmpresa);

if (!$usuario) {

    responderErrorResetEmpresa('Esta empresa no tiene usuario de acceso.');

}


// =====================================================
// RESTABLECER LA CONTRASEÑA A LA INICIAL
// =====================================================
//
// Misma contraseña inicial que guardar_empresa.php y
// usuarios/resetear_password.php. cambiar_password vuelve a
// 1, para que la empresa esté obligada a cambiarla en su
// próximo acceso.
//
// =====================================================

$passwordInicial = PASSWORD_INICIAL;

$stmtActualizar = $pdo->prepare("
    UPDATE usuarios
    SET
        password = :password,
        cambiar_password = 1
    WHERE id = :id
");

$stmtActualizar->execute([
    ':password' => password_hash($passwordInicial, PASSWORD_DEFAULT),
    ':id'       => $usuario['id'],
]);

// Con la contraseña ya restablecida, cualquier "Recordarme"
// de esta cuenta deja de valer, en todos sus dispositivos.
olvidarTodosLosTokensDeUsuario($pdo, (int) $usuario['id']);

registrarLog(
    LOG_ADVERTENCIA,
    'Contraseña restablecida',
    'Se ha restablecido la contraseña de acceso de la empresa "' . $empresa['nombre'] . '" (usuario "' . $usuario['username'] . '").'
);

$usuarioAcceso = loginAcceso($empresa['codigo_empresa'], (int) $usuario['id'], $usuario['username']);

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

    <?php include '../../templates/header.php'; ?>

    <div class="app-container">

        <?php include '../../templates/sidebar.php'; ?>

        <main class="main-content">

            <div class="page-header">

                <div>

                    <h1>Empresas</h1>

                    <p>
                        Contraseña restablecida correctamente
                    </p>

                </div>

            </div>


            <div class="config-card">

                <h2>
                    Contraseña restablecida
                </h2>

                <div class="form-info">

                    <p>
                        La contraseña de acceso de la empresa
                        <strong><?= htmlspecialchars($empresa['nombre']) ?></strong>
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


                <div class="form-actions">

                    <a href="empresas.php" class="config-cancel-button">
                        Volver a empresas
                    </a>

                    <a href="editar_empresa.php?id=<?= (int) $empresa['id'] ?>" class="config-save-button">
                        Volver a editar
                    </a>

                </div>

            </div>

        </main>

    </div>

    <?php include '../../templates/footer.php'; ?>

</body>

</html>
