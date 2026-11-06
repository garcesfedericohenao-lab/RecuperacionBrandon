-- Base de datos del sistema de mantenimiento de vehículos
CREATE DATABASE IF NOT EXISTS mantenimiento_vehiculos
  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE mantenimiento_vehiculos;

-- Tabla de vehículos (la placa es UNIQUE: no se puede repetir)
CREATE TABLE IF NOT EXISTS vehiculos (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  placa       VARCHAR(10)  NOT NULL UNIQUE,
  marca       VARCHAR(50)  NOT NULL,
  modelo      VARCHAR(50)  NOT NULL,
  propietario VARCHAR(100) NOT NULL
);

-- Tabla de mantenimientos (cada uno pertenece a un vehículo)
-- Si se borra el vehículo, se borran también sus mantenimientos
CREATE TABLE IF NOT EXISTS mantenimientos (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  vehiculo_id INT NOT NULL,
  tipo        VARCHAR(60) NOT NULL,
  costo       DECIMAL(10,2) NOT NULL,
  estado      ENUM('Pendiente','En proceso','Terminado') NOT NULL DEFAULT 'Pendiente',
  fecha       DATE NOT NULL,
  FOREIGN KEY (vehiculo_id) REFERENCES vehiculos(id) ON DELETE CASCADE
);

-- Datos de ejemplo (opcional)
INSERT INTO vehiculos (placa, marca, modelo, propietario) VALUES
  ('ABC123', 'Chevrolet', 'Spark 2018', 'Laura Gómez'),
  ('XYZ789', 'Renault', 'Logan 2020', 'Carlos Ruiz');

INSERT INTO mantenimientos (vehiculo_id, tipo, costo, estado, fecha) VALUES
  (1, 'Cambio de aceite', 85000.00, 'Terminado', CURDATE()),
  (2, 'Revisión de frenos', 160000.00, 'En proceso', CURDATE());
