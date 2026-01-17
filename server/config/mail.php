<?php
/**
 * Configuración de correo para envío de enlaces de restablecimiento de contraseña
 * 
 * Variables de entorno recomendadas:
 * MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD, MAIL_FROM_ADDRESS, MAIL_FROM_NAME
 */

return [
    'host' => $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com',
    'port' => $_ENV['MAIL_PORT'] ?? 587,
    'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
    'username' => $_ENV['MAIL_USERNAME'] ?? 'tu-email@gmail.com',
    'password' => $_ENV['MAIL_PASSWORD'] ?? 'tu-contraseña-app',
    'from' => [
        'address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@encuesta.com',
        'name' => $_ENV['MAIL_FROM_NAME'] ?? 'Sistema de Encuesta'
    ],
    'app_url' => $_ENV['APP_URL'] ?? 'http://localhost/encuesta_prueba'
];
?>
