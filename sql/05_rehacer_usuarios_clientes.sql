/* -- -- =====================================================
-- -- PROYECTO: COMPARADORA
-- -- ARCHIVO: 05_rehacer_usuarios_clientes.sql
-- -- DESCRIPCIÓN: Ajuste de campos de Usuarios y Clientes
-- -- =====================================================

-- USE comparadora;


-- =====================================================
-- USUARIOS: NUEVO CAMPO "ESTADO"
-- =====================================================
--
-- Activo/Inactivo. Un usuario Inactivo no puede iniciar
-- sesión (ver procesar_login.php).

ALTER TABLE usuarios
    ADD COLUMN estado ENUM('Activo', 'Inactivo') NOT NULL DEFAULT 'Activo' AFTER id_rol;


-- =====================================================
-- CLIENTES: TABLA REHECHA POR COMPLETO
-- =====================================================
--
-- Se quitan tipo, comercializadora, tarifa y estado (la
-- tabla estaba vacía, así que no se pierde ningún dato
-- real). identificacion pasa a llamarse nif, correo pasa
-- a llamarse email, y se añaden apellidos, direccion y
-- telefono. Campos finales: nombre, apellidos, direccion,
-- telefono, email, nif.

ALTER TABLE clientes
    DROP COLUMN tipo,
    DROP COLUMN comercializadora,
    DROP COLUMN tarifa,
    DROP COLUMN estado,
    CHANGE COLUMN identificacion nif VARCHAR(20) NOT NULL,
    CHANGE COLUMN correo email VARCHAR(150) NOT NULL,
    ADD COLUMN apellidos VARCHAR(150) NOT NULL AFTER nombre,
    ADD COLUMN direccion VARCHAR(255) NULL AFTER apellidos,
    ADD COLUMN telefono VARCHAR(30) NULL AFTER direccion; */
