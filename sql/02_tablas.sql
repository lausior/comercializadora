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

);


-- =====================================================
-- TABLA: CLIENTES
-- =====================================================

CREATE TABLE clientes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    nombre VARCHAR(150) NOT NULL,

    tipo ENUM('Particular', 'Empresa') NOT NULL,

    identificacion VARCHAR(20) NOT NULL UNIQUE,

    correo VARCHAR(150) NOT NULL,

    comercializadora VARCHAR(100) NOT NULL,

    tarifa ENUM('PVPC', 'Mercado libre', 'Tarifa fija') NOT NULL,

    estado ENUM('Activo', 'Pendiente', 'Inactivo') NOT NULL DEFAULT 'Activo',

    creado_por INT UNSIGNED,

    CONSTRAINT fk_clientes_creado_por
        FOREIGN KEY (creado_por)
        REFERENCES usuarios(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL

);


-- =====================================================
-- TABLA: LOGS
-- =====================================================

CREATE TABLE logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    tipo ENUM('Información', 'Éxito', 'Advertencia', 'Error') NOT NULL,

    id_usuario INT UNSIGNED,

    -- Usuario y rol se guardan como texto (no solo el id) porque
    -- un log es un registro histórico: no debe cambiar si el
    -- usuario se borra o cambia de rol más adelante. El rol es
    -- además lo que usa rolesVisiblesEnLogs() (config/permisos.php)
    -- para decidir qué filas puede ver cada rol en el listado.
    usuario VARCHAR(50) NOT NULL,

    rol VARCHAR(20) NOT NULL,

    evento VARCHAR(100) NOT NULL,

    descripcion VARCHAR(255) NOT NULL,

    ip VARCHAR(45) NOT NULL,

    CONSTRAINT fk_logs_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL

);


-- =====================================================
-- TABLA: TAREAS
-- =====================================================

CREATE TABLE tareas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    titulo VARCHAR(150) NOT NULL,

    area ENUM('Tarifas', 'Clientes', 'Incidencias', 'Comparador', 'Sistema') NOT NULL,

    fecha DATE NOT NULL,

    hora TIME NULL,

    -- Texto libre, no una cuenta real de `usuarios`: quien
    -- crea la tarea puede asignarla a cualquier nombre (por
    -- ejemplo, alguien que todavía no tiene usuario en el
    -- sistema).
    responsable VARCHAR(100),

    estado ENUM('Pendiente', 'En curso', 'Completada') NOT NULL DEFAULT 'Pendiente',

    creado_por INT UNSIGNED,

    CONSTRAINT fk_tareas_creado_por
        FOREIGN KEY (creado_por)
        REFERENCES usuarios(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL

); */