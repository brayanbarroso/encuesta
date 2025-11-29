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
        echo '<html><head><title>Generar Hash</title><style>body{font-family:Arial;margin:2rem}form{max-width:400px}label{display:block;margin-top:0.5rem}input,button{padding:0.5rem;margin-top:0.3rem;width:100%;box-sizing:border-box}button{background:#007bff;color:white;border:none;cursor:pointer;border-radius:4px}button:hover{background:#0056b3}</style></head><body>';
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

    header('Content-Type: application/json; charset=utf-8');
    $hash = password_hash($pass, PASSWORD_BCRYPT);
    try {
        $stmt = $pdo->prepare('INSERT INTO admins (username, email, password_hash) VALUES (?, ?, ?)');
        $stmt->execute([$username, $email ?: null, $hash]);
        echo json_encode(['success' => true, 'message' => "Usuario '$username' creado correctamente"]);
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
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
