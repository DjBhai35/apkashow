<?php
/**
 * CinemaVault - Application Configuration Template
 * Copy this file to `config.php` and update your MySQL database credentials.
 * Compatible with Apache/cPanel/Hostinger/XAMPP/Laragon.
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
    error_reporting(E_ALL);
}

// Session Security Configuration
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    // Enable cookie_secure if running on HTTPS
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', 1);
    }
    session_start();
}

// Base URL Auto-detection
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$basePath = rtrim($scriptDir, '/admin');
$basePath = rtrim($basePath, '/api');
$basePath = rtrim($basePath, '/');

defined('BASE_URL') || define('BASE_URL', $protocol . $host . $basePath);
defined('SITE_ROOT') || define('SITE_ROOT', realpath(__DIR__ . '/..'));

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'cinemavault_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Default Admin Session Key
define('ADMIN_SESSION_KEY', 'cinemavault_admin_user');
