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
$stmt = $pdo->query("SELECT r.id, r.identificacion, r.data, r.created_at, u.nombre FROM responses r LEFT JOIN users u ON u.identificacion = r.identificacion ORDER BY r.created_at ASC");

// Encabezados
$headers = [
    'N° Encuesta',
    'Fecha',
    'Identificación',
    'Nombre',

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
        $row['identificacion'],
        $row['nombre'] ?? '',

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

// Fijar anchos de columna (en caracteres) por índice de columna
$highestCol = $sheet->getHighestColumn();
$highestColIndex = Coordinate::columnIndexFromString($highestCol);

// Definir anchos preferidos por columna (1-based index)
$widths = [
    1 => 8,   // N° Encuesta
    2 => 15,  // Fecha
    3 => 18,  // Identificación

    4 => 20,  // q1
    5 => 20,  // q2
    6 => 20,  // q2_no_comment

    7 => 12,  // q3
    8 => 20,  // q3_no_comment

    9 => 12,  // q4
    10 => 20, // q4_no_comment

    11 => 12, // q5
    12 => 20, // q5_no_comment

    13 => 20, // q6

    14 => 12, // q7
    15 => 20, // q7_no_comment

    16 => 20, // fortalezas
    17 => 20, // oportunidades
    18 => 20, // debilidades
    19 => 20, // amenazas

    20 => 15, // autorizado

    21 => 15   // Nombre
];

for ($i = 1; $i <= $highestColIndex; $i++) {
    $w = isset($widths[$i]) ? $widths[$i] : 20;
    $sheet->getColumnDimensionByColumn($i)->setWidth($w);
}

// Escribir archivo en salida
$filename = 'encuesta_respuestas_' . date('Ymd_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
