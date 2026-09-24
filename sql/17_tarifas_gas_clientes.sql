-- -- =====================================================
-- -- PROYECTO: COMPARADORA
-- -- ARCHIVO: 17_tarifas_gas_clientes.sql
-- -- DESCRIPCIÓN: Tarifas de gas y tarifa actual de los clientes
-- -- =====================================================

-- USE comparadora;


-- =====================================================
-- TARIFAS: GAS Y TARIFA ACTUAL DEL CLIENTE
-- =====================================================
--
-- La misma tabla guarda ahora dos tipos de tarifa:
--   - la oferta de una comercializadora (id_comercializadora)
--   - la tarifa que el cliente tiene hoy contratada
--     (id_cliente), con la que después se compara.
-- Cada fila pertenece exactamente a uno de los dos (ver
-- chk_tarifas_titular).
--
-- peaje pasa a VARCHAR para admitir también los peajes de
-- gas (RL.1-RL.4); los valores válidos por servicio están en
-- includes/tarifas.php (PEAJES_LUZ / PEAJES_GAS).
--
-- Gas: termino_fijo en €/día y termino_variable en €/kWh.
-- En las tarifas de gas las columnas de luz quedan a NULL,
-- y al revés.

ALTER TABLE tarifas
    MODIFY COLUMN id_comercializadora INT UNSIGNED NULL,
    ADD COLUMN id_cliente INT UNSIGNED NULL AFTER id_comercializadora,
    MODIFY COLUMN peaje VARCHAR(10) NOT NULL,
    ADD COLUMN termino_fijo DECIMAL(10,6) NULL AFTER precio_excedentes,
    ADD COLUMN termino_variable DECIMAL(10,6) NULL AFTER termino_fijo,
    ADD CONSTRAINT fk_tarifas_cliente
        FOREIGN KEY (id_cliente)
        REFERENCES clientes(id)
        ON DELETE CASCADE,
    ADD CONSTRAINT chk_tarifas_titular
        CHECK ((id_comercializadora IS NULL) <> (id_cliente IS NULL));
