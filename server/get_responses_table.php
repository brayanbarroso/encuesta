<?php
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

$stmt = $pdo->query("SELECT id, identificacion, data, created_at FROM responses ORDER BY created_at ASC");

$rows = [];

while ($row = $stmt->fetch()) {

    $answers = json_decode($row['data'], true);

    $rows[] = [
        'id' => $row['id'],
        'identificacion' => $row['identificacion'],
        'created_at' => $row['created_at'],

        // Preguntas cerradas
        'q1' => $answers['q1'] ?? '',
        'q1_no_comment' => $answers['q1_no_comment'] ?? '',

        'q2' => $answers['q2'] ?? '',
        'q2_no_comment' => $answers['q2_no_comment'] ?? '',

        'q3' => $answers['q3'] ?? '',

        'q4' => $answers['q4'] ?? '',
        'q4_no_comment' => $answers['q4_no_comment'] ?? '',

        'q5' => $answers['q5'] ?? '',
        'q5_no_comment' => $answers['q5_no_comment'] ?? '',

        'q6' => $answers['q6'] ?? '',
        'q6_no_comment' => $answers['q6_no_comment'] ?? '',

        // FODA
        'fortalezas' => $answers['fortalezas'] ?? '',
        'debilidades' => $answers['debilidades'] ?? '',
        'oportunidades' => $answers['oportunidades'] ?? '',
        'amenazas' => $answers['amenazas'] ?? '',
        'otros' => $answers['otros'] ?? '',

        'autorizado' => $answers['autorizado'] ?? ''
    ];
}

echo json_encode($rows, JSON_UNESCAPED_UNICODE);
exit;
