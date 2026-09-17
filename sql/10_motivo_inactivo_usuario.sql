/* -- -- =====================================================
-- -- PROYECTO: COMPARADORA
-- -- ARCHIVO: 10_motivo_inactivo_usuario.sql
-- -- DESCRIPCIÓN: Nuevo campo "motivo_inactivo" en Usuarios
-- -- =====================================================

-- USE comparadora;


-- =====================================================
-- USUARIOS: NUEVO CAMPO "MOTIVO_INACTIVO"
-- =====================================================
--
-- Texto libre con el motivo por el que un usuario se ha
-- marcado como Inactivo, rellenado desde el propio
-- formulario de crear/editar usuario cuando se elige ese
-- estado. NULL si el usuario está Activo.

ALTER TABLE usuarios
    ADD COLUMN motivo_inactivo TEXT NULL AFTER estado; */
