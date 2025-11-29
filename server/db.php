<?php
// server/db.php
// Nota: No seteamos Content-Type aquí; cada script lo setea según su necesidad

$DB_HOST = 'localhost';
$DB_NAME = 'coo_survey';
$DB_USER = 'root';
$DB_PASS = 'bbarroso01';
$DB_CHARSET = 'utf8mb4';

$dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=$DB_CHARSET";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
    // echo "Conexion exitosa";
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB connection failed: ' . $e->getMessage()]);
    exit;
}
?>