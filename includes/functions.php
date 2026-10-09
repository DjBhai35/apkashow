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
        optimize_image_if_possible($destPath, $mime);
        $relativeUrl = 'assets/uploads/' . trim($targetFolder, '/') . '/' . $fileName;
        return ['success' => true, 'url' => $relativeUrl, 'fileName' => $fileName];
    }

    return ['success' => false, 'error' => 'Failed to move uploaded file to destination. Please check folder permissions.'];
}

/**
 * Optimize / Resize uploaded image if GD library is available
 */
function optimize_image_if_possible($filePath, $mime, $maxWidth = 1920) {
    if (!extension_loaded('gd') || !function_exists('getimagesize')) {
        return;
    }
    $info = @getimagesize($filePath);
    if (!$info) return;
    list($width, $height) = $info;
    if ($width <= $maxWidth) {
        return;
    }
    $newWidth = $maxWidth;
    $newHeight = (int)round(($height / $width) * $newWidth);

    $src = null;
    switch ($mime) {
        case 'image/jpeg':
            $src = @imagecreatefromjpeg($filePath);
            break;
        case 'image/png':
            $src = @imagecreatefrompng($filePath);
            break;
        case 'image/webp':
            if (function_exists('imagecreatefromwebp')) {
                $src = @imagecreatefromwebp($filePath);
            }
            break;
    }
    if (!$src) return;

    $dst = imagecreatetruecolor($newWidth, $newHeight);
    if ($mime === 'image/png' || $mime === 'image/webp') {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
    }
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    switch ($mime) {
        case 'image/jpeg':
            imagejpeg($dst, $filePath, 88);
            break;
        case 'image/png':
            imagepng($dst, $filePath, 8);
            break;
        case 'image/webp':
            imagewebp($dst, $filePath, 88);
            break;
    }
    imagedestroy($src);
    imagedestroy($dst);
}

/**
 * Secure Video File Uploader (MP4, WebM)
 * Supports common trailer video formats, 1-10 minutes, up to server limit
 */
function upload_video_file($fileArray, $targetFolder = 'trailers', $maxBytes = 104857600) {
    if (empty($fileArray) || $fileArray['error'] !== UPLOAD_ERR_OK) {
        $errorCode = $fileArray['error'] ?? UPLOAD_ERR_NO_FILE;
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errorMsg = 'Video file exceeds the server upload limit. Please check php.ini (upload_max_filesize and post_max_size).';
                break;
            case UPLOAD_ERR_PARTIAL:
                $errorMsg = 'Video file was only partially uploaded. Please try again.';
                break;
            case UPLOAD_ERR_NO_FILE:
                $errorMsg = 'No video file was uploaded.';
                break;
            default:
                $errorMsg = 'Video upload error (Code: ' . $errorCode . ').';
                break;
        }
        return ['success' => false, 'error' => $errorMsg];
    }

    if ($fileArray['size'] > $maxBytes) {
        return ['success' => false, 'error' => 'Video size exceeds maximum allowed limit (' . round($maxBytes / (1024 * 1024)) . 'MB).'];
    }

    $clientExt = strtolower(pathinfo($fileArray['name'], PATHINFO_EXTENSION));
    $allowedExts = ['mp4' => 'mp4', 'webm' => 'webm', 'm4v' => 'mp4', 'mov' => 'mp4'];

    if (!array_key_exists($clientExt, $allowedExts)) {
        return ['success' => false, 'error' => 'Invalid video format (.' . e($clientExt) . '). Only MP4 and WebM videos are supported.'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($fileArray['tmp_name']);
    $allowedMimes = [
        'video/mp4', 'video/webm', 'video/quicktime', 'video/x-m4v',
        'application/octet-stream'
    ];

    if (!in_array($mime, $allowedMimes)) {
        return ['success' => false, 'error' => 'Invalid video file type (' . e($mime) . '). Only MP4 and WebM are permitted.'];
    }

    $extension = $allowedExts[$clientExt];
    $fileName = 'trailer_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
    $targetDir = SITE_ROOT . '/assets/uploads/' . trim($targetFolder, '/') . '/';

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $destPath = $targetDir . $fileName;
    if (move_uploaded_file($fileArray['tmp_name'], $destPath)) {
        $relativeUrl = 'assets/uploads/' . trim($targetFolder, '/') . '/' . $fileName;
        return ['success' => true, 'url' => $relativeUrl, 'fileName' => $fileName];
    }

    return ['success' => false, 'error' => 'Failed to save trailer file. Please check folder permissions for assets/uploads/' . $targetFolder . '.'];
}

/**
 * Resolve Video URL (Handles local upload or remote URL)
 */
function resolve_video_url($url) {
    if (empty($url)) {
        return '';
    }
    if (preg_match('#^https?://#i', $url)) {
        return $url;
    }
    return BASE_URL . '/' . ltrim($url, '/');
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

/**
 * Automatically ensure new columns and tables exist without breaking existing databases
 */
function ensure_schema_updates() {
    static $checked = false;
    if ($checked) return;
    $checked = true;

    try {
        $db = getDB();
        // Check if trailer_file column exists in movies
        $stmt = $db->query("SHOW COLUMNS FROM `movies` LIKE 'trailer_file'");
        $col = $stmt->fetch();
        if (!$col) {
            $db->exec("ALTER TABLE `movies` ADD COLUMN `trailer_file` VARCHAR(255) NULL AFTER `trailer_url`");
        }

        // Ensure movie_images table exists
        $db->exec("CREATE TABLE IF NOT EXISTS `movie_images` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `movie_id` INT NOT NULL,
            `image_url` VARCHAR(255) NOT NULL,
            `caption` VARCHAR(255) NULL,
            `sort_order` INT NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_movie_id` (`movie_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    } catch (Exception $e) {
        // Continue silently if DB not connected yet
    }
}

// Run schema verification automatically on load
ensure_schema_updates();
