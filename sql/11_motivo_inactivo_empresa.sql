/* -- -- =====================================================
-- -- PROYECTO: COMPARADORA
-- -- ARCHIVO: 11_motivo_inactivo_empresa.sql
-- -- DESCRIPCIÓN: Nuevo campo "motivo_inactivo" en Empresas
-- -- =====================================================

-- USE comparadora;


-- =====================================================
-- EMPRESAS: NUEVO CAMPO "MOTIVO_INACTIVO"
-- =====================================================
--
-- Texto libre con el motivo por el que una empresa se ha
-- marcado como Inactiva, rellenado desde el propio
-- formulario de crear/editar empresa cuando se elige ese
-- estado. NULL si la empresa está Activa. Igual que
-- motivo_inactivo en Usuarios (10_motivo_inactivo_usuario.sql).

ALTER TABLE empresas
    ADD COLUMN motivo_inactivo TEXT NULL AFTER estado; */
