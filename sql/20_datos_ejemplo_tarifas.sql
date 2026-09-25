-- -- =====================================================
-- -- PROYECTO: COMPARADORA
-- -- ARCHIVO: 20_datos_ejemplo_tarifas.sql
-- -- DESCRIPCIÓN: Datos de ejemplo de tarifas (comercializadoras y clientes) para probar la comparativa
-- -- =====================================================

-- USE comparadora;


-- =====================================================
-- DATOS DE EJEMPLO: TARIFAS Y FACTURAS
-- =====================================================
--
-- Solo para pruebas. Usa las comercializadoras y los
-- clientes de ejemplo que ya existen (se buscan por CIF /
-- NIF); si alguno no existe, su fila no se inserta.
--
-- Comercializadoras (ofertas):
--   Luz 2.0TD -> A Todo Gas (2 tarifas), Kilovatio y Medio,
--                Enchufe Usted Mismo (1 activa + 1 inactiva)
--   Luz 3.0TD -> A Todo Gas, Kilovatio y Medio, Enchufe
--                Usted Mismo
--   Gas RL.1  -> Chispa y Chollo, Gas Naturalmente Caro,
--                A Todo Gas
--   Gas RL.2  -> Chispa y Chollo, Gas Naturalmente Caro,
--                A Todo Gas, Kilovatio y Medio
--
-- Clientes (tarifa actual + datos de su factura):
--   Luz Tenue       -> luz 2.0TD y gas RL.2
--   Eva Luación     -> luz 2.0TD (con excedentes) y gas RL.1
--   Panini D'Agostini -> luz 3.0TD (con energía reactiva)
--
-- El total de la factura actual de cada cliente está
-- calculado con sus precios y consumos (misma fórmula que el
-- Excel: energía + potencia + reactiva + excesos -
-- excedentes, impuesto eléctrico, contador, otros conceptos
-- e IVA), para que la comparativa dé ahorros coherentes.
--
-- Para borrarlos: DELETE FROM tarifas WHERE nombre LIKE '%(ejemplo)%';
-- (datos_factura se borra en cascada).

SET @usuario_srg = (SELECT id FROM usuarios WHERE username = 'srg' LIMIT 1);

SET @atodogas   = (SELECT id FROM comercializadoras WHERE cif = 'B13579248');
SET @kilovatio  = (SELECT id FROM comercializadoras WHERE cif = 'B33445560');
SET @enchufe    = (SELECT id FROM comercializadoras WHERE cif = 'B22334452');
SET @chispa     = (SELECT id FROM comercializadoras WHERE cif = 'B34567891');
SET @gascaro    = (SELECT id FROM comercializadoras WHERE cif = 'B67890129');

SET @luztenue   = (SELECT id FROM clientes WHERE nif = '12345678B');
SET @eva        = (SELECT id FROM clientes WHERE nif = '12345678Z');
SET @panini     = (SELECT id FROM clientes WHERE nif = '123456789');


-- =====================================================
-- COMERCIALIZADORAS: LUZ 2.0TD
-- =====================================================
-- energía €/kWh (P1-P3), potencia €/kW día (P1-P3),
-- excedentes €/kWh

INSERT INTO tarifas (id_comercializadora, tipo_suministro, peaje, nombre,
    energia_p1, energia_p2, energia_p3, potencia_p1, potencia_p2, potencia_p3,
    precio_excedentes, activa, creado_por)
SELECT * FROM (
    SELECT @atodogas AS c, 'luz' AS s, '2.0TD' AS p, 'Tarifa Estable 12 meses (ejemplo)' AS n,
        0.109900 AS e1, 0.109900 AS e2, 0.109900 AS e3, 0.123030 AS p1, 0.037337 AS p2, 0.018000 AS p3,
        0.060000 AS ex, 1 AS a, @usuario_srg AS u
    UNION ALL SELECT @atodogas, 'luz', '2.0TD', 'Tres Periodos (ejemplo)',
        0.165000, 0.120000, 0.085000, 0.090000, 0.025000, 0.015000, 0.050000, 1, @usuario_srg
    UNION ALL SELECT @kilovatio, 'luz', '2.0TD', 'Plan Hogar (ejemplo)',
        0.118000, 0.118000, 0.118000, 0.110000, 0.040000, 0.020000, 0.070000, 1, @usuario_srg
    UNION ALL SELECT @enchufe, 'luz', '2.0TD', 'Luz Fácil (ejemplo)',
        0.125000, 0.115000, 0.095000, 0.095000, 0.030000, 0.015000, NULL, 1, @usuario_srg
    UNION ALL SELECT @enchufe, 'luz', '2.0TD', 'Tarifa Antigua (ejemplo)',
        0.180000, 0.160000, 0.140000, 0.130000, 0.050000, 0.025000, NULL, 0, @usuario_srg
) AS filas
WHERE c IS NOT NULL;


