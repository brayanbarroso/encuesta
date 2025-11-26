<?php
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

// Buffer output to catch accidental HTML/PHP warnings
ob_start();
try {
    // Get all responses
    $stmt = $pdo->query("SELECT data FROM responses ORDER BY created_at ASC");

    $yesno_questions = [];
    $foda = ['fortalezas' => [], 'oportunidades' => [], 'debilidades' => [], 'amenazas' => []];
    $services = []; // q6 counts
    $total = 0;

// normalizer for yes/no values
$normalize_yesno = function($v) {
    if ($v === null) return 'other';
    // if array, join values to a string safely
    if (is_array($v)) {
        $v = implode(', ', array_filter(array_map('strval', $v)));
    }
    $s = mb_strtolower(trim((string)$v));
    if ($s === '') return 'other';
    $yesValues = ['si','sí','s','yes','y','1','true','verdad'];
    $noValues = ['no','n','0','false'];
    if (in_array($s, $yesValues, true)) return 'yes';
    if (in_array($s, $noValues, true)) return 'no';
    return 'other';
};

// helper to collect tokens from FODA or services
$collect_tokens = function($val) {
    if ($val === null) return [];
    if (is_array($val)) {
        $tokens = [];
        foreach ($val as $v) {
            $t = trim((string)$v);
            if ($t !== '') $tokens[] = $t;
        }
        return $tokens;
    }
    // string: split by comma or semicolon or newline
    $s = trim((string)$val);
    if ($s === '') return [];
    $parts = preg_split('/[,;\n]+/', $s);
    $tokens = [];
    foreach ($parts as $p) {
        $p2 = trim($p);
        if ($p2 !== '') $tokens[] = $p2;
    }
    return $tokens;
};

while ($row = $stmt->fetch()) {
    $total++;
    $answers = json_decode($row['data'], true);
    if (!is_array($answers)) continue;

    foreach ($answers as $key => $val) {
        // handle yes/no type aggregation
        if (preg_match('/^q(\d)$/', $key)) {
            // treat q1..q7 as possible yes/no; accumulate counts for each
            if (!isset($yesno_questions[$key])) $yesno_questions[$key] = ['yes' => 0, 'no' => 0, 'other' => 0];
            $res = $normalize_yesno($val);
            $yesno_questions[$key][$res]++;
        }
    }

    // FODA
    foreach (['fortalezas','oportunidades','debilidades','amenazas'] as $k) {
        if (isset($answers[$k])) {
            $tokens = $collect_tokens($answers[$k]);
            foreach ($tokens as $t) {
                if ($t === '') continue;
                if (!isset($foda[$k][$t])) $foda[$k][$t] = 0;
                $foda[$k][$t]++;
            }
        }
    }

    // Services (q6)
    if (isset($answers['q6'])) {
        $tokens = $collect_tokens($answers['q6']);
        foreach ($tokens as $t) {
            if ($t === '') continue;
            if (!isset($services[$t])) $services[$t] = 0;
            $services[$t]++;
        }
    }
}

// prepare sorted FODA top lists (top 10)
$foda_top = [];
foreach ($foda as $k => $map) {
    arsort($map);
    $foda_top[$k] = array_slice($map, 0, 20, true);
}

// prepare top services
arsort($services);
$services_top = array_slice($services, 0, 20, true);

// Prepare yes/no percentages
$yesno_percent = [];
foreach ($yesno_questions as $q => $counts) {
    $sum = $counts['yes'] + $counts['no'] + $counts['other'];
    $yes = $counts['yes'];
    $no = $counts['no'];
    $other = $counts['other'];
    $yes_pct = $sum > 0 ? round($yes * 100 / $sum, 1) : 0;
    $no_pct = $sum > 0 ? round($no * 100 / $sum, 1) : 0;
    $other_pct = $sum > 0 ? round($other * 100 / $sum, 1) : 0;
    $yesno_percent[$q] = [
        'counts' => $counts,
        'percent' => ['yes' => $yes_pct, 'no' => $no_pct, 'other' => $other_pct],
        'total' => $sum
    ];
}

$response = [
    'total_responses' => $total,
    'yesno' => $yesno_percent,
    'foda' => $foda_top,
    'services' => $services_top
];

    // capture any accidental output (warnings/html)
    $extra = ob_get_clean();
    if ($extra !== '') {
        $response['_debug_output'] = $extra;
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;

} catch (\Throwable $e) {
    $extra = ob_get_clean();
    http_response_code(500);
    $err = ['error' => $e->getMessage()];
    if ($extra !== '') $err['_debug_output'] = $extra;
    echo json_encode($err, JSON_UNESCAPED_UNICODE);
    exit;
}
