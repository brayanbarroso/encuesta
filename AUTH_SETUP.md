# Sistema de Autenticación con Hash de Contraseñas

## 1. Crear la tabla de administradores

Ejecuta el SQL en tu BD (phpMyAdmin o cliente MySQL):

```sql
CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  email VARCHAR(150),
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Admin por defecto (usuario: admin, contraseña: admin123)
INSERT IGNORE INTO admins (username, email, password_hash) VALUES
('admin', 'admin@cooeducord.local', '$2y$10$YJl8sQCCHWJ8XkNJ4R5k.uQh0Q.gHV8ZrXvNq6qF8LUKh8Z8KiEI6');
```

## 2. Credenciales por defecto

- **Usuario**: `admin`
- **Contraseña**: `admin123`

## 3. Gestionar usuarios

### Opción A: Interfaz Web (recomendado)

Abre en navegador: **http://localhost/encuesta_prueba/server/admin_setup.php**

Opciones:

- **Generar Hash**: Introduce una contraseña y obtén su hash bcrypt
- **Resetear admin**: Vuelve a establecer admin@admin123
- **Crear Nuevo Usuario**: Crea un nuevo usuario administrador

### Opción B: SQL Manual

Para crear un nuevo usuario:

1. Genera el hash:

   ```php
   echo password_hash('tucontraseña', PASSWORD_BCRYPT);
   ```

2. Inserta en la BD:

   ```sql
   INSERT INTO admins (username, email, password_hash) VALUES
   ('usuario2', 'usuario2@example.com', '<hash_generado>');
   ```

3. Para cambiar contraseña:
   ```sql
   UPDATE admins SET password_hash = '<hash_nuevo>' WHERE username = 'admin';
   ```

## 4. Cambiar contraseña de un usuario existente

1. Ve a: http://localhost/encuesta_prueba/server/admin_setup.php?action=hash
2. Introduce la nueva contraseña y copia el hash
3. Ejecuta en phpMyAdmin:
   ```sql
   UPDATE admins SET password_hash = '<hash>' WHERE username = 'usuario';
   ```

## 5. Detalles técnicos

- **Algoritmo**: bcrypt (PASSWORD_BCRYPT)
- **Verificación**: `password_verify($pass, $hash)` en PHP
- **Seguridad**: Las contraseñas nunca se almacenan en texto plano
- **Timeout de sesión**: 30 minutos sin actividad

## 6. Cambios en el código

- `server/login.php`: Ahora consulta tabla `admins` y verifica con `password_verify()`
- `server/check_session.php`: Valida sesión activa (sin cambios)
- `server/logout.php`: Destruye sesión (sin cambios)

## Troubleshooting

**Error: "Table 'coo_survey.admins' doesn't exist"**
→ Asegúrate de ejecutar el SQL anterior en phpMyAdmin

**Error: "Usuario o contraseña incorrectos"**
→ Verifica que el usuario existe en la tabla `admins`
→ Confirm el hash bcrypt está correcto (comienza con `$2y$`)

**Olvié la contraseña del admin**
→ Ejecuta: http://localhost/encuesta_prueba/server/admin_setup.php?action=reset
→ Vuelve a: admin / admin123
