<script>
    /*
     * Aplica el ancho del sidebar (guardado en localStorage) y el
     * estado plegado ANTES de que el navegador pinte la página.
     *
     * Va aquí, como script síncrono al principio de <body>, y no
     * en sidebar.js (que espera a DOMContentLoaded) porque esta es
     * una app multi-página: cada sección es una recarga completa,
     * y DOMContentLoaded no salta hasta que TODO el HTML de la
     * página (tablas incluidas) ha terminado de analizarse. Si el
     * ancho se aplicara ahí, el sidebar se vería primero con el
     * ancho por defecto y saltaría al ancho real después,
     * especialmente notorio al pasar a plegado (236px -> 64px).
     */
    (function () {

        var MIN_WIDTH = 64;
        var MAX_WIDTH = 350;
        var DEFAULT_WIDTH = 236;
        var MOBILE_BREAKPOINT = 680;

        if (window.innerWidth <= MOBILE_BREAKPOINT) {
            document.body.classList.add('sidebar-collapsed');
            return;
        }

        var savedWidth = parseInt(localStorage.getItem('sidebarWidth'), 10);

        var width = (!isNaN(savedWidth) && savedWidth >= MIN_WIDTH && savedWidth <= MAX_WIDTH)
            ? savedWidth
            : DEFAULT_WIDTH;

        document.documentElement.style.setProperty('--sidebar-width', width + 'px');

        if (width <= MIN_WIDTH) {
            document.body.classList.add('sidebar-collapsed');
        }

    })();
</script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<header class="topbar">

    <div class="topbar-left">

        <div class="logo">
            <span class="logo-icon">⚡</span>
            <span>Comparador</span>
        </div>

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

        $rolesLegibles = [
            'SRG'          => 'SRG',
            'NG_ASESORES'  => 'NG Asesores',
            'EMPRESA'      => 'Empresa',
            'USUARIO'      => 'Usuario',
        ];

        $rolLegible = $rolesLegibles[$_SESSION['rol'] ?? ''] ?? ($_SESSION['rol'] ?? '');

        ?>

        <?php if ($nombreSesion !== ''): ?>

            <div class="topbar-user" title="Sesión iniciada como <?= htmlspecialchars($nombreSesion) ?>">

                <div class="topbar-user-avatar">
                    <?= htmlspecialchars($inicialesSesion) ?>
                </div>

                <div class="topbar-user-info">
                    <strong><?= htmlspecialchars($nombreSesion) ?></strong>
                    <span><?= htmlspecialchars($rolLegible) ?></span>
                </div>

            </div>

        <?php endif; ?>

    </div>


    <div class="topbar-right">

        <!-- Bloqueo de pantalla -->
        <a
            href="/comercializadora/views/login/bloquear.php"
            class="topbar-button"
            title="Bloquear pantalla"
        >
            <span>🔒</span>
            <span>Bloquear</span>
        </a>


        <!-- Cerrar sesión -->
        <a
            href="/comercializadora/views/login/logout.php"
            class="logout-button"
        >
            <span>↪</span>
            <span>Cerrar sesión</span>
        </a>

    </div>

</header>