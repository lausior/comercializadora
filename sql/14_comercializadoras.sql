/* -- -- =====================================================
-- -- PROYECTO: COMPARADORA
-- -- ARCHIVO: 14_comercializadoras.sql
-- -- DESCRIPCIÓN: Tabla para el listado de Comercializadoras
-- -- =====================================================

-- USE comparadora;


-- =====================================================
-- TABLA: COMERCIALIZADORAS
-- =====================================================
--
-- A diferencia de Empresas, una comercializadora no tiene
-- acceso de login propio ni estado Activo/Inactivo: es solo
-- una ficha de datos (nombre, CIF, dirección, teléfono, email)
-- que gestionan SRG, NG y EMPRESA desde su propio listado
-- (ver views/comercializadoras/).
--
-- creado_por sigue el mismo criterio que en clientes/tareas:
-- SRG ve todas las comercializadoras, NG y EMPRESA solo las
-- que ellos mismos han creado (ver puedeVerComercializadora()
-- en config/permisos.php).

-- CREATE TABLE comercializadoras (
--     id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--
--     nombre VARCHAR(150) NOT NULL,
--
--     cif VARCHAR(20) NOT NULL UNIQUE,
--
--     direccion VARCHAR(255),
--
--     telefono VARCHAR(30),
--
--     email VARCHAR(150),
--
--     creado_por INT UNSIGNED,
--
--     CONSTRAINT fk_comercializadoras_creado_por
--         FOREIGN KEY (creado_por)
--         REFERENCES usuarios(id)
--         ON DELETE SET NULL
--
-- ); */
