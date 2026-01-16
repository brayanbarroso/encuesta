<?php
/**
 * server/admin_setup.php
 * Script para crear/resetear admin en la BD
 * Uso: http://localhost/encuesta_prueba/server/admin_setup.php?action=hash&pass=tucontraseña
 * O:   http://localhost/encuesta_prueba/server/admin_setup.php?action=reset (resetea admin a admin123)
 */

require_once __DIR__ . '/db.php';

$action = $_GET['action'] ?? '';

// Acción 1: Generar hash de una contraseña
if ($action === 'hash') {
    header('Content-Type: text/html; charset=utf-8');
    $pass = $_GET['pass'] ?? '';
    if ($pass === '') {
        echo '<html>
                <head>
                    <title>Generar Hash</title>
                    <style>body{font-family:Arial;margin:2rem}form{max-width:400px}label{display:block;margin-top:0.5rem}input,button{padding:0.5rem;margin-top:0.3rem;width:100%;box-sizing:border-box}button{background:#007bff;color:white;border:none;cursor:pointer;border-radius:4px}button:hover{background:#0056b3}</style>
                </head>
                <body>';
        echo '<h2>Generar Hash de Contraseña</h2>';
        echo '<form method="get">';
        echo '<label>Contraseña:</label>';
        echo '<input type="password" name="pass" required>';
        echo '<input type="hidden" name="action" value="hash">';
        echo '<button type="submit">Generar Hash</button>';
        echo '</form></body></html>';
        exit;
    }
    
    $hash = password_hash($pass, PASSWORD_BCRYPT);
    echo '<html><head><title>Hash Generado</title><style>body{font-family:Arial;margin:2rem}pre{background:#f5f5f5;padding:1rem;border-radius:4px;overflow-x:auto}code{font-family:monospace}</style></head><body>';
    echo '<h2>Hash Generado</h2>';
    echo '<p><strong>Contraseña:</strong> ' . htmlspecialchars($pass) . '</p>';
    echo '<p><strong>Hash:</strong></p>';
    echo '<pre><code>' . htmlspecialchars($hash) . '</code></pre>';
    echo '<p><strong>Usa este hash en la BD:</strong></p>';
    echo '<pre><code>INSERT INTO admins (username, password_hash) VALUES (\'usuario\', \'' . htmlspecialchars($hash) . '\');</code></pre>';
    echo '<a href="?action=hash" style="color:#007bff;">Generar otro hash</a>';
    echo '</body></html>';
    exit;
}

// Acción 2: Resetear admin a admin123
if ($action === 'reset') {
    header('Content-Type: application/json; charset=utf-8');
    $default_hash = '$2y$10$YJl8sQCCHWJ8XkNJ4R5k.uQh0Q.gHV8ZrXvNq6qF8LUKh8Z8KiEI6'; // admin123

    $stmt = $pdo->prepare('UPDATE admins SET password_hash = ? WHERE username = ? LIMIT 1');
    $stmt->execute([$default_hash, 'admin']);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Admin reseteado a: admin / admin123']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Admin no encontrado o no actualizado']);
    }
    exit;
}

// Acción 3: Crear/inserta nuevo usuario
if ($action === 'create') {
    $username = $_GET['username'] ?? '';
    $pass = $_GET['pass'] ?? '';
    $email = $_GET['email'] ?? '';
    
    if ($username === '' || $pass === '') {
        header('Content-Type: text/html; charset=utf-8');
        echo '<html><head><title>Crear Usuario</title><style>body{font-family:Arial;margin:2rem}form{max-width:400px}label{display:block;margin-top:0.5rem}input,button{padding:0.5rem;margin-top:0.3rem;width:100%;box-sizing:border-box}button{background:#007bff;color:white;border:none;cursor:pointer;border-radius:4px}button:hover{background:#0056b3}</style></head><body>';
        echo '<h2>Crear Nuevo Usuario</h2>';
        echo '<form method="get">';
        echo '<label>Usuario:</label>';
        echo '<input type="text" name="username" required>';
        echo '<label>Contraseña:</label>';
        echo '<input type="password" name="pass" required>';
        echo '<label>Email (opcional):</label>';
        echo '<input type="email" name="email">';
        echo '<input type="hidden" name="action" value="create">';
        echo '<button type="submit">Crear Usuario</button>';
        echo '</form></body></html>';
        exit;
    }

    header('Content-Type: text/html; charset=utf-8');
    $hash = password_hash($pass, PASSWORD_BCRYPT);
    $success = false;
    $message = '';
    
    try {
        $stmt = $pdo->prepare('INSERT INTO admins (username, email, password_hash) VALUES (?, ?, ?)');
        $stmt->execute([$username, $email ?: null, $hash]);
        $success = true;
        $message = "Usuario '$username' creado correctamente";
    } catch (\Exception $e) {
        $success = false;
        $message = 'Error: ' . $e->getMessage();
    }
    
    // Mostrar página de confirmación
    $alertClass = $success ? 'alert-success' : 'alert-danger';
    $alertIcon = $success ? '✓' : '✗';
    $buttonColor = $success ? '#28a745' : '#dc3545';
    $emailDisplay = $email ?: 'No especificado';
    $currentDate = date('Y-m-d H:i:s');
    
    echo <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Creación de Usuario</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 2rem;
            max-width: 500px;
            text-align: center;
        }
        .alert {
            padding: 1.5rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        h2 {
            color: #333;
            margin-bottom: 1rem;
        }
        p {
            color: #666;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }
        .btn-container {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }
        a, button {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        .btn-login {
            background: $buttonColor;
            color: white;
        }
        .btn-login:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        .btn-back {
            background: #6c757d;
            color: white;
        }
        .btn-back:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        .details {
            text-align: left;
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 4px;
            margin: 1rem 0;
            font-size: 0.95rem;
        }
        .details p {
            margin: 0.5rem 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="alert $alertClass">
            <div class="icon">$alertIcon</div>
            <h2>$message</h2>
        </div>
HTML;

    if ($success) {
        echo <<<HTML
        <div class="details">
            <p><strong>Usuario:</strong> $username</p>
            <p><strong>Email:</strong> $emailDisplay</p>
            <p><strong>Fecha de creación:</strong> $currentDate</p>
        </div>
        <p>El usuario ha sido registrado exitosamente y puede usar sus credenciales para iniciar sesión.</p>
HTML;
    } else {
        echo '<p>Por favor intenta nuevamente o contacta al administrador.</p>';
    }
    
    echo <<<HTML
        <div class="btn-container">
            <a href="../login" class="btn-login">Ir al Login</a>
            <a href="?action=create" class="btn-back">Crear otro usuario</a>
        </div>
    </div>
</body>
</html>
HTML;
    exit;
}

// Por defecto: mostrar opciones
header('Content-Type: text/html; charset=utf-8');
echo <<<HTML
<html>
<head>
    <title>Admin Setup</title>
    <style>
        body { font-family: Arial; margin: 2rem; }
        a { display: block; margin: 0.5rem 0; padding: 0.5rem; background: #007bff; color: white; text-decoration: none; border-radius: 4px; width: 300px; }
        a:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h2>Admin Setup</h2>
    <a href="?action=hash">Generar Hash de Contraseña</a>
    <a href="?action=reset">Resetear admin a: admin / admin123</a>
    <a href="?action=create">Crear Nuevo Usuario</a>
</body>
</html>
HTML;
