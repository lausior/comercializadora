<?php

/*=====================================================
/CONEXIÓN CON LA BASE DE DATOS Y LOGS
=====================================================*/
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/logs.php';


/* =====================================================
   "RECORDARME" DEL LOGIN
   =====================================================
   Cuando se marca la casilla "Recordarme" al iniciar sesión
   (ver views/login/procesar_login.php), se crea un token
   persistente, independiente de la sesión de PHP, que permite
   volver a entrar sin contraseña aunque se cierre el
   navegador.

   Viaja en una cookie como "selector:validador":

     - selector   -> va en claro; solo identifica la fila en
                     BD (como un "usuario" del token).
     - validador  -> el secreto real. En BD solo se guarda su
                     hash (sha256), nunca en claro, igual que
                     la contraseña: aunque alguien leyera la
                     tabla `recordarme_tokens`, no podría
                     fabricar una cookie válida a partir de
                     ella.

   Cada vez que el token sirve para reanudar sesión, se
   sustituye por uno nuevo (rotación): si alguien copiara una
   cookie ajena, dejaría de servirle en cuanto su dueño real
   volviera a usarla.
========================================================= */

define('RECORDARME_COOKIE', 'recordarme');
define('RECORDARME_DIAS', 30);


/**
 * Crea un token de "recordarme" para el usuario y deja la
 * cookie lista en el navegador. Se llama justo después de un
 * login correcto (views/login/procesar_login.php), solo si se
 * marcó la casilla.
 */
function activarRecordarme(PDO $pdo, int $idUsuario): void
{
    guardarTokenRecordarme(
        $pdo,
        $idUsuario,
        bin2hex(random_bytes(12)),
        bin2hex(random_bytes(32))
    );
}


/**
 * Guarda un token (selector + hash del validador) en BD y
 * escribe la cookie correspondiente. Función interna
 * compartida por activarRecordarme() y por la rotación que
 * hace reanudarSesionRecordarme() en cada uso.
 */
