<?php
/**
 * CinemaVault - One-Click Database Setup & Diagnostics Utility
 * Allows shared hosting / cPanel users to easily test or initialize their database.
 */
require_once __DIR__ . '/includes/config.php';

$message = '';
$error = '';
$isInstalled = false;

// Attempt checking if database already has tables
try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $check = $pdo->query("SHOW TABLES LIKE 'movies'")->fetch();
    if ($check) {
        $isInstalled = true;
    }
} catch (Exception $e) {
    // Database may not exist yet or wrong credentials
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['install_now'])) {
    try {
        // 1. Connect to MySQL server without DB name first in case DB needs creation
        $rootDsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=" . DB_CHARSET;
        $rootPdo = new PDO($rootDsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        
        // Create database if not exists
        $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        // 2. Connect to the target database
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

        // 3. Read and execute database/schema.sql
        $schemaPath = __DIR__ . '/database/schema.sql';
        if (!file_exists($schemaPath)) {
            throw new Exception("schema.sql not found at {$schemaPath}");
        }

        $sql = file_get_contents($schemaPath);
        // Execute multi-query statements safely
        $pdo->exec($sql);

        $message = "Database `" . DB_NAME . "` and all initial seed records (categories, curated movies, banners, and default admin) were successfully installed!";
        $isInstalled = true;
    } catch (Exception $e) {
        $error = "Installation failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CinemaVault - One-Click Database Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #07090e; color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; display: flex; align-items: center; min-height: 100vh; }
        .setup-card { background: #0e121b; border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 18px; box-shadow: 0 25px 60px rgba(0, 0, 0, 0.9); }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="setup-card p-4 p-md-5">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <span class="fs-1">🎬</span>
                    <div>
                        <h3 class="fw-bold text-white mb-0">CinemaVault Setup Assistant</h3>
                        <small class="text-muted">Automated schema importer & diagnostic tool</small>
                    </div>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-success bg-success bg-opacity-10 border-success text-success p-3 rounded-3 mb-4">
                        <i class="bi bi-check-circle-fill me-2 fs-5"></i> <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger bg-danger bg-opacity-10 border-danger text-danger p-3 rounded-3 mb-4">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($isInstalled && empty($error)): ?>
                    <div class="p-4 rounded-3 bg-dark border border-secondary border-opacity-25 mb-4">
                        <h5 class="text-white fw-bold mb-2 text-success"><i class="bi bi-check2-all me-2"></i>Installation Verified</h5>
                        <p class="text-muted small mb-3">Your database is active and populated with curated high-definition movies, categories, and banners.</p>
                        <div class="small text-secondary mb-3">
                            <div><strong>Default Admin Username:</strong> <code>admin</code></div>
                            <div><strong>Default Admin Password:</strong> <code>admin123</code></div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="index.php" class="btn btn-danger px-4">Visit Movie Website</a>
                            <a href="admin/login.php" class="btn btn-outline-light px-4">Open Admin Studio</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="mb-4">
                        <h6 class="text-white fw-bold">Current Environment Configuration:</h6>
                        <ul class="list-group list-group-flush bg-transparent small mb-4">
                            <li class="list-group-item bg-transparent text-light border-secondary border-opacity-25 d-flex justify-content-between">
                                <span class="text-muted">Database Host:</span>
                                <code><?php echo htmlspecialchars(DB_HOST); ?>:<?php echo htmlspecialchars(DB_PORT); ?></code>
                            </li>
                            <li class="list-group-item bg-transparent text-light border-secondary border-opacity-25 d-flex justify-content-between">
                                <span class="text-muted">Database Target Name:</span>
                                <code><?php echo htmlspecialchars(DB_NAME); ?></code>
                            </li>
                            <li class="list-group-item bg-transparent text-light border-secondary border-opacity-25 d-flex justify-content-between">
                                <span class="text-muted">Database User:</span>
                                <code><?php echo htmlspecialchars(DB_USER); ?></code>
                            </li>
                        </ul>

                        <form method="POST" action="">
                            <button type="submit" name="install_now" class="btn btn-danger btn-lg w-100 py-3 fw-bold">
                                <i class="bi bi-lightning-charge-fill me-2"></i> Execute One-Click Installation & Import Seed Data
                            </button>
                        </form>
                    </div>
                <?php endif; ?>

                <div class="text-muted small border-top border-secondary border-opacity-25 pt-3">
                    <i class="bi bi-info-circle me-1"></i> You can also import <code>database/schema.sql</code> manually via phpMyAdmin anytime.
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
