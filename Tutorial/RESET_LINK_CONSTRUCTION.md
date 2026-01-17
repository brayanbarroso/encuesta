# 🔗 Construcción del Enlace de Restablecimiento - Detalles Técnicos

## 📍 Ubicación en Código

**Archivo:** `server/request_password_reset.php`  
**Líneas:** 41-43

```php
// Construir enlace de restablecimiento
$appUrl = $_ENV['APP_URL'] ?? 'http://localhost/encuesta_prueba';
$resetLink = $appUrl . "/public/forgot-password.html?token=" . urlencode($token);
```

---

## 🔍 Desglose Paso a Paso

### Paso 1: Obtener URL Base

```php
$appUrl = $_ENV['APP_URL'] ?? 'http://localhost/encuesta_prueba';
```

**Explicación:**
- Lee la variable `APP_URL` del archivo `.env`
- Si no existe, usa valor por defecto: `http://localhost/encuesta_prueba`
- Esto permite cambiar fácilmente entre desarrollo y producción

**Ejemplos:**
- Desarrollo: `http://localhost/encuesta_prueba`
- Producción: `https://tudominio.com/encuesta_prueba`

### Paso 2: Generar Token Seguro

```php
$token = bin2hex(random_bytes(32));
```

**Explicación:**
- `random_bytes(32)` - Genera 32 bytes de datos aleatorios criptográficamente seguros
- `bin2hex()` - Convierte bytes a string hexadecimal (cada byte = 2 caracteres hex)
- Resultado: 64 caracteres hexadecimales

**Ejemplo:**
```
random_bytes(32) → [9f, 3a, 7c, 12, ...] (32 bytes)
            ↓
bin2hex()   → "9f3a7c12..." (64 caracteres hex)
```

### Paso 3: URL Encode del Token

```php
urlencode($token)
```

**Explicación:**
- Convierte caracteres especiales a formato URL-safe
- En este caso, el token hex es seguro, pero se asegura

**Ejemplo:**
```
Token original:  "abc123def456..."
urlencode():     "abc123def456..." (sin cambios, ya es seguro)
```

### Paso 4: Construir Enlace Completo

```php
$resetLink = $appUrl . "/public/forgot-password.html?token=" . urlencode($token);
```

**Construcción:**
```
$appUrl              = "http://localhost/encuesta_prueba"
+ "/public/forgot-password.html"  (página)
+ "?token="          (parámetro URL)
+ urlencode($token)  (valor del token)
________________
$resetLink = "http://localhost/encuesta_prueba/public/forgot-password.html?token=abc123..."
```

---

## 📧 Resultado: Enlace en Email

El enlace se ve así en el email que recibe el usuario:

```
http://localhost/encuesta_prueba/public/forgot-password.html?token=9f3a7c12a5e8d2f1b4c6e8a1d3f5b7c9e1a3d5f7b9c1e3a5d7f9b1c3e5f7b9
```

**Desglose del enlace:**

| Parte | Valor |
|-------|-------|
| Protocolo | `http://` |
| Host | `localhost` |
| Puerto | (default 80) |
| Ruta | `/encuesta_prueba/public/forgot-password.html` |
| Query | `?token=9f3a7c12...` |

---

## 🔄 Flujo en Tiempo Real

### 1️⃣ Usuario solicita reset

```
Frontend (forgot-password.html)
  ↓
POST /server/request_password_reset.php
  body: {"identifier": "admin"}
```

### 2️⃣ Backend genera token y construye enlace

```php
// Línea 35-37: Generar token
$token = bin2hex(random_bytes(32));
$expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
// $token = "9f3a7c12a5e8d2f1b4c6e8a1d3f5b7c9e1a3d5f7b9c1e3a5d7f9b1c3e5f7b9"

// Línea 39-41: Guardar en BD
$updateStmt = $pdo->prepare('UPDATE admins SET reset_token = ?, reset_token_expires = ? WHERE id = ?');
$updateStmt->execute([$token, $expiresAt, $user['id']]);
// BD ahora contiene el token

// Línea 41-43: Construir enlace
$appUrl = $_ENV['APP_URL'] ?? 'http://localhost/encuesta_prueba';
$resetLink = $appUrl . "/public/forgot-password.html?token=" . urlencode($token);
// $resetLink = "http://localhost/encuesta_prueba/public/forgot-password.html?token=9f3a7c12..."
```

### 3️⃣ Backend envía email

```php
// Línea 46-48: Enviar correo
$mailer = new MailSender();
$mailer->sendPasswordReset($user['email'], $resetLink, $user['username']);
// Email enviado con $resetLink en el body
```

### 4️⃣ Usuario recibe email

```
┌────────────────────────────────────────┐
│ Gmail/Outlook/etc                      │
├────────────────────────────────────────┤
│ From: noreply@encuesta.com            │
│ To: usuario@example.com               │
│ Subject: Restablecimiento de           │
│          Contraseña                    │
├────────────────────────────────────────┤
│ Haz clic en el botón:                 │
│                                        │
│ [Restablecer Contraseña]              │
│  ↓                                     │
│ http://localhost/encuesta_prueba/     │
│ public/forgot-password.html?token=... │
└────────────────────────────────────────┘
```

### 5️⃣ Usuario hace click en enlace

```
URL que se abre:
http://localhost/encuesta_prueba/public/forgot-password.html?token=9f3a7c12...

JavaScript detecta:
const token = new URLSearchParams(window.location.search).get("token");
// token = "9f3a7c12..."
```

### 6️⃣ Frontend valida token

```javascript
const response = await fetch(
  `./server/validate_reset_token.php?token=${encodeURIComponent(token)}`
);

// Request:
// GET /server/validate_reset_token.php?token=9f3a7c12...
```

