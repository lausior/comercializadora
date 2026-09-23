/* -- -- =====================================================
-- -- PROYECTO: COMPARADORA
-- -- ARCHIVO: 15_comercializadoras_luz_gas.sql
-- -- DESCRIPCIÓN: Qué suministra cada comercializadora (luz, gas o ambos)
-- -- =====================================================

-- USE comparadora;


-- =====================================================
-- COMERCIALIZADORAS: NUEVOS CAMPOS "SUMINISTRA_LUZ" / "SUMINISTRA_GAS"
-- =====================================================
--
-- Una comercializadora puede suministrar luz, gas, o las dos
-- cosas — nunca ninguna (se exige marcar al menos una al
-- crear/editar, ver guardar_comercializadora.php/
-- actualizar_comercializadora.php). Determina qué opciones
-- aparecen en el selector de Tarifas al elegir esa
-- comercializadora (ver views/tarifas/).

-- ALTER TABLE comercializadoras
--     ADD COLUMN suministra_luz TINYINT(1) NOT NULL DEFAULT 0 AFTER email,
--     ADD COLUMN suministra_gas TINYINT(1) NOT NULL DEFAULT 0 AFTER suministra_luz; */
