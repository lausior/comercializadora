-- -- =====================================================
-- -- PROYECTO: COMPARADORA
-- -- ARCHIVO: 21_mas_datos_ejemplo_tarifas.sql
-- -- DESCRIPCIÓN: Más datos de ejemplo de tarifas (todos los peajes y el resto de comercializadoras y clientes)
-- -- =====================================================

-- USE comparadora;


-- =====================================================
-- MÁS DATOS DE EJEMPLO: TARIFAS Y FACTURAS
-- =====================================================
--
-- Complementa 20_datos_ejemplo_tarifas.sql (ejecutar
-- después). Solo para pruebas. Comercializadoras y clientes
-- se buscan por CIF / NIF; si alguno no existe, su fila no
-- se inserta. Cada tarifa se asigna (creado_por) al mismo
-- usuario que creó su comercializadora o cliente, para que
-- la vea quien los ve.
--
-- Comercializadoras (ofertas):
--   Luz 2.0TD -> Voltios del Barrio, Saltan Chispas,
--                Electrificados
--   Luz 3.0TD -> Voltios del Barrio, Saltan Chispas,
--                Electrificados
--   Luz 6.1TD -> Kilovatio y Medio, Enchufe Usted Mismo,
--                Voltios del Barrio, Electrificados
--   Gas RL.1  -> Voltios del Barrio, Kilovatio y Medio
--   Gas RL.2  -> Voltios del Barrio
--   Gas RL.3  -> Chispa y Chollo, Gas Naturalmente Caro,
--                Voltios del Barrio, Kilovatio y Medio
--   Gas RL.4  -> Chispa y Chollo, Gas Naturalmente Caro,
--                Voltios del Barrio, Kilovatio y Medio
--
-- Clientes (tarifa actual + datos de su factura):
--   Dolores De Barriga -> luz 2.0TD y gas RL.3
--   Mariano Cacahuete  -> luz 3.0TD y gas RL.2
--   Margarita Flores   -> luz 6.1TD y gas RL.4
--   Panini D'Agostini  -> gas RL.3
--
-- El total de cada factura está calculado con sus precios
-- y consumos (misma fórmula que la comparativa).
--
-- Para borrarlos: DELETE FROM tarifas WHERE nombre LIKE '%(ejemplo)%';
-- (datos_factura se borra en cascada).


SET @chispa     = (SELECT id FROM comercializadoras WHERE cif = 'B34567891');
SET @gascaro    = (SELECT id FROM comercializadoras WHERE cif = 'B67890129');
SET @voltios    = (SELECT id FROM comercializadoras WHERE cif = 'B89012348');
SET @kilovatio  = (SELECT id FROM comercializadoras WHERE cif = 'B33445560');
SET @enchufe    = (SELECT id FROM comercializadoras WHERE cif = 'B22334452');
SET @saltan     = (SELECT id FROM comercializadoras WHERE cif = 'B90123456');
SET @electro    = (SELECT id FROM comercializadoras WHERE cif = 'B01234566');

SET @dolores    = (SELECT id FROM clientes WHERE nif = '56789012B');
SET @mariano    = (SELECT id FROM clientes WHERE nif = '77889900D');
SET @margarita  = (SELECT id FROM clientes WHERE nif = '78901234X');
SET @panini     = (SELECT id FROM clientes WHERE nif = '123456789');


-- =====================================================
-- COMERCIALIZADORAS: LUZ 2.0TD
-- =====================================================
-- energía €/kWh (P1-P3), potencia €/kW día (P1-P3),
-- excedentes €/kWh

INSERT INTO tarifas (id_comercializadora, tipo_suministro, peaje, nombre,
    energia_p1, energia_p2, energia_p3, potencia_p1, potencia_p2, potencia_p3,
    precio_excedentes, activa, creado_por)
SELECT filas.*, (SELECT creado_por FROM comercializadoras WHERE id = filas.c) FROM (
    SELECT @voltios AS c, 'luz' AS s, '2.0TD' AS p, 'Voltios Noche (ejemplo)' AS n,
        0.172000 AS e1, 0.112000 AS e2, 0.072000 AS e3, 0.098000 AS p1, 0.032000 AS p2, 0.016000 AS p3,
        0.055000 AS ex, 1 AS a
    UNION ALL SELECT @saltan, 'luz', '2.0TD', 'Chispazo Fijo (ejemplo)',
        0.112500, 0.112500, 0.112500, 0.105000, 0.036000, 0.018000, 0.065000, 1
    UNION ALL SELECT @electro, 'luz', '2.0TD', 'Electro Hogar (ejemplo)',
        0.131000, 0.121000, 0.101000, 0.089000, 0.029000, 0.014000, NULL, 1
) AS filas
WHERE c IS NOT NULL;


