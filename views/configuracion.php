<?php

session_start();

require_once __DIR__ . '/../config/permisos.php';
requerirPermiso('configuracion');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../includes/logs.php';
require_once __DIR__ . '/../includes/empresas.php';

$minutosBloqueoAutomatico = obtenerBloqueoAutomaticoMinutos($pdo);

$intentosLoginMax       = obtenerIntentosLoginMax($pdo);
$minutosBloqueoIntentos = obtenerMinutosBloqueoIntentos($pdo);

$idEmpresaSesion = (int) ($_SESSION['id_empresa'] ?? 0);
$logoEmpresa     = obtenerLogoEmpresa($pdo, $idEmpresaSesion);

$logoError = $_SESSION['logo_error'] ?? '';
unset($_SESSION['logo_error']);

$retencionLogsDias = obtenerRetencionLogsDias($pdo);
$ultimoBorradoLogs = obtenerUltimoBorradoLogs($pdo);

// El borrado forzado solo afecta a los logs que este rol
// puede ver (rolesVisiblesEnLogs()): SRG los ve todos, pero
// NG y EMPRESA no, así que el aviso se ajusta para no dar a
// entender que se borra la tabla entera.
$alcanceBorrado = rolActual() === ROL_SRG
    ? 'TODOS los logs'
    : 'todos los logs que puedes ver';

$mensajeBorrado = $retencionLogsDias > 0
    ? '¿Seguro que quieres borrar ahora mismo ' . $alcanceBorrado . ' con más de ' . $retencionLogsDias . ' días de antigüedad?'
    : 'No hay una retención definida ("Nunca"), así que se borrarán ' . $alcanceBorrado . '. ¿Seguro que quieres continuar?';

?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Configuración - Comparador Eléctrico</title>

    <link rel="stylesheet" href="../css/style.css">

</head>

<body>


<?php include '../templates/header.php'; ?>


<!-- =========================
     CONTENEDOR PRINCIPAL
========================== -->

