# 🔐 Flujo Completo: Construcción del Enlace y Restablecimiento de Contraseña

## 📋 Resumen del Sistema

El sistema de "Olvidé mi contraseña" está **completamente implementado** con:
1. ✅ Construcción de enlace de restablecimiento
2. ✅ Validación de tokens
3. ✅ Restablecimiento seguro de contraseña
4. ✅ Integración con PHPMailer para envío de correos

---

## 🔄 Flujo Completo del Usuario

```
┌─────────────────────────────────────────────────────────────┐
│ PASO 1: Usuario solicita restablecimiento                   │
├─────────────────────────────────────────────────────────────┤
│ URL: /public/forgot-password.html                           │
│ Ingresa: usuario o email                                    │
│ Acción: Click en "Enviar enlace"                           │
└─────────────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│ PASO 2: Backend procesa solicitud                           │
├─────────────────────────────────────────────────────────────┤
│ Archivo: server/request_password_reset.php                 │
│ Acciones:                                                   │
│  • Busca usuario por username o email                      │
│  • Genera token seguro: bin2hex(random_bytes(32))          │
│  • Expiry: NOW() + 1 hora                                  │
│  • Guarda en BD: admins.reset_token, reset_token_expires   │
│  • Envía correo con link via MailSender                    │
│ Respuesta: JSON {'success': true, 'message': '...'}        │
└─────────────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│ PASO 3: Email enviado al usuario                           │
├─────────────────────────────────────────────────────────────┤
│ Contiene: Enlace como:                                      │
│ http://localhost/encuesta_prueba/public/forgot-password    │
│ .html?token=abc123...                                       │
│                                                             │
│ El link contiene el TOKEN de restablecimiento              │
└─────────────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│ PASO 4: Usuario hace click en el enlace del email          │
├─────────────────────────────────────────────────────────────┤
│ URL: /public/forgot-password.html?token=abc123...          │
│ JavaScript detecta el parámetro 'token'                    │
│ Llamada: validateTokenAndShowResetForm(token)              │
└─────────────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│ PASO 5: Validar token en backend                           │
├─────────────────────────────────────────────────────────────┤
│ Archivo: server/validate_reset_token.php                   │
│ Método: GET /validate_reset_token.php?token=abc123         │
│ Verifica:                                                   │
│  • Token existe en BD                                      │
│  • Token no ha expirado (reset_token_expires > NOW())      │
│ Respuesta: {'valid': true/false}                           │
└─────────────────────────────────────────────────────────────┘
                         ↓
         ┌──────────────────────────────────────────┐
         │ ¿Token válido?                           │
         └──────────────────────────────────────────┘
           ↙                                    ↘
        SÍ                                       NO
        ↓                                        ↓
    MOSTRAR FORM                         REDIRIGIR A LOGIN
    DE NUEVA CONTRASEÑA                 (Error: "Token expirado")
        ↓
┌─────────────────────────────────────────────────────────────┐
│ PASO 6: Mostrar formulario Step 2                           │
├─────────────────────────────────────────────────────────────┤
│ HTML: #step2 se muestra                                    │
│ Campos:                                                    │
│  • Nueva contraseña                                        │
│  • Confirmar contraseña                                    │
│  • Token (hidden input)                                    │
│ Usuario ingresa nueva contraseña                          │
└─────────────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│ PASO 7: Usuario envía nueva contraseña                     │
├─────────────────────────────────────────────────────────────┤
│ Acción: Click en "Restablecer Contraseña"                 │
│ Validación frontend:                                       │
│  • Campos no vacíos                                        │
│  • Contraseñas coinciden                                   │
│  • Mínimo 6 caracteres                                     │
│ POST a: /server/reset_password.php                         │
│ Body: {token, password, confirm_password}                  │
└─────────────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│ PASO 8: Backend restablece contraseña                      │
├─────────────────────────────────────────────────────────────┤
│ Archivo: server/reset_password.php                         │
│ Acciones:                                                   │
│  • Valida token (existe y no expirado)                     │
│  • Valida contraseña (≥6 caracteres)                       │
│  • Hash contraseña: password_hash($pwd, PASSWORD_BCRYPT)   │
│  • UPDATE admins SET:                                      │
│    - password_hash = nuevo_hash                            │
│    - reset_token = NULL (elimina token)                    │
│    - reset_token_expires = NULL                            │
│  • Respuesta: {'success': true}                            │
└─────────────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│ PASO 9: Usuario redirectionado a login                     │
├─────────────────────────────────────────────────────────────┤
│ Mensaje: "Contraseña restablecida correctamente"           │
│ Redirige a: /public/login.html (después de 2 segundos)     │
│ Usuario: Puede iniciar sesión con nueva contraseña         │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔗 Construcción del Enlace de Restablecimiento

### En: `server/request_password_reset.php` (línea 41-43)

```php
// Construir enlace de restablecimiento
$appUrl = $_ENV['APP_URL'] ?? 'http://localhost/encuesta_prueba';
$resetLink = $appUrl . "/public/forgot-password.html?token=" . urlencode($token);
```

**Desglose:**

| Parte | Valor | Descripción |
|-------|-------|-----------|
| `$appUrl` | `http://localhost/encuesta_prueba` | URL base (configurable via .env) |
| Ruta | `/public/forgot-password.html` | Página de restablecimiento |
| Parámetro | `?token=` | Indica que viene un token |
| Token | `abc123...` | Token seguro generado (64 caracteres) |
| Completo | `http://localhost/.../forgot-password.html?token=abc123...` | Enlace enviado por email |

