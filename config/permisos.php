<?php

/* =====================================================
   CONFIGURACIÓN CENTRAL DE PERMISOS POR ROL
   =====================================================
   Único sitio donde se define qué rol puede ver oacceder a qué sección de la aplicación.
========================================================= */

// requerirPermiso() necesita $pdo para poder reanudar sesión
// mediante "Recordarme" (ver includes/recordarme.php) cuando
// no hay sesión activa.
require_once __DIR__ . '/database.php';


// -------------------------------------------------------
// Nombres de rol EXACTOS como están en la tabla `roles`.nombre
// -------------------------------------------------------

define('ROL_SRG', 'SRG');
define('ROL_NG', 'NG_ASESORES');
define('ROL_EMPRESA', 'EMPRESA');
define('ROL_ADMIN', 'ADMIN');
define('ROL_USUARIO', 'USUARIO');


// -------------------------------------------------------
// Grupos de roles
// -------------------------------------------------------
//
// EMPRESA es la cuenta de acceso de la propia empresa (una
// por empresa, se crea junto a ella). ADMIN es una persona
// del equipo de la empresa con sus mismas funciones: la
// ayuda a gestionar la aplicación. Los dos "gestionan la
// empresa" y comparten los mismos datos (ver
// idsEquipoEmpresa()).

const ROLES_GESTION_EMPRESA = [ROL_EMPRESA, ROL_ADMIN];

// Roles que una empresa (EMPRESA o ADMIN) puede dar a su
// gente desde la sección Usuarios. EMPRESA no está: esa
// cuenta es única y se crea con la empresa.
const ROLES_EQUIPO_EMPRESA = [ROL_USUARIO, ROL_ADMIN];


// -------------------------------------------------------
// Qué roles pueden ver cada sección / página del panel
// -------------------------------------------------------

$GLOBALS['PERMISOS_SECCIONES'] = [

    'inicio'         => [ROL_SRG, ROL_NG, ROL_EMPRESA, ROL_ADMIN, ROL_USUARIO],

    'comercializadoras' => [ROL_SRG, ROL_NG, ROL_EMPRESA, ROL_ADMIN],
    'tarifas'        => [ROL_SRG, ROL_NG, ROL_EMPRESA, ROL_ADMIN],

    'planificador'   => [ROL_SRG, ROL_NG, ROL_EMPRESA, ROL_ADMIN],
    'partes'         => [ROL_SRG, ROL_NG, ROL_EMPRESA, ROL_ADMIN],
    'incidencias'    => [ROL_SRG, ROL_NG, ROL_EMPRESA, ROL_ADMIN],
    'clientes'       => [ROL_SRG, ROL_NG, ROL_EMPRESA, ROL_ADMIN],

    'seguridad'      => [ROL_SRG, ROL_NG, ROL_EMPRESA, ROL_ADMIN, ROL_USUARIO],
    'usuarios'       => [ROL_SRG, ROL_NG, ROL_EMPRESA, ROL_ADMIN],

    // "logs" controla si se VE el enlace del menú.
    // Qué filas de log se ven DENTRO de logs.php es un
    // filtro aparte (ver función nivelesLogVisibles() más abajo).
    'logs'           => [ROL_SRG, ROL_NG, ROL_EMPRESA, ROL_ADMIN],

    'empresas'       => [ROL_SRG, ROL_NG],
    'ofertas'        => [ROL_SRG, ROL_NG, ROL_EMPRESA, ROL_ADMIN, ROL_USUARIO],
    'configuracion'  => [ROL_SRG, ROL_NG, ROL_EMPRESA, ROL_ADMIN],
    'ayuda'          => [ROL_SRG, ROL_NG, ROL_EMPRESA, ROL_ADMIN, ROL_USUARIO],

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

    // Sin sesión, pero puede que llegue una cookie de
    // "Recordarme" válida: se intenta reanudar sesión con ella
    // antes de mandar al login (ver includes/recordarme.php).
    if (!isset($_SESSION['id_usuario'])) {

        global $pdo;

        require_once __DIR__ . '/../includes/recordarme.php';
        reanudarSesionRecordarme($pdo);

    }

    if (!isset($_SESSION['id_usuario'])) {
        header('Location: /comercializadora/views/login/login.php');
        exit;
    }

    // Si el usuario tiene pendiente cambiar la contraseña
    // (primer acceso, o se la ha reseteado un SRG/NG), no
    // puede entrar a NINGUNA sección hasta que la cambie.
    if (
        $seccion !== 'cambiar_password' &&
        !empty($_SESSION['cambiar_password'])
    ) {
        header('Location: /comercializadora/views/login/cambiar_password.php');
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
 *   EMPRESA  -> (y ADMIN) ven los datos de su empresa y a
 *               los ADMIN/USUARIO de su equipo.
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
 * Nombre legible de un rol ("NG_ASESORES" -> "NG Asesores").
 */
function etiquetaRol(string $rol): string
{
    return [
        ROL_SRG     => 'SRG',
        ROL_NG      => 'NG Asesores',
        ROL_EMPRESA => 'Empresa',
        ROL_ADMIN   => 'Admin',
        ROL_USUARIO => 'Usuario',
    ][$rol] ?? $rol;
}


/**
 * ¿El rol actual gestiona una empresa (cuenta EMPRESA o
 * ADMIN de su equipo)?
 */
function esGestorEmpresa(?string $rol = null): bool
{
    return in_array($rol ?? rolActual(), ROLES_GESTION_EMPRESA, true);
}


/**
 * Ids de todos los usuarios de la empresa del usuario actual
 * (su cuenta EMPRESA, sus ADMIN y sus USUARIO).
 *
 * EMPRESA y ADMIN comparten los datos de su empresa: ven y
 * gestionan todo lo que haya creado cualquiera de ellos
 * (clientes, comercializadoras, tarifas, tareas, usuarios).
 * Se calcula una vez por petición.
 */
function idsEquipoEmpresa(): array
{
    static $ids = null;

    if ($ids === null) {

        global $pdo;

        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE id_empresa = ?");
        $stmt->execute([(int) ($_SESSION['id_empresa'] ?? 0)]);

        $ids = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));

    }

    return $ids;
}


