# Guía de Testing - Sistema de Envío de Correos

## Verificación de Instalación

### 1. Verificar que PHPMailer está instalado

```bash
# En la raíz del proyecto
ls -la vendor/phpmailer/phpmailer/

# O si tienes Composer:
composer show | grep phpmailer
```

### 2. Verificar que archivo .env existe

```bash
ls -la .env
```

## Pasos para Testing

### Opción A: Con Mailtrap (Recomendado para desarrollo)

**Mailtrap** es un simulador SMTP gratuito perfecto para testing sin enviar correos reales.

1. **Registrarse en Mailtrap**

   - Ve a https://mailtrap.io
   - Crea una cuenta gratuita
   - Inicia sesión

2. **Obtener credenciales SMTP**

   - En el dashboard, selecciona tu inbox
   - Abre "Integrations" → "PHP"
   - Copia los valores mostrados

3. **Actualizar .env**

   ```
   MAIL_HOST=smtp.mailtrap.io
   MAIL_PORT=465
   MAIL_ENCRYPTION=ssl
   MAIL_USERNAME=tu-usuario-mailtrap
   MAIL_PASSWORD=tu-password-mailtrap
   MAIL_FROM_ADDRESS=testing@example.com
   MAIL_FROM_NAME=Sistema de Encuesta
   APP_URL=http://localhost/encuesta_prueba
   ```

4. **Testing**
   - Abre http://localhost/encuesta_prueba/public/forgot-password.html
   - Ingresa un usuario válido (ej: "admin" o "admin@example.com")
   - Haz clic en "Enviar enlace"
   - Ve a https://mailtrap.io - el correo aparecerá en tu inbox de prueba

### Opción B: Con Gmail

⚠️ **Importante:** Debes usar una "Contraseña de Aplicación", no tu contraseña de Gmail.

1. **Habilitar acceso desde aplicaciones**

   - Abre: https://myaccount.google.com/apppasswords
   - Si no ves esa opción, habilita primero 2FA en tu cuenta

2. **Generar Contraseña de Aplicación**

   - Selecciona: Aplicación = Correo, Dispositivo = Windows/Linux/Mac
   - Copia la contraseña de 16 caracteres generada

3. **Actualizar .env**

   ```
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_ENCRYPTION=tls
   MAIL_USERNAME=tu-email@gmail.com
   MAIL_PASSWORD=abcd efgh ijkl mnop
   MAIL_FROM_ADDRESS=tu-email@gmail.com
   MAIL_FROM_NAME=Sistema de Encuesta
   APP_URL=http://localhost/encuesta_prueba
   ```

4. **Testing**
   - Abre http://localhost/encuesta_prueba/public/forgot-password.html
   - Ingresa tu email o usuario
   - Haz clic en "Enviar enlace"
   - Revisa tu bandeja de entrada de Gmail

### Opción C: Con SendGrid

1. **Crear cuenta en SendGrid**

   - Ve a https://sendgrid.com
   - Crea una cuenta gratuita

2. **Obtener API Key**

   - En el dashboard, ve a "Settings" → "API Keys"
   - Crea una nueva API Key (copia la clave completa)

3. **Actualizar .env**

   ```
   MAIL_HOST=smtp.sendgrid.net
   MAIL_PORT=587
   MAIL_ENCRYPTION=tls
   MAIL_USERNAME=apikey
   MAIL_PASSWORD=SG.tu-api-key-completa
   MAIL_FROM_ADDRESS=noreply@tudominio.com
   MAIL_FROM_NAME=Sistema de Encuesta
   APP_URL=http://localhost/encuesta_prueba
   ```

4. **Testing**
   - Mismo proceso que arriba

## Verificación del Flujo Completo

### 1. Testing: Solicitud de Restablecimiento

```
Entrada:
- Usuario: admin
- Email: admin@example.com

Resultado esperado:
✓ Mensaje: "Enlace de restablecimiento enviado a tu correo"
✓ Correo recibido en Mailtrap/Gmail/SendGrid
✓ Correo contiene enlace con token válido
```

### 2. Testing: Validación de Enlace

```
1. Copia el enlace del correo recibido
2. Abrelo en tu navegador: http://localhost/encuesta_prueba/public/forgot-password.html?token=abc123...
3. El formulario debe cambiar a Step 2 (campo de nueva contraseña)
```

### 3. Testing: Restablecimiento de Contraseña

