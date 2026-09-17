/* -- -- =====================================================
-- -- PROYECTO: COMPARADORA
-- -- ARCHIVO: 06_estado_empresas.sql
-- -- DESCRIPCIÓN: Nuevo campo "estado" en Empresas
-- -- =====================================================

-- USE comparadora;


-- =====================================================
-- EMPRESAS: NUEVO CAMPO "ESTADO"
-- =====================================================
--
-- Activo/Inactivo, igual que en Usuarios. Es un campo
-- informativo/filtrable en el listado; no bloquea nada
-- por sí mismo (a diferencia del estado de Usuarios, que
-- sí impide iniciar sesión).

ALTER TABLE empresas
    ADD COLUMN estado ENUM('Activo', 'Inactivo') NOT NULL DEFAULT 'Activo' AFTER email; */
