DROP DATABASE IF EXISTS finanzas_app;

CREATE DATABASE finanzas_app
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE finanzas_app;

SET NAMES utf8mb4;

-- =========================
-- USUARIOS
-- =========================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    email VARCHAR(150) UNIQUE,
    password VARCHAR(255),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =========================
-- NEGOCIOS
-- =========================
CREATE TABLE negocios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    nombre VARCHAR(100),
    tipo ENUM('personal', 'negocio') DEFAULT 'negocio',
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =========================
-- CUENTAS
-- =========================
CREATE TABLE cuentas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    negocio_id INT NULL,
    nombre VARCHAR(100),
    tipo ENUM('banco', 'efectivo', 'ahorro', 'virtual') DEFAULT 'banco',
    saldo DECIMAL(12,2) DEFAULT 0,
    moneda VARCHAR(10) DEFAULT 'CRC',
    activo BOOLEAN DEFAULT TRUE,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (negocio_id) REFERENCES negocios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =========================
-- SUBCUENTAS
-- =========================
CREATE TABLE subcuentas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cuenta_id INT,
    nombre VARCHAR(100),
    descripcion TEXT,
    saldo DECIMAL(12,2) DEFAULT 0,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cuenta_id) REFERENCES cuentas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =========================
-- CATEGORIAS
-- =========================
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    nombre VARCHAR(100),
    tipo ENUM('ingreso', 'gasto'),
    color VARCHAR(20),
    icono VARCHAR(50),
    es_default BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =========================
-- TRANSACCIONES
-- =========================
CREATE TABLE transacciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    negocio_id INT,
    cuenta_id INT,
    tipo ENUM('ingreso', 'gasto', 'transferencia'),
    monto DECIMAL(12,2),
    fecha DATETIME,
    descripcion TEXT,
    categoria_id INT NULL,
    cuenta_destino_id INT NULL,
    estado ENUM('completado', 'pendiente') DEFAULT 'completado',
    es_recurrente BOOLEAN DEFAULT FALSE,
    recordatorio_id INT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (negocio_id) REFERENCES negocios(id) ON DELETE CASCADE,
    FOREIGN KEY (cuenta_id) REFERENCES cuentas(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL,
    FOREIGN KEY (cuenta_destino_id) REFERENCES cuentas(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =========================
-- ETIQUETAS
-- =========================
CREATE TABLE etiquetas_transacciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaccion_id INT,
    etiqueta VARCHAR(50),
    FOREIGN KEY (transaccion_id) REFERENCES transacciones(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =========================
-- DISTRIBUCIONES
-- =========================
CREATE TABLE distribuciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaccion_id INT,
    subcuenta_id INT,
    monto DECIMAL(12,2),
    FOREIGN KEY (transaccion_id) REFERENCES transacciones(id) ON DELETE CASCADE,
    FOREIGN KEY (subcuenta_id) REFERENCES subcuentas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =========================
-- CLIENTES
-- =========================
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    negocio_id INT,
    nombre VARCHAR(100),
    telefono VARCHAR(20),
    email VARCHAR(100),
    notas TEXT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (negocio_id) REFERENCES negocios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =========================
-- RECORDATORIOS (aquí te corrijo el diseño)
-- =========================
CREATE TABLE recordatorios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    negocio_id INT,
    tipo ENUM('pagar', 'cobrar'),
    nombre VARCHAR(100),
    monto DECIMAL(12,2),
    fecha_vencimiento DATETIME,
    es_automatico BOOLEAN DEFAULT FALSE,
    frecuencia ENUM('ninguna', 'diario', 'semanal', 'mensual', 'anual') DEFAULT 'ninguna',
    categoria_id INT NULL,
    cuenta_id INT NULL,
    subcuenta_id INT NULL, -- 🔥 agregado (te evita errores futuros)
    cliente_id INT NULL,   -- 🔥 agregado (ya lo estabas usando antes)
    estado ENUM('pendiente', 'pagado', 'vencido') DEFAULT 'pendiente',
    ultima_ejecucion DATETIME NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (negocio_id) REFERENCES negocios(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL,
    FOREIGN KEY (cuenta_id) REFERENCES cuentas(id) ON DELETE SET NULL,
    FOREIGN KEY (subcuenta_id) REFERENCES subcuentas(id) ON DELETE SET NULL,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =========================
-- PAGOS CLIENTES
-- =========================
CREATE TABLE pagos_clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT,
    usuario_id INT,
    negocio_id INT,
    monto DECIMAL(12,2),
    fecha DATETIME,
    estado ENUM('pagado', 'pendiente') DEFAULT 'pendiente',
    descripcion TEXT,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (negocio_id) REFERENCES negocios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;