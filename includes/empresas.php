<?php

/* =====================================================
   LOGO DE EMPRESA
========================================================= */


/**
 * Devuelve la ruta relativa (desde la raíz del proyecto) al
 * logo de la empresa, o null si no tiene ninguno subido.
 */
function obtenerLogoEmpresa(PDO $pdo, int $idEmpresa): ?string
{
    $stmt = $pdo->prepare("SELECT logo FROM empresas WHERE id = ?");
    $stmt->execute([$idEmpresa]);

    $logo = $stmt->fetchColumn();

    return $logo !== false && $logo !== null ? $logo : null;
}


/**
 * Guarda la ruta del logo de la empresa.
 */
function guardarLogoEmpresa(PDO $pdo, int $idEmpresa, ?string $rutaRelativa): void
{
    $stmt = $pdo->prepare("UPDATE empresas SET logo = ? WHERE id = ?");
    $stmt->execute([$rutaRelativa, $idEmpresa]);
}
