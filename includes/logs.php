<?php

require_once __DIR__ . '/../config/database.php';


/* =====================================================
   TIPOS DE EVENTO
   =====================================================
   Mismos valores que el ENUM `tipo` de la tabla `logs`
   (ver sql/02_tablas.sql). Usar estas constantes en vez
   de escribir el texto a mano evita que un typo rompa el
   INSERT (el ENUM rechaza cualquier valor que no sea
   exactamente uno de estos).
========================================================= */

define('LOG_INFO', 'Información');
define('LOG_EXITO', 'Éxito');
define('LOG_ADVERTENCIA', 'Advertencia');
define('LOG_ERROR', 'Error');


/**
 * Inserta una fila en la tabla `logs`.
 *
 * Por defecto registra la acción a nombre de quien tiene
 * la sesión iniciada ($_SESSION), que es el caso normal
 * (un usuario logueado crea/edita/elimina algo). Los tres
 * últimos parámetros solo hace falta pasarlos explícitos
 * cuando todavía no hay sesión (login fallido) o cuando el
 * evento ocurre justo antes de destruirla (logout).
 */
function registrarLog(
    string $tipo,
    string $evento,
    string $descripcion,
    ?int $idUsuario = null,
    ?string $usuario = null,
    ?string $rol = null
): void {

    global $pdo;

    $idUsuario = $idUsuario ?? ($_SESSION['id_usuario'] ?? null);
    $usuario   = $usuario   ?? ($_SESSION['username']   ?? 'desconocido');
    $rol       = $rol       ?? ($_SESSION['rol']         ?? 'desconocido');

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

    $stmt->execute([
        ':tipo'        => $tipo,
        ':id_usuario'  => $idUsuario,
        ':usuario'     => $usuario,
        ':rol'         => $rol,
        ':evento'      => $evento,
        ':descripcion' => $descripcion,
        ':ip'          => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
    ]);

}
