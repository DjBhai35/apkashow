<?php
/**
 * ApkaShow - One-Click Database Setup & Diagnostics Utility
 * Production Domain: apkashow.com
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['install_now'])) {
        try {
            $rootDsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=" . DB_CHARSET;
            $rootPdo = new PDO($rootDsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            
            // Create database if not exists
            $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            // Connect to target DB
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

            $schemaPath = __DIR__ . '/database/schema.sql';
            if (!file_exists($schemaPath)) {
                throw new Exception("schema.sql not found at {$schemaPath}");
            }

            $sql = file_get_contents($schemaPath);
            $pdo->exec($sql);

            $message = "Database schema and curated movies successfully created! Now please set your secure master administrator password below.";
            $isInstalled = true;
        } catch (Exception $e) {
            $error = "Installation failed: " . $e->getMessage();
        }
    } elseif (isset($_POST['setup_admin'])) {
        $adminUser = trim($_POST['admin_username'] ?? 'admin');
        $adminEmail = trim($_POST['admin_email'] ?? 'admin@apkashow.com');
        $adminPass = trim($_POST['admin_password'] ?? '');
        $adminPassConfirm = trim($_POST['admin_password_confirm'] ?? '');

        if (empty($adminUser) || empty($adminEmail) || empty($adminPass)) {
            $error = "All admin account fields are required.";
        } elseif ($adminPass !== $adminPassConfirm) {
            $error = "Passwords do not match.";
        } elseif (strlen($adminPass) < 8) {
            $error = "Admin password must be at least 8 characters for security.";
        } else {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

                $hash = password_hash($adminPass, PASSWORD_BCRYPT);

                // Insert or update administrator user #1
                $stmt = $pdo->prepare("INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `role`, `status`, `force_password_change`) 
                                       VALUES (1, ?, ?, ?, 'ApkaShow Super Administrator', 'admin', 1, 0)
                                       ON DUPLICATE KEY UPDATE `username` = VALUES(`username`), `email` = VALUES(`email`), `password` = VALUES(`password`), `force_password_change` = 0");
                $stmt->execute([$adminUser, $adminEmail, $hash]);

                $message = "Admin user '{$adminUser}' configured with secure Bcrypt password. You can now log in!";
                $isInstalled = true;
            } catch (Exception $e) {
                $error = "Failed to configure administrator: " . $e->getMessage();
            }
        }
    }
}

// Check if admin user exists
$hasAdmin = false;
if ($isInstalled) {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $hasAdmin = (bool)$pdo->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1")->fetch();
    } catch (Exception $e) {}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ApkaShow - Setup & Security Diagnostics</title>
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
                        <h3 class="fw-bold text-white mb-0">ApkaShow Setup & Security Assistant</h3>
                        <small class="text-muted">Production Database Importer & Secure Admin Provisioner &bull; apkashow.com</small>
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

                <?php if ($isInstalled && $hasAdmin): ?>
                    <div class="p-4 rounded-3 bg-dark border border-secondary border-opacity-25 mb-4">
                        <h5 class="text-white fw-bold mb-2 text-success"><i class="bi bi-check2-all me-2"></i>Installation & Admin Security Verified</h5>
                        <p class="text-muted small mb-3">Your database is active, populated with curated high-definition movies, categories, and protected with your custom administrator credentials.</p>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="index.php" class="btn btn-danger px-4">Visit ApkaShow Website</a>
                            <a href="admin/login.php" class="btn btn-outline-light px-4">Open Admin Studio</a>
                        </div>
                    </div>
                <?php elseif ($isInstalled && !$hasAdmin): ?>
                    <div class="p-4 rounded-3 bg-dark border border-warning border-opacity-25 mb-4">
                        <h5 class="text-white fw-bold mb-2 text-warning"><i class="bi bi-shield-lock me-2"></i>Create Master Administrator Password</h5>
                        <p class="text-secondary small mb-3">The database tables exist, but no master admin credentials have been assigned yet. Enter your secure admin credentials below:</p>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label text-light small fw-bold">Admin Username</label>
                                <input type="text" name="admin_username" class="form-control bg-dark border-secondary text-white" value="admin" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-light small fw-bold">Admin Email</label>
                                <input type="email" name="admin_email" class="form-control bg-dark border-secondary text-white" value="admin@apkashow.com" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-light small fw-bold">Secure Password (min 8 characters)</label>
                                <input type="password" name="admin_password" class="form-control bg-dark border-secondary text-white" placeholder="••••••••••••" required minlength="8">
                            </div>
                            <div class="mb-4">
                                <label class="form-label text-light small fw-bold">Confirm Password</label>
                                <input type="password" name="admin_password_confirm" class="form-control bg-dark border-secondary text-white" placeholder="••••••••••••" required minlength="8">
                            </div>
                            <button type="submit" name="setup_admin" class="btn btn-danger w-100 py-2 fw-bold">
                                Save Master Administrator & Complete Setup
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="mb-4">
                        <h6 class="text-white fw-bold">Current Environment Configuration:</h6>
                        <ul class="list-group list-group-flush bg-transparent small mb-4">
                            <li class="list-group-item bg-transparent text-light border-secondary border-opacity-25 d-flex justify-content-between">
                                <span class="text-muted">Target Host:</span>
                                <code><?php echo htmlspecialchars(DB_HOST); ?>:<?php echo htmlspecialchars(DB_PORT); ?></code>
                            </li>
                            <li class="list-group-item bg-transparent text-light border-secondary border-opacity-25 d-flex justify-content-between">
                                <span class="text-muted">Database Name:</span>
                                <code><?php echo htmlspecialchars(DB_NAME); ?></code>
                            </li>
                            <li class="list-group-item bg-transparent text-light border-secondary border-opacity-25 d-flex justify-content-between">
                                <span class="text-muted">Database User:</span>
                                <code><?php echo htmlspecialchars(DB_USER); ?></code>
                            </li>
                        </ul>

                        <form method="POST" action="">
                            <button type="submit" name="install_now" class="btn btn-danger btn-lg w-100 py-3 fw-bold">
                                <i class="bi bi-lightning-charge-fill me-2"></i> Initialize Database Schema & Seed Data
                            </button>
                        </form>
                    </div>
                <?php endif; ?>

                <div class="text-muted small border-top border-secondary border-opacity-25 pt-3">
                    <i class="bi bi-shield-check text-success me-1"></i> For maximum security in production, delete or restrict access to <code>install.php</code> after completing setup.
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
