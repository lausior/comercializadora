/* -- -- =====================================================
-- -- PROYECTO: COMPARADORA
-- -- ARCHIVO: 04_configuracion.sql
-- -- DESCRIPCIÓN: Ajustes globales del sistema (clave/valor)
-- -- =====================================================

-- USE comparadora;


-- =====================================================
-- TABLA: CONFIGURACION
-- =====================================================
--
-- Tabla genérica de clave/valor para ajustes globales del
-- sistema (no ligados a un usuario ni a una empresa). Por
-- ahora solo guarda el tiempo de bloqueo automático por
-- inactividad (sección Seguridad), pero sirve para
-- cualquier ajuste futuro del mismo tipo.

CREATE TABLE configuracion (
    clave VARCHAR(50) PRIMARY KEY,
    valor VARCHAR(255) NOT NULL
);


-- =====================================================
-- VALOR INICIAL: BLOQUEO AUTOMÁTICO
-- =====================================================
--
-- Minutos de inactividad tras los que se bloquea la sesión.
-- 0 = "Nunca" (bloqueo automático desactivado).

INSERT INTO configuracion (clave, valor) VALUES
('bloqueo_automatico_minutos', '15'); */
