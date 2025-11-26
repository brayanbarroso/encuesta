<?php
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

// Obtener usuarios que respondieron la encuesta
$stmt = $pdo->query("
    SELECT 
        u.id,
        u.identificacion,
        u.nombre,
        u.telefono,
        r.id as response_id,
        r.created_at
    FROM users u
    INNER JOIN responses r ON u.identificacion = r.identificacion
    ORDER BY r.created_at ASC
");

$rows = [];
while ($row = $stmt->fetch()) {
    $rows[] = [
        'id' => $row['id'],
        'identificacion' => $row['identificacion'],
        'nombre' => $row['nombre'],
        'telefono' => $row['telefono'],
        'response_id' => $row['response_id'],
        'created_at' => $row['created_at']
    ];
}

echo json_encode($rows, JSON_UNESCAPED_UNICODE);
exit;
