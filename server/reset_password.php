<?php
session_start();
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
$token = trim($input['token'] ?? '');
$newPassword = trim($input['password'] ?? '');
$confirmPassword = trim($input['confirm_password'] ?? '');

if ($token === '' || $newPassword === '' || $confirmPassword === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
    exit;
}

if ($newPassword !== $confirmPassword) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden']);
    exit;
}

if (strlen($newPassword) < 6) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']);
    exit;
}

try {
    // Buscar usuario con token válido (no expirado)
    $stmt = $pdo->prepare('SELECT id FROM admins WHERE reset_token = ? AND reset_token_expires > NOW() LIMIT 1');
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Enlace inválido o expirado']);
        exit;
    }

    // Hash la nueva contraseña
    $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);

    // Actualizar contraseña y limpiar token
    $updateStmt = $pdo->prepare('UPDATE admins SET password_hash = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?');
    $updateStmt->execute([$passwordHash, $user['id']]);

    echo json_encode(['success' => true, 'message' => 'Contraseña restablecida correctamente. Por favor, inicia sesión']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    exit;
}
?>