-- =====================================================
-- COMERCIALIZADORAS: LUZ 3.0TD Y 6.1TD
-- =====================================================

INSERT INTO tarifas (id_comercializadora, tipo_suministro, peaje, nombre,
    energia_p1, energia_p2, energia_p3, energia_p4, energia_p5, energia_p6,
    potencia_p1, potencia_p2, potencia_p3, potencia_p4, potencia_p5, potencia_p6,
    precio_excedentes, activa, creado_por)
SELECT filas.*, (SELECT creado_por FROM comercializadoras WHERE id = filas.c) FROM (
    SELECT @voltios AS c, 'luz' AS s, '3.0TD' AS p, 'Pyme Barrio (ejemplo)' AS n,
        0.178000 AS e1, 0.152000 AS e2, 0.128000 AS e3, 0.114000 AS e4, 0.101000 AS e5, 0.090000 AS e6,
        0.047000 AS p1, 0.028500 AS p2, 0.014200 AS p3, 0.012300 AS p4, 0.008200 AS p5, 0.004600 AS p6,
        0.050000 AS ex, 1 AS a
    UNION ALL SELECT @saltan, 'luz', '3.0TD', 'Chispas Empresa (ejemplo)',
        0.135000, 0.135000, 0.135000, 0.135000, 0.135000, 0.135000,
        0.049000, 0.030000, 0.015000, 0.013000, 0.008800, 0.005000, NULL, 1
    UNION ALL SELECT @electro, 'luz', '3.0TD', 'Electro Pyme (ejemplo)',
        0.169000, 0.147000, 0.126000, 0.111000, 0.098000, 0.087000,
        0.051000, 0.031000, 0.015500, 0.013500, 0.009100, 0.005200, NULL, 1

    UNION ALL SELECT @kilovatio, 'luz', '6.1TD', 'Industria Plus (ejemplo)',
        0.132000, 0.118000, 0.103000, 0.092000, 0.081000, 0.072000,
        0.071000, 0.036000, 0.019000, 0.014500, 0.004200, 0.002100, NULL, 1
    UNION ALL SELECT @enchufe, 'luz', '6.1TD', 'Gran Consumo (ejemplo)',
        0.121000, 0.121000, 0.121000, 0.121000, 0.121000, 0.121000,
        0.068000, 0.034000, 0.018000, 0.014000, 0.004000, 0.002000, NULL, 1
    UNION ALL SELECT @voltios, 'luz', '6.1TD', 'Industrial Barrio (ejemplo)',
        0.140000, 0.124000, 0.108000, 0.096000, 0.084000, 0.075000,
        0.066000, 0.033000, 0.017500, 0.013800, 0.003900, 0.001900, NULL, 1
    UNION ALL SELECT @electro, 'luz', '6.1TD', 'Electro Industria (ejemplo)',
        0.128000, 0.115000, 0.101000, 0.090000, 0.079000, 0.070000,
        0.074000, 0.037000, 0.019500, 0.015000, 0.004400, 0.002200, NULL, 0
) AS filas
WHERE c IS NOT NULL;


-- =====================================================
-- COMERCIALIZADORAS: GAS RL.1 A RL.4
-- =====================================================
-- término fijo €/día, término variable €/kWh

INSERT INTO tarifas (id_comercializadora, tipo_suministro, peaje, nombre,
    termino_fijo, termino_variable, activa, creado_por)
