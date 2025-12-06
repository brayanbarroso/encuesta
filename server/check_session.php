<?php
/**
 * server/check_session.php
 * Script para validar sesión. Si no está autenticado, redirige a login.
 */
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // No autenticado
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

// Verificar timeout (inactividad): 5 minutos
$timeout = 5 * 60; // 5 minutos en segundos
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > $timeout) {
    session_destroy();
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(401);
    echo json_encode(['error' => 'Sesión expirada']);
    exit;
}

// Actualizar login_time para extender sesión
$_SESSION['login_time'] = time();
