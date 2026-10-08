<?php
/**
 * ApkaShow - Application Configuration Template
 * Copy this file to `config.php` and set your MySQL database credentials.
 * Production Domain: apkashow.com
 * Compatible with Apache/cPanel/Hostinger/LiteSpeed/XAMPP/Laragon.
 */

// Define Application Environment ('development' or 'production')
defined('ENVIRONMENT') || define('ENVIRONMENT', 'production');

if (ENVIRONMENT === 'production') {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
} else {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL & ~E_NOTICE);
}

// Session Security Configuration
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', 1);
    }
    session_start();
}

// Production Canonical Domain and Base URL Auto-detection
$productionDomain = 'https://apkashow.com';
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'apkashow.com';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$basePath = preg_replace('#/(admin|api|includes)$#', '', $scriptDir);
$basePath = rtrim($basePath, '/');

defined('CANONICAL_DOMAIN') || define('CANONICAL_DOMAIN', $productionDomain);
defined('BASE_URL') || define('BASE_URL', ($host === 'apkashow.com' || $host === 'www.apkashow.com') ? $productionDomain : ($protocol . $host . $basePath));
defined('SITE_ROOT') || define('SITE_ROOT', realpath(__DIR__ . '/..'));

// Database Credentials (Set via environment variables or define here)
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'apkashow_db');
define('DB_USER', getenv('DB_USER') ?: 'apkashow_user');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// Admin Session Identifier
define('ADMIN_SESSION_KEY', 'apkashow_admin_session');
