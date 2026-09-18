<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/permisos.php';

?>


<!-- =====================================================
     MENÚ VERTICAL
===================================================== -->

<aside class="sidebar" id="sidebar">

    <!-- Botón para comprimir / expandir el menú -->
    <button type="button" class="sidebar-resizer" id="sidebarResizer" title="Comprimir / expandir menú">
        <span class="sidebar-resizer-arrow">‹</span>
    </button>


    <!-- =================================================
         NAVEGACIÓN
    ================================================== -->

    <nav class="sidebar-nav">


        <!-- INICIO -->

        <?php if (tienePermiso('inicio')): ?>
            <a href="/comercializadora/index.php" class="menu-item" title="Inicio">
                <span class="menu-icon"><i class="bi bi-house-door"></i></span>
                <span class="menu-label">Inicio</span>
            </a>
        <?php endif; ?>


        <!-- PLANIFICADOR -->

        <?php if (tienePermiso('planificador')): ?>
            <a href="/comercializadora/views/planificador/planificador.php" class="menu-item" title="Planificador de tareas">
                <span class="menu-icon"><i class="bi bi-calendar3"></i></span>
                <span class="menu-label">Planificador</span>
            </a>
        <?php endif; ?>


        <!-- PARTES -->

        <?php if (tienePermiso('partes')): ?>
            <a href="/comercializadora/views/partes.php" class="menu-item" title="Gestión de partes">
                <span class="menu-icon"><i class="bi bi-clipboard-check"></i></span>
                <span class="menu-label">Partes</span>
            </a>
        <?php endif; ?>


        <!-- INCIDENCIAS -->

        <?php if (tienePermiso('incidencias')): ?>
            <a href="/comercializadora/views/incidencias.php" class="menu-item" title=" Gestión de incidencias">
                <span class="menu-icon"><i class="bi bi-exclamation-triangle"></i></span>
                <span class="menu-label">Incidencias</span>
            </a>
        <?php endif; ?>


        <!-- CLIENTES -->

        <?php if (tienePermiso('clientes')): ?>
            <a href="/comercializadora/views/clientes/clientes.php" class="menu-item" title="Gestión de clientes">
                <span class="menu-icon"><i class="bi bi-people"></i></span>
                <span class="menu-label">Clientes</span>
            </a>
        <?php endif; ?>

        <!-- OFERTAS -->

        <?php if (tienePermiso('ofertas')): ?>
            <a href="/comercializadora/views/ofertas.php" class="menu-item" title="Ofertas">
                <span class="menu-icon"><i class="bi bi-tags"></i></span>
                <span class="menu-label">Ofertas</span>
            </a>
        <?php endif; ?>


        <!-- SEPARADOR -->

        <div class="menu-separator"></div>



        <!-- EMPRESAS -->

        <?php if (tienePermiso('empresas')): ?>
            <a href="/comercializadora/views/empresas/empresas.php" class="menu-item" title="Gestión de empresas">
                <span class="menu-icon"><i class="bi bi-building"></i></span>
                <span class="menu-label">Empresas</span>
            </a>
        <?php endif; ?>

        <!-- USUARIOS -->

        <?php if (tienePermiso('usuarios')): ?>
            <a href="/comercializadora/views/usuarios/usuarios.php" class="menu-item" title="Gestión de usuarios">
                <span class="menu-icon"><i class="bi bi-person-gear"></i></span>
                <span class="menu-label">Usuarios</span>
            </a>
        <?php endif; ?>

        <!-- SEGURIDAD -->

        <?php if (tienePermiso('seguridad')): ?>
            <a href="/comercializadora/views/seguridad.php" class="menu-item" title="Seguridad">
                <span class="menu-icon"><i class="bi bi-shield-lock"></i></span>
                <span class="menu-label">Seguridad</span>
            </a>
        <?php endif; ?>

        <!-- LOGS -->

        <?php if (tienePermiso('logs')): ?>
            <a href="/comercializadora/views/logs.php" class="menu-item" title="Gestión de logs">
                <span class="menu-icon"><i class="bi bi-clock-history"></i></span>
                <span class="menu-label">Logs</span>
            </a>
        <?php endif; ?>


        <!-- CONFIGURACIÓN -->

        <?php if (tienePermiso('configuracion')): ?>
            <a href="/comercializadora/views/configuracion.php" class="menu-item" title="Configuración">
                <span class="menu-icon"><i class="bi bi-gear"></i></span>
                <span class="menu-label">Configuración</span>
            </a>
        <?php endif; ?>


        <!-- AYUDA -->

        <?php if (tienePermiso('ayuda')): ?>

            <div class="menu-group">

                <button type="button" class="menu-item menu-toggle" id="ayuda-toggle" title="Ayuda">

                    <span class="menu-icon">
                        <i class="bi bi-question-circle"></i>
                    </span>

                    <span class="menu-label">
                        Ayuda
                    </span>

                    <span class="menu-arrow">
                        <i class="bi bi-chevron-down"></i>
                    </span>

                </button>


                <!-- SUBMENÚ AYUDA -->

                <div class="submenu" id="ayuda-submenu">

                    <button type="button" class="submenu-item about-menu-trigger" id="abrirInfoSrg"
                        title="Acerca de">

                        <span class="submenu-icon">
                            <i class="bi bi-info-circle"></i>
                        </span>

                        <span>
                            Acerca de
                        </span>

                    </button>


                    <a href="/comercializadora/views/manual_usuario.php" class="submenu-item" title="Manual de usuario">

                        <span class="submenu-icon">
                            <i class="bi bi-book"></i>
                        </span>

                        <span>
                            Manual de usuario
                        </span>

                    </a>

                </div>

                <div class="modal-overlay" id="modalInfoSrg" style="display: none;">

                    <div class="modal-confirmacion about-company-modal" role="dialog" aria-modal="true"
                        aria-labelledby="tituloInfoSrg">

                        <button type="button" class="modal-detalle-close about-company-close" id="cerrarInfoSrg"
                            aria-label="Cerrar información de SRG">
                            ×
                        </button>

                        <div class="about-company-logo" aria-hidden="true">SRG</div>

                        <h2 id="tituloInfoSrg">SRG</h2>

                        <p class="about-company-description">
                            Empresa desarrolladora del aplicativo Comparador Eléctrico.
                        </p>

                        <div class="about-company-links">

                            <a href="https://srg.es" target="_blank" rel="noopener noreferrer">
                                <span aria-hidden="true">↗</span>
                                <span>https://srg.es</span>
                            </a>

                            <a href="mailto:info@srg.es">
                                <span aria-hidden="true">@</span>
                                <span>info@srg.es</span>
                            </a>

                        </div>

                    </div>

                </div>

            </div>

        <?php endif; ?>


        <!-- SEPARADOR -->

        <div class="menu-separator"></div>

        <!-- Cerrar sesión -->
        <a href="/comercializadora/views/login/logout.php" class="menu-item" title="Cerrar sesión">
            <span class="menu-icon"><i class="bi bi-door-open"></i></span>
            <span class="menu-label">Cerrar sesión</span>
        </a>

    </nav>

</aside>


<script src="/comercializadora/js/sidebar.js"></script>