-- =====================================================
-- COMERCIALIZADORAS: LUZ 3.0TD
-- =====================================================

INSERT INTO tarifas (id_comercializadora, tipo_suministro, peaje, nombre,
    energia_p1, energia_p2, energia_p3, energia_p4, energia_p5, energia_p6,
    potencia_p1, potencia_p2, potencia_p3, potencia_p4, potencia_p5, potencia_p6,
    precio_excedentes, activa, creado_por)
SELECT * FROM (
    SELECT @atodogas AS c, 'luz' AS s, '3.0TD' AS p, 'Empresa 6 Periodos (ejemplo)' AS n,
        0.185000 AS e1, 0.160000 AS e2, 0.135000 AS e3, 0.120000 AS e4, 0.105000 AS e5, 0.095000 AS e6,
        0.050000 AS p1, 0.030000 AS p2, 0.015000 AS p3, 0.013000 AS p4, 0.009000 AS p5, 0.005000 AS p6,
        0.050000 AS ex, 1 AS a, @usuario_srg AS u
    UNION ALL SELECT @kilovatio, 'luz', '3.0TD', 'Pyme Precio Fijo (ejemplo)',
        0.140000, 0.140000, 0.140000, 0.140000, 0.140000, 0.140000,
        0.045000, 0.028000, 0.014000, 0.012000, 0.008000, 0.004500, NULL, 1, @usuario_srg
    UNION ALL SELECT @enchufe, 'luz', '3.0TD', 'Negocio (ejemplo)',
        0.190000, 0.165000, 0.140000, 0.125000, 0.110000, 0.100000,
        0.048000, 0.029000, 0.014500, 0.012500, 0.008500, 0.004800, NULL, 1, @usuario_srg
) AS filas
WHERE c IS NOT NULL;


-- =====================================================
-- COMERCIALIZADORAS: GAS RL.1 Y RL.2
-- =====================================================
-- término fijo €/día, término variable €/kWh

INSERT INTO tarifas (id_comercializadora, tipo_suministro, peaje, nombre,
    termino_fijo, termino_variable, activa, creado_por)
SELECT * FROM (
    SELECT @chispa AS c, 'gas' AS s, 'RL.1' AS p, 'Gas Ahorro (ejemplo)' AS n, 0.265000 AS f, 0.069000 AS v, 1 AS a, @usuario_srg AS u
    UNION ALL SELECT @gascaro,  'gas', 'RL.1', 'Gas Premium (ejemplo)',  0.320000, 0.095000, 1, @usuario_srg
    UNION ALL SELECT @atodogas, 'gas', 'RL.1', 'Gas Estable (ejemplo)',  0.280000, 0.072000, 1, @usuario_srg
    UNION ALL SELECT @chispa,   'gas', 'RL.2', 'Gas Ahorro (ejemplo)',   0.330000, 0.066000, 1, @usuario_srg
    UNION ALL SELECT @gascaro,  'gas', 'RL.2', 'Gas Premium (ejemplo)',  0.390000, 0.090000, 1, @usuario_srg
    UNION ALL SELECT @atodogas, 'gas', 'RL.2', 'Gas Estable (ejemplo)',  0.340000, 0.070000, 1, @usuario_srg
    UNION ALL SELECT @kilovatio,'gas', 'RL.2', 'Gas Hogar (ejemplo)',    0.300000, 0.075000, 1, @usuario_srg
) AS filas
WHERE c IS NOT NULL;


-- =====================================================
-- CLIENTES: TARIFA ACTUAL + DATOS DE SU FACTURA
-- =====================================================
-- Una tarifa de cliente y, justo después, su fila de
-- datos_factura (LAST_INSERT_ID() es la tarifa recién
-- creada).

-- Luz Tenue: luz 2.0TD, 31 días, sin excedentes.
INSERT INTO tarifas (id_cliente, tipo_suministro, peaje, nombre,
    energia_p1, energia_p2, energia_p3, potencia_p1, potencia_p2, potencia_p3, activa, creado_por)
SELECT @luztenue, 'luz', '2.0TD', 'Iberdrola Plan Estable (ejemplo)',
    0.145000, 0.135000, 0.125000, 0.115000, 0.040000, 0.020000, 1, @usuario_srg
FROM DUAL WHERE @luztenue IS NOT NULL;

INSERT INTO datos_factura (id_tarifa, consumo_p1, consumo_p2, consumo_p3,
    potencia_contratada_p1, potencia_contratada_p2, potencia_contratada_p3,
    dias, excedentes_kwh, alquiler_contador, energia_reactiva, excesos_potencia, otros_conceptos,
    iva, impuesto_electrico, total_factura)
