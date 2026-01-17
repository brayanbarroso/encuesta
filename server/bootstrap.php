<?php
/**
 * Cargador de variables de entorno desde archivo .env
 * Coloca esto al inicio de tu aplicación (por ejemplo en index.php)
 */

function loadEnv($filePath = __DIR__ . '/../.env') {
    if (!file_exists($filePath)) {
        return;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Saltar comentarios
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Parsear línea KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remover comillas si existen
            $value = preg_replace('/^["\'](.+)["\']$/', '$1', $value);
            
            // Establecer en $_ENV
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// Uso: loadEnv();
?>