```
Entrada:
- Nueva contraseña: MiNuevaContraseña123
- Confirmar contraseña: MiNuevaContraseña123

Resultado esperado:
✓ Mensaje: "Contraseña actualizada correctamente"
✓ Redirección a login.html
✓ Puedes iniciar sesión con la nueva contraseña
```

### 4. Testing: Token Expirado

```
1. Solicita restablecimiento normalmente
2. Espera 1 hora (o modifica reset_token_expires en BD a fecha pasada)
3. Intenta abrir el enlace
4. Resultado esperado: "Enlace inválido o expirado" + redirección a login
```

## Debugging

### Ver Logs

Los errores de correo se guardan en:

- **XAMPP Linux**: `/opt/lampp/logs/error_log`
- **XAMPP Windows**: `C:\xampp\apache\logs\error.log`
- **Apache genérico**: `/var/log/apache2/error.log`

Comando para ver en tiempo real:

```bash
# Linux/Mac
tail -f /ruta/al/error.log

# Windows (PowerShell)
Get-Content C:\xampp\apache\logs\error.log -Wait
```

### Prueba de Conexión SMTP

Crea un archivo `test-smtp.php`:

```php
<?php
require_once __DIR__ . '/server/MailSender.php';
require_once __DIR__ . '/server/bootstrap.php';

loadEnv(__DIR__ . '/.env');

try {
    $mailer = new MailSender();
    echo "✓ Conexión SMTP exitosa\n";
} catch (Exception $e) {
    echo "✗ Error de conexión: " . $e->getMessage() . "\n";
}
?>
```

Ejecuta:

```bash
php test-smtp.php
```

### Verificar Variables de Entorno

Crea un archivo `test-env.php`:

```php
<?php
require_once __DIR__ . '/server/bootstrap.php';
loadEnv(__DIR__ . '/.env');

echo "MAIL_HOST: " . ($_ENV['MAIL_HOST'] ?? 'NOT SET') . "\n";
echo "MAIL_PORT: " . ($_ENV['MAIL_PORT'] ?? 'NOT SET') . "\n";
echo "MAIL_USERNAME: " . ($_ENV['MAIL_USERNAME'] ?? 'NOT SET') . "\n";
echo "MAIL_FROM_ADDRESS: " . ($_ENV['MAIL_FROM_ADDRESS'] ?? 'NOT SET') . "\n";
echo "APP_URL: " . ($_ENV['APP_URL'] ?? 'NOT SET') . "\n";
?>
```

## Problemas Comunes

### "Connection refused" o "Network unreachable"

**Causa:** El servidor SMTP no es accesible

**Soluciones:**

1. Verifica HOST y PORT en .env
2. Abre terminal y prueba: `telnet smtp.gmail.com 587`
3. Revisa que el firewall permita puerto 587/465
4. Si usas VPN, desactívala temporalmente

### "Authentication failed"

**Causa:** Credenciales incorrectas

**Soluciones:**

1. Verifica usuario y contraseña en .env
2. Para Gmail, usa "Contraseña de Aplicación" (no tu contraseña)
3. Copia el valor exacto sin espacios en blanco
4. Revisa que el usuario esté habilitado en tu proveedor

### "Class 'PHPMailer' not found"

**Causa:** PHPMailer no instalado

**Soluciones:**

1. Ejecuta: `composer require phpmailer/phpmailer`
2. Verifica que `vendor/` exista en raíz del proyecto
3. Verifica que el autoloader (`vendor/autoload.php`) se carga correctamente

### Correos van a Spam

**Causa:** Faltan configuraciones de autenticación

**Soluciones:**

1. Configura SPF, DKIM y DMARC en tu dominio
2. Usa un email "from" que sea del mismo dominio que envía
3. Usa sendgrid o servicios especializados para producción

## Comandos Útiles

### Reinstalar dependencias

```bash
rm -rf vendor
rm composer.lock
composer install
```

### Limpiar caché de Composer

```bash
composer clear-cache
```

### Verificar versión de PHP

```bash
php -v
```

### Listar extensiones PHP instaladas

```bash
php -m | grep -i mail
```

## Checklist Pre-Producción

- [ ] PHPMailer instalado y funcional
- [ ] Archivo .env configurado con credenciales correctas
- [ ] Archivo .env está en .gitignore
- [ ] Testing completo del flujo realizado
- [ ] Logs funcionando correctamente
- [ ] HTTPS configurado en producción
- [ ] APP_URL apunta a dominio correcto
- [ ] Email "from" es válido del dominio
- [ ] Respuesta de error no revela detalles sensibles
- [ ] Token expiry configurado adecuadamente