SELECT LAST_INSERT_ID(), 85.590, 87.330, 155.910, 4.600, 4.600, 4.600,
    31, 0, 0.83, 0, 0, 0, 21, 5.11269632, 88.31
FROM DUAL WHERE @luztenue IS NOT NULL;

-- Eva Luación: luz 2.0TD, 30 días, con placas (60 kWh de
-- excedentes a 0,04 €/kWh) y 2,99 € de mantenimiento.
INSERT INTO tarifas (id_cliente, tipo_suministro, peaje, nombre,
    energia_p1, energia_p2, energia_p3, potencia_p1, potencia_p2, potencia_p3, precio_excedentes, activa, creado_por)
SELECT @eva, 'luz', '2.0TD', 'Endesa One Luz (ejemplo)',
    0.160000, 0.140000, 0.110000, 0.120000, 0.045000, 0.020000, 0.040000, 1, @usuario_srg
FROM DUAL WHERE @eva IS NOT NULL;

INSERT INTO datos_factura (id_tarifa, consumo_p1, consumo_p2, consumo_p3,
    potencia_contratada_p1, potencia_contratada_p2, potencia_contratada_p3,
    dias, excedentes_kwh, alquiler_contador, energia_reactiva, excesos_potencia, otros_conceptos,
    iva, impuesto_electrico, total_factura)
SELECT LAST_INSERT_ID(), 120.000, 95.000, 180.000, 5.750, 5.750, 5.750,
    30, 60, 0.83, 0, 0, 2.99, 21, 5.11269632, 108.67
FROM DUAL WHERE @eva IS NOT NULL;

-- Panini D'Agostini: luz 3.0TD (negocio), 30 días, 15 kW en
-- todas las franjas y 3,20 € de energía reactiva.
INSERT INTO tarifas (id_cliente, tipo_suministro, peaje, nombre,
    energia_p1, energia_p2, energia_p3, energia_p4, energia_p5, energia_p6,
    potencia_p1, potencia_p2, potencia_p3, potencia_p4, potencia_p5, potencia_p6, activa, creado_por)
SELECT @panini, 'luz', '3.0TD', 'Naturgy Negocios (ejemplo)',
    0.210000, 0.185000, 0.155000, 0.140000, 0.120000, 0.105000,
    0.055000, 0.033000, 0.016000, 0.014000, 0.009500, 0.005500, 1, @usuario_srg
FROM DUAL WHERE @panini IS NOT NULL;

INSERT INTO datos_factura (id_tarifa, consumo_p1, consumo_p2, consumo_p3, consumo_p4, consumo_p5, consumo_p6,
    potencia_contratada_p1, potencia_contratada_p2, potencia_contratada_p3,
    potencia_contratada_p4, potencia_contratada_p5, potencia_contratada_p6,
    dias, excedentes_kwh, alquiler_contador, energia_reactiva, excesos_potencia, otros_conceptos,
    iva, impuesto_electrico, total_factura)
SELECT LAST_INSERT_ID(), 450, 520, 380, 300, 260, 900, 15, 15, 15, 15, 15, 15,
    30, 0, 1.50, 3.20, 0, 0, 21, 5.11269632, 612.77
FROM DUAL WHERE @panini IS NOT NULL;

-- Eva Luación: gas RL.1, 650 kWh en 60 días.
INSERT INTO tarifas (id_cliente, tipo_suministro, peaje, nombre, termino_fijo, termino_variable, activa, creado_por)
SELECT @eva, 'gas', 'RL.1', 'Naturgy Gas (ejemplo)', 0.300000, 0.085000, 1, @usuario_srg
FROM DUAL WHERE @eva IS NOT NULL;

INSERT INTO datos_factura (id_tarifa, consumo_gas, dias, alquiler_contador, otros_conceptos, iva, total_factura)
SELECT LAST_INSERT_ID(), 650, 60, 1.20, 0, 21, 91.92
FROM DUAL WHERE @eva IS NOT NULL;

-- Luz Tenue: gas RL.2, 1.400 kWh en 61 días.
INSERT INTO tarifas (id_cliente, tipo_suministro, peaje, nombre, termino_fijo, termino_variable, activa, creado_por)
SELECT @luztenue, 'gas', 'RL.2', 'Repsol Gas (ejemplo)', 0.350000, 0.080000, 1, @usuario_srg
FROM DUAL WHERE @luztenue IS NOT NULL;

INSERT INTO datos_factura (id_tarifa, consumo_gas, dias, alquiler_contador, otros_conceptos, iva, total_factura)
SELECT LAST_INSERT_ID(), 1400, 61, 1.20, 0, 21, 166.77
FROM DUAL WHERE @luztenue IS NOT NULL;
