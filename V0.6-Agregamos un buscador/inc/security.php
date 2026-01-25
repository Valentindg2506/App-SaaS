<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Security Helper Module
 */

function secure_headers() {
    header("X-Frame-Options: DENY");
    header("X-XSS-Protection: 1; mode=block");
    header("X-Content-Type-Options: nosniff");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    // In production with HTTPS: header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function validate_table_name($table) {
    // Whitelist of allowed tables
    $allowed_tables = [
        'usuario_sistema',
        'cliente',
        'pedido',
        'factura',
        'personal',
        'configuracion',
        'servicio',
        'aviso',
        'pago',
        'registro_log',
        'prospectos'
    ];
    
    if (!in_array($table, $allowed_tables)) {
        die("Acceso denegado: Tabla no válida.");
    }
    return $table; // Return cleaned/validated table
}

function clean_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
?>