**Ejemplo real:**
```
http://localhost/encuesta_prueba/public/forgot-password.html?token=a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6
```

---

## 📧 Email Enviado

El correo recibido por el usuario contiene:

```html
<a href="http://localhost/encuesta_prueba/public/forgot-password.html?token=a1b2...">
  Restablecer Contraseña
</a>
```

Cuando el usuario hace click:
1. URL carga: `/public/forgot-password.html?token=a1b2...`
2. JavaScript detecta el parámetro `token`
3. Valida token llamando a `validate_reset_token.php`
4. Si es válido, muestra Step 2 (formulario de nueva contraseña)

---

## 📁 Archivos del Sistema

### Backend (PHP)

#### 1. `server/request_password_reset.php`
**Propósito:** Genera token y envía correo
```php
// Línea 41-43: Construye el enlace
$resetLink = $appUrl . "/public/forgot-password.html?token=" . urlencode($token);

// Línea 46-48: Envía correo
$mailer = new MailSender();
$mailer->sendPasswordReset($user['email'], $resetLink, $user['username']);
```

**Entrada:** `POST /server/request_password_reset.php`
```json
{"identifier": "admin"}
```

**Salida:** 
```json
{"success": true, "message": "Enlace de restablecimiento enviado a tu correo"}
```

#### 2. `server/validate_reset_token.php`
**Propósito:** Valida que el token es válido y no expirado
```php
// Verifica:
// • Token existe en BD
// • reset_token_expires > NOW()
```

**Entrada:** `GET /server/validate_reset_token.php?token=abc123`

**Salida:**
```json
{"valid": true, "message": "Token válido"}
```
o
```json
{"valid": false, "message": "Enlace inválido o expirado"}
```

#### 3. `server/reset_password.php`
**Propósito:** Actualiza la contraseña y limpia el token

```php
// Validaciones:
// • Token existe y no expirado
// • Contraseña ≥ 6 caracteres
// • Contraseñas coinciden

// Acciones:
// • Hash con bcrypt
// • UPDATE BD
// • Limpia reset_token
```

**Entrada:** `POST /server/reset_password.php`
```json
{
  "token": "abc123...",
  "password": "NuevaPass123",
  "confirm_password": "NuevaPass123"
}
```

**Salida:**
```json
{"success": true, "message": "Contraseña restablecida correctamente..."}
```

### Frontend (HTML/JS)

#### 4. `public/forgot-password.html`
**Estructura:**
```html
<!-- Step 1: Solicitar reset -->
<div id="step1">
  Formulario: usuario/email
  Botón: "Enviar enlace"
</div>

<!-- Step 2: Restablecer contraseña -->
<div id="step2" style="display:none">
  Formulario: nueva contraseña + confirmación
  Botón: "Restablecer Contraseña"
</div>
```

#### 5. `public/assets/js/forgot-password.js`
**Funciones principales:**

