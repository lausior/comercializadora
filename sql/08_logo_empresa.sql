/* -- -- =====================================================
-- -- PROYECTO: COMPARADORA
-- -- ARCHIVO: 08_logo_empresa.sql
-- -- DESCRIPCIÓN: Nuevo campo "logo" en Empresas
-- -- =====================================================

-- USE comparadora;


-- =====================================================
-- EMPRESAS: NUEVO CAMPO "LOGO"
-- =====================================================
--
-- Ruta relativa (desde la raíz del proyecto) a la imagen de
-- logo subida por la empresa desde Configuración. NULL si la
-- empresa no ha subido ninguna (se usa el logo genérico de
-- la aplicación).

ALTER TABLE empresas
    ADD COLUMN logo VARCHAR(255) NULL AFTER estado; */
