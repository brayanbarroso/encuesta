<?php
// server/validate_reset_token.php
session_start();
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

$token = trim($_GET['token'] ?? '');

if ($token === '') {
    http_response_code(400);
    echo json_encode(['valid' => false, 'message' => 'Token requerido']);
    exit;
}

try {
    // Verificar si el token es válido (existe y no está expirado)
    $stmt = $pdo->prepare('SELECT id FROM admins WHERE reset_token = ? AND reset_token_expires > NOW() LIMIT 1');
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(401);
        echo json_encode(['valid' => false, 'message' => 'Enlace inválido o expirado']);
        exit;
    }

    echo json_encode(['valid' => true, 'message' => 'Token válido']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['valid' => false, 'message' => 'Error: ' . $e->getMessage()]);
    exit;
}
?>
