# Configuración de Envío de Correos - Sistema de Encuesta

## Requisitos

1. **Composer** instalado en tu servidor
2. **PHPMailer** (se instala vía Composer)
3. Configuración SMTP válida (Gmail, SendGrid, tu servidor de correo, etc.)

## Instalación de PHPMailer

Ejecuta en la raíz del proyecto:

```bash
composer require phpmailer/phpmailer
```

## Configuración

### 1. Crear archivo `.env`

Copia el archivo `.env.example` y renómbralo a `.env`:

```bash
cp .env.example .env
```

### 2. Configurar variables SMTP

Edita el archivo `.env` con tus credenciales SMTP:

```
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=contraseña-app
MAIL_FROM_ADDRESS=noreply@encuesta.com
MAIL_FROM_NAME=Sistema de Encuesta
APP_URL=https://tudominio.com/encuesta_prueba
```

### 3. Ejemplos de Configuración por Proveedor

#### Gmail

```
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=contraseña-de-aplicacion
```

**Pasos en Gmail:**

1. Ve a https://myaccount.google.com/apppasswords
2. Selecciona "Correo" y "Windows"
3. Copia la contraseña generada en MAIL_PASSWORD

#### SendGrid

```
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=apikey
MAIL_PASSWORD=tu-api-key-sendgrid
```

#### Office 365

```
MAIL_HOST=smtp.office365.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=tu-email@tuempresa.onmicrosoft.com
MAIL_PASSWORD=tu-contraseña
```

#### SMTP Personalizado (Hosting)

Consulta con tu proveedor de hosting por los datos SMTP.

## Uso

El sistema se usa automáticamente cuando:

1. Usuario solicita restablecimiento de contraseña en `/public/forgot-password.html`
2. Se ejecuta `POST /server/request_password_reset.php`
3. Un correo HTML profesional se envía al usuario

## Archivos Agregados

- `server/MailSender.php` - Clase para envío de correos con PHPMailer
- `server/config/mail.php` - Configuración centralizada de correo
- `server/bootstrap.php` - Cargador de variables de entorno
- `.env.example` - Ejemplo de configuración
- `README_MAIL.md` - Este archivo

## Desarrollo y Testing

Para testear sin producción:

### Opción 1: Usar Mailtrap (simulador SMTP)

1. Regístrate en https://mailtrap.io
2. Crea una inbox de prueba
3. Copia las credenciales SMTP a `.env`
4. Los correos se verán en Mailtrap sin ser reales

### Opción 2: Ver logs

Los errores de correo se registran en:

```
/var/log/apache2/error.log  (en Linux)
%APACHE_HOME%/logs/error.log (en Windows con XAMPP)
```

## Troubleshooting

### "SMTP Connection refused"

- Verifica que MAIL_HOST y MAIL_PORT sean correctos
- Revisa que el firewall permita puerto 587 (o 465 para SSL)
- Prueba conectar manualmente: `telnet smtp.gmail.com 587`

### "Autenticación fallida"

- Confirma usuario y contraseña en `.env`
- Para Gmail, asegúrate de usar "Contraseña de Aplicación", no tu contraseña
- Revisa que el usuario SMTP esté habilitado en tu proveedor

### No recibo correos

- Revisa la carpeta Spam/Basura
- Verifica que MAIL_FROM_ADDRESS sea un email válido del dominio/proveedor
- Revisa logs de error

### "Class 'PHPMailer' not found"

- Ejecuta: `composer require phpmailer/phpmailer`
- Asegúrate de que `vendor/autoload.php` se carga (está en `MailSender.php`)

## Seguridad

⚠️ **IMPORTANTE:**

- **Nunca** compartas el archivo `.env` (agrégalo a `.gitignore`)
- **No** guardes contraseñas en código
- Usa variables de entorno o gestor de secretos
- En producción, usa HTTPS para toda comunicación

## Monitoreo

Para monitorear envíos de correo, agrega logs:

```php
// En MailSender.php, dentro de sendPasswordReset()
error_log("Email sent to: $toEmail | Token expires: $expiresAt");
```

Luego consulta `error.log` de Apache.