<div class="app-container">


    <?php include '../templates/sidebar.php'; ?>


    <!-- =========================
         CONTENIDO
    ========================== -->

    <main class="main-content">


        <div class="page-header">

            <div>
                <h1>Configuración</h1>
                <p>Configuración general del comparador</p>
            </div>

        </div>


        <!-- =========================
             LOGO / SESIÓN / RETENCIÓN DE LOGS / CONTROL
             DE ACCESOS, los cuatro bloques de Configuración
             juntos en la misma rejilla de 2 columnas
             (clase .security-grid).
        ========================== -->

        <div class="security-grid">

            <!-- LOGO DE EMPRESA -->

            <section class="config-card">

                <div class="config-card-header">

                    <div class="config-icon">
                        🖼️
                    </div>

                    <div>
                        <h2>Logo de empresa</h2>
                        <p>Imagen que identifica a tu empresa en la aplicación</p>
                    </div>

                </div>

                <?php if ($logoError !== ''): ?>

                    <div class="form-error-general" style="display: block; margin-bottom: 17px;">
                        <?= htmlspecialchars($logoError) ?>
                    </div>

                <?php endif; ?>

                <div class="logo-empresa-block">

                    <div class="logo-empresa-preview">

                        <?php if ($logoEmpresa !== null): ?>
                            <img src="/comercializadora/<?= htmlspecialchars($logoEmpresa) ?>" alt="Logo de la empresa">
                        <?php else: ?>
                            <span>🏢</span>
                        <?php endif; ?>

                    </div>

                    <div class="logo-empresa-acciones">

                        <form action="guardar_logo_empresa.php" method="POST" enctype="multipart/form-data">

                            <input type="file" name="logo" accept="image/png, image/jpeg, image/webp" required>

                            <button type="submit" class="config-save-button">
                                Subir logo
                            </button>

                        </form>

                        <?php if ($logoEmpresa !== null): ?>

                            <form action="eliminar_logo_empresa.php" method="POST">

                                <button type="submit" class="config-secondary-button">
                                    Quitar logo
                                </button>

                            </form>

                        <?php endif; ?>

                        <p class="logo-empresa-ayuda">
                            PNG, JPG o WEBP. Máximo 2 MB.
                        </p>

                    </div>

                </div>

            </section>


            <!-- SESIÓN -->

            <section class="config-card">

                <div class="config-card-header">

                    <div class="config-icon">
                        🔒
                    </div>

                    <div>
                        <h2>Sesión</h2>
                        <p>Bloqueo automático por inactividad</p>
                    </div>

                </div>

                <div class="config-grid">

                    <div class="config-field">

                        <label for="bloqueo_automatico_minutos">
                            Bloqueo automático
                        </label>

                        <form
                            id="formBloqueoAutomatico"
                            method="POST"
                            action="guardar_bloqueo_automatico.php"
                        >

                            <select
                                name="minutos"
                                id="bloqueo_automatico_minutos"
                                class="security-select"
                            >

                                <option value="5" <?= $minutosBloqueoAutomatico === 5 ? 'selected' : '' ?>>
                                    5 minutos
                                </option>

                                <option value="10" <?= $minutosBloqueoAutomatico === 10 ? 'selected' : '' ?>>
                                    10 minutos
                                </option>

                                <option value="15" <?= $minutosBloqueoAutomatico === 15 ? 'selected' : '' ?>>
                                    15 minutos
                                </option>

                                <option value="30" <?= $minutosBloqueoAutomatico === 30 ? 'selected' : '' ?>>
                                    30 minutos
                                </option>

                                <option value="0" <?= $minutosBloqueoAutomatico === 0 ? 'selected' : '' ?>>
                                    Nunca
                                </option>

                            </select>

                        </form>

                    </div>

                </div>

            </section>


            <!-- RETENCIÓN DE LOGS -->

            <section class="config-card">

                <div class="config-card-header">

                    <div class="config-icon">
                        🗑️
                    </div>

                    <div>
                        <h2>Retención de logs</h2>
                        <p>Borrado automático de los logs del aplicativo</p>
                    </div>

                </div>

                <div class="config-grid">

                    <div class="config-field">

                        <label for="retencion_logs_dias">
                            Tiempo de retención
                        </label>

                        <form
                            id="formRetencionLogs"
                            method="POST"
                            action="guardar_retencion_logs.php"
                        >

                            <select
                                name="dias"
                                id="retencion_logs_dias"
                                class="security-select"
                            >

                                <option value="0" <?= $retencionLogsDias === 0 ? 'selected' : '' ?>>
                                    Nunca
                                </option>

                                <option value="30" <?= $retencionLogsDias === 30 ? 'selected' : '' ?>>
                                    30 días
                                </option>

                                <option value="60" <?= $retencionLogsDias === 60 ? 'selected' : '' ?>>
                                    60 días
                                </option>

                                <option value="90" <?= $retencionLogsDias === 90 ? 'selected' : '' ?>>
                                    90 días
                                </option>

                                <option value="365" <?= $retencionLogsDias === 365 ? 'selected' : '' ?>>
                                    1 año
                                </option>

                            </select>

                        </form>

                    </div>

                </div>

                <div class="logs-retencion-footer">

                    <span class="logs-retencion-info">
                        Último borrado automático:
                        <strong>
                            <?= $ultimoBorradoLogs !== null
                                ? date('d/m/Y H:i', strtotime($ultimoBorradoLogs))
                                : 'Nunca' ?>
                        </strong>
                    </span>

                    <button type="button" class="config-secondary-button" onclick="window.abrirModalBorrarLogs()">
                        🗑️ Borrar logs ahora
                    </button>

                </div>

            </section>


            <!-- CONTROL DE ACCESOS -->

            <section class="config-card">

                <div class="config-card-header">

                    <div class="config-icon">
                        🔐
                    </div>

                    <div>
                        <h2>Control de accesos</h2>
                        <p>Bloqueo por intentos fallidos</p>
                    </div>

                </div>

                <form action="guardar_intentos_login.php" method="POST">

                    <div class="config-grid">

                        <div class="config-field">

                            <label for="intentos_login_max">
                                Intentos de login antes de bloqueo
                            </label>

                            <input type="number" name="intentos_login_max" id="intentos_login_max"
                                class="security-select" min="1" max="20"
                                value="<?= $intentosLoginMax ?>" required>

                        </div>

                        <div class="config-field">

                            <label for="minutos_bloqueo_intentos">
                                Minutos de bloqueo tras intentos fallidos
                            </label>

                            <input type="number" name="minutos_bloqueo_intentos" id="minutos_bloqueo_intentos"
                                class="security-select" min="1" max="1440"
                                value="<?= $minutosBloqueoIntentos ?>" required>

                        </div>

                    </div>

                    <div class="form-actions">

                        <button type="submit" class="config-save-button">
                            Guardar cambios
                        </button>

                    </div>

                </form>

            </section>

        </div>


    </main>

</div>


<!-- =====================================================
     MODAL CONFIRMAR BORRADO DE LOGS
====================================================== -->

<div id="modalBorrarLogs" class="modal-overlay" style="display: none;">

    <div class="modal-confirmacion">

        <div class="modal-icon">
            ⚠
        </div>

        <h2>Borrar logs ahora</h2>

        <p>
            <?= htmlspecialchars($mensajeBorrado) ?>
        </p>

        <p class="modal-warning">
            Esta acción no se puede deshacer.
        </p>

        <form action="borrar_logs_ahora.php" method="POST">

            <div class="modal-actions">

                <button type="button" class="modal-button modal-button-cancel" onclick="window.cerrarModalBorrarLogs()">
                    Cancelar
                </button>

                <button type="submit" class="modal-button modal-button-delete">
                    Borrar logs
                </button>

            </div>

        </form>

    </div>

</div>


<?php include '../templates/footer.php'; ?>

<script src="../js/configuracion.js"></script>

</body>

</html>
