<?php
require_once __DIR__ . '/db.php';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=encuesta_respuestas_' . date('Ymd_His') . '.csv');

$output = fopen('php://output', 'w');

// Encabezados amigables
$headers = [
    'ID de respuesta',
    'Fecha de envío',

    '1. ¿Conoce el portafolio de servicios?',
    
    '2. ¿El portafolio de servicios, está acorde con sus necesidades?',
    'Comentario si respondió NO (P2)',

    '3. Nivel de satisfacción con la atención y tiempos de respuesta',
    'Comentario si respondió NO (P3)',

    '4. ¿Consulta plataformas virtuales?',
    'Comentario si respondió NO (P4)',

    '5. ¿Usaría la oficina virtual?',
    'Comentario si respondió NO (P5)',

    '6. ¿Considera que los canales no oficiales son un problema?',

    'Fortalezas (2 respuestas)',
    'Debilidades (2 respuestas)',
    'Oportunidades (2 respuestas)',
    'Amenazas (2 respuestas)',
    'Otros comentarios',

    'Autorizó tratamiento de datos'
];

fputcsv($output, $headers);

// Obtener respuestas
$stmt = $pdo->query("SELECT * FROM responses ORDER BY created_at DESC");

while ($row = $stmt->fetch()) {

    $answers = json_decode($row['data'], true);

    $csvRow = [
        $row['id'],
       
        $row['created_at'],

        $answers['q1'] ?? '',

        $answers['q2'] ?? '',
        $answers['q2_no_comment'] ?? '',

        $answers['q3'] ?? '',
        $answers['q3_no_comment'] ?? '',

        $answers['q4'] ?? '',
        $answers['q4_no_comment'] ?? '',

        $answers['q5'] ?? '',
        $answers['q5_no_comment'] ?? '',

        $answers['q6'] ?? '',

        $answers['fortalezas'] ?? '',
        $answers['debilidades'] ?? '',
        $answers['oportunidades'] ?? '',
        $answers['amenazas'] ?? '',
        $answers['otros'] ?? '',

        $answers['autorizado'] ?? ''
    ];

    fputcsv($output, $csvRow);
}

fclose($output);
exit;

