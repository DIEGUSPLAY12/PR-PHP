-- Esquema
CREATE TABLE IF NOT EXISTS attractions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  description TEXT,
  image_url VARCHAR(255) DEFAULT NULL,
  maintenance TINYINT(1) NOT NULL DEFAULT 0,
  duration_minutes INT DEFAULT NULL,
  min_height_cm INT DEFAULT NULL,
  category VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ticket_types (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(50) UNIQUE NOT NULL,
  label VARCHAR(100) NOT NULL,
  price DECIMAL(8,2) NOT NULL,
  description VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  buyer_email VARCHAR(150) NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'PENDING',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  ticket_type_id INT NOT NULL,
  quantity INT NOT NULL,
  unit_price DECIMAL(8,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (ticket_type_id) REFERENCES ticket_types(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Semilla mínima (ajústala a tu gusto)
INSERT INTO ticket_types (code,label,price,description) VALUES
('ADULT','Adulto',25.00,'Entrada general adulto'),
('CHILD','Nino (4-12)',15.00,'Entrada reducida para ninos'),
('SENIOR','Senior',18.00,'Entrada para mayores de 65')
ON DUPLICATE KEY UPDATE price=VALUES(price), label=VALUES(label), description=VALUES(description);

INSERT INTO attractions (name, description, image_url, maintenance, duration_minutes, min_height_cm, category) VALUES
('Estadio del Gol', 'Tour completo por el estadio legendario', NULL, 0, 90, NULL, 'Tour'),
('Tunel de los Campeones', 'Recorrido por la historia del futbol', NULL, 1, 30, NULL, 'Museo'),
('Carrusel de Balones', 'Carrusel familiar con balones gigantes', NULL, 0, 5, NULL, 'Familiar'),
('Penalti Challenge', 'Prueba tu precision como delantero', NULL, 0, 15, NULL, 'Interactivo'),
('Simulador Champions', 'Simulador 4D de partidos epicos', NULL, 0, 20, NULL, 'Simulador'),
('Rio del Mundial', 'Paseo acuatico entre copas del mundo', NULL, 0, 10, NULL, 'Acuatico'),
('Montana Rusa 90 Minutos', 'Montana rusa tematica de futbol', NULL, 1, 90, 130, 'Montana Rusa'),
('Zona Infantil Futbolin', 'Juegos y mini-campos para ninos', NULL, 0, NULL, NULL, 'Infantil'),
('La Chilena Extrema', 'Pendulo gigante estilo remate acrobatico', NULL, 0, 3, 125, 'Adrenalina'),
('Noria del Trofeo', 'Noria panoramica con vistas al parque', NULL, 0, 15, NULL, 'Familiar');
