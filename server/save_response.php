<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

$input = json_decode(file_get_contents('php://input'), true);
$ident = trim($input['identificacion'] ?? '');
$answers = $input['answers'] ?? null;

if (!$ident || !$answers || !is_array($answers)) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos.']);
    exit;
}

// 1. Validar si el usuario existe
$stmt = $pdo->prepare('SELECT id FROM users WHERE identificacion = ? LIMIT 1');
$stmt->execute([$ident]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Usuario no existe.']);
    exit;
}

// 2. Validar si ya respondió
$stmt = $pdo->prepare('SELECT id FROM responses WHERE identificacion = ? LIMIT 1');
$stmt->execute([$ident]);
if ($row = $stmt->fetch()) {
    echo json_encode([
        'success' => false,
        'message' => 'Ya has respondido esta encuesta.',
        'id' => $row['id']   // devolvemos el ID existente
    ]);
    exit;
}

// 3. Guardar nueva respuesta
$ins = $pdo->prepare('INSERT INTO responses (identificacion, data) VALUES (?, ?)');
$ins->execute([$ident, json_encode($answers, JSON_UNESCAPED_UNICODE)]);

// obtener ID de la respuesta insertada
$id = $pdo->lastInsertId();

echo json_encode([
    'success' => true,
    'message' => 'Respuesta guardada.',
    'id' => $id    // este ID es tu consecutivo
]);
exit;
