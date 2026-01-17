<?php
/**
 * test-email-setup.php
 * 
 * Script para verificar que el sistema de envío de correos está configurado correctamente
 * 
 * Uso: Abre en tu navegador http://localhost/encuesta_prueba/test-email-setup.php
 * O en terminal: php test-email-setup.php
 */

session_start();

// Determinar si es AJAX o HTML
$isAjax = $_SERVER['REQUEST_METHOD'] === 'POST';

if (!$isAjax) {
    // Mostrar HTML
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Test Sistema de Correos</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { background: #f5f5f5; padding: 20px; }
            .container { max-width: 800px; background: white; border-radius: 8px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .test-item { padding: 15px; margin: 15px 0; border-left: 4px solid #ddd; background: #fafafa; }
            .test-item.success { border-left-color: #28a745; background: #f0fff4; }
            .test-item.error { border-left-color: #dc3545; background: #fff5f5; }
            .test-item.warning { border-left-color: #ffc107; background: #fffbf0; }
            .spinner-small { display: inline-block; width: 20px; height: 20px; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1 class="mb-4">🔧 Test Sistema de Envío de Correos</h1>
            
            <div id="results"></div>
            
            <hr class="my-4">
            
            <h4 class="mt-4">Enviar Correo de Prueba</h4>
            <form id="testEmailForm">
                <div class="mb-3">
                    <label class="form-label">Email destinatario:</label>
                    <input type="email" class="form-control" id="testEmail" placeholder="tu-email@example.com" required>
                </div>
                <button type="submit" class="btn btn-primary">Enviar Correo de Prueba</button>
            </form>
            
            <div id="sendResult" style="display: none; margin-top: 20px;"></div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', runTests);
            document.getElementById('testEmailForm').addEventListener('submit', handleTestEmail);

            async function runTests() {
                const results = document.getElementById('results');
                results.innerHTML = '<p>Ejecutando verificaciones...</p>';
                
                const tests = [
                    testPhpVersion(),
                    testFileExists('vendor/autoload.php'),
                    testFileExists('.env'),
                    testEnvLoaded(),
                    testMailerClass(),
                    testEnvVariables()
                ];

                const allResults = await Promise.all(tests);
                results.innerHTML = allResults.join('');
            }

            async function testPhpVersion() {
                const result = await fetch('test-email-setup.php?action=php-version');
                const data = await result.json();
                return formatTest('Versión PHP', data.status, data.message, data.details);
            }

            async function testFileExists(file) {
                const result = await fetch('test-email-setup.php?action=file-exists&file=' + file);
                const data = await result.json();
                return formatTest(file, data.status, data.message);
            }

            async function testEnvLoaded() {
                const result = await fetch('test-email-setup.php?action=env-loaded');
                const data = await result.json();
                return formatTest('Archivo .env', data.status, data.message);
            }

            async function testMailerClass() {
                const result = await fetch('test-email-setup.php?action=mailer-class');
                const data = await result.json();
                return formatTest('Clase MailSender', data.status, data.message);
            }

            async function testEnvVariables() {
                const result = await fetch('test-email-setup.php?action=env-variables');
                const data = await result.json();
                return formatTest('Variables SMTP', data.status, data.message, data.details);
            }

            function formatTest(name, status, message, details = '') {
                const className = status === 'success' ? 'success' : (status === 'warning' ? 'warning' : 'error');
                const icon = status === 'success' ? '✓' : (status === 'warning' ? '⚠' : '✗');
                
                let html = `<div class="test-item ${className}">
                    <strong>${icon} ${name}</strong><br>
                    ${message}`;
                
                if (details) {
                    html += `<br><small class="text-muted">${details}</small>`;
                }
                
                html += '</div>';
                return html;
            }

            async function handleTestEmail(e) {
                e.preventDefault();
                const email = document.getElementById('testEmail').value;
                const resultDiv = document.getElementById('sendResult');
                resultDiv.innerHTML = '<p>Enviando correo...</p>';
                
                try {
                    const response = await fetch('test-email-setup.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ email })
                    });
                    
                    const data = await response.json();
                    const className = data.success ? 'alert-success' : 'alert-danger';
                    resultDiv.innerHTML = `<div class="alert ${className}">${data.message}</div>`;
                    
                    if (data.success) {
                        document.getElementById('testEmailForm').reset();
                    }
                } catch (error) {
                    resultDiv.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
                }
            }
        </script>
    </body>
    </html>
    <?php
    exit;
}

// AJAX handlers
header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'php-version':
            echo json_encode([
                'status' => 'success',
                'message' => 'PHP está correctamente instalado',
                'details' => 'Versión: ' . phpversion()
            ]);
            break;

        case 'file-exists':
            $file = $_GET['file'] ?? '';
            $path = __DIR__ . '/' . ltrim($file, '/');
            $exists = file_exists($path);
            echo json_encode([
                'status' => $exists ? 'success' : 'error',
                'message' => $exists ? "✓ Archivo existe" : "✗ Archivo no encontrado: $path"
            ]);
            break;

        case 'env-loaded':
            require_once __DIR__ . '/server/bootstrap.php';
            $loaded = function_exists('loadEnv');
            echo json_encode([
                'status' => $loaded ? 'success' : 'error',
                'message' => $loaded ? "✓ Bootstrap loader disponible" : "✗ Bootstrap loader no disponible"
            ]);
            break;

        case 'mailer-class':
            try {
                require_once __DIR__ . '/server/bootstrap.php';
                require_once __DIR__ . '/server/MailSender.php';
                
                $reflection = new ReflectionClass('MailSender');
                echo json_encode([
                    'status' => 'success',
                    'message' => '✓ Clase MailSender disponible'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'status' => 'error',
                    'message' => '✗ Error: ' . $e->getMessage()
                ]);
            }
            break;

        case 'env-variables':
            require_once __DIR__ . '/server/bootstrap.php';
            loadEnv(__DIR__ . '/.env');
            
            $vars = ['MAIL_HOST', 'MAIL_PORT', 'MAIL_USERNAME', 'MAIL_FROM_ADDRESS', 'APP_URL'];
            $missing = [];
            
            foreach ($vars as $var) {
                if (empty($_ENV[$var])) {
                    $missing[] = $var;
                }
            }
            
            if (empty($missing)) {
                echo json_encode([
                    'status' => 'success',
                    'message' => '✓ Todas las variables SMTP están configuradas',
                    'details' => "Host: " . $_ENV['MAIL_HOST'] . " | Puerto: " . $_ENV['MAIL_PORT']
                ]);
            } else {
                echo json_encode([
                    'status' => 'warning',
                    'message' => '⚠ Variables faltantes: ' . implode(', ', $missing),
                    'details' => 'Edita el archivo .env y proporciona todas las variables SMTP'
                ]);
            }
            break;

        case 'send-test':
            $email = $_POST['email'] ?? '';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Email inválido']);
                break;
            }

            require_once __DIR__ . '/server/bootstrap.php';
            require_once __DIR__ . '/server/MailSender.php';
            loadEnv(__DIR__ . '/.env');

            try {
                $mailer = new MailSender();
                $resetLink = ($_ENV['APP_URL'] ?? 'http://localhost/encuesta_prueba') . 
                            "/public/forgot-password.html?token=TEST_TOKEN_12345";
                
                $mailer->sendPasswordReset($email, $resetLink, 'Test User');
                
                echo json_encode([
                    'success' => true,
                    'message' => '✓ Correo enviado correctamente a ' . $email
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => '✗ Error al enviar: ' . $e->getMessage()
                ]);
            }
            break;

        default:
            // POST sin action = enviar correo de prueba
            $data = json_decode(file_get_contents('php://input'), true);
            $email = $data['email'] ?? '';
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Email inválido']);
                exit;
            }

            require_once __DIR__ . '/server/bootstrap.php';
            require_once __DIR__ . '/server/MailSender.php';
            loadEnv(__DIR__ . '/.env');

            try {
                $mailer = new MailSender();
                $resetLink = ($_ENV['APP_URL'] ?? 'http://localhost/encuesta_prueba') . 
                            "/public/forgot-password.html?token=TEST_TOKEN_12345";
                
                $mailer->sendPasswordReset($email, $resetLink, 'Test User');
                
                echo json_encode([
                    'success' => true,
                    'message' => '✓ Correo de prueba enviado correctamente a: ' . htmlspecialchars($email)
                ]);
            } catch (Exception $e) {
                error_log('Test email error: ' . $e->getMessage());
                echo json_encode([
                    'success' => false,
                    'message' => '✗ Error al enviar correo: ' . $e->getMessage()
                ]);
            }
    }
} catch (Exception $e) {
    error_log('Test script error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}
?>
