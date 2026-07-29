<?php
error_reporting(E_ALL);
ini_set('display_errors', '0'); // Disable error display in production
ini_set('log_errors', '1');
ini_set('error_log', 'php-error.log');

// Session configuration
if (session_status() !== PHP_SESSION_ACTIVE) {
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params([
            'lifetime' => 3600,
            'path' => '/',
            'domain' => isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null,
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    } else {
        session_set_cookie_params(
            3600,
            '/',
            isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '',
            true,
            true
        );
    }
}
