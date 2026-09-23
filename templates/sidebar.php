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


        <!-- COMERCIALIZADORAS -->
        <?php if (tienePermiso('comercializadoras')): ?>
            <a href="/comercializadora/views/comercializadoras/comercializadoras.php" class="menu-item" title="Comercializadoras">
                <span class="menu-icon"><i class="bi bi-shop"></i></span>
                <span class="menu-label">Comercializadoras</span>
            </a>
        <?php endif; ?>


        <!-- TARIFAS -->
        <?php if (tienePermiso('tarifas')): ?>
            <a href="/comercializadora/views/tarifas/tarifas.php" class="menu-item" title="Tarifas">
                <span class="menu-icon"><i class="bi bi-currency-euro"></i></span>
                <span class="menu-label">Tarifas</span>
            </a>
        <?php endif; ?>


        <!-- PLANIFICADOR -->
        <?php if (tienePermiso('planificador')): ?>
            <a href="/comercializadora/views/planificador/planificador.php" class="menu-item"
                title="Planificador de tareas">
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

            <button type="button" class="menu-item" id="abrirModalSeguridad" title="Seguridad">
                <span class="menu-icon"><i class="bi bi-shield-lock"></i></span>
                <span class="menu-label">Seguridad</span>
            </button>

            <?php

            // Mensaje pendiente de un envío anterior del
            // formulario (ver actualizar_password.php), si lo
            // hay. Si lo hay, el modal se abre ya al cargar la
            // página, con el aviso puesto, en vez de quedarse
            // cerrado esperando a que se pulse el botón.
        
            $passwordError = $_SESSION['password_error'] ?? '';
            unset($_SESSION['password_error']);

            $passwordSuccess = $_SESSION['password_success'] ?? '';
            unset($_SESSION['password_success']);

            // También se abre solo si se llega con
            // "?abrir=seguridad" (enlace antiguo a la extinta
            // seguridad.php, ver ese archivo).
            $abrirModalSeguridadAlCargar =
                $passwordError !== ''
                || $passwordSuccess !== ''
                || ($_GET['abrir'] ?? '') === 'seguridad';

            ?>

            <div class="modal-overlay" id="modalSeguridad"
                style="display: <?= $abrirModalSeguridadAlCargar ? 'flex' : 'none' ?>;">

                <div class="modal-detalle">

                    <div class="modal-detalle-header">

                        <div class="modal-detalle-titulo">
                            <h2>Cambiar contraseña</h2>
                            <span>Actualiza la contraseña de tu cuenta</span>
                        </div>

                        <button type="button" class="modal-detalle-close" id="cerrarModalSeguridad" aria-label="Cerrar">
                            ✕
                        </button>

                    </div>

                    <div class="security-password-form">

                        <div class="form-error-general" id="form-error-general" role="alert"
                            style="<?= $passwordError !== '' ? 'display: block;' : 'display: none;' ?>">
                            <?= htmlspecialchars($passwordError) ?>
                        </div>

                        <div class="form-success-general" id="form-success-general" role="status"
                            style="<?= $passwordSuccess !== '' ? 'display: block;' : 'display: none;' ?>">
                            <?= htmlspecialchars($passwordSuccess) ?>
                        </div>


                        <form action="/comercializadora/views/actualizar_password.php" method="POST" novalidate>

                            <!-- A qué página volver tras guardar: la
                                 propia página actual, sea cual sea
                                 (ver actualizar_password.php). -->
                            <input type="hidden" name="volver_a"
                                value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/comercializadora/index.php', ENT_QUOTES, 'UTF-8') ?>">


                            <!-- CONTRASEÑA NUEVA -->

                            <div class="form-group">
                                <!-- 
                                <label for="password_nueva">
                                    Contraseña nueva
                                </label> -->

                                <div class="password-wrapper">

                                    <input type="password" id="password_nueva" name="password_nueva"
                                        placeholder="Escribe tu nueva contraseña" autocomplete="new-password" required
                                        minlength="8">

                                    <button type="button" class="password-toggle" data-target="password_nueva"
                                        aria-label="Mostrar contraseña">
                                        <i class="bi bi-eye-slash"></i>
                                    </button>

                                </div>


                                <!-- REPETIR CONTRASEÑA -->

                                <div class="form-group">

                                    <!-- <label for="password_confirmar">
                                    Repite la contraseña nueva
                                </label> -->

                                    <div class="password-wrapper">

                                        <input type="password" id="password_confirmar" name="password_confirmar"
                                            placeholder="Repite la nueva contraseña" autocomplete="new-password" required
                                            minlength="8">

                                        <button type="button" class="password-toggle" data-target="password_confirmar"
                                            aria-label="Mostrar contraseña">
                                            <i class="bi bi-eye-slash"></i>
                                        </button>

                                    </div>

                                </div>

                                <ul class="password-requisitos" id="passwordRequisitos">

                                    <li data-req="longitud">Mínimo 8 caracteres</li>
                                    <li data-req="mayuscula">Mayúsculas</li>
                                    <li data-req="minuscula">Minúsculas</li>
                                    <li data-req="numero">Números</li>
                                    <li data-req="especial">Caracteres especiales</li>
                                    <li data-req="coinciden">Las contraseñas coinciden</li>

                                </ul>

                            </div>

                            <div class="form-actions">

                                <button type="submit" class="config-save-button">
                                    Guardar nueva contraseña
                                </button>

                            </div>


                        </form>


                    </div>

                </div>

            </div>

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

                    <button type="button" class="submenu-item about-menu-trigger" id="abrirInfoSrg" title="Acerca de">

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
<script src="/comercializadora/js/seguridad.js"></script>