```javascript
// 1. Detecta token en URL
const token = new URLSearchParams(window.location.search).get("token");

// 2. Valida token al cargar
validateTokenAndShowResetForm(token)
  ↓
  Llama: validate_reset_token.php
  Si válido: Muestra Step 2

// 3. Maneja solicitud de reset
handleRequestReset()
  ↓
  POST request_password_reset.php
  Envía: {identifier}
  Si éxito: Muestra mensaje

// 4. Maneja restablecimiento
handleResetPassword()
  ↓
  POST reset_password.php
  Envía: {token, password, confirm_password}
  Si éxito: Redirige a login
```

---

## 🔐 Seguridad

### Tokens
- **Generación:** `bin2hex(random_bytes(32))` = 64 caracteres aleatorios
- **Almacenamiento:** Base de datos (en columna `reset_token`)
- **Expiry:** 1 hora (`reset_token_expires`)
- **Limpieza:** Se elimina después de usar

### Contraseñas
- **Hashing:** bcrypt (`PASSWORD_BCRYPT`)
- **Validación:** Mínimo 6 caracteres
- **Confirmación:** Se valida coincidencia

### Privacidad
- **No revela usuarios:** Si usuario NO existe, respuesta es idéntica
- **Logs internos:** Errores se registran, no se muestran al usuario
- **HTTPS recomendado:** En producción obligatoriamente

---

## 🧪 Testing del Flujo Completo

```bash
# 1. Abrir página
http://localhost/encuesta_prueba/public/forgot-password.html

# 2. Ingresar usuario
Username: admin
Click: "Enviar enlace"

# 3. Esperar correo
Gmail/Mailtrap/etc recibe correo con enlace

# 4. Copiar enlace del correo
http://localhost/encuesta_prueba/public/forgot-password.html?token=abc123...

# 5. Pegar en navegador
Formulario de Step 2 aparece

# 6. Ingresar nueva contraseña
Nueva contraseña: MiNuevaPass123
Confirmar: MiNuevaPass123
Click: "Restablecer Contraseña"

# 7. Redirigido a login
Ingresa usuario + nueva contraseña
¡Acceso concedido!
```

---

## 📊 Base de Datos

### Tabla: `admins`

**Columnas utilizadas:**

```sql
id                    INT PRIMARY KEY
username              VARCHAR(50)
email                 VARCHAR(100)
password_hash         VARCHAR(255)        -- bcrypt hash
reset_token           VARCHAR(255) NULL   -- Token único (64 caracteres)
reset_token_expires   DATETIME NULL       -- Expiración (NOW() + 1 hora)
```

**Ciclo de vida del token:**

```
1. Usuario solicita reset
   → reset_token = "abc123..."
   → reset_token_expires = 2026-01-16 15:30:00

2. Usuario (si es válido, usa el enlace)
   → Entra a Step 2
   → Ingresa nueva contraseña

3. Backend procesa reset
   → password_hash = new_bcrypt_hash
   → reset_token = NULL (elimina)
   → reset_token_expires = NULL (elimina)

4. Token no puede usarse 2 veces
   → Siguiente intento: "Token no encontrado"
```

---

## ✅ Checklist de Funcionalidad

- [x] Token generado (bin2hex + random_bytes)
- [x] Token guardado en BD con expiración
- [x] Enlace construido correctamente
- [x] Enlace enviado por email (via MailSender)
- [x] URL contiene token como parámetro
- [x] JavaScript detecta token en URL
- [x] Token validado antes de mostrar formulario
- [x] Contraseña validada (≥6 caracteres)
- [x] Contraseñas coinciden validadas
- [x] Contraseña hasheada con bcrypt
- [x] Token eliminado después de usar (previene reutilización)
- [x] Redirección a login después de éxito
- [x] Error si token expirado
- [x] Error si token inválido

---

## 🎯 URLs Importantes

| Acción | URL |
|--------|-----|
| Formulario | `/public/forgot-password.html` |
| Con token | `/public/forgot-password.html?token=abc123...` |
| Backend: Solicitud | `POST /server/request_password_reset.php` |
| Backend: Validación | `GET /server/validate_reset_token.php?token=abc123` |
| Backend: Reset | `POST /server/reset_password.php` |
| Login después | `/public/login.html` |

---

## 📝 Resumen

✅ **Sistema completo implementado:**
1. Construcción de enlace con token seguro
2. Envío de correo con el enlace
3. Validación del token
4. Restablecimiento seguro de contraseña
5. Limpieza de token para prevenir reutilización
6. Redirección a login

**Estado:** 🟢 Listo para producción (después de configurar .env con credenciales SMTP)
