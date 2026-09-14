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
            href="/comercializadora/bloquear.php"
            class="topbar-button"
            title="Bloquear pantalla"
        >
            <span>🔒</span>
            <span>Bloquear</span>
        </a>


        <!-- Cerrar sesión -->
        <a
            href="/comercializadora/logout.php"
            class="logout-button"
        >
            <span>↪</span>
            <span>Cerrar sesión</span>
        </a>

    </div>

</header>