# Implementación: Sistema de Envío de Correos para Restablecimiento de Contraseña

## ✅ Completado

Se ha implementado un sistema profesional de envío de correos usando **PHPMailer** para el restablecimiento de contraseñas.

### Archivos Creados

1. **`server/MailSender.php`** - Clase principal para envío de correos

   - Configuración automática de SMTP
   - Template HTML profesional para correos
   - Manejo de excepciones y logs
   - Método: `sendPasswordReset($email, $resetLink, $username)`

2. **`server/config/mail.php`** - Configuración centralizada de correo

   - Soporta variables de entorno
   - Fácil de extender para otros tipos de correo

3. **`server/bootstrap.php`** - Cargador de variables de entorno

   - Función `loadEnv()` que lee archivo `.env`
   - Compatible con cualquier archivo de configuración

4. **`.env`** - Configuración con ejemplos para 3 proveedores

   - Gmail (con Contraseña de Aplicación)
   - Mailtrap (para testing)
   - SendGrid

5. **`.env.example`** - Plantilla de configuración

   - Documentado con instrucciones
   - Agregar a control de versiones

6. **`README_MAIL.md`** - Guía completa de instalación

   - Instrucciones por proveedor
   - Troubleshooting
   - Seguridad

7. **`MAIL_TESTING.md`** - Guía de testing y debugging

   - Pasos para testing con Mailtrap, Gmail, SendGrid
   - Verificación del flujo completo
   - Debugging y logs

8. **`install-mail.sh`** - Script de instalación para Linux/Mac
9. **`install-mail.ps1`** - Script de instalación para Windows PowerShell

### Archivos Modificados

1. **`server/request_password_reset.php`**

   - Ahora carga variables de entorno
   - Integra MailSender para envío real
   - Mantiene respuesta segura (no revela si usuario existe)
   - Logs de errores para debugging

2. **`public/assets/js/forgot-password.js`**

   - Elimina debug_link de respuesta normal
   - Solo muestra debug_link si está presente (modo desarrollo)
   - Simplificado para producción

3. **`composer.json`**

   - Agregada dependencia: `phpmailer/phpmailer ^7.0`

4. **`.gitignore`**
   - Agregado: `.env`, `vendor/`, `.env.local`
   - Protege credenciales sensibles

## 🚀 Inicio Rápido

### Paso 1: Configurar Proveedor de Correo

**Opción A: Gmail (Recomendado para usuarios personales)**

```
1. Ve a https://myaccount.google.com/apppasswords
2. Selecciona "Correo" y tu dispositivo
3. Copia la contraseña generada (16 caracteres)
4. Edita .env:
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_ENCRYPTION=tls
   MAIL_USERNAME=tu-email@gmail.com
   MAIL_PASSWORD=abcd efgh ijkl mnop
   MAIL_FROM_ADDRESS=tu-email@gmail.com
```

**Opción B: Mailtrap (Recomendado para testing)**

```
1. Regístrate en https://mailtrap.io
2. Copia credenciales de tu inbox
3. Edita .env con valores de Mailtrap
4. Los correos se verán en Mailtrap, no se envían reales
```

### Paso 2: Verificar Instalación

PHPMailer ya está instalado. Verifica:

```bash
ls vendor/phpmailer/phpmailer/
# Debe existir la carpeta
```

### Paso 3: Probar Flujo

1. Abre: http://localhost/encuesta_prueba/public/forgot-password.html
2. Ingresa un usuario válido (ej: "admin" o "admin@example.com")
3. Haz clic en "Enviar enlace"
4. Revisa tu correo/Mailtrap
5. Haz clic en el enlace del correo
6. Ingresa nueva contraseña

## 📧 Template de Correo

El correo se envía en HTML con:

- Header profesional (azul)
- Botón de acción "Restablecer Contraseña"
- Enlace directo (para clientes que no soportan botones)
- Aviso de expiración (1 hora)
- Footer con info y copyright

**Ventajas:**

- Compatible con todos los clientes de correo
- Responsive (mobile-friendly)
- Branding personalizable
- Texto alternativo para clientes sin HTML

## 🔐 Seguridad

### Implementado:

- ✅ Tokens seguros (bin2hex + random_bytes)
- ✅ Expiry de tokens (1 hora)
- ✅ No revela si usuario existe
- ✅ Logs de errores (no se muestra al usuario)
- ✅ Credenciales en .env (no en código)
- ✅ .gitignore previene que .env se suba a git

### Recomendaciones Producción:

- Usar HTTPS obligatoriamente
- Cambiar token expiry si es necesario
- Configurar SPF/DKIM/DMARC en tu dominio
- Usar servicio especializado (SendGrid, AWS SES) para mejor deliverability
- Monitorear logs regularmente

## 📊 Variables de Entorno

```
MAIL_HOST        - Host SMTP (ej: smtp.gmail.com)
MAIL_PORT        - Puerto SMTP (587 para TLS, 465 para SSL)
MAIL_ENCRYPTION  - Tipo: tls o ssl
MAIL_USERNAME    - Usuario SMTP
MAIL_PASSWORD    - Contraseña SMTP
MAIL_FROM_ADDRESS- Email remitente (debe ser válido)
MAIL_FROM_NAME   - Nombre que aparece en "De:"
APP_URL          - URL base de tu aplicación
```

## 🐛 Debugging

### Ver Logs

```bash
# En XAMPP Windows:
type C:\xampp\apache\logs\error.log

# En Linux:
tail -f /var/log/apache2/error.log
```

### Verificar Conexión SMTP

```bash
php -r "
require 'server/bootstrap.php';
require 'server/MailSender.php';
loadEnv('.env');
try {
    new MailSender();
    echo 'OK';
} catch (Exception \$e) {
    echo 'ERROR: ' . \$e->getMessage();
}
"
```

## 📋 Checklist

- [x] PHPMailer instalado
- [x] Configuración SMTP separada en archivo
- [x] Bootstrap loader de .env
- [x] MailSender con template HTML
- [x] Integración en request_password_reset.php
- [x] Documentación completa
- [x] .gitignore actualizado
- [ ] Configurar .env con tus credenciales SMTP
- [ ] Probar flujo completo
- [ ] En producción: usar HTTPS

## 🤝 Soporte

Consulta:

- **README_MAIL.md** - Guía de instalación y configuración
- **MAIL_TESTING.md** - Testing, debugging y troubleshooting
- **MailSender.php** - Código fuente documentado

## Próximas Mejoras (Opcionales)

- [ ] Agregar plantillas adicionales (bienvenida, notificaciones)
- [ ] Queue system para envíos asincronos
- [ ] Tracking de envíos (open rate, clicks)
- [ ] Retry automático si falla primer intento
- [ ] Soporte para múltiples proveedores SMTP