### 7️⃣ Backend valida en BD

```php
// validate_reset_token.php
$stmt = $pdo->prepare('
  SELECT id FROM admins 
  WHERE reset_token = ? 
  AND reset_token_expires > NOW() 
  LIMIT 1
');
$stmt->execute([$token]);
// Busca el token en la BD
// Verifica que no haya expirado
```

### 8️⃣ Si válido, mostrar Step 2

```javascript
if (data.valid) {
  document.getElementById("step1").style.display = "none";
  document.getElementById("step2").style.display = "block";
  document.getElementById("resetToken").value = token;
}
```

### 9️⃣ Usuario ingresa nueva contraseña

```
Formulario mostrado:
┌─────────────────────────────┐
│ Nueva Contraseña:  ••••••  │
│ Confirmar:         ••••••  │
│ [Restablecer]              │
└─────────────────────────────┘

Usuario ingresa: MiNuevaPass123
```

### 🔟 Backend actualiza contraseña

```php
// reset_password.php
// Verifica token nuevamente
$stmt = $pdo->prepare('
  SELECT id FROM admins 
  WHERE reset_token = ? 
  AND reset_token_expires > NOW() 
  LIMIT 1
');
$stmt->execute([$token]);

// Hash la contraseña
$passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);

// Actualiza y limpia token
$updateStmt = $pdo->prepare('
  UPDATE admins 
  SET password_hash = ?, 
      reset_token = NULL, 
      reset_token_expires = NULL 
  WHERE id = ?
');
$updateStmt->execute([$passwordHash, $user['id']]);
```

---

## 🔐 Seguridad del Token

### Propiedades

| Propiedad | Valor | Descripción |
|-----------|-------|-----------|
| Longitud | 64 caracteres | Suficientemente largo para evitar ataques |
| Formato | Hexadecimal | Seguro en URLs (sin caracteres especiales) |
| Aleatorio | criptográfico | Imposible de predecir o forzar |
| Único | Por usuario | No se repite |
| Temporal | 1 hora | Expira automáticamente |
| Deletable | Após usar | No se puede reutilizar |

### Token Válido

```
Mientras:
  • Existe en BD
  • NO ha expirado (reset_token_expires > NOW())
  • Usuario no ha usado aún

El token es: ✅ VÁLIDO
```

### Token Inválido

```
Cuando:
  • NO existe en BD
  • HA expirado (NOW() > reset_token_expires)
  • Ya fue usado (reset_token = NULL)
  • Usuario intenta 2 veces

El token es: ❌ INVÁLIDO
```

---

## 📝 Ejemplo Completo

### Escenario Real

**Usuario:** `admin`  
**Email:** `admin@example.com`  
**Hora:** 14:30

```
1️⃣ Usuario ingresa "admin" en forgot-password.html

2️⃣ JavaScript POST:
   URL: http://localhost/encuesta_prueba/server/request_password_reset.php
   Body: {"identifier": "admin"}

3️⃣ Backend genera:
   Token:       "a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6a1b2c3d4e5f"
   ExpiresAt:   2026-01-16 15:30 (NOW + 1 hora)
   ResetLink:   "http://localhost/encuesta_prueba/public/forgot-password.html?token=a1b2c3d4..."

4️⃣ Backend guarda en BD:
   admins.reset_token = "a1b2c3d4..."
   admins.reset_token_expires = "2026-01-16 15:30"

5️⃣ Backend envía email a admin@example.com:
   Subject: Restablecimiento de Contraseña
   Body contiene:
   http://localhost/encuesta_prueba/public/forgot-password.html?token=a1b2c3d4...

6️⃣ Usuario abre email y hace click en enlace

7️⃣ Frontend valida:
   GET /server/validate_reset_token.php?token=a1b2c3d4...
   ✓ Token existe en BD
   ✓ NO ha expirado (14:35 < 15:30)
   → Mostrar Step 2

8️⃣ Usuario ingresa: MiNuevaPass123

9️⃣ Frontend POST:
   URL: /server/reset_password.php
   Body: {token: "a1b2c3d4...", password: "MiNuevaPass123", ...}

🔟 Backend actualiza:
   admins.password_hash = "$2y$10$...bcrypt hash..."
   admins.reset_token = NULL (elimina)
   admins.reset_token_expires = NULL (elimina)

1️⃣1️⃣ Usuario redirigido a /public/login.html
   ✓ Puede iniciar sesión con nueva contraseña

1️⃣2️⃣ Si intenta usar el token 2 veces:
   ✗ Token no encontrado (fue eliminado)
```

---

## 🎯 Variables Clave

### En `.env`
```
APP_URL=http://localhost/encuesta_prueba
```
→ Define la URL base del enlace

### En `request_password_reset.php`
```
$token = bin2hex(random_bytes(32))
```
→ Token único y seguro (64 caracteres)

### En BD (`admins` table)
```
reset_token = "a1b2c3d4..." (VARCHAR 255)
reset_token_expires = "2026-01-16 15:30" (DATETIME)
```
→ Almacenamiento del token y su expiración

---

## ✅ Resumen

**El enlace se construye así:**

```
BASE_URL + "/public/forgot-password.html?token=" + TOKEN_SEGURO
                                                         ↑
                                                  64 caracteres
                                                  hexadecimales
                                                  aleatorios
```

**Ejemplo final:**
```
http://localhost/encuesta_prueba/public/forgot-password.html?token=a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6a1b2c3d4e5f
```

**Seguridad:**
- ✅ Token aleatorio (imposible de adivinar)
- ✅ URL-safe (funciona en URLs)
- ✅ Con expiración (1 hora)
- ✅ De un solo uso (se elimina después)
- ✅ Enviado por email seguro (TLS/SSL)