SELECT filas.*, (SELECT creado_por FROM comercializadoras WHERE id = filas.c) FROM (
    SELECT @voltios AS c, 'gas' AS s, 'RL.1' AS p, 'Gas Barrio (ejemplo)' AS n, 0.255000 AS f, 0.071000 AS v, 1 AS a
    UNION ALL SELECT @kilovatio, 'gas', 'RL.1', 'Gas Hogar (ejemplo)',        0.290000, 0.068500, 1
    UNION ALL SELECT @voltios,   'gas', 'RL.2', 'Gas Barrio (ejemplo)',       0.315000, 0.068000, 1

    UNION ALL SELECT @chispa,    'gas', 'RL.3', 'Gas Ahorro (ejemplo)',       0.590000, 0.062000, 1
    UNION ALL SELECT @gascaro,   'gas', 'RL.3', 'Gas Premium (ejemplo)',      0.720000, 0.084000, 1
    UNION ALL SELECT @voltios,   'gas', 'RL.3', 'Gas Negocio (ejemplo)',      0.610000, 0.064500, 1
    UNION ALL SELECT @kilovatio, 'gas', 'RL.3', 'Gas Pyme (ejemplo)',         0.560000, 0.066000, 1

    UNION ALL SELECT @chispa,    'gas', 'RL.4', 'Gas Ahorro (ejemplo)',       1.450000, 0.058000, 1
    UNION ALL SELECT @gascaro,   'gas', 'RL.4', 'Gas Premium (ejemplo)',      1.720000, 0.079000, 1
    UNION ALL SELECT @voltios,   'gas', 'RL.4', 'Gas Industria (ejemplo)',    1.380000, 0.061000, 1
    UNION ALL SELECT @kilovatio, 'gas', 'RL.4', 'Gas Gran Consumo (ejemplo)', 1.510000, 0.056500, 1
) AS filas
WHERE c IS NOT NULL;


-- =====================================================
-- CLIENTES: TARIFA ACTUAL + DATOS DE SU FACTURA
-- =====================================================
-- Una tarifa de cliente y, justo después, su fila de
-- datos_factura (LAST_INSERT_ID() es la tarifa recién
-- creada; si el cliente no existe no se inserta ninguna de
-- las dos).

-- Dolores De Barriga: luz 2.0TD, 30 días.
INSERT INTO tarifas (id_cliente, tipo_suministro, peaje, nombre,
    energia_p1, energia_p2, energia_p3, potencia_p1, potencia_p2, potencia_p3, activa, creado_por)
SELECT @dolores, 'luz', '2.0TD', 'TotalEnergies A Tu Aire (ejemplo)',
    0.158000, 0.141000, 0.119000, 0.108000, 0.038000, 0.019000, 1,
    (SELECT creado_por FROM clientes WHERE id = @dolores)
FROM DUAL WHERE @dolores IS NOT NULL;

INSERT INTO datos_factura (id_tarifa, consumo_p1, consumo_p2, consumo_p3,
    potencia_contratada_p1, potencia_contratada_p2, potencia_contratada_p3,
    dias, alquiler_contador, otros_conceptos, iva, impuesto_electrico, total_factura)
SELECT LAST_INSERT_ID(), 72.400, 81.150, 132.600, 3.450, 3.450, 3.450,
    30, 0.81, 0.00, 21, 5.11269632, 71.87
FROM DUAL WHERE @dolores IS NOT NULL;

-- Dolores De Barriga: gas RL.3, 61 días (calefacción).
INSERT INTO tarifas (id_cliente, tipo_suministro, peaje, nombre,
    termino_fijo, termino_variable, activa, creado_por)
SELECT @dolores, 'gas', 'RL.3', 'Naturgy Gas Plus (ejemplo)', 0.650000, 0.079000, 1,
    (SELECT creado_por FROM clientes WHERE id = @dolores)
FROM DUAL WHERE @dolores IS NOT NULL;

INSERT INTO datos_factura (id_tarifa, consumo_gas, dias, alquiler_contador, otros_conceptos,
    iva, total_factura)
SELECT LAST_INSERT_ID(), 4210.000, 61, 2.36, 0.00, 21, 465.18
FROM DUAL WHERE @dolores IS NOT NULL;

-- Mariano Cacahuete: luz 3.0TD, 31 días, con reactiva.
INSERT INTO tarifas (id_cliente, tipo_suministro, peaje, nombre,
    energia_p1, energia_p2, energia_p3, energia_p4, energia_p5, energia_p6,
    potencia_p1, potencia_p2, potencia_p3, potencia_p4, potencia_p5, potencia_p6, activa, creado_por)
SELECT @mariano, 'luz', '3.0TD', 'Endesa Tempo Pyme (ejemplo)',
    0.192000, 0.168000, 0.143000, 0.128000, 0.112000, 0.101000,
    0.052000, 0.031500, 0.015800, 0.013700, 0.009300, 0.005300, 1,
    (SELECT creado_por FROM clientes WHERE id = @mariano)
FROM DUAL WHERE @mariano IS NOT NULL;

INSERT INTO datos_factura (id_tarifa, consumo_p1, consumo_p2, consumo_p3, consumo_p4, consumo_p5, consumo_p6,
    potencia_contratada_p1, potencia_contratada_p2, potencia_contratada_p3,
    potencia_contratada_p4, potencia_contratada_p5, potencia_contratada_p6,
    dias, alquiler_contador, energia_reactiva, otros_conceptos, iva, impuesto_electrico, total_factura)
