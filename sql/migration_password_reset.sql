-- Migration: Agregar columnas para restablecimiento de contraseña
ALTER TABLE admins ADD COLUMN reset_token VARCHAR(255) DEFAULT NULL;
ALTER TABLE admins ADD COLUMN reset_token_expires DATETIME DEFAULT NULL;
