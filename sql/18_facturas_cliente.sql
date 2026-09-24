-- -- =====================================================
-- -- PROYECTO: COMPARADORA
-- -- ARCHIVO: 18_facturas_cliente.sql
-- -- DESCRIPCIÓN: Datos de la factura actual del cliente
-- -- =====================================================

-- USE comparadora;


-- =====================================================
-- TABLA: FACTURAS_CLIENTE
-- =====================================================
--
-- Datos de la factura que el cliente paga hoy, junto a su
-- tarifa actual (una fila por cada tarifa de cliente de
-- `tarifas`, es decir, con id_cliente). Se rellenan en la
-- misma rejilla que sus precios ("Tarifa del cliente", ver
-- views/tarifas/) y son lo que después se usa para simular
-- la factura con cada comercializadora.
--
-- Luz: consumo y potencia contratada por franja, días,
-- excedentes, alquiler del contador, energía reactiva,
-- excesos de potencia, otros conceptos, IVA, impuesto
-- eléctrico y total de la factura.
-- Gas: consumo_gas, días, alquiler del contador, otros
-- conceptos, IVA y total (el resto queda a NULL).
--
-- iva e impuesto_electrico en %, con los valores que
-- permite includes/tarifas.php (OPCIONES_IVA,
-- OPCIONES_IMPUESTO_ELECTRICO).

CREATE TABLE facturas_cliente (
    id_tarifa INT UNSIGNED PRIMARY KEY,

    consumo_p1 DECIMAL(12,3) NULL,
    consumo_p2 DECIMAL(12,3) NULL,
    consumo_p3 DECIMAL(12,3) NULL,
    consumo_p4 DECIMAL(12,3) NULL,
    consumo_p5 DECIMAL(12,3) NULL,
    consumo_p6 DECIMAL(12,3) NULL,

    potencia_contratada_p1 DECIMAL(12,3) NULL,
    potencia_contratada_p2 DECIMAL(12,3) NULL,
    potencia_contratada_p3 DECIMAL(12,3) NULL,
    potencia_contratada_p4 DECIMAL(12,3) NULL,
    potencia_contratada_p5 DECIMAL(12,3) NULL,
    potencia_contratada_p6 DECIMAL(12,3) NULL,

    consumo_gas DECIMAL(12,3) NULL,

    dias SMALLINT UNSIGNED NULL,

    excedentes_kwh DECIMAL(12,3) NULL,

    alquiler_contador DECIMAL(10,2) NULL,

    energia_reactiva DECIMAL(10,2) NULL,

    excesos_potencia DECIMAL(10,2) NULL,

    otros_conceptos DECIMAL(10,2) NULL,

    iva DECIMAL(5,2) NULL,

    impuesto_electrico DECIMAL(10,8) NULL,

    total_factura DECIMAL(10,2) NULL,

    CONSTRAINT fk_facturas_cliente_tarifa
        FOREIGN KEY (id_tarifa)
        REFERENCES tarifas(id)
        ON DELETE CASCADE

);
