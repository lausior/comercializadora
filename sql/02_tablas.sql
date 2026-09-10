/* -- -- =====================================================
-- -- PROYECTO: COMPARADORA
-- -- ARCHIVO: 02_tables.sql
-- -- DESCRIPCIÓN: Creación de tablas y relaciones
-- -- =====================================================

-- USE comparadora;


-- -- =====================================================
-- -- TABLA: EMPRESAS
-- -- =====================================================

-- CREATE TABLE empresas (
--     id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--     codigo_empresa INT UNSIGNED NOT NULL UNIQUE,
--     nombre VARCHAR(150) NOT NULL,
--     cif VARCHAR(20) NOT NULL UNIQUE,
--     direccion VARCHAR(255),
--     telefono VARCHAR(30),
--     email VARCHAR(150)
-- );


-- -- =====================================================
-- -- TABLA: ROLES
-- -- =====================================================

-- CREATE TABLE roles (
--     id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--     nombre VARCHAR(50) NOT NULL UNIQUE,
--     descripcion VARCHAR(255)
-- );


-- =====================================================
-- TABLA: USUARIOS
-- =====================================================

CREATE TABLE usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    username VARCHAR(50) NOT NULL UNIQUE,

    nombre VARCHAR(100) NOT NULL,

    apellidos VARCHAR(150) NOT NULL,

    email VARCHAR(150) NOT NULL UNIQUE,

    telefono VARCHAR(30),

    password VARCHAR(255) NOT NULL,

    cambiar_password TINYINT(1) NOT NULL DEFAULT 1,

    id_empresa INT UNSIGNED NOT NULL,

    id_rol INT UNSIGNED NOT NULL,

    CONSTRAINT fk_usuarios_empresa
        FOREIGN KEY (id_empresa)
        REFERENCES empresas(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_usuarios_rol
        FOREIGN KEY (id_rol)
        REFERENCES roles(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT

); */