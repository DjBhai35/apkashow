<?php
/**
 * ApkaShow - Admin Authentication & Authorization
 * Session security, login verification, CSRF checking
 */
require_once __DIR__ . '/../includes/functions.php';

// Enforce admin login for any script including this file
require_admin_auth();

$adminUser = get_current_admin();
$adminCurrentPage = basename($_SERVER['PHP_SELF'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $adminTitle ?? 'ApkaShow Admin Studio'; ?></title>
    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- ApkaShow Custom Cinematic Style -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/cinematic.css">
    <style>
        body { background-color: #080b11; }
        .admin-sidebar { min-height: 100vh; background-color: #0e121b; border-right: 1px solid rgba(255,255,255,0.06); }
        .admin-topbar { background-color: #0e121b; border-bottom: 1px solid rgba(255,255,255,0.06); }
        .admin-link { color: #94a3b8; font-weight: 500; font-size: 0.92rem; padding: 0.7rem 1rem; border-radius: 8px; display: flex; align-items: center; gap: 10px; text-decoration: none; transition: all 0.2s; }
        .admin-link:hover, .admin-link.active { background: rgba(229, 9, 20, 0.15); color: #fff; }
        .admin-link.active { border-left: 3px solid #e50914; font-weight: 600; }
        .table-dark-custom { --bs-table-bg: #111622; --bs-table-border-color: rgba(255,255,255,0.07); }
    </style>
</head>
<body>

<div class="d-flex flex-column flex-lg-row">
    <!-- Admin Sidebar Navigation -->
    <div class="admin-sidebar p-3 d-flex flex-column" style="width: 270px; flex-shrink: 0;">
        <div class="d-flex align-items-center gap-2 mb-4 px-2 py-2 border-bottom border-secondary border-opacity-25">
            <span class="fs-4">🎬</span>
            <div class="brand-font fw-bold text-white fs-5">
                APKA<span class="text-danger">SHOW</span> <small class="text-muted fs-6">ADMIN</small>
            </div>
        </div>

        <nav class="d-flex flex-column gap-1 flex-grow-1">
            <a href="<?php echo BASE_URL; ?>/admin/index.php" class="admin-link <?php echo ($adminCurrentPage === 'index.php') ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2 text-danger"></i> Dashboard
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/movies.php" class="admin-link <?php echo ($adminCurrentPage === 'movies.php' || $adminCurrentPage === 'movie_edit.php') ? 'active' : ''; ?>">
                <i class="bi bi-film text-warning"></i> Movies Manager
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/categories.php" class="admin-link <?php echo ($adminCurrentPage === 'categories.php') ? 'active' : ''; ?>">
                <i class="bi bi-grid-fill text-info"></i> Categories
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/banners.php" class="admin-link <?php echo ($adminCurrentPage === 'banners.php') ? 'active' : ''; ?>">
                <i class="bi bi-images text-primary"></i> Featured Banners
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/ads.php" class="admin-link <?php echo ($adminCurrentPage === 'ads.php') ? 'active' : ''; ?>">
                <i class="bi bi-badge-ad text-danger"></i> Ad Placements & AdSense
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/settings.php" class="admin-link <?php echo ($adminCurrentPage === 'settings.php') ? 'active' : ''; ?>">
                <i class="bi bi-sliders text-success"></i> Site & WhatsApp Settings
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/messages.php" class="admin-link <?php echo ($adminCurrentPage === 'messages.php') ? 'active' : ''; ?>">
                <i class="bi bi-chat-square-dots text-light"></i> User Inquiries
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/users.php" class="admin-link <?php echo ($adminCurrentPage === 'users.php') ? 'active' : ''; ?>">
                <i class="bi bi-people text-secondary"></i> Admin Accounts
            </a>

            <div class="mt-4 pt-3 border-top border-secondary border-opacity-25">
                <a href="<?php echo BASE_URL; ?>/index.php" target="_blank" class="admin-link">
                    <i class="bi bi-box-arrow-up-right"></i> View Live Website
                </a>
                <a href="<?php echo BASE_URL; ?>/admin/logout.php" class="admin-link text-danger">
                    <i class="bi bi-power"></i> Log Out
                </a>
            </div>
        </nav>
    </div>

    <!-- Admin Main Content Area -->
    <div class="flex-grow-1 d-flex flex-column" style="min-width: 0;">
        <!-- Topbar -->
        <header class="admin-topbar p-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold text-white mb-0"><?php echo $pageHeading ?? 'Control Center'; ?></h5>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-sm-block">
                    <div class="text-white small fw-bold"><?php echo e($adminUser['full_name'] ?? 'Administrator'); ?></div>
                    <small class="text-muted"><?php echo e($adminUser['email'] ?? 'admin@apkashow.com'); ?></small>
                </div>
                <div class="bg-danger text-white rounded-circle p-2 px-3 fw-bold">
                    <i class="bi bi-person-fill"></i>
                </div>
            </div>
        </header>

        <!-- Flash Messages -->
        <div class="p-4 flex-grow-1">
            <?php $flash = get_flash(); if ($flash): ?>
                <div class="alert alert-<?php echo e($flash['type']); ?> alert-dismissible fade show" role="alert">
                    <?php echo e($flash['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
