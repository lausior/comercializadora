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

    <!-- Zona para arrastrar el ancho -->
    <div class="sidebar-resizer" id="sidebarResizer" title="Arrastrar para cambiar el ancho">
        <span class="sidebar-resizer-arrow">‹</span>
    </div>


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
        <a href="/comercializadora/views/planificador.php" class="menu-item" title="Planificador">
            <span class="menu-icon"><i class="bi bi-calendar3"></i></span>
            <span class="menu-label">Planificador</span>
        </a>
        <?php endif; ?>


        <!-- PARTES -->

        <?php if (tienePermiso('partes')): ?>
        <a href="/comercializadora/views/partes.php" class="menu-item" title="Partes">
            <span class="menu-icon"><i class="bi bi-clipboard-check"></i></span>
            <span class="menu-label">Partes</span>
        </a>
        <?php endif; ?>


        <!-- INCIDENCIAS -->

        <?php if (tienePermiso('incidencias')): ?>
        <a href="/comercializadora/views/incidencias.php" class="menu-item" title="Incidencias">
            <span class="menu-icon"><i class="bi bi-exclamation-triangle"></i></span>
            <span class="menu-label">Incidencias</span>
        </a>
        <?php endif; ?>


        <!-- CLIENTES -->

        <?php if (tienePermiso('clientes')): ?>
        <a href="/comercializadora/views/clientes/clientes.php" class="menu-item" title="Clientes">
            <span class="menu-icon"><i class="bi bi-people"></i></span>
            <span class="menu-label">Clientes</span>
        </a>
        <?php endif; ?>


        <!-- SEPARADOR -->

        <div class="menu-separator"></div>


        


        <!-- USUARIOS -->

        <?php if (tienePermiso('usuarios')): ?>
        <a href="/comercializadora/views/usuarios/usuarios.php" class="menu-item" title="Usuarios">
            <span class="menu-icon"><i class="bi bi-person-gear"></i></span>
            <span class="menu-label">Usuarios</span>
        </a>
        <?php endif; ?>


        <!-- EMPRESAS -->

        <?php if (tienePermiso('empresas')): ?>
        <a href="/comercializadora/views/empresas/empresas.php" class="menu-item" title="Empresas">
            <span class="menu-icon"><i class="bi bi-building"></i></span>
            <span class="menu-label">Empresas</span>
        </a>
        <?php endif; ?>


        <!-- OFERTAS -->

        <?php if (tienePermiso('ofertas')): ?>
        <a href="/comercializadora/views/ofertas.php" class="menu-item" title="Ofertas">
            <span class="menu-icon"><i class="bi bi-tags"></i></span>
            <span class="menu-label">Ofertas</span>
        </a>
        <?php endif; ?>


        <!-- LOGS -->

        <?php if (tienePermiso('logs')): ?>
        <a href="/comercializadora/views/logs.php" class="menu-item" title="Logs">
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


        <!-- SEGURIDAD -->

        <?php if (tienePermiso('seguridad')): ?>
        <a href="/comercializadora/views/seguridad.php" class="menu-item" title="Seguridad">
            <span class="menu-icon"><i class="bi bi-shield-lock"></i></span>
            <span class="menu-label">Seguridad</span>
        </a>
        <?php endif; ?>


        <!-- AYUDA -->

        <?php if (tienePermiso('ayuda')): ?>
        <a href="/comercializadora/views/ayuda.php" class="menu-item" title="Ayuda">
            <span class="menu-icon"><i class="bi bi-question-circle"></i></span>
            <span class="menu-label">Ayuda</span>
        </a>
        <?php endif; ?>

    </nav>

</aside>


<script src="/comercializadora/js/sidebar.js"></script>