<?php

/* =====================================================
   CONFIGURACIÓN CENTRAL DE PERMISOS POR ROL
   =====================================================
   Único sitio donde se define qué rol puede ver o
   acceder a qué sección de la aplicación.

   Si mañana cambia lo que puede ver un rol, SOLO se
   toca este archivo (no el sidebar, no cada página).
========================================================= */


// -------------------------------------------------------
// Nombres de rol EXACTOS como están en la tabla `roles`.nombre
// -------------------------------------------------------

define('ROL_SRG', 'SRG');
define('ROL_NG', 'NG Asesores');
define('ROL_EMPRESA', 'Empresa');
define('ROL_USUARIO', 'Usuario');


// -------------------------------------------------------
// Qué roles pueden ver cada sección / página del panel
// -------------------------------------------------------

$GLOBALS['PERMISOS_SECCIONES'] = [

    'inicio'         => [ROL_SRG, ROL_NG, ROL_EMPRESA, ROL_USUARIO],

    'planificador'   => [ROL_SRG, ROL_NG, ROL_EMPRESA],
    'partes'         => [ROL_SRG, ROL_NG, ROL_EMPRESA],
    'incidencias'    => [ROL_SRG, ROL_NG, ROL_EMPRESA],
    'clientes'       => [ROL_SRG, ROL_NG, ROL_EMPRESA],

    'seguridad'      => [ROL_SRG, ROL_NG, ROL_EMPRESA, ROL_USUARIO],
    'usuarios'       => [ROL_SRG, ROL_NG, ROL_EMPRESA],

    // "logs" controla si se VE el enlace del menú.
    // Qué filas de log se ven DENTRO de logs.php es un
    // filtro aparte (ver función nivelesLogVisibles() más abajo).
    'logs'           => [ROL_SRG, ROL_NG, ROL_EMPRESA],

    'empresas'       => [ROL_SRG, ROL_NG],
    'ofertas'        => [ROL_SRG, ROL_NG, ROL_EMPRESA, ROL_USUARIO],
    'configuracion'  => [ROL_SRG, ROL_NG, ROL_EMPRESA],
    'ayuda'          => [ROL_SRG, ROL_NG, ROL_EMPRESA, ROL_USUARIO],

];


/* =====================================================
   FUNCIONES DE APOYO
========================================================= */

/**
 * Devuelve el rol del usuario logueado, o null si no hay sesión.
 */
function rolActual(): ?string
{
    return $_SESSION['rol'] ?? null;
}


/**
 * ¿Puede el rol actual acceder a esta sección?
 */
function tienePermiso(string $seccion): bool
{
    $rol = rolActual();

    if ($rol === null) {
        return false;
    }

    $permitidos = $GLOBALS['PERMISOS_SECCIONES'][$seccion] ?? [];

    return in_array($rol, $permitidos, true);
}


/**
 * Corta la ejecución de la página si:
 *   - no hay sesión iniciada, o
 *   - el rol actual no tiene permiso para esa sección.
 *
 * Se coloca como PRIMERA línea de cada página protegida
 * (planificador.php, usuarios.php, empresas.php, etc.).
 *
 * Esto es tan importante como ocultar el enlace del menú:
 * ocultar el enlace no impide que alguien escriba la URL
 * directamente en el navegador.
 */
function requerirPermiso(string $seccion): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['id_usuario'])) {
        header('Location: /comercializadora/login.php');
        exit;
    }

    // Si el usuario tiene pendiente cambiar la contraseña
    // (primer acceso, o se la ha reseteado un SRG/NG), no
    // puede entrar a NINGUNA sección hasta que la cambie.
    if (
        $seccion !== 'cambiar_password' &&
        !empty($_SESSION['cambiar_password'])
    ) {
        header('Location: /comercializadora/cambiar_password.php');
        exit;
    }

    if (!tienePermiso($seccion)) {
        header('Location: /comercializadora/index.php?error=sin_permiso');
        exit;
    }
}


/**
 * Para logs.php: qué roles de usuario puede VER cada rol
 * en el listado (para ocultar filas, no solo el menú).
 *
 * SRG ve todo.
 * NG Asesores ve todo menos las acciones hechas por SRG.
 * Empresa ve todo menos las acciones hechas por SRG y NG Asesores.
 */
function rolesVisiblesEnLogs(): array
{
    $rol = rolActual();

    switch ($rol) {

        case ROL_SRG:
            return [ROL_SRG, ROL_NG, ROL_EMPRESA, ROL_USUARIO];

        case ROL_NG:
            return [ROL_NG, ROL_EMPRESA, ROL_USUARIO];

        case ROL_EMPRESA:
            return [ROL_EMPRESA, ROL_USUARIO];

        default:
            return [];

    }
}