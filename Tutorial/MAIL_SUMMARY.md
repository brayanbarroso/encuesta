# 📧 Sistema de Envío de Correos - Resumen Implementación

## 🎯 Objetivo Completado

✅ **Sistema profesional de envío de correos para restablecimiento de contraseña** usando PHPMailer con soporte para múltiples proveedores SMTP.

## 📁 Archivos Creados (9 nuevos)

```
server/
├── MailSender.php              ← Clase principal con PHPMailer
└── config/
    └── mail.php                ← Configuración SMTP centralizada

server/
└── bootstrap.php               ← Cargador de variables .env

.env                            ← Configuración actual (no subir a git)
.env.example                    ← Plantilla de configuración

README_MAIL.md                  ← Guía instalación por proveedor
MAIL_TESTING.md                 ← Guía testing y debugging
MAIL_IMPLEMENTATION.md          ← Este documento

install-mail.sh                 ← Script instalación Linux/Mac
install-mail.ps1                ← Script instalación Windows

test-email-setup.php            ← Herramienta testing web
```

## 📝 Archivos Modificados (4 existentes)

```
server/
└── request_password_reset.php  ← Ahora envía correo real

public/assets/js/
└── forgot-password.js          ← Limpiado para producción

composer.json                   ← Agregada dependencia PHPMailer
.gitignore                      ← Protege credenciales
```

## 🔌 Arquitectura

```
Usuario Solicita Reset
         ↓
forgot-password.html (formulario)
         ↓
request_password_reset.php (genera token)
         ↓
MailSender::sendPasswordReset()
         ↓
PHPMailer (envía vía SMTP)
         ↓
Proveedor SMTP (Gmail, SendGrid, etc)
         ↓
Email entregado al usuario
         ↓
Usuario abre link y reset contraseña
```

## 🛠️ Configuración Rápida

### Opción 1: Gmail (Más fácil)

```bash
# 1. Ve a https://myaccount.google.com/apppasswords
# 2. Selecciona Correo + tu dispositivo
# 3. Copia contraseña de 16 caracteres
# 4. Edita .env:

MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=xxxx xxxx xxxx xxxx
MAIL_FROM_ADDRESS=tu-email@gmail.com
APP_URL=http://localhost/encuesta_prueba
```

### Opción 2: Mailtrap (Testing)

```bash
# 1. Regístrate en https://mailtrap.io (free)
# 2. Copia credenciales SMTP de tu inbox
# 3. Edita .env con valores de Mailtrap
# 4. Todos los correos se ven en Mailtrap (no reales)
```

## ✅ Verificaciones Pre-Deploy

```
✓ PHPMailer instalado (vendor/phpmailer/)
✓ .env configurado con credenciales SMTP
✓ .env está en .gitignore (credenciales protegidas)
✓ bootstrap.php puede cargar variables .env
✓ MailSender.php se instancia sin errores
✓ Template HTML se genera correctamente
✓ Correo test enviado exitosamente
```

## 🧪 Testing (Recomendado)

```bash
# Opción 1: Interfaz web
# Abre en navegador: http://localhost/encuesta_prueba/test-email-setup.php
# Verifica todas las pruebas pasan
# Envía correo de prueba

# Opción 2: Terminal
php test-email-setup.php
```

## 📊 Funcionalidades

### MailSender Class

```php
$mailer = new MailSender();

// Envía correo con template profesional HTML
$mailer->sendPasswordReset(
    $email,        // Destinatario
    $resetLink,    // Link con token
    $username      // Para personalizar saludo
);
```

### Template Correo

- ✅ Header profesional (logo, color)
- ✅ Botón de acción prominente
- ✅ Enlace directo (fallback)
- ✅ Aviso de expiración (1 hora)
- ✅ Footer con copyright
- ✅ Responsive (mobile-friendly)
- ✅ Versión texto alternativo

### Seguridad

- ✅ Tokens seguros (bin2hex + random_bytes)
- ✅ Expiry de tokens (1 hora)
- ✅ No revela si usuario existe
- ✅ Logs de error (no expone al usuario)
- ✅ Credenciales en .env (nunca en código)
- ✅ .gitignore previene subida accidental

## 🔐 Variables de Entorno

```
MAIL_HOST          SMTP server (ej: smtp.gmail.com)
MAIL_PORT          Puerto SMTP (587 = TLS, 465 = SSL)
MAIL_ENCRYPTION    tls o ssl
MAIL_USERNAME      Usuario para autenticación
MAIL_PASSWORD      Contraseña (NUNCA compartir!)
MAIL_FROM_ADDRESS  Email remitente (debe ser válido)
MAIL_FROM_NAME     Nombre visible "De:"
APP_URL            URL base de tu app
```

## 🚀 Flujo Completo

```
1. Usuario: http://localhost/encuesta_prueba/public/forgot-password.html
2. Ingresa: username o email
3. Backend:
   - Genera token único (1 hora validad)
   - Guarda en BD
   - Envía correo vía MailSender
4. Usuario recibe correo con:
   - Botón "Restablecer Contraseña"
   - Link directo
   - Aviso de expiración
5. Usuario hace clic en enlace
6. Frontend: Valida token
7. Usuario: Ingresa nueva contraseña
8. Backend:
   - Valida token
   - Hashea contraseña
   - Limpia token
9. Login: Usuario accede con nueva contraseña
```

## 📚 Documentación

| Archivo                    | Propósito                                  |
| -------------------------- | ------------------------------------------ |
| **README_MAIL.md**         | Instalación por proveedor, troubleshooting |
| **MAIL_TESTING.md**        | Testing completo, debugging, logs          |
| **MAIL_IMPLEMENTATION.md** | Resumen técnico, seguridad                 |
| **test-email-setup.php**   | Herramienta web para verificar setup       |

## 🐛 Debugging

### Ver Logs

```bash
# Windows (XAMPP)
Get-Content C:\xampp\apache\logs\error.log -Wait

# Linux
tail -f /var/log/apache2/error.log
```

### Verificar Conexión SMTP

```bash
php -r "
require 'server/bootstrap.php';
require 'server/MailSender.php';
loadEnv('.env');
try { new MailSender(); echo 'OK'; }
catch (Exception \$e) { echo 'ERROR: ' . \$e->getMessage(); }
"
```

## ⚠️ Errores Comunes

| Error                       | Solución                                        |
| --------------------------- | ----------------------------------------------- |
| "Connection refused"        | Verifica HOST y PORT en .env                    |
| "Authentication failed"     | Para Gmail usa Contraseña de App, no password   |
| "Class PHPMailer not found" | Ejecuta: `composer require phpmailer/phpmailer` |
| "Unable to open .env"       | Copia .env.example a .env                       |

## 📋 Next Steps

1. **Editar .env** con tus credenciales SMTP
2. **Probar** vía test-email-setup.php
3. **Verificar** correo recibido
4. **Testing** flujo completo (request + reset)
5. **En Producción:**
   - Usar HTTPS obligatoriamente
   - Considerar servicio especializado (SendGrid, AWS SES)
   - Configurar SPF/DKIM/DMARC

## 📞 Soporte

- Lee **README_MAIL.md** para instalación
- Lee **MAIL_TESTING.md** para testing/debugging
- Ejecuta **test-email-setup.php** para diagnosticar problemas
- Revisa logs de Apache/PHP

---

**Estado:** ✅ Implementación completada
**Fecha:** $(date)
**PHPMailer Version:** 7.0.2
**Composer:** Required
