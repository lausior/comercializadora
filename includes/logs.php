<?php

/*===================================================== 
/CONEXIÓN CON LA BASE DE DATOS 
=====================================================*/
require_once __DIR__ . '/../config/database.php';


/* =====================================================
TIPOS DE EVENTO
=====================================================
Creamos unas constantes para representar los diferentes tipos de logs que puede guardar nuestra aplicación.
*/

// LOG_INFO NO se usa como nombre porque ya existe como constante
// nativa de PHP (nivel de syslog, valor entero 6): definirla de
// nuevo aquí como string provocaba el warning "Constant LOG_INFO
// already defined" cada vez que se cargaba este archivo.
define('LOG_INFORMACION', 'Información');
define('LOG_EXITO', 'Éxito');
define('LOG_ADVERTENCIA', 'Advertencia');
define('LOG_ERROR', 'Error');

/* ===================================================== 
FUNCIÓN PARA REGISTRAR UN LOG 
=====================================================
Función que se encarga de guardar un nuevo registro en la tabla "logs" de la base de datos.
*/

function registrarLog(
    string $tipo, //Información, Éxito, Advertencia o Error
    string $evento, //Qué acción se ha producido
    string $descripcion, //Explicación de lo ocurrido
    ?int $idUsuario = null, //Parámetros opcionales (si no se proporcionan, la función intenta obtenerlos de la sesión actual)
    ?string $usuario = null,
    ?string $rol = null
): void {


    /*================================================= 
    ACCEDER A LA CONEXIÓN CON LA BASE DE DATOS 
    =================================================*/
    global $pdo;

    /*================================================= 
    OBTENER LOS DATOS DEL USUARIO
    =================================================*/

    $idUsuario = $idUsuario ?? ($_SESSION['id_usuario'] ?? null);
    $usuario = $usuario ?? ($_SESSION['username'] ?? 'desconocido');
    $rol = $rol ?? ($_SESSION['rol'] ?? 'desconocido');


    /*================================================= 
    PREPARAR CONSULTA SQL 
    =================================================
    Crea una nueva fila en la tabla 'logs'*/
    $stmt = $pdo->prepare("
        INSERT INTO logs (
            tipo,
            id_usuario,
            usuario,
            rol,
            evento,
            descripcion,
            ip
        )
        VALUES (
            :tipo,
            :id_usuario,
            :usuario,
            :rol,
            :evento,
            :descripcion,
            :ip
        )
    ");

    /*================================================= 
    EJECUTAR LA CONSULTA 
    =================================================*/
    $stmt->execute([
        ':tipo' => $tipo,
        ':id_usuario' => $idUsuario,
        ':usuario' => $usuario,
        ':rol' => $rol,
        ':evento' => $evento,
        ':descripcion' => $descripcion,
        ':ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
    ]);

}


/* =====================================================
   RETENCIÓN / BORRADO AUTOMÁTICO DE LOGS
   =====================================================
   Reutiliza la tabla `configuracion` (clave/valor) para
   guardar cuántos días se conservan los logs y cuándo fue
   el último borrado automático.
========================================================= */


/**
 * Días que se conservan los logs antes de borrarse.
 * 0 significa "Nunca" (no se borran automáticamente).
 */
function obtenerRetencionLogsDias(PDO $pdo): int
{
    $stmt = $pdo->prepare("SELECT valor FROM configuracion WHERE clave = ?");
    $stmt->execute(['logs_retencion_dias']);

    $valor = $stmt->fetchColumn();

    return $valor !== false ? (int) $valor : 0;
}


/**
 * Guarda los nuevos días de retención de logs.
 */
function guardarRetencionLogsDias(PDO $pdo, int $dias): void
{
    $stmt = $pdo->prepare("
        INSERT INTO configuracion (clave, valor)
        VALUES ('logs_retencion_dias', ?)
        ON DUPLICATE KEY UPDATE valor = ?
    ");

    $stmt->execute([(string) $dias, (string) $dias]);
}


/**
 * Fecha y hora (DATETIME en texto) del último borrado
 * automático de logs, o null si nunca se ha ejecutado.
 */
function obtenerUltimoBorradoLogs(PDO $pdo): ?string
{
    $stmt = $pdo->prepare("SELECT valor FROM configuracion WHERE clave = ?");
    $stmt->execute(['logs_ultimo_borrado']);

    $valor = $stmt->fetchColumn();

    return $valor !== false ? $valor : null;
}


/**
 * Marca "ahora" como el momento del último borrado automático.
 */
function marcarUltimoBorradoLogs(PDO $pdo): void
{
    $ahora = date('Y-m-d H:i:s');

    $stmt = $pdo->prepare("
        INSERT INTO configuracion (clave, valor)
        VALUES ('logs_ultimo_borrado', ?)
        ON DUPLICATE KEY UPDATE valor = ?
    ");

    $stmt->execute([$ahora, $ahora]);
}


/**
 * Borra los logs con más de $dias días de antigüedad.
 *
 * $rolesVisibles limita el borrado a los logs de esos roles
 * (mismo criterio que rolesVisiblesEnLogs(), en
 * config/permisos.php): así, cuando EMPRESA fuerza el
 * borrado, solo se borran los logs que él mismo puede ver
 * (EMPRESA y USUARIO), y NG/SRG conservan los suyos. Con
 * null (valor por defecto) no se filtra por rol, para no
 * tocar el borrado automático por retención
 * (comprobarYBorrarLogsAntiguos()), que es global.
 *
 * Devuelve el número de filas borradas.
 */
function borrarLogsMasAntiguosQue(PDO $pdo, int $dias, ?array $rolesVisibles = null): int
{
    if ($rolesVisibles !== null) {

        if (empty($rolesVisibles)) {
            return 0;
        }

        $marcadores = implode(',', array_fill(0, count($rolesVisibles), '?'));

        $stmt = $pdo->prepare("
            DELETE FROM logs
            WHERE fecha_hora < (NOW() - INTERVAL ? DAY)
                AND rol IN ($marcadores)
        ");

        $stmt->execute(array_merge([$dias], $rolesVisibles));

        return $stmt->rowCount();

    }

    $stmt = $pdo->prepare("
        DELETE FROM logs
        WHERE fecha_hora < (NOW() - INTERVAL :dias DAY)
    ");

    $stmt->execute([':dias' => $dias]);

    return $stmt->rowCount();
}


/**
 * Borra TODOS los logs sin excepción (o, si se pasa
 * $rolesVisibles, todos los logs de esos roles — ver el
 * mismo criterio en borrarLogsMasAntiguosQue()).
 * Devuelve el número de filas borradas.
 */
function borrarTodosLosLogs(PDO $pdo, ?array $rolesVisibles = null): int
{
    if ($rolesVisibles !== null) {

        if (empty($rolesVisibles)) {
            return 0;
        }

        $marcadores = implode(',', array_fill(0, count($rolesVisibles), '?'));

        $stmtContar = $pdo->prepare("SELECT COUNT(*) FROM logs WHERE rol IN ($marcadores)");
        $stmtContar->execute($rolesVisibles);
        $filas = (int) $stmtContar->fetchColumn();

        $stmtBorrar = $pdo->prepare("DELETE FROM logs WHERE rol IN ($marcadores)");
        $stmtBorrar->execute($rolesVisibles);

        return $filas;

    }

    $filas = (int) $pdo->query("SELECT COUNT(*) FROM logs")->fetchColumn();

    $pdo->exec("DELETE FROM logs");

    return $filas;
}


/**
 * Comprueba si toca ejecutar el borrado automático de logs
 * (según la retención configurada) y lo ejecuta si procede.
 *
 * Pensada para llamarse en cada login: para no lanzar la
 * consulta de borrado en cada inicio de sesión, solo actúa
 * si la retención no es "Nunca" y ha pasado al menos un día
 * desde el último borrado registrado.
 */
function comprobarYBorrarLogsAntiguos(PDO $pdo): void
{
    $dias = obtenerRetencionLogsDias($pdo);

    if ($dias <= 0) {
        return;
    }

    $ultimoBorrado = obtenerUltimoBorradoLogs($pdo);

    if ($ultimoBorrado !== null && strtotime($ultimoBorrado) > strtotime('-1 day')) {
        return;
    }

    borrarLogsMasAntiguosQue($pdo, $dias);
    marcarUltimoBorradoLogs($pdo);
}
