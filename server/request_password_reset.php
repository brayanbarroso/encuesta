<?php
session_start();
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
$identifier = trim($input['identifier'] ?? '');

if ($identifier === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Usuario o email requerido']);
    exit;
}

try {
    // Buscar usuario por username o email
    $stmt = $pdo->prepare('SELECT id, username, email FROM admins WHERE username = ? OR email = ? LIMIT 1');
    $stmt->execute([$identifier, $identifier]);
    $user = $stmt->fetch();

    if (!$user) {
        // Por seguridad, no revelar si existe o no el usuario
        echo json_encode(['success' => true, 'message' => 'Si el usuario existe, recibirá un enlace de restablecimiento por correo']);
        exit;
    }

    // Generar token único (válido por 1 hora)
    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Guardar token en BD
    $updateStmt = $pdo->prepare('UPDATE admins SET reset_token = ?, reset_token_expires = ? WHERE id = ?');
    $updateStmt->execute([$token, $expiresAt, $user['id']]);

    // En producción: enviar email con enlace
    // Para desarrollo, retornar enlace en respuesta (NO hacer esto en producción)
    $resetLink = "https://localhost/encuesta_prueba/public/forgot-password.html?token=" . urlencode($token);

    // Log para depuración (remover en producción)
    error_log("Reset link for {$user['username']}: {$resetLink}");

    echo json_encode([
        'success' => true,
        'message' => 'Enlace de restablecimiento enviado',
        'debug_link' => $resetLink // SOLO para desarrollo
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    exit;
}
?>
