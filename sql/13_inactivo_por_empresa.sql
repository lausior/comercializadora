/* -- -- =====================================================
-- -- PROYECTO: COMPARADORA
-- -- ARCHIVO: 13_inactivo_por_empresa.sql
-- -- DESCRIPCIÓN: Nuevo campo "inactivo_por_empresa" en Usuarios
-- -- =====================================================

-- USE comparadora;


-- =====================================================
-- USUARIOS: NUEVO CAMPO "INACTIVO_POR_EMPRESA"
-- =====================================================
--
-- Marca los usuarios (rol USUARIO) que se han inactivado en
-- cascada porque su empresa ha pasado a Inactivo, para poder
-- reactivar solo a esos cuando la empresa vuelve a Activo (ver
-- includes/empresas.php: inactivarUsuariosPorEmpresa() y
-- reactivarUsuariosPorEmpresa()).
--
-- Sin esta marca no se podría distinguir a esos usuarios de
-- los que ya estaban Inactivos por su cuenta antes de que la
-- empresa se inactivara (vacaciones, baja laboral...): esos no
-- deben reactivarse solo porque la empresa vuelva a Activo.

-- ALTER TABLE usuarios
--     ADD COLUMN inactivo_por_empresa TINYINT(1) NOT NULL DEFAULT 0
--     AFTER motivo_inactivo; */
