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
define('ROL_NG', 'NG_ASESORES');
define('ROL_EMPRESA', 'EMPRESA');
define('ROL_USUARIO', 'USUARIO');


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
 * =====================================================
 * VISIBILIDAD DE DATOS POR ROL
 * =====================================================
 *
 * No es lo mismo "puede entrar a la sección Empresas"
 * (tienePermiso) que "puede ver/gestionar ESTA empresa en
 * concreto" (puedeVerEmpresa). Estas dos funciones deciden
 * lo segundo, y las usan tanto los listados como las
 * páginas de editar/eliminar. Reglas:
 *
 *   SRG      -> puede ver y gestionar todas las empresas
 *               y usuarios, incluida su propia cuenta.
 *   NG       -> ve SOLO las empresas y los usuarios que
 *               ha creado él mismo. No la de SRG.
 *   EMPRESA  -> ve SOLO los usuarios que ha creado ella
 *               misma (su equipo).
 *   USUARIO  -> no tiene acceso a estas secciones.
 *
 * Además, en los LISTADOS (empresas.php, usuarios.php)
 * la propia cuenta/empresa de quien mira nunca aparece,
 * aunque sí sea gestionable si se llega a su edición
 * directamente (por ejemplo, SRG editando los datos de
 * su propia empresa). Ese filtro extra se aplica en el
 * propio listado, no aquí.
 * =====================================================
 */

/**
 * ¿Puede el usuario actual ver/gestionar esta empresa?
 * Solo lo usan SRG y NG (son los únicos con acceso a la
 * sección "empresas"; ver $GLOBALS['PERMISOS_SECCIONES']).
 */
function puedeVerEmpresa(?int $creadoPor): bool
{
    $rol = rolActual();

    if ($rol === ROL_SRG) {
        return true;
    }

    if ($rol === ROL_NG) {

        return $creadoPor !== null
            && $creadoPor === (int) ($_SESSION['id_usuario'] ?? 0);

    }

    return false;
}


/**
 * ¿Puede el usuario actual ver/gestionar un usuario
 * creado por $creadoPor?
 *
 * SRG ve a todos. NG y EMPRESA solo ven a los usuarios
 * que ellos mismos han dado de alta.
 */
function puedeVerUsuario(?int $creadoPor): bool
{
    $rol = rolActual();

    if ($rol === ROL_SRG) {
        return true;
    }

    if ($rol === ROL_NG || $rol === ROL_EMPRESA) {
        return $creadoPor !== null
            && $creadoPor === (int) ($_SESSION['id_usuario'] ?? 0);
    }

    return false;
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