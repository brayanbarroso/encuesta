<?php
// server/check_user.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

$input = json_decode(file_get_contents('php://input'), true);
$ident = trim($input['identificacion'] ?? '');

if (!$ident) {
    echo json_encode(['exists' => false, 'message' => 'No identificacion provided.']);
    exit;
}

$stmt = $pdo->prepare('SELECT id, nombre FROM users WHERE identificacion = ? LIMIT 1');
$stmt->execute([$ident]);
$user = $stmt->fetch();

if ($user) {
    echo json_encode(['exists' => true, 'user' => $user]);
} else {
    echo json_encode(['exists' => false]);
}
?>