function guardarTokenRecordarme(PDO $pdo, int $idUsuario, string $selector, string $validador): void
{
    $expiraEn = date('Y-m-d H:i:s', time() + RECORDARME_DIAS * 86400);

    $stmt = $pdo->prepare("
        INSERT INTO recordarme_tokens (id_usuario, selector, validador_hash, expira_en)
        VALUES (:id_usuario, :selector, :validador_hash, :expira_en)
    ");

    $stmt->execute([
        ':id_usuario'     => $idUsuario,
        ':selector'       => $selector,
        ':validador_hash' => hash('sha256', $validador),
        ':expira_en'      => $expiraEn,
    ]);

    setcookie(RECORDARME_COOKIE, $selector . ':' . $validador, [
        'expires'  => time() + RECORDARME_DIAS * 86400,
        'path'     => '/comercializadora/',
        'httponly' => true,
        'samesite' => 'Lax',
        // 'secure' => true, // activar el día que la app se sirva por HTTPS
    ]);
}


/**
 * Si no hay sesión activa pero llega una cookie de
 * "recordarme" válida, reabre sesión con los mismos datos que
 * pondría un login normal (ver procesar_login.php) y rota el
 * token. Debe llamarse después de session_start().
 *
 * Si la cookie no existe, está mal formada, ha caducado, no
 * coincide con ningún token o el usuario ya no puede acceder
 * (borrado o inactivo), simplemente la borra y no hace nada
 * más: nunca lanza error, como mucho el visitante se queda sin
 * sesión, igual que si no hubiera marcado "Recordarme".
 */
function reanudarSesionRecordarme(PDO $pdo): void
{
    if (isset($_SESSION['id_usuario']) || empty($_COOKIE[RECORDARME_COOKIE])) {
        return;
    }

    $partes = explode(':', $_COOKIE[RECORDARME_COOKIE], 2);

    if (count($partes) !== 2) {
        borrarCookieRecordarme();
        return;
    }

    [$selector, $validador] = $partes;

    $stmt = $pdo->prepare("
        SELECT id, id_usuario, validador_hash, expira_en
        FROM recordarme_tokens
        WHERE selector = ?
        LIMIT 1
    ");

    $stmt->execute([$selector]);

    $token = $stmt->fetch(PDO::FETCH_ASSOC);

    // Selector desconocido, o el validador no coincide (cookie
    // manipulada o robada y ya usada por su dueño real): se
    // borra el token si existía y no se reanuda sesión.
    if (!$token || !hash_equals($token['validador_hash'], hash('sha256', $validador))) {

        if ($token) {
            eliminarTokenRecordarme($pdo, (int) $token['id']);
        }

        borrarCookieRecordarme();
        return;

    }

    if (strtotime($token['expira_en']) < time()) {
        eliminarTokenRecordarme($pdo, (int) $token['id']);
        borrarCookieRecordarme();
        return;
    }

    // Mismos datos que carga procesar_login.php al iniciar sesión.
    $stmtUsuario = $pdo->prepare("
        SELECT
            u.id,
            u.username,
            u.nombre,
            u.apellidos,
            u.cambiar_password,
            u.id_empresa,
            u.id_rol,
            u.estado,
            e.codigo_empresa,
            e.nombre AS empresa,
            r.nombre AS rol
        FROM usuarios u
        INNER JOIN empresas e ON e.id = u.id_empresa
        INNER JOIN roles r ON r.id = u.id_rol
        WHERE u.id = ?
        LIMIT 1
    ");

    $stmtUsuario->execute([$token['id_usuario']]);

    $usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

    if (!$usuario || $usuario['estado'] !== 'Activo') {
        eliminarTokenRecordarme($pdo, (int) $token['id']);
        borrarCookieRecordarme();
        return;
    }

    session_regenerate_id(true);

    $_SESSION['id_usuario']       = $usuario['id'];
    $_SESSION['username']         = $usuario['username'];
    $_SESSION['nombre']           = $usuario['nombre'];
    $_SESSION['apellidos']        = $usuario['apellidos'];
    $_SESSION['id_empresa']       = $usuario['id_empresa'];
    $_SESSION['empresa']          = $usuario['empresa'];
    $_SESSION['codigo_empresa']   = $usuario['codigo_empresa'];
    $_SESSION['id_rol']           = $usuario['id_rol'];
    $_SESSION['rol']              = $usuario['rol'];
    $_SESSION['cambiar_password'] = (int) $usuario['cambiar_password'];

    // Rotación: el token usado deja de valer, se sustituye por
    // uno nuevo antes de seguir.
    eliminarTokenRecordarme($pdo, (int) $token['id']);

    guardarTokenRecordarme(
        $pdo,
        (int) $usuario['id'],
        bin2hex(random_bytes(12)),
        bin2hex(random_bytes(32))
    );

    registrarLog(
        LOG_INFORMACION,
        'Inicio de sesión',
        'Sesión reanudada automáticamente mediante "Recordarme".'
    );
}


/**
 * Olvida el "Recordarme" de ESTE navegador: borra su token de
 * BD (si tiene uno) y la cookie. Se llama al cerrar sesión y
 * al bloquear la pantalla: en los dos casos, quien vuelva a
 * esa pantalla debe volver a escribir la contraseña, sin que
 * un "Recordarme" antiguo se salte ese paso.
 */
function olvidarRecordarme(PDO $pdo): void
{
    if (!empty($_COOKIE[RECORDARME_COOKIE])) {

        $selector = explode(':', $_COOKIE[RECORDARME_COOKIE], 2)[0];

        $pdo->prepare("DELETE FROM recordarme_tokens WHERE selector = ?")
            ->execute([$selector]);

    }

    borrarCookieRecordarme();
}


/**
 * Olvida el "Recordarme" del usuario en TODOS los
 * dispositivos. Se llama al cambiar la contraseña (propia o
 * por un reseteo de un administrador): un token creado con la
 * contraseña anterior no debe seguir sirviendo para entrar.
 */
function olvidarTodosLosTokensDeUsuario(PDO $pdo, int $idUsuario): void
{
    $pdo->prepare("DELETE FROM recordarme_tokens WHERE id_usuario = ?")
        ->execute([$idUsuario]);
}


function eliminarTokenRecordarme(PDO $pdo, int $idToken): void
{
    $pdo->prepare("DELETE FROM recordarme_tokens WHERE id = ?")->execute([$idToken]);
}


function borrarCookieRecordarme(): void
{
    if (!isset($_COOKIE[RECORDARME_COOKIE])) {
        return;
    }

    unset($_COOKIE[RECORDARME_COOKIE]);

    setcookie(RECORDARME_COOKIE, '', [
        'expires'  => time() - 42000,
        'path'     => '/comercializadora/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}
