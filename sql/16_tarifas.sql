-- -- =====================================================
-- -- PROYECTO: COMPARADORA
-- -- ARCHIVO: 16_tarifas.sql
-- -- DESCRIPCIÓN: Tarifas (precios) de cada comercializadora
-- -- =====================================================

-- USE comparadora;


-- =====================================================
-- TABLA: TARIFAS
-- =====================================================
--
-- Una fila por cada tarifa que ofrece una comercializadora
-- (por ejemplo, "Tarifa 12 meses" de Naturgy). Los precios
-- se introducen y se cambian a mano desde Tarifas (ver
-- views/tarifas/), y son los que después se cruzan con los
-- datos de la factura de un cliente para compararlas.
--
-- El peaje decide cuántas franjas se usan (ver PEAJES en
-- includes/tarifas.php):
--   2.0TD -> energía P1-P3, potencia P1-P3
--   3.0TD -> energía P1-P6, potencia P1-P6
--   6.1TD -> energía P1-P6, potencia P1-P6
-- Las franjas que el peaje no usa se guardan a NULL.
--
-- Unidades: energía y excedentes en €/kWh, potencia en
-- €/kW·día. precio_excedentes a NULL = la tarifa no
-- compensa excedentes.
--
-- activa: solo las tarifas activas entran en las
-- comparativas; así una tarifa que la comercializadora deja
-- de ofrecer puede apartarse sin borrarla.
--
-- Por ahora solo se usan tarifas de luz: las de gas tienen
-- otra estructura (peajes RL, término fijo/variable) y
-- tendrán su propia definición más adelante.

CREATE TABLE tarifas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    id_comercializadora INT UNSIGNED NOT NULL,

    tipo_suministro ENUM('luz', 'gas') NOT NULL DEFAULT 'luz',

    nombre VARCHAR(150) NOT NULL,

    peaje ENUM('2.0TD', '3.0TD', '6.1TD') NOT NULL,

    energia_p1 DECIMAL(10,6) NULL,
    energia_p2 DECIMAL(10,6) NULL,
    energia_p3 DECIMAL(10,6) NULL,
    energia_p4 DECIMAL(10,6) NULL,
    energia_p5 DECIMAL(10,6) NULL,
    energia_p6 DECIMAL(10,6) NULL,

    potencia_p1 DECIMAL(10,6) NULL,
    potencia_p2 DECIMAL(10,6) NULL,
    potencia_p3 DECIMAL(10,6) NULL,
    potencia_p4 DECIMAL(10,6) NULL,
    potencia_p5 DECIMAL(10,6) NULL,
    potencia_p6 DECIMAL(10,6) NULL,

    precio_excedentes DECIMAL(10,6) NULL,

    activa TINYINT(1) NOT NULL DEFAULT 1,

    creado_por INT UNSIGNED,

    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    actualizado_en DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_tarifas_comercializadora
        FOREIGN KEY (id_comercializadora)
        REFERENCES comercializadoras(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_tarifas_creado_por
        FOREIGN KEY (creado_por)
        REFERENCES usuarios(id)
        ON DELETE SET NULL

);
