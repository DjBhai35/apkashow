<?php
/**
 * ApkaShow - Core Application Functions & Security Helpers
 * Domain: apkashow.com
 */

require_once __DIR__ . '/db.php';

/**
 * Escape string for safe HTML output (XSS protection)
 */
function e($string) {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF Token
 */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Output CSRF Token as Hidden Form Field
 */
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

/**
 * Verify CSRF Token
 */
function verify_csrf_token($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate URL-friendly slug
 */
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'n-a-' . time() : $text;
}

/**
 * Fetch All Global Settings as Key-Value Array
 */
function get_all_settings() {
    static $settings = null;
    if ($settings !== null) {
        return $settings;
    }
    $settings = [];
    try {
        $db = getDB();
        $stmt = $db->query("SELECT `key_name`, `key_value` FROM `settings`");
        while ($row = $stmt->fetch()) {
            $settings[$row['key_name']] = $row['key_value'];
        }
    } catch (Exception $e) {
        $settings = [
            'site_name' => 'ApkaShow',
            'site_tagline' => 'Stream Premium Motivational, Billionaire & Cinematic Masterpieces',
            'whatsapp_number' => '+1234567890',
            'whatsapp_message' => 'Hello ApkaShow! I would like to inquire about movies.',
            'site_logo_text' => 'APKA<span class="text-gradient">SHOW</span>'
        ];
    }
    return $settings;
}

/**
 * Fetch a specific site setting
 */
function get_setting($key, $default = '') {
    $settings = get_all_settings();
    return $settings[$key] ?? $default;
}

/**
 * Update or Insert a Setting
 */
function set_setting($key, $value) {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO `settings` (`key_name`, `key_value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `key_value` = ?");
    return $stmt->execute([$key, $value, $value]);
}

/**
 * Build WhatsApp Chat Link from configured phone number
 */
function get_whatsapp_url() {
    $rawPhone = get_setting('whatsapp_number', '+1234567890');
    $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
    $defaultMessage = get_setting('whatsapp_message', 'Hello ApkaShow!');
    return 'https://api.whatsapp.com/send?phone=' . urlencode($cleanPhone) . '&text=' . urlencode($defaultMessage);
}

/**
 * Admin Authentication Check
 */
function is_admin_logged_in() {
    return !empty($_SESSION[ADMIN_SESSION_KEY]);
}

/**
 * Current Admin User Data
 */
function get_current_admin() {
    return $_SESSION[ADMIN_SESSION_KEY] ?? null;
}

/**
 * Require Admin Session or Redirect to Login
 */
function require_admin_auth() {
    if (!is_admin_logged_in()) {
        header("Location: " . BASE_URL . "/admin/login.php");
        exit();
    }
    
    // Check if first-time password change is required
    $user = get_current_admin();
    $currentPage = basename($_SERVER['PHP_SELF'] ?? '');
    if (!empty($user['force_password_change']) && $currentPage !== 'users.php' && $currentPage !== 'logout.php') {
        set_flash('warning', 'Security Alert: You must update your admin password before proceeding.');
        header("Location: " . BASE_URL . "/admin/users.php?force=1");
        exit();
    }
}

/**
 * Set Flash Message
 */
function set_flash($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and Clear Flash Message
 */
function get_flash() {
    if (!empty($_SESSION['flash_message'])) {
        $msg = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $msg;
    }
    return null;
}

/**
 * Format Views Count (e.g. 14.2K, 1.2M)
 */
function format_views($count) {
    if ($count >= 1000000) {
        return round($count / 1000000, 1) . 'M';
    } elseif ($count >= 1000) {
        return round($count / 1000, 1) . 'K';
    }
    return number_format((int)$count);
}

/**
 * Secure Image File Uploader
 */
function upload_image_file($fileArray, $targetFolder = 'movies', $maxBytes = 5242880) {
    if (empty($fileArray) || $fileArray['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'No file uploaded or upload error code: ' . ($fileArray['error'] ?? 'unknown')];
    }

    if ($fileArray['size'] > $maxBytes) {
        return ['success' => false, 'error' => 'File size exceeds maximum allowed limit (5MB).'];
    }

    $allowedMimes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($fileArray['tmp_name']);

    if (!array_key_exists($mime, $allowedMimes)) {
        return ['success' => false, 'error' => 'Invalid file format. Only JPG, PNG, WEBP, and GIF are allowed.'];
    }

    $extension = $allowedMimes[$mime];
    $fileName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
    $targetDir = SITE_ROOT . '/assets/uploads/' . trim($targetFolder, '/') . '/';

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $destPath = $targetDir . $fileName;
    if (move_uploaded_file($fileArray['tmp_name'], $destPath)) {
        $relativeUrl = 'assets/uploads/' . trim($targetFolder, '/') . '/' . $fileName;
        return ['success' => true, 'url' => $relativeUrl, 'fileName' => $fileName];
    }

    return ['success' => false, 'error' => 'Failed to move uploaded file to destination. Please check folder permissions.'];
}

/**
 * Resolve Image URL
 */
function resolve_image_url($url, $placeholder = 'poster') {
    if (empty($url)) {
        if ($placeholder === 'banner') {
            return 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=1600&q=80';
        }
        return 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=600&q=80';
    }
    if (preg_match('#^https?://#i', $url)) {
        return $url;
    }
    return BASE_URL . '/' . ltrim($url, '/');
}

/**
 * Build Canonical URL for production (https://apkashow.com/...)
 */
function canonical_url_for($path) {
    return CANONICAL_DOMAIN . '/' . ltrim($path, '/');
}
