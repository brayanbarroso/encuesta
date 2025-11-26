<?php
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

 $stmt = $pdo->query("SELECT r.id, r.identificacion, r.data, r.created_at, u.nombre FROM responses r LEFT JOIN users u ON u.identificacion = r.identificacion ORDER BY r.created_at ASC");

$rows = [];

while ($row = $stmt->fetch()) {

    $answers = json_decode($row['data'], true);

    $rows[] = [
        // info general
        'id' => $row['id'],

        'created_at' => $row['created_at'],

        'identificacion' => $row['identificacion'],
        'nombre' => $row['nombre'] ?? '',

        // Preguntas cerradas
        // pregunta 1
        'q1' => $answers['q1'] ?? '',
        // pregunta 2
        'q2' => $answers['q2'] ?? '',
        'q2_no_comment' => $answers['q2_no_comment'] ?? '',
        // pregunta 3
        'q3' => $answers['q3'] ?? '',
        'q3_no_comment' => $answers['q3_no_comment'] ?? '',
        // pregunta 4
        'q4' => $answers['q4'] ?? '',
        'q4_no_comment' => $answers['q4_no_comment'] ?? '',
        // pregunta 5
        'q5' => $answers['q5'] ?? '',
        'q5_no_comment' => $answers['q5_no_comment'] ?? '',
        // pregunta 6
        'q6' => $answers['q6'] ?? '',
        // pregunta 7
        'q7' => $answers['q7'] ?? '',
        'q7_no_comment' => $answers['q7_no_comment'] ?? '',

        // FODA
        'fortalezas' => $answers['fortalezas'] ?? '',
        'oportunidades' => $answers['oportunidades'] ?? '',
        'debilidades' => $answers['debilidades'] ?? '',
        'amenazas' => $answers['amenazas'] ?? '',
        // Autorizacion Datos
        'autorizado' => $answers['autorizado'] ?? ''
    ];
}

echo json_encode($rows, JSON_UNESCAPED_UNICODE);
exit;