SELECT LAST_INSERT_ID(), 410.000, 520.000, 380.000, 290.000, 250.000, 610.000,
    25.000, 25.000, 25.000, 25.000, 25.000, 30.000,
    31, 1.62, 6.48, 0.00, 21, 5.11269632, 578.56
FROM DUAL WHERE @mariano IS NOT NULL;

-- Mariano Cacahuete: gas RL.2, 31 días.
INSERT INTO tarifas (id_cliente, tipo_suministro, peaje, nombre,
    termino_fijo, termino_variable, activa, creado_por)
SELECT @mariano, 'gas', 'RL.2', 'Endesa Gas (ejemplo)', 0.365000, 0.082000, 1,
    (SELECT creado_por FROM clientes WHERE id = @mariano)
FROM DUAL WHERE @mariano IS NOT NULL;

INSERT INTO datos_factura (id_tarifa, consumo_gas, dias, alquiler_contador, otros_conceptos,
    iva, total_factura)
SELECT LAST_INSERT_ID(), 980.000, 31, 1.20, 0.00, 21, 115.16
FROM DUAL WHERE @mariano IS NOT NULL;

-- Margarita Flores: luz 6.1TD, 30 días, con excesos y
-- otros conceptos.
INSERT INTO tarifas (id_cliente, tipo_suministro, peaje, nombre,
    energia_p1, energia_p2, energia_p3, energia_p4, energia_p5, energia_p6,
    potencia_p1, potencia_p2, potencia_p3, potencia_p4, potencia_p5, potencia_p6, activa, creado_por)
SELECT @margarita, 'luz', '6.1TD', 'Iberdrola Empresas Fijo (ejemplo)',
    0.149000, 0.133000, 0.116000, 0.103000, 0.091000, 0.081000,
    0.076000, 0.038000, 0.020000, 0.015500, 0.004500, 0.002300, 1,
    (SELECT creado_por FROM clientes WHERE id = @margarita)
FROM DUAL WHERE @margarita IS NOT NULL;

INSERT INTO datos_factura (id_tarifa, consumo_p1, consumo_p2, consumo_p3, consumo_p4, consumo_p5, consumo_p6,
    potencia_contratada_p1, potencia_contratada_p2, potencia_contratada_p3,
    potencia_contratada_p4, potencia_contratada_p5, potencia_contratada_p6,
    dias, alquiler_contador, energia_reactiva, excesos_potencia, otros_conceptos,
    iva, impuesto_electrico, total_factura)
SELECT LAST_INSERT_ID(), 9800.000, 11200.000, 8600.000, 7400.000, 6900.000, 15300.000,
    450.000, 450.000, 450.000, 450.000, 450.000, 500.000,
    30, 28.50, 42.30, 18.75, 35.00, 21, 5.11269632, 11207.36
FROM DUAL WHERE @margarita IS NOT NULL;

-- Margarita Flores: gas RL.4, 30 días.
INSERT INTO tarifas (id_cliente, tipo_suministro, peaje, nombre,
    termino_fijo, termino_variable, activa, creado_por)
SELECT @margarita, 'gas', 'RL.4', 'Repsol Gas Empresas (ejemplo)', 1.690000, 0.074000, 1,
    (SELECT creado_por FROM clientes WHERE id = @margarita)
FROM DUAL WHERE @margarita IS NOT NULL;

INSERT INTO datos_factura (id_tarifa, consumo_gas, dias, alquiler_contador, otros_conceptos,
    iva, total_factura)
SELECT LAST_INSERT_ID(), 12500.000, 30, 9.80, 0.00, 21, 1227.85
FROM DUAL WHERE @margarita IS NOT NULL;

-- Panini D'Agostini: gas RL.3, 30 días.
INSERT INTO tarifas (id_cliente, tipo_suministro, peaje, nombre,
    termino_fijo, termino_variable, activa, creado_por)
SELECT @panini, 'gas', 'RL.3', 'Iberdrola Gas Negocio (ejemplo)', 0.690000, 0.077000, 1,
    (SELECT creado_por FROM clientes WHERE id = @panini)
FROM DUAL WHERE @panini IS NOT NULL;

INSERT INTO datos_factura (id_tarifa, consumo_gas, dias, alquiler_contador, otros_conceptos,
    iva, total_factura)
SELECT LAST_INSERT_ID(), 2350.000, 30, 2.10, 0.00, 21, 253.19
FROM DUAL WHERE @panini IS NOT NULL;
