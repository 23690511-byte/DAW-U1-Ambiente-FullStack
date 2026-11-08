-- =============================================================================
-- Practica 4: CRUD de Usuarios y Servicios con Vue, PHP, PDO y MySQL
-- =============================================================================

-- Actualización de la tabla usuarios
CREATE TABLE IF NOT EXISTS usuarios (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario VARCHAR(50) NOT NULL UNIQUE,
  nombre VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  rol ENUM('ADMIN_SISTEMA','ADMIN_SERVICIOS','CLIENTE') NOT NULL DEFAULT 'CLIENTE',
  activo TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Creación de tabla servicios
CREATE TABLE IF NOT EXISTS servicios (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  descripcion TEXT NOT NULL,
  precio DECIMAL(10,2) NOT NULL,
  duracion_minutos INT UNSIGNED NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Registros iniciales de prueba para servicios
INSERT INTO servicios (nombre, descripcion, precio, duracion_minutos, activo)
VALUES
('Desarrollo Web', 'Desarrollo de aplicaciones web empresariales', 8500.00, 120, 1),
('Soporte Técnico', 'Servicio de soporte tecnológico', 3500.00, 60, 1),
('Consultoría', 'Consultoría para proyectos tecnológicos', 5000.00, 90, 1),
('Capacitación', 'Capacitación especializada', 2500.00, 120, 0)
ON DUPLICATE KEY UPDATE id=id;
