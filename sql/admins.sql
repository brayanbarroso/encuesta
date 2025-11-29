-- Tabla para administradores/usuarios con acceso a reportes
CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  email VARCHAR(150),
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Admin por defecto (usuario: admin, contraseña: admin123)
-- Hash generado con: password_hash('admin123', PASSWORD_BCRYPT)
INSERT IGNORE INTO admins (username, email, password_hash) VALUES 
('admin', 'admin@cooeducord.com', '$2y$10$YJl8sQCCHWJ8XkNJ4R5k.uQh0Q.gHV8ZrXvNq6qF8LUKh8Z8KiEI6');

-- Puedes crear más usuarios ejecutando:
-- INSERT INTO admins (username, email, password_hash) VALUES ('user2', 'user2@example.com', '<hash>');
-- Para generar un hash de una contraseña, usa: password_hash('tucontraseña', PASSWORD_BCRYPT)
