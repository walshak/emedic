<?php
error_reporting(E_ALL);
ini_set('display_errors', '1'); // Disable error display in production
ini_set('log_errors', '1');
ini_set('error_log', 'php-error.log');

// Session configuration
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 3600, // 1 hour
        'path' => '/',
        'domain' => isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null,
        'secure' => true, // Enable in production (HTTPS)
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
}
