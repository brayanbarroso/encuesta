<?php
require_once __DIR__ . '/db.php';

// formato: csv (por defecto) o xls (HTML compatible con Excel)
$format = isset($_GET['format']) ? strtolower(trim($_GET['format'])) : 'csv';

if ($format === 'xls') {
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename=encuesta_respuestas_' . date('Ymd_His') . '.xls');
} else {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=encuesta_respuestas_' . date('Ymd_His') . '.csv');
}

$output = fopen('php://output', 'w');

// Encabezados amigables
$headers = [
    'N° Encuesta',
    'Fecha',

    '1. Conoce el portafolio de servicios?',
    
    '2. El portafolio de servicios, está acorde con sus necesidades?',
    'Comentario si respondió NO (P2)',

    '3.cree usted que las actividades realizadas, satisfacen las necesidades de los asociados',
    'Comentario si respondió NO (P3)',

    '4.¿Consulta usted las plataformas virtuales?',
    'Comentario si respondió NO (P4)',

    '5.¿Usaría usted oficina virtual?',
    'Comentario si respondió NO (P5)',

    '6.¿servicios que haz utilizado en la sede recreacional?',

    '7.Recomendaría usted los servicios de la Cooperativa',
    'Comentario si respondió NO (P7)',

    'Fortalezas (2 respuestas)',
    'Oportunidades (2 respuestas)',
    'Debilidades (2 respuestas)',
    'Amenazas (2 respuestas)',

    'Autorizó tratamiento de datos'
];

if ($format === 'csv') {
    // escribir BOM UTF-8 para compatibilidad con Excel
    fwrite($output, "\xEF\xBB\xBF");
    fputcsv($output, $headers);
} else {
    // iniciar tabla HTML para .xls
    echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
    echo "<table border=1><thead><tr>";
    foreach ($headers as $h) {
        echo '<th>' . htmlspecialchars($h, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</th>';
    }
    echo "</tr></thead><tbody>\n";
}

// Obtener respuestas
$stmt = $pdo->query("SELECT * FROM responses ORDER BY created_at ASC");

while ($row = $stmt->fetch()) {

    $answers = json_decode($row['data'], true);

    // Helper para convertir arrays a string
    $safe = fn($v) => is_array($v) ? implode(', ', array_filter($v)) : (string)($v ?? '');

    $csvRow = [
        $row['id'],
       
        $row['created_at'],

        $safe($answers['q1'] ?? ''),

        $safe($answers['q2'] ?? ''),
        $safe($answers['q2_no_comment'] ?? ''),

        $safe($answers['q3'] ?? ''),
        $safe($answers['q3_no_comment'] ?? ''),

        $safe($answers['q4'] ?? ''),
        $safe($answers['q4_no_comment'] ?? ''),

        $safe($answers['q5'] ?? ''),
        $safe($answers['q5_no_comment'] ?? ''),

        $safe($answers['q6'] ?? ''),

        $safe($answers['q7'] ?? ''),
        $safe($answers['q7_no_comment'] ?? ''),

        $safe($answers['fortalezas'] ?? ''),
        $safe($answers['oportunidades'] ?? ''),
        $safe($answers['debilidades'] ?? ''),
        $safe($answers['amenazas'] ?? ''),

        $safe($answers['autorizado'] ?? '')
    ];

    if ($format === 'csv') {
        fputcsv($output, $csvRow);
    } else {
        // para .xls (HTML), imprimimos filas de tabla
        $esc = fn($v) => htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        echo "<tr>";
        foreach ($csvRow as $cell) {
            echo "<td>" . $esc($cell) . "</td>";
        }
        echo "</tr>\n";
    }
}

// si se generó HTML para .xls, cerramos la tabla
if ($format === 'xls') {
    echo "</tbody></table>";
}

fclose($output);
exit;

