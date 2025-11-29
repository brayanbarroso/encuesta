<?php
session_start();

require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
$user = trim($input['user'] ?? '');
$pass = trim($input['pass'] ?? '');

if ($user === '' || $pass === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Usuario y contraseña requeridos']);
    exit;
}

try {
    // Buscar usuario en BD
    $stmt = $pdo->prepare('SELECT id, username, password_hash FROM admins WHERE username = ? LIMIT 1');
    $stmt->execute([$user]);
    $row = $stmt->fetch();

    if (!$row) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Usuario o contraseña incorrectos']);
        exit;
    }

    // Verificar contraseña con password_verify (hash bcrypt)
    if (password_verify($pass, $row['password_hash'])) {
        // Crear sesión
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['login_time'] = time();

        echo json_encode(['success' => true, 'message' => 'Autenticado correctamente']);
        exit;
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Usuario o contraseña incorrectos']);
        exit;
    }

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error en autenticación: ' . $e->getMessage()]);
    exit;
}

