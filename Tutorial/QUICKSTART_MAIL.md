# 🚀 INICIO RÁPIDO - Sistema de Envío de Correos

## ¿Qué se implementó?

✅ **Sistema profesional de envío de correos** para la función "Olvidé mi contraseña"

El usuario ahora recibirá un **correo HTML** con un botón/enlace para restablecer su contraseña, con token válido por 1 hora.

## ⚡ 3 Pasos para Activar

### Paso 1: Elegir Proveedor (2 minutos)

**Opción A - Gmail (Recomendado)**

1. Abre: https://myaccount.google.com/apppasswords
2. Selecciona "Correo" y tu dispositivo
3. Copia la contraseña de 16 caracteres
4. Pasa a Paso 2

**Opción B - Mailtrap (Para Testing)**

1. Regístrate gratis en: https://mailtrap.io
2. Copia las credenciales SMTP
3. Pasa a Paso 2

**Opción C - SendGrid**

1. Crea cuenta en: https://sendgrid.com
2. Copia API Key
3. Pasa a Paso 2

### Paso 2: Configurar .env (1 minuto)

Abre el archivo `.env` en la raíz del proyecto:

```
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=xxxx xxxx xxxx xxxx
MAIL_FROM_ADDRESS=tu-email@gmail.com
MAIL_FROM_NAME=Sistema de Encuesta
APP_URL=http://localhost/encuesta_prueba
```

**Para Mailtrap, usa:**

```
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_ENCRYPTION=ssl
MAIL_USERNAME=tu-usuario
MAIL_PASSWORD=tu-password
MAIL_FROM_ADDRESS=testing@example.com
APP_URL=http://localhost/encuesta_prueba
```

### Paso 3: ¡Probar! (30 segundos)

Abre en tu navegador:

```
http://localhost/encuesta_prueba/test-email-setup.php
```

Verás una página con pruebas automáticas. Si todo sale en verde ✓, estás listo.

**Bonus:** Al final puedes enviar un correo de prueba ingresando tu email.

## 📧 ¿Cómo Funciona?

### Para el Usuario:

1. **Formulario:** Ingresa usuario o email en `/public/forgot-password.html`
2. **Correo:** Recibe email profesional con botón "Restablecer Contraseña"
3. **Link:** Hace clic en el botón y se abre el formulario de nueva contraseña
4. **Contraseña:** Ingresa nueva contraseña y confirma
5. **Listo:** Puede iniciar sesión con la nueva contraseña

### Tecnología:

- ✅ PHPMailer 7.0 (librería profesional)
- ✅ Variables de entorno (.env)
- ✅ Tokens seguros (válidos 1 hora)
- ✅ Correo HTML responsivo (mobile-friendly)
- ✅ Manejo de errores robusto

## 🗂️ Archivos Importantes

| Archivo                 | Descripción                             |
| ----------------------- | --------------------------------------- |
| `.env`                  | **EDITAR AQUÍ** - Tus credenciales SMTP |
| `test-email-setup.php`  | Herramienta de testing web              |
| `README_MAIL.md`        | Guía completa de instalación            |
| `MAIL_TESTING.md`       | Guía de testing y debugging             |
| `server/MailSender.php` | Clase que envía correos                 |

## ⚠️ Importante

- **NO** hagas push del archivo `.env` a Git
- Ya está en `.gitignore` (protegido)
- La contraseña que ingresaste está segura

## 🆘 Problemas?

### "Error de conexión"

- Verifica que MAIL_HOST y MAIL_PORT sean correctos
- Abre `test-email-setup.php` para diagnostic

### "Autenticación fallida"

- Para Gmail: asegúrate usar "Contraseña de Aplicación", NO tu contraseña normal
- Ve nuevamente a https://myaccount.google.com/apppasswords

### "No recibo correos"

- Si usas Gmail, revisa Spam
- Ejecuta test-email-setup.php y verifica todos los checks
- Lee MAIL_TESTING.md para debugging

## 📚 Documentación

Para más detalles, lee estos archivos:

1. **README_MAIL.md** - Instalación por proveedor (Gmail, SendGrid, etc)
2. **MAIL_TESTING.md** - Testing completo, logs, troubleshooting
3. **MAIL_SUMMARY.md** - Resumen técnico de la implementación

## ✨ Features

- ✅ Correo HTML profesional
- ✅ Template responsivo (funciona en mobile)
- ✅ Tokens seguros (1 hora válido)
- ✅ Múltiples proveedores SMTP soportados
- ✅ Logs de error para debugging
- ✅ Manejo seguro de credenciales
- ✅ Sin cambios en la BD (usa columnas existentes)

## 🎯 Próximas Mejoras (Opcional)

- Agregar más templates (bienvenida, notificaciones)
- Queue system para envíos asincronos
- Retry automático si falla
- Tracking de correos (abiertos, clicks)

## 📞 Resumen

| Paso | Acción                        | Tiempo        |
| ---- | ----------------------------- | ------------- |
| 1    | Elegir proveedor SMTP         | 2 min         |
| 2    | Configurar .env               | 1 min         |
| 3    | Ejecutar test-email-setup.php | 1 min         |
| ✅   | **¡Listo!**                   | **4 minutos** |

---

**¿Preguntas?** Consulta README_MAIL.md o MAIL_TESTING.md

**¿Ya está todo listo?** Abre http://localhost/encuesta_prueba/public/forgot-password.html

¡Que disfrutes tu sistema de envío de correos! 📧✨
