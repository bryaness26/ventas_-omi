-- Script de creación de la base de datos y tablas para "Ñomi"
CREATE DATABASE IF NOT EXISTS `ventas_nomi` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ventas_nomi`;

-- Tabla de Usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `usuario` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `rol` VARCHAR(20) NOT NULL DEFAULT 'vendedor',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de Ventas
CREATE TABLE IF NOT EXISTS `ventas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `fecha_venta` DATE NOT NULL,
  `cliente` VARCHAR(100) NOT NULL,
  `cant_pequena` INT NOT NULL DEFAULT 0,
  `cant_grande` INT NOT NULL DEFAULT 0,
  `producto` VARCHAR(50) NOT NULL, -- "Pequeña", "Grande" o "Mixta"
  `cantidad` INT NOT NULL,
  `monto_total` DECIMAL(10, 2) NOT NULL, -- Guardado en dólares ($)
  `metodo_pago` VARCHAR(50) NOT NULL, -- Pago Móvil, Efectivo, Transferencia
  `usuario_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de Gastos
CREATE TABLE IF NOT EXISTS `gastos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `fecha_gasto` DATE NOT NULL,
  `producto_comprado` VARCHAR(150) NOT NULL,
  `cantidad` INT NOT NULL,
  `monto_total` DECIMAL(10, 2) NOT NULL, -- Guardado en dólares ($)
  `usuario_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar usuario administrador por defecto
-- Contraseña en hash para: admin123 (generada con password_hash en PHP)
-- Hash: $2y$10$P6wXXAOUvabgOmFvlMl4V.1ABmP.DiP/FrDTIXBdW.4okPeq1qUO2
INSERT INTO `usuarios` (`nombre`, `usuario`, `password`, `rol`)
VALUES ('Administrador Ñomi', 'admin', '$2y$10$P6wXXAOUvabgOmFvlMl4V.1ABmP.DiP/FrDTIXBdW.4okPeq1qUO2', 'administrador')
ON DUPLICATE KEY UPDATE `id`=`id`;
