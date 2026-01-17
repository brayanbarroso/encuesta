<?php
session_start();
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/MailSender.php';

// Cargar variables de entorno
loadEnv(__DIR__ . '/../.env');

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

    // Construir enlace de restablecimiento
    $appUrl = $_ENV['APP_URL'] ?? 'http://localhost/encuesta_prueba';
    $resetLink = $appUrl . "/recuperar-contrasena?token=" . urlencode($token);

    // Enviar correo
    try {
        $mailer = new MailSender();
        $mailer->sendPasswordReset($user['email'], $resetLink, $user['username']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Enlace de restablecimiento enviado a tu correo'
        ]);
    } catch (Exception $e) {
        error_log("Failed to send password reset email: " . $e->getMessage());
        
        // Respuesta segura (no revelar que falló el correo)
        echo json_encode([
            'success' => true,
            'message' => 'Si el usuario existe, recibirá un enlace de restablecimiento por correo'
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    error_log('Password reset request error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error procesando tu solicitud']);
    exit;
}
?>
