<script>
    /*
     * Aplica el estado plegado del sidebar (guardado en
     * localStorage) ANTES de que el navegador pinte la página.
     *
     * Va aquí, como script síncrono al principio de <body>, y no
     * en sidebar.js (que espera a DOMContentLoaded) porque esta es
     * una app multi-página: cada sección es una recarga completa,
     * y DOMContentLoaded no salta hasta que TODO el HTML de la
     * página (tablas incluidas) ha terminado de analizarse. Si el
     * estado se aplicara ahí, el sidebar se vería primero expandido
     * y saltaría a comprimido después.
     */
    (function () {

        var MOBILE_BREAKPOINT = 680;

        var colapsado =
            window.innerWidth <= MOBILE_BREAKPOINT ||
            localStorage.getItem('sidebarCollapsed') === 'true';

        if (colapsado) {
            document.body.classList.add('sidebar-collapsed');
        }

    })();
</script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../includes/empresas.php';

$minutosBloqueoAutomatico = obtenerBloqueoAutomaticoMinutos($pdo);

$logoEmpresaSesion = isset($_SESSION['id_empresa'])
    ? obtenerLogoEmpresa($pdo, (int) $_SESSION['id_empresa'])
    : null;

?>
<script>
    window.BLOQUEO_AUTOMATICO_MINUTOS = <?= (int) $minutosBloqueoAutomatico ?>;
</script>
<script src="/comercializadora/js/bloqueo-automatico.js"></script>
<script src="/comercializadora/js/acerca.js"></script>
<header class="topbar">

    <div class="topbar-left">

        <div class="logo">
            <span class="logo-icon">⚡</span>
            <span>Comparador</span>
        </div>

    </div>


    <div class="topbar-right">

        <!-- Bloqueo de pantalla (solo icono) -->
        <a
            href="/comercializadora/views/login/bloquear.php"
            class="topbar-button topbar-button-icon"
            title="Bloquear pantalla"
            aria-label="Bloquear pantalla"
        >
            <span>🔒</span>
        </a>

        <?php

        // =====================================================
        // NOMBRE DEL USUARIO CON LA SESIÓN INICIADA
        // =====================================================

        $nombreSesion = trim(
            ($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellidos'] ?? '')
        );

        if ($nombreSesion === '') {
            $nombreSesion = $_SESSION['username'] ?? '';
        }

        $inicialesSesion = mb_strtoupper(
            mb_substr($_SESSION['nombre'] ?? '', 0, 1, 'UTF-8') .
            mb_substr($_SESSION['apellidos'] ?? '', 0, 1, 'UTF-8'),
            'UTF-8'
        );

        // etiquetaRol() está en config/permisos.php.
        $rolLegible = function_exists('etiquetaRol')
            ? etiquetaRol($_SESSION['rol'] ?? '')
            : ($_SESSION['rol'] ?? '');

        ?>

        <?php if ($nombreSesion !== ''): ?>

            <div class="topbar-user" title="Sesión iniciada como <?= htmlspecialchars($nombreSesion) ?>">

                <?php if ($logoEmpresaSesion !== null): ?>

                    <div class="topbar-user-avatar topbar-user-avatar-img">
                        <img src="/comercializadora/<?= htmlspecialchars($logoEmpresaSesion) ?>" alt="">
                    </div>

                <?php else: ?>

                    <div class="topbar-user-avatar">
                        <?= htmlspecialchars($inicialesSesion) ?>
                    </div>

                <?php endif; ?>

                <div class="topbar-user-info">
                    <strong><?= htmlspecialchars($nombreSesion) ?></strong>
                    <span><?= htmlspecialchars($rolLegible) ?></span>
                </div>

            </div>

        <?php endif; ?>

    </div>

</header>