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


/**
 * Traduce el valor guardado en motivo_inactivo (usuarios o
 * empresas) a la etiqueta que se le muestra al usuario en los
 * selects de crear/editar. Si no hay motivo o no se reconoce el
 * valor, devuelve '-' (mismo criterio que el resto del listado
 * para datos ausentes).
 */
function etiquetaMotivoInactivo(?string $valor): string
{
    $etiquetas = [
        'vacaciones'       => 'Vacaciones',
        'baja_laboral'     => 'Baja laboral',
        'baja_empresa'     => 'Baja en la empresa',
        'empresa_inactiva' => 'Empresa inactiva',
        'impago'           => 'Impago',
        'fin_contrato'     => 'Fin de contrato',
    ];

    return $etiquetas[$valor] ?? '-';
}


/* =====================================================
   CASCADA DE ESTADO: EMPRESA -> SUS USUARIOS
   =====================================================
   El usuario de acceso con rol EMPRESA ya se mantiene
   sincronizado 1:1 con su empresa en cambiar_estado_empresa.php
   / cambiar_estado_usuario.php / actualizar_empresa.php /
   actualizar_usuario.php. Estas dos funciones son la parte
   nueva: cuando la empresa se inactiva, sus empleados (rol
   USUARIO) que estuvieran Activos se inactivan también, y al
   reactivar la empresa solo se reactiva a esos mismos, nunca a
   los que ya estaban Inactivos por su cuenta (vacaciones, baja
   laboral...) antes de que la empresa se inactivara.
========================================================= */


/**
 * Inactiva a los usuarios (rol USUARIO) de la empresa que en
 * este momento estén Activos, marcándolos con
 * inactivo_por_empresa para poder reactivar solo a esos luego
 * (ver reactivarUsuariosPorEmpresa()). El motivo se guarda como
 * "empresa_inactiva" ("Empresa inactiva"), distinto de "Baja en
 * la empresa": ese es un motivo elegido a mano (el empleado deja
 * la empresa), este lo pone el sistema porque es la empresa
 * entera la que se ha inactivado.
 */
function inactivarUsuariosPorEmpresa(PDO $pdo, int $idEmpresa): void
{
    $pdo->prepare("
        UPDATE usuarios u
        INNER JOIN roles r ON r.id = u.id_rol
        SET u.estado = 'Inactivo',
            u.motivo_inactivo = 'empresa_inactiva',
            u.inactivo_por_empresa = 1
        WHERE u.id_empresa = ?
            AND r.nombre = ?
            AND u.estado = 'Activo'
    ")->execute([$idEmpresa, ROL_USUARIO]);
}


/**
 * Reactiva únicamente a los usuarios de la empresa que se
 * inactivaron en cascada al inactivarse la empresa (ver
 * inactivarUsuariosPorEmpresa()). Los que estén Inactivos por
 * su cuenta no llevan esta marca y se quedan como están.
 */
function reactivarUsuariosPorEmpresa(PDO $pdo, int $idEmpresa): void
{
    $pdo->prepare("
        UPDATE usuarios
        SET estado = 'Activo',
            motivo_inactivo = NULL,
            inactivo_por_empresa = 0
        WHERE id_empresa = ?
            AND inactivo_por_empresa = 1
    ")->execute([$idEmpresa]);
}