/**
 * Regla común de clientes, tareas y comercializadoras (y de
 * sus tarifas): SRG ve todo; NG solo lo que ha creado él;
 * EMPRESA y ADMIN, todo lo creado por alguien de su empresa.
 */
function puedeVerDatoCreadoPor(?int $creadoPor): bool
{
    $rol = rolActual();

    if ($rol === ROL_SRG) {
        return true;
    }

    if ($creadoPor === null) {
        return false;
    }

    if ($rol === ROL_NG) {
        return $creadoPor === (int) ($_SESSION['id_usuario'] ?? 0);
    }

    if (esGestorEmpresa($rol)) {
        return in_array($creadoPor, idsEquipoEmpresa(), true);
    }

    return false;
}


/**
 * ¿Puede el usuario actual ver/gestionar este usuario?
 *
 * $usuario necesita 'id', 'creado_por', 'id_empresa' y 'rol'.
 *
 * SRG ve a todos. NG, a los que él mismo ha dado de alta.
 * EMPRESA y ADMIN, a los ADMIN y USUARIO de su empresa
 * (los haya creado quien los haya creado), nunca a la cuenta
 * EMPRESA (esa se gestiona desde Empresas) ni a sí mismos
 * (así un ADMIN no puede quitarse permisos, desactivarse o
 * borrarse; su contraseña la cambia desde Seguridad).
 */
function puedeVerUsuario(array $usuario): bool
{
    $rol = rolActual();

    if ($rol === ROL_SRG) {
        return true;
    }

    if ($rol === ROL_NG) {
        return $usuario['creado_por'] !== null
            && (int) $usuario['creado_por'] === (int) ($_SESSION['id_usuario'] ?? 0);
    }

    if (esGestorEmpresa($rol)) {
        return (int) $usuario['id_empresa'] === (int) ($_SESSION['id_empresa'] ?? 0)
            && in_array($usuario['rol'], ROLES_EQUIPO_EMPRESA, true)
            && (int) $usuario['id'] !== (int) ($_SESSION['id_usuario'] ?? 0);
    }

    return false;
}


/**
 * ¿Puede el usuario actual ver/gestionar un cliente
 * creado por $creadoPor? (ver puedeVerDatoCreadoPor()).
 */
function puedeVerCliente(?int $creadoPor): bool
{
    return puedeVerDatoCreadoPor($creadoPor);
}


/**
 * ¿Puede el usuario actual ver/gestionar una tarea del
 * planificador creada por $creadoPor? (ver
 * puedeVerDatoCreadoPor()).
 */
function puedeVerTarea(?int $creadoPor): bool
{
    return puedeVerDatoCreadoPor($creadoPor);
}


/**
 * ¿Puede el usuario actual ver/gestionar una comercializadora
 * creada por $creadoPor? (ver puedeVerDatoCreadoPor()).
 */
function puedeVerComercializadora(?int $creadoPor): bool
{
    return puedeVerDatoCreadoPor($creadoPor);
}


/**
 * Para logs.php: qué roles de usuario puede VER cada rol
 * en el listado (para ocultar filas, no solo el menú).
 *
 * SRG ve todo.
 * NG Asesores ve todo menos las acciones hechas por SRG.
 * Empresa y Admin ven todo menos las acciones hechas por SRG
 * y NG Asesores.
 */
function rolesVisiblesEnLogs(): array
{
    $rol = rolActual();

    switch ($rol) {

        case ROL_SRG:
            return [ROL_SRG, ROL_NG, ROL_EMPRESA, ROL_ADMIN, ROL_USUARIO];

        case ROL_NG:
            return [ROL_NG, ROL_EMPRESA, ROL_ADMIN, ROL_USUARIO];

        case ROL_EMPRESA:
        case ROL_ADMIN:
            return [ROL_EMPRESA, ROL_ADMIN, ROL_USUARIO];

        default:
            return [];

    }
}
