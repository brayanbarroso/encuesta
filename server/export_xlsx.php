<?php
// server/export_xlsx.php

require_once __DIR__ . '/db.php';

// comprueba que la librería esté instalada
$autoload = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoload)) {
    http_response_code(500);
    echo "Composer autoload not found. Run: composer require phpoffice/phpspreadsheet";
    exit;
}

require $autoload;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\Cell;

// Obtener respuestas
$stmt = $pdo->query("SELECT * FROM responses ORDER BY created_at ASC");

// Encabezados
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

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Escribir encabezados
$col = 1;
foreach ($headers as $h) {
    $cell = Coordinate::stringFromColumnIndex($col) . "1";
    $sheet->setCellValue($cell, $h);
    $col++;
}

// Helper para convertir arrays a string
$safe = function ($v) {
    if (is_array($v)) return implode(', ', array_filter($v));
    if ($v === null) return '';
    return (string)$v;
};

$rowNum = 2;
while ($row = $stmt->fetch()) {
    $answers = json_decode($row['data'], true);

    $values = [
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

    $col = 1;
    foreach ($values as $v) {
        $cell = Coordinate::stringFromColumnIndex($col) . $rowNum;
        $sheet->setCellValue($cell, $v);
        $col++;
    }

    $rowNum++;
}

// Auto size columns
$highestCol = $sheet->getHighestColumn();
$highestColIndex = Coordinate::columnIndexFromString($highestCol);
for ($i = 1; $i <= $highestColIndex; $i++) {
    $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
}

// Escribir archivo en salida
$filename = 'encuesta_respuestas_' . date('Ymd_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
