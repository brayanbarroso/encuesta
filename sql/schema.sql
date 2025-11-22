CREATE DATABASE IF NOT EXISTS cooeducord_encuesta CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cooeducord_encuesta;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  identificacion VARCHAR(60) NOT NULL UNIQUE,
  nombre VARCHAR(200) NOT NULL,
  email VARCHAR(150),
  telefono VARCHAR(20),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE responses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  identificacion VARCHAR(60) NOT NULL,
  data JSON NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_ident ON responses(identificacion);
