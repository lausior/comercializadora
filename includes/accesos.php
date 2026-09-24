<?php

/* =====================================================
   ACCESO DE LOS USUARIOS (LOGIN Y CONTRASEÑA INICIAL)
   =====================================================
   Lo que comparten Usuarios, Empresas (su usuario de acceso)
   y el bloqueo de sesión: el formato del login y la
   contraseña con la que nace (o se restablece) una cuenta.
========================================================= */


// Contraseña que se asigna al crear un usuario (o el acceso
// de una empresa) y al restablecerla. Siempre va con
// cambiar_password = 1, así que hay que cambiarla en el
// primer acceso.
const PASSWORD_INICIAL = '123456';


/**
 * Login completo tal como se escribe al entrar:
 * "codigo_empresa-id_usuario-username".
 */
function loginAcceso(string $codigoEmpresa, int $idUsuario, string $username): string
{
    return $codigoEmpresa . '-' . $idUsuario . '-' . $username;
}
