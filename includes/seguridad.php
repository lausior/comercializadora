<?php

/* =====================================================
   CONFIGURACIÓN DE SEGURIDAD (tabla `configuracion`,
   clave/valor genérica para ajustes globales del sistema)
========================================================= */


/**
 * Minutos de inactividad tras los que se bloquea la sesión
 * automáticamente. 0 significa "Nunca".
 */
function obtenerBloqueoAutomaticoMinutos(PDO $pdo): int
{
    $stmt = $pdo->prepare(
        "SELECT valor FROM configuracion WHERE clave = ?"
    );
    $stmt->execute(['bloqueo_automatico_minutos']);

    $valor = $stmt->fetchColumn();

    return $valor !== false ? (int) $valor : 15;
}


/**
 * Guarda el nuevo tiempo de bloqueo automático (minutos).
 */
function guardarBloqueoAutomaticoMinutos(PDO $pdo, int $minutos): void
{
    $stmt = $pdo->prepare("
        INSERT INTO configuracion (clave, valor)
        VALUES ('bloqueo_automatico_minutos', ?)
        ON DUPLICATE KEY UPDATE valor = ?
    ");

    $stmt->execute([(string) $minutos, (string) $minutos]);
}


/**
 * Número de intentos de login fallidos permitidos antes de
 * bloquear el acceso.
 */
function obtenerIntentosLoginMax(PDO $pdo): int
{
    $stmt = $pdo->prepare(
        "SELECT valor FROM configuracion WHERE clave = ?"
    );
    $stmt->execute(['intentos_login_max']);

    $valor = $stmt->fetchColumn();

    return $valor !== false ? (int) $valor : 5;
}


/**
 * Guarda el nuevo número máximo de intentos de login.
 */
function guardarIntentosLoginMax(PDO $pdo, int $intentos): void
{
    $stmt = $pdo->prepare("
        INSERT INTO configuracion (clave, valor)
        VALUES ('intentos_login_max', ?)
        ON DUPLICATE KEY UPDATE valor = ?
    ");

    $stmt->execute([(string) $intentos, (string) $intentos]);
}


/**
 * Minutos que permanece bloqueado el acceso tras agotar los
 * intentos de login permitidos.
 */
function obtenerMinutosBloqueoIntentos(PDO $pdo): int
{
    $stmt = $pdo->prepare(
        "SELECT valor FROM configuracion WHERE clave = ?"
    );
    $stmt->execute(['minutos_bloqueo_intentos']);

    $valor = $stmt->fetchColumn();

    return $valor !== false ? (int) $valor : 15;
}


/**
 * Guarda los nuevos minutos de bloqueo tras intentos fallidos.
 */
function guardarMinutosBloqueoIntentos(PDO $pdo, int $minutos): void
{
    $stmt = $pdo->prepare("
        INSERT INTO configuracion (clave, valor)
        VALUES ('minutos_bloqueo_intentos', ?)
        ON DUPLICATE KEY UPDATE valor = ?
    ");

    $stmt->execute([(string) $minutos, (string) $minutos]);
}
