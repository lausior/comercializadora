-- -- =====================================================
-- -- PROYECTO: COMPARADORA
-- -- ARCHIVO: 19_datos_factura.sql
-- -- DESCRIPCIÓN: Datos de factura también en las tarifas de luz de las comercializadoras
-- -- =====================================================

-- USE comparadora;


-- =====================================================
-- FACTURAS_CLIENTE -> DATOS_FACTURA
-- =====================================================
--
-- Los datos de factura (consumos, potencias contratadas,
-- días, IVA, impuesto eléctrico...) ya no son solo de las
-- tarifas de clientes: las tarifas de luz de las
-- comercializadoras llevan también esas columnas en su
-- rejilla (ver gruposColumnasTarifa() en
-- includes/tarifas.php). La tabla sigue siendo una fila por
-- tarifa (id_tarifa); solo cambia de nombre.

RENAME TABLE facturas_cliente TO datos_factura;

ALTER TABLE datos_factura
    DROP FOREIGN KEY fk_facturas_cliente_tarifa,
    ADD CONSTRAINT fk_datos_factura_tarifa
        FOREIGN KEY (id_tarifa)
        REFERENCES tarifas(id)
        ON DELETE CASCADE;
